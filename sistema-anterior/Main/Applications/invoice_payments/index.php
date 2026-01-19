<?php
class Invoice_paymentsApplication extends Core\Application{

	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}

	function index($r){
		$invoicePayments = InvoicePayment::all([
            'conditions' => ['invoice_id = ?', $this->invoice->id],
        ]);
		$this->render(array('invoicePayments' => $invoicePayments));
	}

	function save(){
		$r = -5;
		$this->invoicePayment->set_attributes(array(
			'amount' => $this->params->amount,
            'comments' => $this->params->comments,
            'invoice_id' => $this->params->invoice_id,
		));

		if($this->invoicePayment->is_valid()){
			$r = $this->invoicePayment->save() ? 1 : 0;
		}
		echo json_encode(array('status' => $r, 'id' => $this->invoicePayment->id, 'errors' => $this->invoicePayment->errors->get_all()));
	}

	function borrar($r){
		$r = $this->invoicePayment->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function __getObjectAndForm(){
		$this->set('__active', 'invoicePayments', true);
		$this->invoicePayment = !empty($this->params->id) ? InvoicePayment::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new InvoicePayment();
		$this->context('invoicePayment', $this->invoicePayment); // set to template

        if(!empty($this->params->invoice_id)){
            $this->invoice = Boleta::find(['conditions' => 'sha1(id) = "'.$this->params->invoice_id.'"']);
            $this->set('invoice', $this->invoice, true);
            $this->invoicePayment->invoice_id = $this->invoice->id;
        }

		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->invoicePayment);
			$this->context('form', $this->form);
		}
	}

	private function __getForm($object){
		$this->crystal->load('Form:*');

		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
            'invoice_id' => array(
                'type' => 'hidden',
            ),
			'amount' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
            'comments' => array(
				'class' => 'form-control',
                'type'=> 'textarea',
			),
		);

		$form = new Form($object, $options);
		return $form;
	}

}
