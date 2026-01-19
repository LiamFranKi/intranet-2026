<?php
class Cash_accountsApplication extends Core\Application{

	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'CAJERO' => 'index',
            'SECRETARIA' => 'index',
		]);
	}

	function index($r){
        if($this->USUARIO->is('ADMINISTRADOR')){
            $cashAccounts = CashAccount::all();
        }else{
            $cashAccounts = CashAccount::all([
                'conditions' => 'privacy = 1' // publico
            ]);
        }
		

		$this->render(array('cashAccounts' => $cashAccounts));
	}

	function save(){
		$r = -5;
		$this->cashAccount->set_attributes(array(
			'name' => $this->post->name,
            'description' => $this->post->description,
            'cash_currency_id' => $this->post->cash_currency_id,
            'cash_account_type_id' => $this->post->cash_account_type_id,
            'privacy' => $this->post->privacy
		));

		if($this->cashAccount->is_valid()){
			$r = $this->cashAccount->save() ? 1 : 0;
		}
		echo json_encode(array('status' => $r, 'id' => $this->cashAccount->id, 'errors' => $this->cashAccount->errors->get_all()));
	}

	function borrar($r){
		$r = $this->cashAccount->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function __getObjectAndForm(){
		$this->set('__active', 'cashAccounts', true);
		$this->cashAccount = !empty($this->params->id) ? CashAccount::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new CashAccount();
		$this->context('cashAccount', $this->cashAccount); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->cashAccount);
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
            'description' => array(
				'class' => 'form-control',
                'type' => 'textarea'
			),
            'cash_currency_id' => [
                'class' => 'form-control',
                'type' => 'select',
                '__first' => ['', '-- Seleccione --'],
                '__dataset' => true,
                '__options' => [CashCurrency::all(), 'id', '$object->name'],
                'data-bv-notempty' => 'true'
            ],
            'cash_account_type_id' => [
                'class' => 'form-control',
                'type' => 'select',
                '__first' => ['', '-- Seleccione --'],
                '__dataset' => true,
                '__options' => [CashAccountType::all(), 'id', '$object->name'],
                'data-bv-notempty' => 'true'
            ],
            'privacy' => [
                'class' => 'form-control',
                'type' => 'select',
                '__first' => ['', '-- Seleccione --'],
                '__options' => TraitConstants::CASH_ACCOUNT_PRIVACY,
                'data-bv-notempty' => 'true'
            ],
		);

		$form = new Form($object, $options);
		return $form;
	}

}
