<?php
class Prematricula extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'prematriculas';
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


	function before_create(){
		$this->fecha_hora = date('Y-m-d H:i');
		$entrevista_fecha = $this->calcFechaEntrevista($this->fecha_hora);
		$this->entrevista_fecha_hora = $entrevista_fecha.' 15:30:00';
	}

	function calcFechaEntrevista($fecha){
		//$fecha = date('Y-m-d');
		$required = 5; // Viernes
		$diaSemana = date('N');

		if($diaSemana == $required){
			return date('Y-m-d', strtotime($fecha.' +7 days'));
		}

		if($diaSemana < $required){
			$restantes = $required - $diaSemana;
			return date('Y-m-d', strtotime($fecha.' +'.$restantes.' days'));
		}

		if($diaSemana > $required){
			$restantes = 7 - ($diaSemana - $required);
			return date('Y-m-d', strtotime($fecha.' +'.$restantes.' days'));
		}
	}

	function getFechaHoraEntrevista(){
		return date('d-m-Y h:i A', strtotime($this->entrevista_fecha_hora));
	}

	function getFechaEntrevista(){
		return date('d-m-Y', strtotime($this->entrevista_fecha_hora));
	}

	function getHoraEntrevista(){
		return date('h:i A', strtotime($this->entrevista_fecha_hora));
	}

	function getData(){
		return !empty($this->data) ? unserialize(base64_decode($this->data)) : [];
	}
}
