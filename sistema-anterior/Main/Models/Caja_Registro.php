<?php
class Caja_Registro extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'caja_registros';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'colegio',
			'class_name' => 'Colegio',
		),
		array(
			'categoria',
			'class_name' => 'Caja_Categoria',
			'foreign_key' => 'categoria_id',
		),
		array(
			'concepto',
			'class_name' => 'Caja_Concepto',
			'foreign_key' => 'concepto_id',
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
}
