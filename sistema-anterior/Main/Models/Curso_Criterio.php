<?php
class Curso_Criterio extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'cursos_criterios';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'curso',
			'class_name' => 'Curso',
		),
	);
	static $has_many = array();
	static $has_one = array();
	
	static $validates_presence_of = array(
		array(
			'descripcion',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
}
