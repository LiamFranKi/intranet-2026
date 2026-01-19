<?php
class Nota extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'notas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'criterio',
			'class_name' => 'Asignatura_Criterio',
			'foreign_key' => 'criterio_id',
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
