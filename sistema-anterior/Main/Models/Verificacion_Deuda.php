<?php
class Verificacion_Deuda extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'verificaciones_deudas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Matricula',
			'class_name' => 'Matricula',
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
