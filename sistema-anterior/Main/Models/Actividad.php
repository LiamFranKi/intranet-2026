<?php
class Actividad extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'actividades';
	static $connection = '';
	
	static $belongs_to = Array();
	static $has_many = Array();
	static $has_one = Array();
	
	static $validates_presence_of = array(
		array(
			'descripcion',
		),
		array(
			'fecha_inicio',
		),
		array(
			'fecha_fin',
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
