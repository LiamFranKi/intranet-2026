<?php
class Apoderado extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'apoderados';
	static $connection = '';
	
	static $belongs_to = array();
	static $has_many = array(
		array(
			'Familias',
			'class_name' => 'Familia',
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
			'apellido_paterno',
		),
		array(
			'apellido_materno',
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
	
	function before_destroy(){
		Familia::table()->delete(array(
			'apoderado_id' => $this->id
		));
	}

	// create a user
	function after_create(){
		$this->generateUser();
	}

	function after_destroy(){
		if(!is_null($this->usuario)) $this->usuario->delete();
	}
	
	function generateUser(){
		$nombres = explode(' ', $this->nombres);
		//$usuario = strtolower($nombres[0].substr($this->apellido_paterno, 0, 2).substr($this->apellido_materno, 0, 2).$this->id);
		$usuario = $this->nro_documento;
		Usuario::create(array(
			'usuario' => $usuario,
			'password' => sha1($usuario),
			'tipo' => 'APODERADO',
			'estado' => 'ACTIVO',
			'colegio_id' => $this->colegio_id,
			'apoderado_id' => $this->id
		));
	}

	function resetUser(){
		if(is_null($this->usuario)){
			$this->generateUser();
			//$this->usuario = Usuario::find_by_apoderado_id($this->id);
		}

		$usuario = $this->nro_documento;
		$this->usuario->update_attributes(array(
			'usuario' => $usuario,
			'password' => sha1($usuario),
			'tipo' => 'APODERADO',
			'estado' => 'ACTIVO',
			'colegio_id' => $this->colegio_id,
			'apoderado_id' => $this->id
		));
	}

	function getAlumnos(){
		$alumnos = Alumno::all([
			'conditions' => 'familias.apoderado_id = "'.$this->id.'"',
			'joins' => 'INNER JOIN familias ON familias.alumno_id = alumnos.id'
		]);
		return $alumnos;
	}
}
