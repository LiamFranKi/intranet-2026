<?php
class Caja_Categoria extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'caja_categorias';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'colegio',
			'class_name' => 'Colegio',
		),
	);
	static $has_many = array(
		array(
			'conceptos',
			'class_name' => 'Caja_Concepto',
			'foreign_key' => 'categoria_id',
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

	function getTotalIngresos($mes, $anio){
		//echo $mes.' - '. $anio;
		return $this->getCajaRegistros($mes, $anio, 'INGRESO');
	}
	function getTotalEgresos($mes, $anio){
		return $this->getCajaRegistros($mes, $anio, 'EGRESO');
	}

	function getCajaRegistros($mes, $anio, $tipo){

		$registros = Caja_Registro::find(array(
			'select' => 'SUM(monto_total) As total',
			'conditions' => 'colegio_id="'.$this->colegio_id.'" AND categoria_id="'.$this->id.'" AND MONTH(fecha) = "'.$mes.'" AND YEAR(fecha) = "'.$anio.'" AND tipo="'.$tipo.'"' 
		));

		//print_r($registros->attributes);
		
		return (float) $registros->total;
	}
}
