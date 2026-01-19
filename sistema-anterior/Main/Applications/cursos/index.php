<?php
class CursosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$cursos = Curso::all();
		$this->render(array('cursos' => $cursos));
	}
	
	function save(){
		$r = -5;
		$this->curso->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nivel_id' => $this->post->nivel_id,
			'nombre' => $this->post->nombre,
			'abreviatura' => $this->post->abreviatura,
			'descripcion' => $this->post->descripcion,
			'orden' => time(),
			//'examen_mensual' => $this->post->examen_mensual, 
			//'peso_examen_mensual' => $this->post->peso_examen_mensual,
			'link_libro' => $this->post->link_libro
		));

		$imagen = uploadFile('imagen', ['jpg', 'jpeg', 'png'], './Static/Archivos');
		if(!is_null($imagen)){
			$this->curso->imagen = $imagen['new_name'];
		}
		
		if($this->curso->is_valid()){
			$r = $this->curso->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->curso->id, 'errors' => $this->curso->errors->get_all()));
	}

	function borrar($r){
		$r = $this->curso->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'cursos', true);
		$this->curso = !empty($this->params->id) ? Curso::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Curso();
		$this->context('curso', $this->curso); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->curso);
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
			'nivel_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->getNiveles(), 'id', '$object->nombre'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nombre' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'abreviatura' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'descripcion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'orden' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'examen_mensual' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'peso_examen_mensual' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'imagen' => array(
				'class' => 'form-control',
				'type' => 'file'
			),
			'link_libro' => [
				'type' => 'text',
				'class' => 'form-control'
			]
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
