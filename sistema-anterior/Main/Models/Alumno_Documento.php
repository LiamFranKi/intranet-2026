<?php
class Alumno_Documento extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'alumnos_documentos';
	static $connection = 'main';
	
	static $belongs_to = array(
		array(
			'alumno',
			'class_name' => 'Alumno',
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
	
	function getPathArchivo(){
		if(!empty($this->archivo) && file_exists($this->staticDirectory.'/Static/Documentos/'.$this->archivo)){
			return $this->staticDirectory.'/Static/Documentos/'.$this->archivo;
		}
	}
	
	function after_destroy(){
		$path = $this->getPathArchivo();
		@unlink($path);
	}
}
