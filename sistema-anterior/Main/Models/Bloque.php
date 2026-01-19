<?php
class Bloque extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'bloques';
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
			'class_name' => 'Bloque_Curso',
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
	
	public $puntajes = array();

	function puntaje($curso_id, $grupo_id, $nro, $puntaje = null){
		if(!isset($this->puntajes[$curso_id])) $this->puntajes[$curso_id] = array();
		if(!isset($this->puntajes[$curso_id][$grupo_id])) $this->puntajes[$curso_id][$grupo_id] = array();
		if(!isset($this->puntajes[$curso_id][$grupo_id][$nro])) $this->puntajes[$curso_id][$grupo_id][$nro] = array();

		if(!isset($puntaje)) return (object) array(
			'total' => array_sum($this->puntajes[$curso_id][$grupo_id][$nro]),
			'length' => count($this->puntajes[$curso_id][$grupo_id][$nro])
		);
		$this->puntajes[$curso_id][$grupo_id][$nro][] = $puntaje;
		//file_put_contents('puntajes.txt', print_r($this->puntajes, true));
	}

	function clearPuntajes(){
		$this->puntajes = array();
	}

	function getCursosByNivel(){
        $cursos = Curso::all(array(
            'conditions' => 'colegio_id="'.$this->colegio_id.'" AND nivel_id="'.$this->nivel_id.'"',
            'order' => 'orden ASC'
        ));
        return $cursos;
    }

    function getNombre(){
    	return $this->nivel->nombre.' - '.$this->nombre;
    }
    
    function hasCurso($curso_id){
        $curso = Bloque_Curso::find_by_bloque_id_and_curso_id($this->id, $curso_id);
        if($curso) return true;
        return false;
    }
}
