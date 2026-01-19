<?php
class Boletas_ingresosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){

		$desde = !empty($this->get->desde) ? $this->get->desde : date('Y-m-d');
		$hasta = !empty($this->get->hasta) ? $this->get->hasta : date('Y-m-d');


		$boletas_ingresos = Boleta_Ingreso::all([
			'conditions' => ['fecha between date(?) and date(?)', $desde, $hasta]
		]);
		$this->render(array('boletas_ingresos' => $boletas_ingresos, 'desde' => $desde, 'hasta' => $hasta));
	}
	
	function save(){
		$r = -5;
		$this->boleta_ingreso->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'descripcion' => $this->post->descripcion,
			'fecha' => date('Y-m-d', strtotime($this->post->fecha)),
			'tipo' => $this->post->tipo,
			'serie' => $this->post->serie,
			'numero' => $this->post->numero
		));
		
		if($this->boleta_ingreso->is_valid()){
			$r = $this->boleta_ingreso->save() ? 1 : 0;
			if($r == 1){
				
				$this->boleta_ingreso->reduceStocks();
				Boleta_Ingreso_Detalle::table()->delete(array('boleta_ingreso_id' => $this->boleta_ingreso->id));
				foreach($this->post->categoria_id As $key => $categoria_id){
					Boleta_Ingreso_Detalle::create(array(
						'colegio_id' => $this->COLEGIO->id,
						'boleta_ingreso_id' => $this->boleta_ingreso->id,
						'concepto_id' => $this->post->concepto_id[$key],
						'categoria_id' => $this->post->categoria_id[$key],
						'cantidad' => $this->post->cantidad[$key],
						'precio' => $this->post->precio[$key]
					));
				}
			}
		}
		echo json_encode(array($r, 'id' => $this->boleta_ingreso->id, 'errors' => $this->boleta_ingreso->errors->get_all()));
	}

	function borrar($r){
		$r = $this->boleta_ingreso->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'boletas_ingresos', true);
		$this->boleta_ingreso = !empty($this->params->id) ? Boleta_Ingreso::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Boleta_Ingreso();
		$this->context('boleta_ingreso', $this->boleta_ingreso); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->boleta_ingreso);
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
			'descripcion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha' => array(
				'class' => 'form-control calendar',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d')
			),
			'estado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'serie' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'numero' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
