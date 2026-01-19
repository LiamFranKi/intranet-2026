<?php
class Examen extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'examenes';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Personal',
			'class_name' => 'Personal',
		),
	);
	static $has_many = array(
		array(
			'preguntas',
			'class_name' => 'Examen_Pregunta',
			'foreign_key' => 'examen_id',
		),
		array(
			'compartidos',
			'class_name' => 'Examen_Compartido',
			'foreign_key' => 'examen_id',
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

	function getPreguntas($curso_id = null){
		$conditions = 'examen_id = "'.$this->id.'"';
		
		if(!empty($curso_id))
			$conditions .= ' AND categoria = "'.$curso_id.'"';

		$preguntas = Examen_Pregunta::all(array(
			'conditions' => $conditions,
			'order' => 'orden ASC'
		));



		return $preguntas;
	}

	function puntajeGeneral(){
		return $this->tipo_puntaje == 'GENERAL';
	}

	function penalizarIncorrecta(){
		return $this->penalizar_incorrecta == 'SI';
	}

	function getRandomPreguntas(){
		$preguntas = $this->getPreguntas();
		shuffle($preguntas);
		return $preguntas;
	}


	function getCategorias(){
		$categorias = explode("\n", trim($this->categorias));
		$categorias = array_map(function($valor){
			return trim($valor);
		}, $categorias);
		return $categorias;
	}

}
