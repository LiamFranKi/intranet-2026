<?php
class Banco_temasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$banco_temas = Banco_Tema::all();
		$this->render(array('banco_temas' => $banco_temas));
	}
	
	function save(){
		$r = -5;
		$this->banco_tema->set_attributes(array(
			'nombre' => $this->post->nombre,
			'curso_id' => $this->post->curso_id,
			'detalles' => $this->post->detalles,
			'nivel_id' => $this->post->nivel_id,
			'grado' => $this->post->grado
		));
		
		

		$archivo = uploadFile('archivo', ['pdf', 'doc', 'docx'], './Static/Archivos');
		if(!is_null($archivo)){
			$this->banco_tema->archivo = $archivo['new_name'];
		}

		if($this->banco_tema->is_valid()){
			$r = $this->banco_tema->save() ? 1 : 0;
		}

		echo json_encode(array($r, 'id' => $this->banco_tema->id, 'errors' => $this->banco_tema->errors->get_all()));
	}

	function borrar($r){
		$r = $this->banco_tema->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'banco_temas', true);
		$this->banco_tema = !empty($this->params->id) ? Banco_Tema::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Banco_Tema();
		$this->context('banco_tema', $this->banco_tema); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->banco_tema);
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
			'curso_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				//'__dataset' => 'true',
				//'__options' => [Curso::all(['order' => 'nivel_id ASC, nombre ASC']), 'id', '$object->nombre." - ".$object->nivel->nombre'],
				'__first' => ['', '-- Seleccione --']
			),
			'nivel_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__dataset' => 'true',
				'__options' => [Nivel::all(), 'id', '$object->nombre'],
				'__first' => ['', '-- Seleccione --']
			),
			'detalles' => array(
				'class' => 'form-control',
				//'data-bv-notempty' => 'true',
				'style' => 'height: 150px',
				'type' => 'textarea'
			),
			'grado' => [
				'type' => 'select',
				'class' => 'form-control',
				'__first' => ['', '-- Seleccione --']
			],
			'archivo' => [
				'type' => 'file',
				'class' => 'form-control'
			]
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
