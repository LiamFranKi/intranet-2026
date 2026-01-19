<?php
class Tarea extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'tareas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Asignatura',
			'class_name' => 'Asignatura',
		),
	);
	static $has_many = array(
		array(
			'preguntas',
			'class_name' => 'Tarea_Pregunta',
			'foreign_key' => 'tarea_id',
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
		$preguntas = Tarea_Pregunta::find_all_by_tarea_id($this->id, array(
			'order' => 'orden ASC'
		));
		return $preguntas;
	}
}
