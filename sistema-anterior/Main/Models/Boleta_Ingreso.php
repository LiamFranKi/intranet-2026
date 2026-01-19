<?php
class Boleta_Ingreso extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'boletas_ingresos';
	static $connection = '';
	
	static $belongs_to = array();
	static $has_many = array(
		array(
			'detalles',
			'class_name' => 'Boleta_Ingreso_Detalle',
			'foreign_key' => 'boleta_ingreso_id',
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

	function addStocks(){
		foreach($this->detalles As $detalle){
			$detalle->addStock();
		}
	}

	function reduceStocks(){
		foreach($this->detalles As $detalle){
			$detalle->reduceStock();
		}
	}

	function before_destroy(){
		$this->reduceStocks();
	}
}
