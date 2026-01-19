<?php
class Compendio_Pagina_Bloque_Pregunta_Respuesta extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'compendios_paginas_bloques_preguntas_respuestas';
	static $connection = '';
	
	static $belongs_to = Array();
	static $has_many = Array();
	static $has_one = Array();
	
	static $validates_presence_of = Array();
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = Array();
	static $validates_uniqueness_of = Array();
}
