<?php
class CostosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$costos = $this->COLEGIO->getCostos();
		$this->render(array('costos' => $costos));
	}
	
	function save(){
		$r = -5;
		$this->costo->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'descripcion' => $this->post->descripcion,
			'matricula' => $this->post->matricula,
			'pension' => $this->post->pension,
			'agenda' => $this->post->agenda
		));
		
		if($this->costo->is_valid()){
			$r = $this->costo->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->costo->id, 'errors' => $this->costo->errors->get_all()));
	}

	function borrar($r){
		$r = $this->costo->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'costos', true);
		$this->costo = !empty($this->params->id) ? Costo::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Costo();
		$this->context('costo', $this->costo); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->costo);
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
			'matricula' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'pension' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'agenda' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
