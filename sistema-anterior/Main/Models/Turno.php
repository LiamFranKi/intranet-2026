<?php
class Turno extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'turnos';
	static $connection = 'main';
	
	static $belongs_to = Array();
	static $has_many = Array();
	static $has_one = Array();
	
	static $validates_presence_of = array(
		array(
			'colegio_id',
		),
		array(
			'nombre',
		),
		array(
			'abreviatura',
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
