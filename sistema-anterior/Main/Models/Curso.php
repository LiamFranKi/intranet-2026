<?php
class Curso extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'cursos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'colegio',
			'class_name' => 'Colegio',
		),
		array(
			'nivel',
			'class_name' => 'Nivel',
		),
	);
	static $has_many = array();
	static $has_one = array();
	
	static $validates_presence_of = array(
		array(
			'nivel_id',
		),
		array(
			'nombre',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
	
	function getCriterios($ciclo = null){
        $conditions = 'colegio_id="'.$this->colegio_id.'" AND curso_id="'.$this->id.'"';
        if(isset($ciclo)) $conditions .= ' AND (ciclo="'.$ciclo.'" OR ciclo="0")';
        
        $criterios = Curso_Criterio::all(array(
            'conditions' => $conditions,
            'order' => 'orden ASC'
        ));
        
        return $criterios;
    }

    function examenMensual(){
    	return $this->examen_mensual == 'SI';
    }

    function getImagen(){

    	if(!empty($this->imagen) && file_exists('./Static/Archivos/'.$this->imagen)){
    		return '/Static/Archivos/'.$this->imagen;
    	}

        $url = Config::get('url_antiguo');

    	return $url.'/Static/Archivos/'.$this->imagen;
    }
}
