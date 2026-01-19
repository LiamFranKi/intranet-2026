<?php
class Personal extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'personal';
	static $connection = 'main';
	
	static $belongs_to = array();
	static $has_many = array(
		array(
			'asignaturas',
			'class_name' => 'Asignatura',
		),
	);
	static $has_one = array(
		array(
			'usuario',
			'class_name' => 'Usuario',
		),
	);
	
	static $validates_presence_of = array(
		array(
			'nombres',
		),
		array(
			'apellidos',
		),
		array(
			'tipo_documento',
		),
		array(
			'nro_documento',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
	
	function getFullName(){
		return mb_strtoupper($this->apellidos, 'utf-8').', '.ucwords(mb_strtolower($this->nombres, 'utf-8'));
	}

	function getShortName(){
		$nombre = explode(' ', $this->nombres)[0];
		$apellido = explode(' ', $this->apellidos)[0];
		return ucwords(mb_strtolower($nombre.' '.$apellido, 'utf-8'));
	}

	function hasCursoAsignado($grupo_id, $curso_id){
		$asignatura = Asignatura::find_by_personal_id_and_grupo_id_and_curso_id($this->id, $grupo_id, $curso_id);
		if($asignatura) return true;
		return false;
	}

	function hasCursoAsignadoBloque($curso_id, $anio, $examen){
		$asignaturas = Asignatura::all(array(
			'conditions' => 'asignaturas.curso_id = "'.$curso_id.'" AND grupos.grado = "'.$examen->grado.'" AND grupos.anio = "'.$anio.'" AND personal_id = "'.$this->id.'"',
			'joins' => 'INNER JOIN grupos ON grupos.id = asignaturas.grupo_id'
		));
		
		if(count($asignaturas) > 0){
			return true;
		}
		return false;
	}

	function getGruposAsignados($anio){
		$grupos = Grupo::all(array(
			'select' => 'DISTINCT grupos.*',
			'conditions' => '(asignaturas.personal_id="'.$this->id.'" OR grupos.tutor_id = "'.$this->id.'") AND grupos.anio="'.$anio.'"',
			'joins' => array('asignaturas'),
			'order' => 'grupos.nivel_id ASC, grupos.grado ASC, grupos.seccion ASC'
        ));
        return $grupos;
	}

	function getAsignaturas($anio){
		$asignaturas = Asignatura::all(array(
			'conditions' => 'asignaturas.personal_id="'.$this->id.'" AND grupos.anio="'.$anio.'"',
			'joins' => array('grupo'),
			'order' => 'grupos.nivel_id ASC, grupos.grado ASC, grupos.seccion ASC'
		)); 
		return $asignaturas;
	}
	

	function getHorarios($dia, $anio = 2015){

		$horarios = Grupo_Horario::all(array(
			'select' => 'grupos_horarios.*',
			'conditions' => '(grupos_horarios.anio = "'.$anio.'" AND (asignaturas.personal_id = "'.$this->id.'" OR grupos_horarios.personal_id = "'.$this->id.'") AND dia="'.$dia.'")',
			'joins' => 'LEFT JOIN asignaturas ON asignaturas.id = grupos_horarios.asignatura_id
			',
			'order' => 'STR_TO_DATE(hora_inicio, "%l:%i %p") ASC'
		));
		return $horarios;
	}

	function after_destroy(){
		if(!is_null($this->usuario)) $this->usuario->delete();
	}

	function getFileManagerToken(){
		return sha1(base64_encode(base64_encode($this->id)));
	}

	function validFileManagerToken($token){
		return $this->getFileManagerToken() == $token;
	}

	function getFileManagerDirectory(){
		$directory = './Static/Files/'.$this->id;
		if(!file_exists($directory)) mkdir($directory, 0777, true);
		return $directory;
	}

	function get($tipo, $fecha){
		$fecha = date('Y-m-d', strtotime($fecha));
		$asistencia = Trabajador_Asistencia::find(array(
			'conditions' => 'tipo="'.$tipo.'" AND fecha="'.$fecha.'" AND trabajador_id="'.$this->id.'"'
		));
		return $asistencia->hora_real;
	}

	function getAsistencia($tipo, $fecha){
		$fecha = date('Y-m-d', strtotime($fecha));
		$asistencia = Trabajador_Falta::find(array(
			'conditions' => 'asistencia="'.$tipo.'" AND fecha="'.$fecha.'" AND trabajador_id="'.$this->id.'"'
		));
		return $asistencia;
	}

	function getTotalAsistencia($tipo, $from, $to){
		$from = date('Y-m-d', strtotime($from));
		$to = date('Y-m-d', strtotime($to));

		$asistencia = Trabajador_Falta::count(array(
			'conditions' => 'asistencia="'.$tipo.'" AND DATE(fecha) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND trabajador_id="'.$this->id.'"'
		));
		return $asistencia;
	}

	function getPruebaActiva($compartido){
		$prueba = Examen_Prueba::find(array(
			'conditions' => 'compartido_id="'.$compartido->id.'" AND personal_id="'.$this->id.'" AND estado="ACTIVO"'
		));

		if($prueba) $prueba->checkFinished();
		
		return $prueba;
	}

	function canDoTest($compartido){
		$pruebas = Examen_Prueba::count(array(
			'conditions' => 'compartido_id="'.$compartido->id.'" AND personal_id="'.$this->id.'"'
		));

		if(time() > strtotime($compartido->expiracion)) return false;
		return $pruebas < $compartido->intentos;
	}
}
