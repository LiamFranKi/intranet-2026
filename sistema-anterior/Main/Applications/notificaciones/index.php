<?php
class NotificacionesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$notificaciones = Notificacion::all([
			'conditions' => 'para = "TODOS"'
		]);
		$this->render(array('notificaciones' => $notificaciones));
	}
	
	function save(){
		$r = -5;
		$this->notificacion->set_attributes(array(
			'para' => 'TODOS',
			'usuario_id' => $this->USUARIO->id,
			
			'asunto' => $this->post->asunto,
			'fecha_hora' => date('Y-m-d H:i:s'),
			'estado' => "NO ENVIADO",
			'contenido' => $this->post->contenido,
		));
		
		if($this->notificacion->is_valid()){
			$r = $this->notificacion->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->notificacion->id, 'errors' => $this->notificacion->errors->get_all()));
	}

	function borrar($r){
		$r = $this->notificacion->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'notificaciones', true);
		$this->notificacion = !empty($this->params->id) ? Notificacion::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Notificacion();
		$this->context('notificacion', $this->notificacion); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->notificacion);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'para' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'usuario_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'destinatario_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'asunto' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_hora' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'contenido' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
