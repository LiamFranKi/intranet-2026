<?php
class Compendio_Pagina_Bloque extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'compendios_paginas_bloques';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'pagina',
			'class_name' => 'Compendio_Pagina',
			'foreign_key' => 'pagina_id',
		),
	);
	static $has_many = array(
		array(
			'preguntas',
			'class_name' => 'Compendio_Pagina_Bloque_Pregunta',
			'foreign_key' => 'bloque_id',
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

	function getPreguntas(){
		$preguntas = Compendio_Pagina_Bloque_Pregunta::find_all_by_bloque_id($this->id, array(
			'order' => 'id ASC'
		));
		return $preguntas;
	}

	function isDOC(){
		$extension = explode('.', $this->archivo);
		return ($extension[1] == 'doc' || $extension[1] == 'docx');
	}

	function isPDF(){
		$extension = explode('.', $this->archivo);
		return ($extension[1] == 'pdf');
	}
}
