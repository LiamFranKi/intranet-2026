<?php
class Cash_account_flowsApplication extends Core\Application{

	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'CAJERO' => '*',
            'SECRETARIA' => '*',
		]);
	}

	function index($r){
        
		$cashAccountFlows = CashAccountFlow::all([
            'conditions' => ['cash_account_id = ?', $this->cashAccount->id],
            'order' => 'date DESC, time DESC'
        ]);
		$this->render(array('cashAccountFlows' => $cashAccountFlows));
	}

	function save(){
		$r = -5;
		$this->cashAccountFlow->set_attributes(array(
			'description' => $this->post->description,
            'entry' => $this->post->entry,
            'date' => $this->post->date,
            'time' => $this->post->time,
            'type' => $this->post->type,
            'amount' => $this->post->amount,
            'cash_account_id' => $this->post->cash_account_id,

		));

		if($this->cashAccountFlow->is_valid()){
			$r = $this->cashAccountFlow->save() ? 1 : 0;
		}
		echo json_encode(array('status' => $r, 'id' => $this->cashAccountFlow->id, 'errors' => $this->cashAccountFlow->errors->get_all()));
	}

	function borrar($r){
		$r = $this->cashAccountFlow->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function __getObjectAndForm(){
		$this->set('__active', 'cashAccountFlows', true);
		$this->cashAccountFlow = !empty($this->params->id) ? CashAccountFlow::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new CashAccountFlow();
		$this->context('cashAccountFlow', $this->cashAccountFlow); // set to template

        if($this->params->cashAccountId){
            $this->cashAccount = CashAccount::find(['conditions' => 'sha1(id) = "'.$this->params->cashAccountId.'"']);
            if(!is_null($this->cashAccount)){
                $this->cashAccountFlow->cash_account_id = $this->cashAccount->id;
                $this->context('cashAccount', $this->cashAccount, true);
            }
        }

		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->cashAccountFlow);
			$this->context('form', $this->form);
		}
	}

	private function __getForm($object){
		$this->crystal->load('Form:*');

		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'description' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
                'type' => 'text'
			),
            'entry' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
                //'type' => 'text'
			),

            'date' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
                'type' => 'date',
                '__default' => date('Y-m-d')
			),
            'time' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
                'type' => 'time',
                '__default' => date('H:i')
			),
            'type' => [
                'class' => 'form-control',
                'data-bv-notempty' => 'true',
                'type' => 'select',
                '__options' => CashAccountFlow::TYPES
            ],
            'amount' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
                'data-bv-numeric' => 'true'
			),
            'cash_account_id' => [
                'type' => 'hidden',
            ]
		); 

		$form = new Form($object, $options);
		return $form;
	}

}
