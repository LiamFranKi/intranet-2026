<?php
class Boleta_Ingreso_Detalle extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'boletas_ingresos_detalles';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'categoria',
			'class_name' => 'Boleta_Categoria',
			'foreign_key' => 'categoria_id',
		),
		array(
			'concepto',
			'class_name' => 'Boleta_Concepto',
			'foreign_key' => 'concepto_id',
		),
		array(
			'ingreso',
			'class_name' => 'Boleta_Ingreso',
			'foreign_key' => 'boleta_ingreso_id',
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

	public function addStock(){
		if($this->concepto->controlarStock()){
			$this->concepto->stock += $this->cantidad;
			$this->concepto->save();
		}
	}

	public function reduceStock(){
		if(!$this->is_new_record()){
			if($this->concepto->controlarStock()){
				$this->concepto->stock -= $this->cantidad;
				$this->concepto->save(); 
			}
		}
	}

	public function after_save(){
		$this->addStock();
	}
}
