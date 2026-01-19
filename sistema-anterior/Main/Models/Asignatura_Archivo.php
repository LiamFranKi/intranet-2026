<?php
class Asignatura_Archivo extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'asignaturas_archivos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Asignatura',
			'class_name' => 'Asignatura',
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

	function getTipo(){
		$info = pathinfo($this->archivo);
		return strtoupper($info['extension']);
	}

	function getPeso(){
		return fileSizeConvert(filesize('./Static/archivos/'.$this->archivo));
	}

	function after_create(){
		Banco_Tema::create([
			'curso_id' => $this->asignatura->curso_id,
			'nivel_id' => $this->asignatura->grupo->nivel_id,
			'grado' => $this->asignatura->grupo->grado, 
			'nombre' => $this->nombre,
			'archivo' => !empty($this->archivo) ? $this->archivo : '',
		]);
	}
}
