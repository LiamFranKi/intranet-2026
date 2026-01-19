<?php
class Pago_Historial extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'pagos_historial';
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

	function getEstado(){
		return $this->impreso == 'SI' ? 'BOLETAS IMPRESAS' : 'IMPRESION PENDIENTE';
	}

	function getPagos(){
		$pagos = array();
		foreach($this->getData() As $data){
			$pago = Pago::find_by_id($data);
			if($pago) $pagos[] = $pago;
		}
		return $pagos;
	}

	function getData(){
		$data = empty($this->data) ? array() : unserialize(base64_decode($this->data));
		return $data;
	}
}
