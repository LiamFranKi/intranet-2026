<?php
class Administrador extends TraitConstants{
//use Constants;

	static $pk = 'id';
	static $table_name = 'administradores';
	static $connection = 'admin';
	
	static $belongs_to = Array();
	static $has_many = Array();
	static $has_one = Array();
	
	static $validates_presence_of = array(
		array(
			'nombres',
		),
		array(
			'apellidos',
		),
		array(
			'dni',
		),
		array(
			'usuario',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array(
		array(
			'usuario',
			'message' => 'El nombre de usuario no está disponible',
		),
	);
	
	function getFullName(){
		return mb_strtoupper($this->apellidos, 'utf-8').', '.ucwords(mb_strtolower($this->nombres, 'utf-8'));
	}
	
	function getTipo(){
		return $this->TIPOS_ADMINISTRADOR[$this->tipo];
	}
	
	function getEstado(){
		return $this->ESTADOS_ADMINISTRADOR[$this->estado];
	}

}
