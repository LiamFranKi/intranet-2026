<?php
class Cash_currenciesApplication extends Core\Application{

	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}

	function index($r){
		$cashCurrencies = CashCurrency::all();
		$this->render(array('cashCurrencies' => $cashCurrencies));
	}

	function save(){
		$r = -5;
		$this->cashCurrency->set_attributes(array(
			'name' => $this->post->name
		));

		if($this->cashCurrency->is_valid()){
			$r = $this->cashCurrency->save() ? 1 : 0;
		}
		echo json_encode(array('status' => $r, 'id' => $this->cashCurrency->id, 'errors' => $this->cashCurrency->errors->get_all()));
	}

	function borrar($r){
		$r = $this->cashCurrency->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function __getObjectAndForm(){
		$this->set('__active', 'cashCurrencies', true);
		$this->cashCurrency = !empty($this->params->id) ? CashCurrency::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new CashCurrency();
		$this->context('cashCurrency', $this->cashCurrency); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->cashCurrency);
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
