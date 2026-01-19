<?php
class Boletas_conceptosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$boletas_conceptos = Boleta_Concepto::all();
		$this->render(array('boletas_conceptos' => $boletas_conceptos));
	}
	
	function save(){
		$r = -5;
		$this->boleta_concepto->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'categoria_id' => $this->post->categoria_id,
			'subcategoria_id' => $this->post->subcategoria_id,
			'descripcion' => $this->post->descripcion,
			'controlar_stock' => $this->post->controlar_stock,
			'stock' => $this->post->stock,
			'descripcion_proveedor' => $this->post->descripcion_proveedor,
			'ocultar' => $this->post->ocultar,
		));
		
		if($this->boleta_concepto->is_valid()){
			$r = $this->boleta_concepto->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->boleta_concepto->id, 'errors' => $this->boleta_concepto->errors->get_all()));
	}

	function borrar($r){
		$r = $this->boleta_concepto->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function do_actualizar_stock(){
		//print_r($this->boleta_concepto);
		$r = 0;
		if($this->post->stock_inicial > 0){
			$ventas = Boleta_Detalle::find(array(
				'select' => 'SUM(cantidad) As total',
				'conditions' => 'boletas.colegio_id = "'.$this->COLEGIO->id.'" AND boletas_detalles.concepto_id="'.$this->post->id.'" AND boletas.estado = "ACTIVO"',
				'joins' => array('boleta')
			));

			$compras = Boleta_Ingreso_Detalle::find(array(
				'select' => 'SUM(cantidad) As total',
				'conditions' => 'boletas_ingresos.colegio_id = "'.$this->COLEGIO->id.'" AND boletas_ingresos_detalles.concepto_id="'.$this->post->id.'" AND boletas_ingresos.estado = "ACTIVO"',
				'joins' => array('ingreso')
			));



			$stock = $this->post->stock_inicial - $ventas->total + $compras->total;
			$this->boleta_concepto->stock = $stock;
			$this->boleta_concepto->stock_inicial = $this->post->stock_inicial;
			$this->boleta_concepto->precio_inicial = $this->post->precio_inicial;
			$r = $this->boleta_concepto->save() ? 1 : 0;
		}

		echo json_encode(array($r));
	}

	
	function __getObjectAndForm(){
		$this->set('__active', 'boletas_conceptos', true);
		$this->boleta_concepto = !empty($this->params->id) ? Boleta_Concepto::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Boleta_Concepto();
		$this->context('boleta_concepto', $this->boleta_concepto); // set to template
		if(in_array($this->params->Action, array('form', 'actualizar_stock'))){
			$this->form = $this->__getForm($this->boleta_concepto);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'colegio_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'categoria_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__dataset' => 'true',
				'__options' => [Boleta_Categoria::all(), 'id', '$object->nombre'],
				'__first' => ['', '-- Seleccione --']
			),
			'subcategoria_id' => array(
				'class' => 'form-control',
				
				'type' => 'select',
				'__first' => ['', '-- Seleccione --']
			),
			'descripcion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'descripcion_proveedor' => array(
				'class' => 'form-control',
				
			),
			'controlar_stock' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'stock' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'stock_inicial' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'precio_inicial' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'ocultar' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'codigo_existencia' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo_existencia' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'codigo_unidad_medida' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'costo_unitario' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
