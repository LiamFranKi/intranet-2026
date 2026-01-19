<?php
class Examen_Bloque extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'examenes_bloques';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'bloque',
			'class_name' => 'Bloque',
		),
		array(
			'curso',
			'class_name' => 'Curso',
		),
	);
	static $has_many = array(
		array(
			'compartidos',
			'class_name' => 'Examen_Bloque_Compartido',
			'foreign_key' => 'examen_id',
		),
		array(
			'preguntas',
			'class_name' => 'Examen_Bloque_Pregunta',
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

	/*
	function getPreguntas(){
		$preguntas = !empty($this->preguntas) ? unserialize($this->preguntas) : array();
		return $preguntas;
	}
	*/

	function getPreguntas($curso_id = null, $shuffle = false){
		$conditions = 'examen_id = "'.$this->id.'"';
		if(!is_null($curso_id)){
			$conditions .= ' AND curso_id = "'.$curso_id.'"';
		}
		$preguntas = Examen_Bloque_Pregunta::all(array(
			'conditions' => $conditions,
			'order' => 'orden ASC'
		));
		if($shuffle){
			shuffle($preguntas);
		}
		return $preguntas;
	}

	function getTipoArchivo(){
		$extension = explode('.', $this->archivo);
		return $extension[1];
	}

	// Retorna el id de los cursos seleccionados
	function getCursosID(){
		$cursos = !empty($this->cursos) ? unserialize($this->cursos) : array();
		return $cursos;
	}

	function getCursos(){
		$ids = $this->getCursosID();
		$cursos = array();
		foreach($ids As $id){
			$curso = Curso::find($id);
			$cursos[] = $curso;
		}
		return $cursos;
	}
}
