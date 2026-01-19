<?php
class Trabajador extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'trabajadores';
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

	function getFullName(){
		return mb_strtoupper($this->apellidos, 'utf-8').', '.ucwords(mb_strtolower($this->nombres, 'utf-8'));
	}

	function get($tipo, $fecha){
		$fecha = date('Y-m-d', strtotime($fecha));
		$asistencia = Trabajador_Asistencia::find(array(
			'conditions' => 'tipo="'.$tipo.'" AND fecha="'.$fecha.'"'
		));
		return $asistencia->hora_real;
	}
}
