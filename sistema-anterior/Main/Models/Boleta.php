<?php
class Boleta extends TraitConstants{

	static $pk = 'id';
	static $table_name = 'boletas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'sede',
			'class_name' => 'Sede',
			'foreign_key' => 'sede_id',
		),
	);
	static $has_many = array(
		array(
			'detalles',
			'class_name' => 'Boleta_Detalle',
			'foreign_key' => 'boleta_id',
		),
	);
	static $has_one = array();
	
	static $validates_presence_of = Array();
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = Array();
	static $validates_uniqueness_of = Array();

	function getDecimal(){
		$decimal = (string) round(($this->getMontoTotal() - intval($this->getMontoTotal())) * 100, 0);
		return $decimal;
	}

	function getLetras(){
		$letras = strtoupper(num2letras(intval($this->getMontoTotal()))).' CON '.(str_pad($this->getDecimal(), 2, 0, STR_PAD_LEFT)).'/100 SOLES';
		return $letras;
	}

	function getCurrentSerie($ceros = 3){
		return str_pad($this->serie, $ceros, '0', STR_PAD_LEFT);
	}

	function getCurrentNumero($ceros = 8){
		return str_pad($this->numero, $ceros, '0', STR_PAD_LEFT);
	}

	function after_destroy(){
		Boleta_Detalle::table()->delete(array('boleta_id' => $this->id));
	}

	function getNroBoleta(){
		return $this->getCurrentSerie().'-'.$this->getCurrentNumero();
	}
	
	function getMontoTotalDetalles(){
		$total = Boleta_Detalle::find(array(
			'select' => 'SUM(cantidad*precio) As total',
			'conditions' => 'boleta_id="'.$this->id.'"'
		));
		return (float) $total->total;
	}

	function getMontoTotal(){
		
		$monto = $this->getMontoTotalDetalles();
		if(!$this->isServicio()) $monto += $this->getComisionPagoTarjeta();
		return $monto;
	}

    function getTotalPayments(){
        $payments = InvoicePayment::find([
            'select' => 'sum(amount) as total',
            'conditions' => ['invoice_id = ?', $this->id]
        ]);

        return $payments->total;
    }

    function getTotalPaymentsRemain(){
        return $this->getMontoTotal() - $this->getTotalPayments();
    }

	function getComisionDetalle(){
		if($this->isServicio() || $this->tipo_pago == 'EFECTIVO') return 0;
		if(count($this->detalles) <= 0) return 0;

		return round($this->getComisionPagoTarjeta() / count($this->detalles), 2);
	}

	function getComisionPagoTarjeta(){
		if($this->tipo_pago == 'EFECTIVO') return 0;
		
		//if($this->tipo_pago)
		return ($this->getMontoTotalDetalles() * $this->comision_tarjeta / 100);
	}

	function resetStocks(){
		foreach($this->detalles As $detalle){
			$detalle->resetStock();
		}
	}

	function reduceStocks(){
		foreach($this->detalles As $detalle){
			$detalle->reduceStock();
		}
	}

	function before_destroy(){
		$this->resetStocks();
	}

	function getSubcategoria(){
		$subcategoria = Boleta_Subcategoria::find(array(
			'select' => 'boletas.id, boletas_subcategorias.nombre, concar_igv, concar_cuenta, starsoft_cuenta, COUNT(boletas_detalles.id) AS total_detalles',
			'joins' => '
				INNER JOIN boletas_conceptos ON boletas_conceptos.subcategoria_id = boletas_subcategorias.id
				INNER JOIN boletas_detalles ON boletas_detalles.concepto_id = boletas_conceptos.id
				INNER JOIN boletas ON boletas_detalles.boleta_id = boletas.id
			',
			'conditions' => 'boletas.id = "'.$this->id.'"',
			'order' => 'total_detalles DESC',
			'group' => 'boletas_subcategorias.id'
		));
		//print_r($subcategoria);
		return $subcategoria;
	}

	function isServicio(){
		$i = 0;
		foreach($this->detalles As $detalle){
			if($detalle->concepto->categoria_id == 1) ++$i;
		}
		return ($i > 0);
	}

	function isImpreso(){
		return $this->impreso == 'SI';
	}

	function getMontoGravado(){
		return round($this->getMontoTotal() / 1.18, 2);
		//return round($this->getMontoTotalDetalles() - $this->getIGV(), 2);
	}

	function getIGV(){
		return round($this->getMontoTotal() - $this->getMontoGravado(), 2);
		//return round($this->getMontoTotalDetalles() * 0.18, 2);
	}
	

	function updateRC(){
		if(empty($this->fecha_anulado) || $this->fecha_anulado == '0000-00-00'){
			$this->fecha_anulado = date('Y-m-d');
		}

		if($this->numero_anulado == 0){
			$anulados = Boleta::count([
				'conditions' => 'fecha_anulado = "'.$this->fecha_anulado.'" AND estado = "ANULADO"'
			]);
			$this->numero_anulado = $anulados + 1;
		}

		return $this->save();
	}

	function getTipoDocumentoFacturacion(){
		return $this->TIPOS_DOCUMENTO_FACTURACION[$this->tipo_documento];
	}
}
