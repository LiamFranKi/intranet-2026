<?php
class Compendio extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'compendios';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'nivel',
			'class_name' => 'Nivel',
		),
		array(
			'curso',
			'class_name' => 'Curso',
		),
		array(
			'grupo',
			'class_name' => 'Grupo',
		),
	);
	static $has_many = array(
		array(
			'paginas',
			'class_name' => 'Compendio_Pagina',
			'foreign_key' => 'compendio_id',
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

	function after_create(){
		// se crea una página por defecto
		Compendio_Pagina::create(array(
			'compendio_id' => $this->id,
			'pagina' => 1,
		
			'borrable' => 'NO'
		));
	}

	function before_destroy(){
		
	}

	function getIndices(){
		$paginas = Compendio_Pagina::find_all_by_compendio_id_and_agregar_indice($this->id, 'SI', array(
			'order' => 'pagina ASC'
		));
		return $paginas;
	}

	function getGrado(){
		if($this->grado == -1) return 'Avanzada';
		if(preg_match('/inicial/i', strtolower($this->nivel->nombre))){
			return $this->grado.' Años';
		}
		
		return $this->grado.'º';
	}
}
