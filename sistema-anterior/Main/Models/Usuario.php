<?php
class Usuario extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'usuarios';
	static $connection = 'main';
	
	static $belongs_to = array(
		array(
			'personal',
			'class_name' => 'Personal',
		),
		array(
			'alumno',
			'class_name' => 'Alumno',
		),
		array(
			'apoderado',
			'class_name' => 'Apoderado',
		),
		array(
			'colegio',
			'class_name' => 'Colegio',
		),
	);
	static $has_many = array(
		array(
			'devices',
			'class_name' => 'Usuario_Device',
			'foreign_key' => 'usuario_id',
		),
		array(
			'tokens',
			'class_name' => 'Usuario_Token',
			'foreign_key' => 'usuario_id',
		),
	);
	static $has_one = array();
	
	static $validates_presence_of = array();
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();

	public $ms_api;
	
	function administrador(){
		return ($this->tipo == 'ADMINISTRADOR');
	}
	
	function alumno(){
		return ($this->tipo == 'ALUMNO');
	}
	
	function is($tipo){
        if(is_array($tipo)){
            return in_array($this->tipo, $tipo);
        }
		return ($this->tipo == $tipo);
	}

	function getShortName(){
		if($this->tipo == 'ALUMNO'){
			return $this->alumno->getShortName();
		}elseif($this->tipo == 'APODERADO'){
			return $this->apoderado->getShortName();
		}else{
			return $this->personal->getShortName();
		}
	}
	
	function getFullName(){
		if($this->tipo == 'ALUMNO'){
			return $this->alumno->getFullName();
		}elseif($this->tipo == 'APODERADO'){
			return $this->apoderado->getFullName();
		}else{
			return $this->personal->getFullName();
		}
	}
	
	// THIS SHOULD BE USED AFTER "is_valid"
	function isUniqueInCollege(){
		$usuario = Usuario::find(array(
			'conditions' => 'usuario="'.$this->usuario.'" AND colegio_id="'.$this->colegio_id.'"'
		));
		
		if(isset($usuario) && $usuario->id != $this->id){
			$this->errors->add('usuario', 'El nombre de usuario no está disponible');
			return false;
		}
		return true;
	}
	
	function getMensajesNuevosCount(){
		$mensajes = Mensaje::count(array(
			'conditions' => 'destinatario_id="'.$this->id.'" AND tipo="RECIBIDO" AND estado="NO_LEIDO"'
		));
		
		return $mensajes;
	}
	
	function getUltimosMensajes(){
		$mensajes = Mensaje::all(array(
			'conditions' => 'destinatario_id="'.$this->id.'" AND tipo="RECIBIDO" ',
			'order' => 'id DESC',
			'limit' => 3
		));
		
		return $mensajes;
	}
	
	function getFoto(){
		//print_r($this->attributes());
		switch($this->tipo){
			case 'ALUMNO':
				return $this->alumno->getFoto();
			break;
			case 'APODERADO':
				return $this->apoderado->getFoto();
			break;
			default:
				return $this->personal->getFoto();
			break;
		}
	}
	
	function getCargo(){
        switch($this->tipo){
			case 'ALUMNO':
				$cargo = 'ALUMNO';
			break;
			case 'APODERADO':
				$cargo = 'PADRE DE FAMILIA / APODERADO';
			break;
			default:
				$cargo = $this->personal->cargo;
			break;
		}
        return $cargo;
    }
    
    function getMobileMenu(){
        switch($this->tipo){
            case 'ALUMNO':
                $menu = array(
                    array('title' => 'Inicio / Publicaciones', 'href' => '#/publicaciones'),
                    array('title' => 'Cursos Asignados', 'href' => '#/alumno/cursos_asignados/'.$this->alumno->id),
                    array('title' => 'Trabajos Encargados', 'href' => '#/alumno/trabajos_encargados')
                );
            break;
        }
        return $menu;
    }
    
    function getMobileData(){
        $data = array(
            'isAllowed' => $this->isMobileAllowed(),
            'fullName' => $this->getFullName(),
			'id' => $this->id,
            'tipo' => $this->tipo,
            'access_token' => $this->generateToken()
        );
        
        return $data;
    }
    
    function isMobileAllowed(){
        return in_array($this->tipo, array('ALUMNO'));
    }

    function mustOfficeLogged(){
    	return $this->colegio->officeIntegrated();
    }

    function isActive(){
    	return $this->estado == 'ACTIVO';
    }

    function hasOfficeAccount(){
    	return !empty($this->ms_access_token);
    }

    function setMsAPI($api){
    	$this->ms_api = $api;
    }

    function getUltimosMensajesOutlook(){
    	$messages = $this->ms_api->getMessages($this->ms_access_token, 3);
    	if(isset($messages->value)){
			$nuevos = 0;
    		foreach($messages->value As $message){
    			if(!$message->IsRead){
    				++$nuevos;
    			}
    		}

    		return array('mensajes' => $messages->value, 'nuevos' => $nuevos);
    	}
    	return array('mensajes' => array(), 'nuevos' => 0);
    }

    function generateToken(){
    	$token = sha1(md5($this->id*time()));
    	$tokenObject = new Usuario_Token(array(
    		'usuario_id' => $this->id,
    		'token' => $token
    	));

    	$tokenObject->save();
    	return $token;
    }

    function validToken($token){
    	$tokenObject = Usuario_Token::find_by_usuario_id_and_token($this->id, $token);
    	return !is_null($tokenObject);
    }

    

    function shouldChangePassword(){
    	//if($this->tipo == 'ALUMNO' || $this->tipo == 'APODERADO' ||){
    	if(sha1($this->usuario) == $this->password || $this->cambiar_password == 'SI') return true;
    	//}
    	return false;
    }

    function getDeudas(){
    	if($this->tipo == 'ALUMNO'){
    		$matricula = $this->alumno->getMatriculaByAnio($this->colegio->anio_activo);
    		
    		if(!$matricula) return array();

    		return $matricula->getDeudas();
    	}

    	if($this->tipo == 'APODERADO'){
    		$alumnos = Alumno::find_by_sql('
	            SELECT alumnos.* FROM alumnos
	            INNER JOIN familias ON familias.alumno_id = alumnos.id
	            WHERE familias.apoderado_id = "'.$this->apoderado_id.'"
	        ');
	        $deudas = array();

	        foreach($alumnos As $alumno){
	        	$matricula = $alumno->getMatriculaByAnio($this->colegio->anio_activo);

	        	if($matricula){
		        	//print_r($matricula->getDeudas());
		        	$deudasAlumno = $matricula->getDeudas();
		        	if(count($deudasAlumno) > 0){
		        		$deudas[] =  $alumno->getFullName().' - <b>('.implode(', ', $deudasAlumno).')</b>';
		        	}
	        	}
	        }

    		return $deudas;
    	}

    	return array();
    }



    function hasPermiso($location){
    	$permisos = $this->getPermisos();
    	
    	return in_array($location, $permisos);
    }

    function checkPermissions($permission){

    	$permissions = array(
    		'TRABAJADORES' => array(
    			'ALL' => 'ADMINISTRADOR, CAJERO',
    			'VIEW' => 'DIRECTOR, PROMOTORIA, SECRETARIA'
    		),
    		'BOLETAS' => array(
    			'ALL' => 'ADMINISTRADOR, CAJERO, PERSONALIZADO',
    			'VIEW' => 'DIRECTOR, PROMOTORIA, SECRETARIA'
    		),
    		'OBJETIVOS' => array(
    			'ALL' => 'ADMINISTRADOR',
    			'VIEW' => 'CAJERO, DIRECTOR, PROMOTORIA, SECRETARIA'
    		),
    	);

    	$requested = explode('|', $permission);
    	if(isset($permissions[$requested[0]]) && isset($permissions[$requested[0]][$requested[1]])){
    		
    		$allowed = $permissions[$requested[0]][$requested[1]];
    		$allowed = preg_replace('/ +/', '', $allowed);

    		$allowed = explode(',', $allowed);
    		
    		return in_array($this->tipo, $allowed);
    	}
    	
    	return false;
    }

    function getPermisos(){
    	$permisos = empty($this->permisos) ? array() : unserialize(base64_decode($this->permisos));
    	return $permisos;
    }

    function setPermisos($permisos){
    	if(is_null($permisos)){
    		$this->permisos = '';
    	}else{
    		$this->permisos = base64_encode(serialize($permisos));
    	}
    	$this->save();
    }

    function getEncuestaCompartido(){

    	switch($this->tipo){
    		case 'ALUMNO':
    			$tipo = 'ALUMNO';
    			$field = 'alumno_id';
    		break;
    		case 'APODERADO':
    			$tipo = 'APODERADO';
    			$field = 'apoderado_id';
    		break;

    		default:
    			$tipo = 'PERSONAL';
    			$field = 'personal_id';
    		break;
    	}

    	$encuesta = Encuesta::find_by_estado_and_tipo('ACTIVO', $tipo);
    	if($encuesta){
    		$compartido = Encuesta_Compartido::find(array(
    			'conditions' => 'encuesta_id = "'.$encuesta->id.'" AND tipo = "'.$tipo.'" AND '.$field.'="'.$this->{$field}.'" '
    		));

    		if(!$compartido){
    			$compartido = Encuesta_Compartido::create([
    				'encuesta_id' => $encuesta->id,
    				'alumno_id' => $tipo == 'ALUMNO' ? $this->alumno_id : 0,
    				'apoderado_id' => $tipo == 'APODERADO' ? $this->apoderado_id : 0,
    				'personal_id' => $tipo == 'PERSONAL' ? $this->personal_id : 0,
    				'tipo' => $tipo,
    				'orden_preguntas' => 'PREDETERMINADO',
    				'estado' => 'PENDIENTE'
    			]);
    		}

    		return $compartido;
    	}
    }

    function getApiDevices(){
        return $this->devices;
    }
}
