<?php
class Impresion extends ActiveRecord\Model{

	static $pk = 'id';
	static $table_name = 'impresiones';
	static $connection = '';

	static $belongs_to = array(
		array(
			'pago',
			'class_name' => 'Pago',
		),
	);
	static $has_many = array();
	static $has_one = array();

	static $validates_presence_of = Array();
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = Array();
	static $validates_uniqueness_of = Array();

	function getTipoDocumento(){
		if($this->tipo_documento == 'BOLETA') return 'Boleta de Venta';
		return 'Nota de Débito';
	}

	function getTipoPago(){
		return ucwords(strtolower($this->tipo));
	}

	function getSerie($ceros = 3){
		return str_pad($this->serie, $ceros, '0', STR_PAD_LEFT);
	}

	function getNumero($ceros = 8){
		return str_pad($this->numero, $ceros, '0', STR_PAD_LEFT);
	}

	function getSerieNumero(){
		return $this->getSerie().'-'.$this->getNumero();
	}

	function getSerieNumeroPrefijo(){
		$prefijo = $this->pago->matricula->grupo->sede->prefijo_boleta;
		$serie = $prefijo == 'B' ? $prefijo.$this->getSerie() : $prefijo.$this->getSerie(2);
		return $serie.'-'.$this->getNumero();
	}

	function getSerieIntNumero(){
		if($this->serie == 1 && $this->tipo_documento == "NOTA") return $this->getIntSerieIntNumero();

		return $this->getSerie().'-'.$this->numero;
	}

	function getTipo(){
		if($this->serie == 3) return '';


		if($this->tipo_documento == 'BOLETA'){
			return 'B';
		}
		if($this->tipo_documento == "NOTA"){
			return 'BND';
		}

	}

	function getTipoSerieIntNumero(){
		$mes = date('n', strtotime($this->fecha_impresion));
		$anio = date('Y', strtotime($this->fecha_impresion));
		
		//if($anio < 2018) return 'BV'.$this->getSerieIntNumero();

		if(($anio < 2018 || ($anio == 2018 && $mes <= 8))){
			return 'BV'.$this->getSerieIntNumero();
		}

		return 'B'.$this->getSerieIntNumero();
	}


	function getIntSerieIntNumero(){
		return $this->serie.'-'.$this->numero;
	}

	function getFechaCancelado(){
		$colegio = Colegio::find($this->colegio_id == 0 ? 1 : $this->colegio_id);
		if($this->pago->tipo == 0 || ($this->pago->tipo == 2 && $this->pago->incluye_agenda == 'SI')){
			$diaVencimiento = $colegio->getVencimientoPension(-1);
			if(!isset($diaVencimiento)){
				//$diaVencimiento = date('t', strtotime($this->COLEGIO->anio_activo.'-'.($i + 2)));
				$fechaVencimiento = '31-'.($colegio->inicio_pensiones + 1).'-'.$this->pago->matricula->grupo->anio;
			}else{
				$fechaVencimiento = $diaVencimiento.'-'.$this->pago->matricula->grupo->anio;
			}
		}

		if($this->pago->tipo == 1){
			$diaVencimiento = $colegio->getVencimientoPension($this->pago->nro_pago);
			if(!isset($diaVencimiento)){
				$diaVencimiento = date('t', strtotime($colegio->anio_activo.'-'.($this->pago->nro_pago + 2)));
			}
			$fechaVencimiento = $this->pago->matricula->grupo->anio.'-'.($this->pago->nro_pago + 2).'-'.$diaVencimiento;
		}

		if($this->pago->tipo == 2 || $this->pago->tipo == 3){
			$fechaVencimiento = $this->pago->fecha_cancelado;
		}


		return date('Y-m-d', strtotime($fechaVencimiento));

	}

	function updateRC(){
		if(empty($this->fecha_anulado) || $this->fecha_anulado == '0000-00-00'){
			$this->fecha_anulado = date('Y-m-d');
		}

		if($this->numero_anulado == 0){
			$anulados = Impresion::count([
				'conditions' => 'fecha_anulado = "'.$this->fecha_anulado.'" AND estado = "ANULADO"'
			]);
			
			$this->numero_anulado = $anulados + 1;
		}

		return $this->save();
	}

}
