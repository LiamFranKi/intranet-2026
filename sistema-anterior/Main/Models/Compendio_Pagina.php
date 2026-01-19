<?php
class Compendio_Pagina extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'compendios_paginas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'compendio',
			'class_name' => 'Compendio',
			'foreign_key' => 'compendio_id',
		),
	);
	static $has_many = array(
		array(
			'bloques',
			'class_name' => 'Compendio_Pagina_Bloque',
			'foreign_key' => 'pagina_id',
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

	function getBloques(){
		$bloques = Compendio_Pagina_Bloque::find_all_by_pagina_id($this->id, array(
			'order' => 'id ASC'
		));
		return $bloques;
	}

	function before_destroy(){
		Compendio_Pagina_Bloque::table()->delete(array(
			'pagina_id' => $this->id
		));
	}
}
