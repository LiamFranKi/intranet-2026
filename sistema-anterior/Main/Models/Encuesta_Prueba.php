<?php
class Encuesta_Prueba extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'encuestas_pruebas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'compartido',
			'class_name' => 'Encuesta_Compartido',
			'foreign_key' => 'compartido_id',
		),
		array(
			'Alumno',
			'class_name' => 'Alumno',
			'foreign_key' => 'alumno_id',
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

	function setRespuestas($respuestas){
		$this->respuestas = base64_encode(serialize($respuestas));
		$this->save();
	}

	function getRespuestas(){
		$respuestas = empty($this->respuestas) ? array() : unserialize(base64_decode($this->respuestas));
		return $respuestas;
	}
}
