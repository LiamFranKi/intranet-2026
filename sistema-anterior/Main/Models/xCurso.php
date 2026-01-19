<?php
class xCurso extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'cursos';
	static $connection = 'SantaMaria';
	
	static $belongs_to = array();
	static $has_many = array(
		array(
			'criterios',
			'class_name' => 'xCriterio',
			'foreign_key' => 'curso_id',
		),
	);
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
