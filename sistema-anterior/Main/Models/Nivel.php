<?php
class Nivel extends TraitConstants{
//use Constants;
	
	static $pk = 'id';
	static $table_name = 'niveles';
	static $connection = 'main';
	
	static $belongs_to = Array();
	static $has_many = Array();
	static $has_one = Array();
	
	static $validates_presence_of = array(
		array(
			'colegio_id',
		),
		array(
			'nombre',
		),
		array(
			'abreviatura',
		),
		array(
			'nota_aprobatoria',
		),
		array(
			'tipo_calificacion',
		),
		array(
			'tipo_calificacion_final',
		),
		array(
			'nota_maxima',
		),
		array(
			'nota_minima',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
	
	function getTipoCalificacion(){
		return $this->TIPOS_CALIFICACION[$this->tipo_calificacion];
	}
	
	function getTipoCalificacionFinal(){
		return $this->TIPOS_CALIFICACION_FINAL[$this->tipo_calificacion_final];
	}
	
	function calificacionCuantitativa(){
		return ($this->tipo_calificacion == 1);
	}
	
	function calificacionCualitativa(){
		return ($this->tipo_calificacion == 0);
	}
	
	function calificacionPorcentual(){
		return ($this->tipo_calificacion_final == 1);
	}
	
	function calificacionPromedio(){
		return ($this->tipo_calificacion_final == 0);
	}

    function isInicial(){
        return $this->id == 1;
    }
}
