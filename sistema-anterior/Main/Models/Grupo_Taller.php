<?php
class Grupo_Taller extends TraitConstants{
	
	static $pk = 'id';
	static $table_name = 'grupos_talleres';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'colegio',
			'class_name' => 'Colegio',
			'foreign_key' => 'colegio_id',
		),
	);
	static $has_many = array(
		array(
			'matriculas',
			'class_name' => 'Grupo_Taller_Matricula',
			'foreign_key' => 'taller_id',
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


	function getCategoria(){
		return $this->TALLER_CATEGORIAS[$this->categoria_id];
	}

	function before_destroy(){
		foreach($this->matriculas As $matricula){
			$matricula->delete();
		}
	}
}
