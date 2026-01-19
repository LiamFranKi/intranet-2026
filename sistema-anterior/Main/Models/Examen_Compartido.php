<?php
class Examen_Compartido extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'examenes_compartidos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'grupo',
			'class_name' => 'Grupo',
		),
		array(
			'examen',
			'class_name' => 'Examen',
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
		if($this->examen->tipo == 'ALUMNOS')
			sendNotificationForExamen($this);
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


	function canDoTest($id){
		if($this->examen->tipo == 'ALUMNOS'){
			$pruebas = Examen_Prueba::count(array(
				'conditions' => 'compartido_id="'.$compartido->id.'" AND matricula_id="'.$id.'"'
			));
		}else{
			$pruebas = Examen_Prueba::count(array(
				'conditions' => 'compartido_id="'.$compartido->id.'" AND personal_id="'.$id.'"'
			));
		}

		if(time() > strtotime($compartido->expiracion)) return false;
		return $pruebas < $compartido->intentos;
	}
}
