<?php
class Tarea_Pregunta_Alternativa extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'tareas_preguntas_alternativas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'pregunta',
			'class_name' => 'Tarea_Pregunta',
			'foreign_key' => 'pregunta_id',
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

	function correcta(){
		return $this->correcta == 'SI';	
	}
}
