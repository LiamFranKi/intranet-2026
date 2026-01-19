<?php
class Boleta_Concepto extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'boletas_conceptos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'categoria',
			'class_name' => 'Boleta_Categoria',
			'foreign_key' => 'categoria_id',
		),
		array(
			'subcategoria',
			'class_name' => 'Boleta_Subcategoria',
			'foreign_key' => 'subcategoria_id',
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

	function controlarStock(){
		return $this->controlar_stock == 'SI';
	}

	function getLastCosto(){
		$detalle = Boleta_Ingreso_Detalle::find(array(
			'conditions' => 'concepto_id = "'.$this->id.'" ORDER BY id DESC',
			'limit' => 1
		));
		return $detalle;
	}

	function getStockInicial($colegio_id, $anio = null){

		$ventas = Boleta_Detalle::find(array(
			'select' => 'SUM(cantidad) As total',
			'conditions' => 'boletas.colegio_id = "'.$colegio_id.'" AND boletas_detalles.concepto_id="'.$this->id.'" AND boletas.estado = "ACTIVO"',
			'joins' => array('boleta')
		));

		$ingresos = Boleta_Ingreso_Detalle::find(array(
			'select' => 'SUM(cantidad) As total',
			'conditions' => 'boletas_ingresos.colegio_id = "'.$colegio_id.'" AND boletas_ingresos_detalles.concepto_id="'.$this->id.'" AND boletas_ingresos.estado = "ACTIVO"',
			'joins' => array('ingreso')
		));

		$stockInicial = $this->stock - $ingresos->total + $ventas->total;

		if(!is_null($anio)){
			$ventas = Boleta_Detalle::find(array(
				'select' => 'SUM(cantidad) As total',
				'conditions' => 'boletas.colegio_id = "'.$colegio_id.'" AND boletas_detalles.concepto_id="'.$this->id.'" AND boletas.estado = "ACTIVO" AND YEAR(fecha) < "'.$anio.'"',
				'joins' => array('boleta')
			));

			$ingresos = Boleta_Ingreso_Detalle::find(array(
				'select' => 'SUM(cantidad) As total',
				'conditions' => 'boletas_ingresos.colegio_id = "'.$colegio_id.'" AND boletas_ingresos_detalles.concepto_id="'.$this->id.'" AND boletas_ingresos.estado = "ACTIVO" AND YEAR(fecha) < "'.$anio.'"',
				'joins' => array('ingreso')
			));

			$stockInicial = $stockInicial + $ingresos->total - $ventas->total;
		}

		return $stockInicial;
	}

	function getStockUntilDate($colegio_id, $fecha){

		
		$ventas = Boleta_Detalle::find(array(
			'select' => 'SUM(cantidad) As total',
			'conditions' => 'boletas.colegio_id = "'.$colegio_id.'" AND boletas_detalles.concepto_id="'.$this->id.'" AND boletas.estado = "ACTIVO" AND fecha BETWEEN DATE("'.$fecha.'") AND DATE(NOW())',
			'joins' => array('boleta')
		));

		$ingresos = Boleta_Ingreso_Detalle::find(array(
			'select' => 'SUM(cantidad) As total',
			'conditions' => 'boletas_ingresos.colegio_id = "'.$colegio_id.'" AND boletas_ingresos_detalles.concepto_id="'.$this->id.'" AND boletas_ingresos.estado = "ACTIVO" AND fecha BETWEEN DATE("'.$fecha.'") AND DATE(NOW())',
			'joins' => array('ingreso')
		));

		$stockInicial = $this->stock - $ingresos->total + $ventas->total;
		

		return $stockInicial;
	}

	function getPrecioInicial($colegio_id, $anio = null){
		$precioInicial = $this->precio_inicial;

		if(!is_null($anio)){

			$stockInicial = $this->getStockInicial($colegio_id);

			$prevStock = $stockInicial;
			$prevPrecio = $precioInicial;
			$prevTotal = $stockInicial * $precioInicial;
			//$xLog = '';

			$ventas = Boleta_Detalle::all(array(
				'conditions' => 'boletas.colegio_id="'.$colegio_id.'" AND concepto_id = "'.$this->id.'" AND YEAR(boletas.fecha) < "'.$anio.'" AND boletas.estado = "ACTIVO"',
				'joins' => array('boleta'),
				'order' => 'boletas.fecha ASC, boletas.id ASC'
			));

			$ingresos = Boleta_Ingreso_Detalle::all(array(
				'conditions' => 'boletas_ingresos.colegio_id="'.$colegio_id.'" AND concepto_id = "'.$this->id.'" AND YEAR(boletas_ingresos.fecha) < "'.$anio.'" AND boletas_ingresos.estado = "ACTIVO"',
				'joins' => array('ingreso'),
				'order' => 'boletas_ingresos.fecha ASC, boletas_ingresos.id ASC'
			));

			$registros = array();

			foreach($ventas As $venta) $registros[] = $venta;
			foreach($ingresos As $ingreso) $registros[] = $ingreso;
			

			usort($registros, function($a, $b){
				$nro1 = $a instanceOf Boleta_Detalle ? strtotime($a->boleta->fecha) : strtotime($a->ingreso->fecha);
				$nro2 = $b instanceOf Boleta_Detalle ? strtotime($b->boleta->fecha) : strtotime($b->ingreso->fecha);
				return strcmp($nro1, $nro2);
			});

			foreach($registros As $registro){

				//$xLog .= "\n".$prevTotal;

				if($registro instanceOf Boleta_Detalle){
					$venta = $registro;

					$prevStock = $prevStock - $venta->cantidad;
					$prevPrecio = ($prevTotal + ($venta->cantidad * $venta->precio)) / $prevStock;
					$prevTotal = $prevPrecio * $prevStock;


				}

				if($registro instanceOf Boleta_Ingreso_Detalle){
					$ingreso = $registro;

					$prevStock = $prevStock + $ingreso->cantidad;
					$prevPrecio = $prevStock > 0 ? ($prevTotal + ($ingreso->cantidad * $ingreso->precio)) / $prevStock : 0;
					$prevTotal = $prevPrecio * $prevStock;
				}

				
			}



			$precioInicial = $prevPrecio;
		}

		return $precioInicial;
	}
}
