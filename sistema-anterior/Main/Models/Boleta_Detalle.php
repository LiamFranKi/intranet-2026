<?php
class Boleta_Detalle extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'boletas_detalles';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'categoria',
			'class_name' => 'Boleta_Categoria',
			'foreign_key' => 'categoria_id',
		),
		array(
			'concepto',
			'class_name' => 'Boleta_Concepto',
			'foreign_key' => 'concepto_id',
		),
		array(
			'boleta',
			'class_name' => 'Boleta',
			'foreign_key' => 'boleta_id',
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

	function getImporte(){
		$importe = $this->cantidad * $this->getPrecio();
		return $importe;
	}	

	function getImporteGravado(){
		return round($this->getImporte() / 1.18, 2);
		//return $this->getImporte() - $this->getIGV();
	}

	

	public function resetStock(){
		// REGRESA LA CANTIDAD
		if(!$this->is_new_record()){
			if($this->concepto && $this->concepto->controlarStock()){
				$this->concepto->stock += $this->cantidad;
				$this->concepto->save();
			}
		}
	}

	public function reduceStock(){
		if($this->concepto && $this->concepto->controlarStock()){
			$this->concepto->stock -= $this->cantidad;
			$this->concepto->save(); 
		}
	}

	public function after_save(){
		$this->reduceStock();
	}

	function getPrecio(){
		$precio = $this->precio;
		$comision = $this->boleta->getComisionDetalle();

		$precio += $comision > 0 ? round($comision / $this->cantidad, 2) : 0;
		
		return $precio;
	}

	function getPrecioGravado(){
		return round($this->getPrecio() / 1.18, 2);
		//return $this->precio - $this->getIGVUnitario();
	}

	function getIGV(){
		return round($this->getImporte() - $this->getImporteGravado(), 2);
		//return $this->getImporte() * 0.18;
	}

	function getIGVUnitario(){
		return round($this->getPrecio() - $this->getPrecioGravado(), 2);
		//return $this->precio * 0.18;
	}


}
