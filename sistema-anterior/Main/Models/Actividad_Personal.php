<?php
class Actividad_Personal extends TraitConstants{
	
	static $pk = 'id';
	static $table_name = 'actividades_personal';
	static $connection = '';
	
	static $belongs_to = Array();
	static $has_many = Array();
	static $has_one = Array();
	
	static $validates_presence_of = Array();
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = Array();
	static $validates_uniqueness_of = Array();

	function getPermisos(){
		$permissions = empty($this->permisos) ? array() : unserialize(base64_decode($this->permisos));
		if(empty($permissions)) return array();
		return $permissions;
	}
}
