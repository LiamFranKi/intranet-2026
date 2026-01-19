<?php
class BloquesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$bloques = Bloque::all();
		$this->render(array('bloques' => $bloques));
	}
	
	function save(){
		$r = -5;
		$this->bloque->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nombre' => $this->post->nombre,
			'nivel_id' => $this->post->nivel_id,
			'total_notas' => $this->post->total_notas,
		));
		
		if($this->bloque->is_valid()){
			$r = $this->bloque->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->bloque->id, 'errors' => $this->bloque->errors->get_all()));
	}

	function borrar($r){
		$r = $this->bloque->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'bloques', true);
		$this->bloque = !empty($this->params->id) ? Bloque::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Bloque();
		$this->context('bloque', $this->bloque); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->bloque);
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
			'nombre' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nivel_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->niveles, 'id', '$object->nombre'),
			),
			'total_notas' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'min' => 1
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
