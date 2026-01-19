<?php
class Publicacion extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'publicaciones';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'colegio',
			'class_name' => 'Colegio',
		),
		array(
			'usuario',
			'class_name' => 'Usuario',
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
	
	function getImages(){
		$data = !empty($this->images) ? unserialize(base64_decode($this->images)) : array();
		
		return $data;
	}
	
	function before_destroy(){
		foreach($this->getImages() As $imagen){
			$imagen = basename('.'.$imagen);
			@unlink('./Static/Image/Publicaciones/'.$imagen);
		}
		
		foreach($this->getFiles() As $archivo){
			@unlink('./Static/Archivos/'.$archivo);
		}
	}

    function after_create(){
        file_get_contents(Config::get('api_url').'/notifications/post/'.$this->id);
    }
	
	function canView($group){
		$privacyData = explode(',', $this->privacidad);
		if(in_array($group, $privacyData)){
			return true;
		}
	}
	
	function canSeePost($usuario){
        if(!$usuario instanceOf Usuario) $usuario = Usuario::find($usuario);
		if($usuario->id == $this->usuario->id) return true;
		if($usuario->is('ADMINISTRADOR')) return true;
		if($this->canView(-1)) return true; // todos
		if($this->canView(-2) && !$usuario->is('ALUMNO') && !$usuario->is('APODERADO')) return true;
		
		
		
		if($usuario->is('ALUMNO')){
			$matricula = $usuario->alumno->getMatriculaByAnio($this->colegio->anio_activo);
			if($matricula){
				if($this->canView($matricula->grupo_id)){
					return true;
				}
			}
		}
		
		if($usuario->is('DOCENTE')){
			$grupos = $usuario->personal->getGruposAsignados($this->colegio->anio_activo);
			foreach($grupos As $grupo){
				if($this->canView($grupo->id) && $this->usuario->tipo == 'ALUMNO'){
					return true;
				}
			}
		}
        
        return false;
	}


}
