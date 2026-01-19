<?php
class Topico_atencionesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index(){

		$this->crystal->load('Form:*');
		$form = new Form(null, $this->COLEGIO->searchGrupoForm((array) $this->get));

		$grupo = $this->COLEGIO->getGrupo($this->get);
		if($grupo) $matriculas = $grupo->getMatriculas();
		

		$this->render(array('matriculas' => $matriculas, 'form' => $form));
	}

	function alumno($r){
		$topico_atenciones = Topico_Atencion::all([
			'conditions' => 'alumno_id = "'.$this->get->alumno_id.'" AND tipo = "'.$this->get->tipo.'"'
		]);
		$this->render(array('topico_atenciones' => $topico_atenciones));
	}
	
	function save(){
		$r = -5;
		$this->topico_atencion->set_attributes(array(
			'alumno_id' => $this->post->alumno_id,
			'fecha_hora' => $this->post->fecha_hora,
			'motivo' => $this->post->motivo,
			'tratamiento' => $this->post->tratamiento,
			'personal_id' => $this->USUARIO->personal_id,
			'tipo' => $this->post->tipo,
		));
		
		if($this->topico_atencion->is_valid()){
			$r = $this->topico_atencion->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->topico_atencion->id, 'errors' => $this->topico_atencion->errors->get_all()));
	}

	function borrar($r){
		$r = $this->topico_atencion->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'topico_atenciones', true);
		$this->topico_atencion = !empty($this->params->id) ? Topico_Atencion::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Topico_Atencion();
		$this->context('topico_atencion', $this->topico_atencion); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->topico_atencion);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'alumno_id' => array(
				'type' => 'hidden',
				'__default' => $this->get->alumno_id,
			),
			'fecha_hora' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d H:i:s'),
				'readonly' => true
			),
			'motivo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'style' => 'height: 70px'
			),
			'tratamiento' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'style' => 'height: 70px'
			),
	
			'tipo' => array(
				'type' => 'hidden',
				'__default' => $this->get->tipo
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
