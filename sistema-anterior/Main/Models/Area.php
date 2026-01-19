<?php
class Area extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'areas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'nivel',
			'class_name' => 'Nivel',
		),
	);
	static $has_many = array(
		array(
			'cursos',
			'class_name' => 'Area_Curso',
		),
	);
	static $has_one = array();
	
	static $validates_presence_of = array(
		array(
			'colegio_id',
		),
		array(
			'nombre',
		),
		array(
			'nivel_id',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
	
	function getCursosByNivel(){
        $cursos = Curso::all(array(
            'conditions' => 'colegio_id="'.$this->colegio_id.'" AND nivel_id="'.$this->nivel_id.'"',
            'order' => 'orden ASC'
        ));
        return $cursos;
    }
    
    function hasCurso($curso_id){
        $curso = Area_Curso::find_by_area_id_and_curso_id($this->id, $curso_id);
        if($curso) return true;
        return false;
    }
    
    function getAreaByCursoUgel($curso, $nivel_id){
        $area = Area::find(array(
            'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND nombre LIKE "%'.$curso.'%" AND nivel_id="'.$nivel_id.'"'
        ));
        return $area;
    }
}
