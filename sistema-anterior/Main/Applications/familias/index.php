<?php
class FamiliasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$familias = Familia::all([
			'conditions' => ['sha1(apoderado_id) = ?', $this->get->apoderado_id]
		]);

		$this->render(array('familias' => $familias));
	}
	
	function save(){
		$r = -5;
		$this->familia->set_attributes(array(
			'alumno_id' => $this->post->alumno_id,
			'apoderado_id' => $this->post->apoderado_id,
		));
		
		if($this->familia->is_valid()){
			$r = $this->familia->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->familia->id, 'errors' => $this->familia->errors->get_all()));
	}

	function borrar($r){
		$r = $this->familia->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'familias', true);
		$this->familia = !empty($this->params->id) ? Familia::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Familia();
		$this->context('familia', $this->familia); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->familia);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'alumno_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__first' => ['', '-- Seleccione --']
			),
			'apoderado_id' => array(
				'type' => 'hidden'
			),
			
		);

		if(!empty($this->get->apoderado_id) && $object->is_new_record()){
			$apoderado = Apoderado::find([
				'conditions' => ['sha1(id) = ?', $this->get->apoderado_id]
			]);
			$options['apoderado_id']['value'] = $apoderado->id;
		}
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
