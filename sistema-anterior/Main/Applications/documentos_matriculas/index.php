<?php
class Documentos_matriculasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$documentos_matriculas = Documento_Matricula::all();
		$this->render(array('documentos_matriculas' => $documentos_matriculas));
	}
	
	function save(){
		$r = -5;
		$this->documento_matricula->set_attributes(array(
			'nombre' => $this->post->nombre,
			//'archivo' => $this->post->archivo,
		));

		$archivo = uploadFile('archivo', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif']);
		if(!is_null($archivo))
			$this->documento_matricula->archivo = $archivo['new_name'];

		if($this->documento_matricula->is_valid()){
			$r = $this->documento_matricula->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->documento_matricula->id, 'errors' => $this->documento_matricula->errors->get_all()));
	}

	function borrar($r){
		$r = $this->documento_matricula->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'documentos_matriculas', true);
		$this->documento_matricula = !empty($this->params->id) ? Documento_Matricula::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Documento_Matricula();
		$this->context('documento_matricula', $this->documento_matricula); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->documento_matricula);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'nombre' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'archivo' => array(
				'class' => 'form-control',
			
				'type' => 'file'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
