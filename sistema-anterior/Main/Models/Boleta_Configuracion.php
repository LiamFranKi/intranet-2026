<?php
class Boleta_Configuracion extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'boletas_configuracion';
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

	function getCurrentSerie($ceros = 3){
		return str_pad($this->serie, $ceros, '0', STR_PAD_LEFT);
	}

	function getCurrentNumero($ceros = 8){
		return str_pad($this->numero, $ceros, '0', STR_PAD_LEFT);
	}

	function getCurrentSerieMora($ceros = 3){
		return str_pad($this->serie_mora, $ceros, '0', STR_PAD_LEFT);
	}

	function getCurrentNumeroMora($ceros = 8){
		return str_pad($this->numero_mora, $ceros, '0', STR_PAD_LEFT);
	}
}
