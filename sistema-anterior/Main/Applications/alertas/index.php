<?php
class AlertasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$alertas = Alerta::all();
		$this->render(array('alertas' => $alertas));
	}
	
	function save(){
		$r = -5;
		$this->alerta->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nombre' => $this->post->nombre,
			'contenido' => $this->post->contenido,
			'tipo' => $this->post->tipo,
			'dias' => $this->post->dias,
			'position' => $this->post->position,
			'cantidad' => $this->post->cantidad,
			'estado' => $this->post->estado,
			'asunto' => $this->post->asunto,
			'email_remitente' => $this->post->email_remitente,
		));
		
		if($this->alerta->is_valid()){
			$r = $this->alerta->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->alerta->id, 'errors' => $this->alerta->errors->get_all()));
	}

	function borrar($r){
		$r = $this->alerta->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

		
	function __getObjectAndForm(){
		$this->set('__active', 'alertas', true);
		$this->alerta = !empty($this->params->id) ? Alerta::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Alerta();
		$this->context('alerta', $this->alerta); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->alerta);
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
			'contenido' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'dias' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'position' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'cantidad' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'senders' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'asunto' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'email_remitente' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
