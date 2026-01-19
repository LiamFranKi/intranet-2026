<?php
class Costo extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'costos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'colegio',
			'class_name' => 'Colegio',
		),
	);
	static $has_many = array();
	static $has_one = array();
	
	static $validates_presence_of = array(
		array(
			'colegio_id',
		),
		array(
			'descripcion',
		),
		array(
			'matricula',
		),
		array(
			'pension',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
	
	function getMontos(){
		return $this->descripcion.' - > Matrícula: '.$this->colegio->moneda.' '.number_format($this->matricula, 2).' - Pensión '.$this->colegio->moneda.' '.number_format($this->pension, 2);
	}

	function getMontosCorto(){
		return 'Matrícula: '.$this->colegio->moneda.' '.number_format($this->matricula, 2).' - Pensión '.$this->colegio->moneda.' '.number_format($this->pension, 2);
	}
}
