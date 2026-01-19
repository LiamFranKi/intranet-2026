<?php
class Encuesta_Compartido extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'encuestas_compartidos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'alumno',
			'class_name' => 'Alumno',
		),
		array(
			'personal',
			'class_name' => 'Personal',
		),
		array(
			'apoderado',
			'class_name' => 'Apoderado',
		),
		array(
			'encuesta',
			'class_name' => 'Encuesta',
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

	function getFullName(){
		if($this->tipo == 'ALUMNO'){
			return $this->alumno->getFullName();
		}elseif($this->tipo == 'APODERADO'){
			return $this->apoderado->getFullName();
		}else{
			return $this->personal->getFullName();
		}
	}
}
