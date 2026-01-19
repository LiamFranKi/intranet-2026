<?php
class Grupo extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'grupos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'turno',
			'class_name' => 'Turno',
		),
		array(
			'nivel',
			'class_name' => 'Nivel',
		),
		array(
			'tutor',
			'class_name' => 'Personal',
			'foreign_key' => 'tutor_id',
		),
		array(
			'sede',
			'class_name' => 'Sede',
			'foreign_key' => 'sede_id',
		),
	);
	static $has_many = array(
		array(
			'matriculas',
			'class_name' => 'Matricula',
		),
		array(
			'asignaturas',
			'class_name' => 'Asignatura',
		),
	);
	static $has_one = array();
	
	static $validates_presence_of = array(
		array(
			'colegio_id',
		),
		array(
			'nivel_id',
		),
		array(
			'grado',
		),
		array(
			'seccion',
		),
		array(
			'anio',
		),
		array(
			'turno_id',
		),
		array(
			'sede_id',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
	
	function getAllCompendios($personal){
		$compendios = Compendio::all(array(
			'conditions' => '((nivel_id = "'.$this->nivel_id.'" AND grado = "'.$this->grado.'") OR (grupo_id = "'.$this->id.'"))',
			'order' => 'titulo ASC'
		));
		if(is_null($personal))
			return $compendios;

		foreach($compendios As $key => $compendio){
			if(!$personal->hasCursoAsignado($this->id, $compendio->curso_id))
				unset($compendios[$key]);
		}
		return $compendios;
	}

	function getCompendios($personal = null){ 
		$compendios = Compendio::all(array(
			'conditions' => '((nivel_id = "'.$this->nivel_id.'" AND grado = "'.$this->grado.'") OR (grupo_id = "'.$this->id.'")) AND estado = "ACTIVO"',
			'order' => 'titulo ASC'
		));
		if(is_null($personal))
			return $compendios;

		foreach($compendios As $key => $compendio){
			if(!$personal->hasCursoAsignado($this->id, $compendio->curso_id))
				unset($compendios[$key]);
		}
		return $compendios;
	}

	function getHorarios($dia){
		$horarios = Grupo_Horario::find_all_by_dia_and_grupo_id_and_tipo($dia, $this->id, 'GRUPO', array(
			'order' => 'STR_TO_DATE(hora_inicio, "%l:%i %p") ASC'
		));
		return $horarios;
	}

	function getGrado(){
		if($this->grado == -1) return 'Avanzada';
		if(preg_match('/inicial/i', strtolower($this->nivel->nombre))){
			return $this->grado.' Años';
		}
		
		return $this->grado.'º';
	}

	function getGradoDescribed(){
		if($this->grado == -1) return 'Avanzada';
		if(preg_match('/inicial/i', strtolower($this->nivel->nombre))){
			return $this->grado.' Años';
		}
		
		return $this->grado.'º GRADO';
	}
	
	function getNombre(){
		return mb_strtoupper($this->nivel->nombre.' - '.$this->getGrado().' '.$this->seccion.' - '.$this->turno->nombre.' - '.$this->anio, 'utf-8');
	}

	function getNombreShort(){
		return mb_strtoupper($this->nivel->nombre.' - '.$this->getGrado().' '.$this->seccion.' - '.$this->turno->nombre, 'utf-8');
	}

	function getNombreShort2(){
		return mb_strtoupper($this->nivel->nombre.' - '.$this->getGrado().' '.$this->seccion.' - '.$this->anio, 'utf-8');
	}

    function getNombreShort4(){
		return mb_strtoupper($this->nivel->nombre.' - '.$this->getGrado().' '.$this->seccion, 'utf-8');
	}

	function getNombreShortSede(){
		return mb_strtoupper($this->nivel->nombre.' - '.$this->getGrado().' '.$this->seccion.' - '.$this->anio.' - '.$this->sede->nombre, 'utf-8');
	}

	function getNombreShort3(){
		return mb_strtoupper($this->getGrado().' '.$this->seccion, 'utf-8');
	}


	function getMatriculas(){
		$matriculas = Matricula::all(array(
			'conditions' => 'grupo_id="'.$this->id.'" AND (estado="0" OR estado=4)',
			'joins' => array('alumno'),
			'order' => $this->ALUMNOS_ORDER
		));
		return $matriculas;
	}

	function getMatriculasRetirados(){
		$matriculas = Matricula::all(array(
			'conditions' => 'grupo_id="'.$this->id.'" AND estado=2',
			'joins' => array('alumno'),
			'order' => $this->ALUMNOS_ORDER
		));
		return $matriculas;
	}
    
	function getAsignaturas(){
		$asignaturas = Asignatura::all(array(
			'conditions' => 'grupo_id="'.$this->id.'"',
		));
		return $asignaturas;
	}

	function getAsignaturasDocente($personal_id){
		$asignaturas = Asignatura::all(array(
			'conditions' => 'grupo_id="'.$this->id.'" AND personal_id = "'.$personal_id.'"',
		));
		return $asignaturas;
	}

	function getCursosDocente($personal_id){
		$cursos = array();
		foreach($this->getAsignaturas() As $asignatura){
			if($asignatura->personal_id == $personal_id)
				$cursos[] = $asignatura->curso;
		}
		return $cursos;
	}

	function getAsignaturaByCurso($curso_id){
		$asignatura = Asignatura::find_by_curso_id_and_grupo_id($curso_id, $this->id);
		return $asignatura;
	}

	function hasCurso($curso_id){
		$asignatura = Asignatura::find_by_curso_id_and_grupo_id($curso_id, $this->id);
		return !is_null($asignatura);
	}

	function getFileManagerToken(){
		return sha1(base64_encode(base64_encode($this->id)));
	}

	function validFileManagerToken($token){
		return $this->getFileManagerToken() == $token;
	}

	function getFileManagerDirectory(){
		$directory = './Static/GroupFiles/'.$this->id;
		if(!file_exists($directory)) mkdir($directory, 0777, true);
		return $directory;
	}

	function getTotalGenero($sexo){
		$total = 0;
		foreach($this->getMatriculas() As $matricula){
			if($sexo == $matricula->alumno->sexo){
				$total++;
			}
		}
		return $total;
	}
}
