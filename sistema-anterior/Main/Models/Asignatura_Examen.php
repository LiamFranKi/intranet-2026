<?php
class Asignatura_Examen extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'asignaturas_examenes';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Asignatura',
			'class_name' => 'Asignatura',
		),
		array(
			'Trabajador',
			'class_name' => 'Trabajador',
		),
	);
	static $has_many = array(
		array(
			'preguntas',
			'class_name' => 'Asignatura_Examen_Pregunta',
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

	function before_destroy(){
		
	}

	function getPreguntas(){
		$preguntas = Asignatura_Examen_Pregunta::find_all_by_examen_id($this->id, array(
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

	function hasTiempoLimite(){
		return $this->tiempo > 0;
	}

	function canModificar(){
		//return true;

		return time() <= strtotime($this->fecha_desde.' '.$this->hora_desde);
	}
}
