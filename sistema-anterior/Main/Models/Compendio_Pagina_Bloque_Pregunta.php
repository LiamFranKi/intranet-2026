<?php
class Compendio_Pagina_Bloque_Pregunta extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'compendios_paginas_bloques_preguntas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'bloque',
			'class_name' => 'Compendio_Pagina_Bloque',
			'foreign_key' => 'bloque_id',
		),
	);
	static $has_many = array(
		array(
			'alternativas',
			'class_name' => 'Compendio_Pagina_Bloque_Pregunta_Alternativa',
			'foreign_key' => 'pregunta_id',
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

	function getAlternativas(){
		$alternativas = Compendio_Pagina_Bloque_Pregunta_Alternativa::find_all_by_pregunta_id($this->id, array(
			'order' => 'id ASC'
		));
		return $alternativas;
	}

	function getRespuesta($matricula_id){
		$respuesta = Compendio_Pagina_Bloque_Pregunta_Respuesta::find_by_matricula_id_and_pregunta_id($matricula_id, $this->id);
		return $respuesta;
	}

	function getRespuestaDocente($personal_id){
		$respuesta = Compendio_Pagina_Bloque_Pregunta_Respuesta::find_by_personal_id_and_pregunta_id($personal_id, $this->id);
		return $respuesta;
	}
}
