<?php
class Topico_Atencion extends TraitConstants{
//	use Constants;
    
	static $pk = 'id';
	static $table_name = 'topico_atenciones';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'personal',
			'class_name' => 'Personal',
		),
	);
	static $has_many = array();
	static $has_one = array();
	
	static $validates_presence_of = array(
		array(
			'fecha_hora',
		),
		array(
			'motivo',
		),
		array(
			'tratamiento',
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
