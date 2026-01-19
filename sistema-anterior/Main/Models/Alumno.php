<?php
class Alumno extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'alumnos';
	static $connection = 'main';
	
	static $belongs_to = array(
		array(
			'pais_nacimiento',
			'class_name' => 'Pais',
			'foreign_key' => 'pais_nacimiento_id',
		),
		array(
			'departamento_nacimiento',
			'class_name' => 'Departamento',
			'foreign_key' => 'departamento_nacimiento_id',
		),
		array(
			'provincia_nacimiento',
			'class_name' => 'Provincia',
			'foreign_key' => 'provincia_nacimiento_id',
		),
		array(
			'distrito_nacimiento',
			'class_name' => 'Distrito',
			'foreign_key' => 'distrito_nacimiento_id',
		),
		array(
			'Colegio',
			'class_name' => 'Colegio',
		),
	);
	static $has_many = array(
		array(
			'documentos',
			'class_name' => 'Alumno_Documento',
		),
		array(
			'familias',
			'class_name' => 'Familia',
		),
		array(
			'matriculas',
			'class_name' => 'Matricula',
		),

        array(
			'avatar_sales',
			'class_name' => 'AvatarShopSale',
            'foreign_key' => 'student_id',
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
			'apellido_paterno',
		),
		array(
			'apellido_materno',
		),
		array(
			'nombres',
		),
		array(
			'tipo_documento',
		),
		array(
			'nro_documento',
		),
		array(
			'codigo',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array(
		array(
			'nro_documento',
			'message' => 'Documento ya registrado',
		),
	);
	
	function getApellidosNombres(){
		return mb_strtoupper($this->apellido_paterno.' '.$this->apellido_materno.' '.$this->nombres, 'utf-8');
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
			'tipo' => 'ALUMNO',
			'estado' => 'ACTIVO',
			'colegio_id' => $this->colegio_id,
			'alumno_id' => $this->id
		));
	}

	function resetUser(){
		if(is_null($this->usuario)){
			$this->generateUser();
			//$this->usuario = Usuario::find_by_alumno_id($this->id);
		}

		$usuario = $this->nro_documento;
		$this->usuario->update_attributes(array(
			'usuario' => $usuario,
			'password' => sha1($usuario),
			'tipo' => 'ALUMNO',
			'estado' => 'ACTIVO',
			'colegio_id' => $this->colegio_id,
			'alumno_id' => $this->id
		));
	}
	
	function getActividadesNacimiento(){
		$data = !empty($this->actividades_nacimiento) ? unserialize(base64_decode($this->actividades_nacimiento)) : array();
		return $data;
	}
	
	function getPadreMadre(){
		$padre = Apoderado::find(array(
			'conditions' => 'familias.alumno_id = "'.$this->id.'" AND apoderados.parentesco=0',
			'joins' => 'INNER JOIN familias ON familias.apoderado_id = apoderados.id'
		));

		if(!is_null($padre)){
			return $padre;
		}

		$madre = Apoderado::find(array(
			'conditions' => 'familias.alumno_id = "'.$this->id.'" AND apoderados.parentesco=1',
			'joins' => 'INNER JOIN familias ON familias.apoderado_id = apoderados.id'
		));

		if(!is_null($madre)){
			return $madre;
		}

		$apoderado = Apoderado::find(array(
			'conditions' => 'familias.alumno_id = "'.$this->id.'" AND apoderados.parentesco=2',
			'joins' => 'INNER JOIN familias ON familias.apoderado_id = apoderados.id'
		));

		return $apoderado;
	}

	function getApoderadoByParentesco($parentesco){
		$apoderado = Apoderado::find(array(
			'conditions' => 'familias.alumno_id = "'.$this->id.'" AND apoderados.parentesco="'.$parentesco.'"',
			'joins' => 'INNER JOIN familias ON familias.apoderado_id = apoderados.id'
		));
		return $apoderado;
	}

	function getApoderados(){
		$apoderados = Apoderado::all(array(
			'conditions' => 'familias.alumno_id = "'.$this->id.'"',
			'joins' => 'INNER JOIN familias ON familias.apoderado_id = apoderados.id'
		));
		return $apoderados;
	}
	
	function getControlesPesoTalla(){
		$data = !empty($this->controles_peso_talla) ? unserialize(base64_decode($this->controles_peso_talla)) : array();
		return $data;
	}
	
	function getOtrosControles(){
		$data = !empty($this->otros_controles) ? unserialize(base64_decode($this->otros_controles)) : array();
		return $data;
	}
	
	function getEnfermedadesSufridas(){
		$data = !empty($this->enfermedades_sufridas) ? unserialize(base64_decode($this->enfermedades_sufridas)) : array();
		return $data;
	}
	
	function getVacunas(){
		$data = !empty($this->vacunas) ? unserialize(base64_decode($this->vacunas)) : array();
		return $data;
	}
	
	function getTrabajos(){
		$data = !empty($this->trabajos) ? unserialize(base64_decode($this->trabajos)) : array();
		return $data;
	}
	
	function getDomicilio(){
		$data = !empty($this->domicilio) ? unserialize(base64_decode($this->domicilio)) : array();
		return $data;
	}
	
	function getFirstApoderado(){
        $familia = Familia::first(array(
            'conditions' => 'alumno_id="'.$this->id.'"'
        ));

        return $familia->apoderado;
    }


	
	function getLastDomicilio(){
		$domicilios = $this->getDomicilio();
		$newData = array();
		foreach($domicilios As $domicilio){
			if(empty($domicilio['direccion'])) continue;
			$newData[] = $domicilio;
		}
		return array_pop($newData);
	}

	function getLastMatricula(){
		$matricula = Matricula::find(array(
			'conditions' => 'alumno_id = "'.$this->id.'"',
			'order' => 'id DESC'
		));

		return $matricula;
	}

    function getLastMatriculaByYear(){
		$matricula = Matricula::find(array(
			'conditions' => 'alumno_id = "'.$this->id.'"',
            'joins' => ['grupo'],
			'order' => 'grupos.anio DESC'
		));

		return $matricula;
	}
	
	function getMatriculasCount(){
		$matriculas = Matricula::count(array(
			'conditions' => 'alumno_id="'.$this->id.'"',
		));
		return $matriculas;
	}

	function getMatriculas(){
		$matriculas = Matricula::all(array(
			'conditions' => 'grupos.colegio_id="'.$this->colegio_id.'" AND alumno_id="'.$this->id.'"',
			'order' => 'nivel_id ASC, grado ASC, seccion ASC, fecha_registro ASC',
			'joins' => array('grupo')
		));
		return $matriculas;
	}
	
	function getMatriculasNivel($nivel_id){
		return Matricula::all(array(
			'conditions' => 'grupos.nivel_id="'.$nivel_id.'" AND alumno_id="'.$this->id.'"',
			'order' => 'grupos.nivel_id ASC, grado ASC',
			'joins' => array('grupo')
		));
	}
	
	function getFechaInscripcion(){
		return $this->getFecha($this->fecha_inscripcion);
	}
	
	function getMatriculaByAnio($anio){
		// matriculas.estado = "0" AND 
		$matricula = Matricula::find(array(
			'conditions' => 'grupos.colegio_id="'.$this->colegio_id.'" AND alumno_id="'.$this->id.'" AND grupos.anio="'.$anio.'"',
			'joins' => array('grupo')
		));
		return $matricula;
	}

	function getEdad(){
		if(empty($this->fecha_nacimiento)) return null;
		$from = new DateTime($this->fecha_nacimiento);
		$to   = new DateTime('today');
		return $from->diff($to)->y;

		# procedural
		//echo date_diff(date_create($this->fecha_nacimiento), date_create('today'))->y;
	}

	function getTipoDocumentoFacturacion(){
		return $this->TIPOS_DOCUMENTO_FACTURACION[$this->tipo_documento];
	}

    function getStarsAmount(){
        $stars = EnrollmentIncident::find([
            'select' => 'SUM(points) AS total',
            'joins' => 'inner join matriculas on matriculas.id = enrollment_incidents.enrollment_id',
            'conditions' => ['matriculas.alumno_id = ?', $this->id]
        ]);
        return $stars->total | 0;
    }

    function getAvatars(){
        
        return AvatarShopItem::all([
            'conditions' => ['avatar_shop_sales.student_id = ?', $this->id],
            'joins' => 'INNER JOIN avatar_shop_sales ON avatar_shop_sales.item_id = avatar_shop_items.id',
            'order' => 'avatar_shop_items.level ASC, avatar_shop_items.name ASC'
        ]);
    }
}
