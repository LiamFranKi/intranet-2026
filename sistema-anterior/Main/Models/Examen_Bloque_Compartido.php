<?php
class Examen_Bloque_Compartido extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'examenes_bloques_compartidos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'grupo',
			'class_name' => 'Grupo',
		),
		array( 
			'examen',
			'class_name' => 'Examen_Bloque',
			'foreign_key' => 'examen_id',
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

	function after_create(){
		//sendNotificationForExamenBloque($this);
	}

	function getEstado(){
		if(time() <= strtotime($this->expiracion)) return 'Activo';
		return 'Inactivo';
	}

	function activo(){
		return $this->getEstado() == 'Activo';
	}

	function hasTiempoLimite(){
		return $this->tiempo > 0;
	}
}
