<?php
class Cash_account_typesApplication extends Core\Application{

	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}

	function index($r){
		$cashAccountTypes = CashAccountType::all();
		$this->render(array('cashAccountTypes' => $cashAccountTypes));
	}

	function save(){
		$r = -5;
		$this->cashAccountType->set_attributes(array(
			'name' => $this->post->name
		));

		if($this->cashAccountType->is_valid()){
			$r = $this->cashAccountType->save() ? 1 : 0;
		}
		echo json_encode(array('status' => $r, 'id' => $this->cashAccountType->id, 'errors' => $this->cashAccountType->errors->get_all()));
	}

	function borrar($r){
		$r = $this->cashAccountType->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function __getObjectAndForm(){
		$this->set('__active', 'cashAccountTypes', true);
		$this->cashAccountType = !empty($this->params->id) ? CashAccountType::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new CashAccountType();
		$this->context('cashAccountType', $this->cashAccountType); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->cashAccountType);
			$this->context('form', $this->form);
		}
	}

	private function __getForm($object){
		$this->crystal->load('Form:*');

		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'name' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
		);

		$form = new Form($object, $options);
		return $form;
	}

}
