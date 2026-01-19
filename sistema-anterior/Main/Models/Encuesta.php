<?php
class Encuesta extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'encuestas';
	static $connection = '';
	
	static $belongs_to = array();
	static $has_many = array(
		array(
			'preguntas',
			'class_name' => 'Encuesta_Pregunta',
		),
		array(
			'compartidos',
			'class_name' => 'Encuesta_Compartido',
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

	function getPreguntas(){
		$preguntas = Encuesta_Pregunta::find_all_by_encuesta_id($this->id, array(
			'order' => 'orden ASC'
		));
		return $preguntas;
	}
}
