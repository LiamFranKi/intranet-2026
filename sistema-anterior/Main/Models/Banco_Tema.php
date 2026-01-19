<?php
class Banco_Tema extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'banco_temas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Curso',
			'class_name' => 'Curso',
		),
		array(
			'Nivel',
			'class_name' => 'Nivel',
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
