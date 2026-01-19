<?php
class Pago_Comedor extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'pagos_comedor';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Matricula',
			'class_name' => 'Matricula',
			'foreign_key' => 'matricula_id',
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

	function setFechas($fechas){
		$this->rollback();
		$fechas = explode(',', $fechas);
		if(count($fechas) > 0){
			foreach($fechas As $fecha){
				Pago_Comedor_Fecha::create(array(
					'pago_id' => $this->id,
					'fecha' => $fecha
				));
			}
		}
	}

	function getFechas(){
		$xfechas = Pago_Comedor_Fecha::find_all_by_pago_id($this->id);
		$fechas = [];
		foreach($xfechas As $fecha){
			$fechas[] = $fecha->fecha;
		}
		return $fechas;
	}



	function rollback(){
		Pago_Comedor_Fecha::table()->delete(array('pago_id' => $this->id));
	}
}
