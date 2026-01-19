<?php
class Asignatura_Criterio extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'asignaturas_criterios';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'asignatura',
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
	
	function getIndicadores(){
		$indicadores = Asignatura_Indicador::find_all_by_criterio_id($this->id);
		return $indicadores;
	}
}
