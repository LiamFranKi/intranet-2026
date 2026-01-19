<?php
class Asignaturas_indicadoresApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => '*'
		]);
	}
	
	function index($r){
		$asignaturas_indicadores = $this->criterio->getIndicadores();
		$this->render(array('asignaturas_indicadores' => $asignaturas_indicadores));
	}
	
	function save(){
		$r = -5;
		$this->asignatura_indicador->set_attributes(array(
			'criterio_id' => $this->post->criterio_id,
			'descripcion' => $this->post->descripcion,
			'cuadros' => $this->post->cuadros,
		));
		
		if($this->asignatura_indicador->is_valid()){
			$r = $this->asignatura_indicador->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->asignatura_indicador->id, 'errors' => $this->asignatura_indicador->errors->get_all()));
	}

	function borrar($r){
		$r = $this->asignatura_indicador->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'asignaturas_indicadores', true);
		$this->asignatura_indicador = !empty($this->params->id) ? Asignatura_Indicador::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Asignatura_Indicador();
		$this->context('asignatura_indicador', $this->asignatura_indicador); // set to template
		if(!empty($this->params->criterio_id)){
			$this->criterio = Asignatura_Criterio::find([
				'conditions' => ['sha1(id) = ?', $this->params->criterio_id]
			]);
			$this->context('criterio', $criterio);
		}
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->asignatura_indicador);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'criterio_id' => array(
				'type' => 'hidden',
				'__default' => $this->criterio->id
			),
			'descripcion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'cuadros' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'data-bv-integer' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
