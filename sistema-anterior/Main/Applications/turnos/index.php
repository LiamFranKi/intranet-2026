<?php
class TurnosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$turnos = Turno::all();
		$this->render(array('turnos' => $turnos));
	}
	
	function save(){
		$r = -5;
		$this->turno->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nombre' => $this->post->nombre,
			'abreviatura' => $this->post->abreviatura,
		));
		
		if($this->turno->is_valid()){
			$r = $this->turno->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->turno->id, 'errors' => $this->turno->errors->get_all()));
	}

	function borrar($r){
		$r = $this->turno->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'turnos', true);
		$this->turno = !empty($this->params->id) ? Turno::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Turno();
		$this->context('turno', $this->turno); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->turno);
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
			'abreviatura' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
