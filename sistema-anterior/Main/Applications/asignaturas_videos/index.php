<?php
class Asignaturas_videosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => '*'
		]);
	}
	
	function index($r){
		$asignaturas_videos = Asignatura_Video::all();
		$this->render(array('asignaturas_videos' => $asignaturas_videos));
	}
	
	function save(){
		$r = -5;
		$this->asignatura_video->set_attributes(array(
			'asignatura_id' => $this->post->asignatura_id,
			'trabajador_id' => $this->USUARIO->personal_id,
			'descripcion' => $this->post->descripcion,
			'enlace' => $this->post->enlace,
			'fecha_hora' => date('Y-m-d H:i:s'),
			'ciclo' => $this->post->ciclo
		));
		
		if($this->asignatura_video->is_valid()){
			$r = $this->asignatura_video->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->asignatura_video->id, 'errors' => $this->asignatura_video->errors->get_all()));
	}

	function borrar($r){
		$r = $this->asignatura_video->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'asignaturas_videos', true);
		$this->asignatura_video = !empty($this->params->id) ? Asignatura_Video::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Asignatura_Video();
		$this->context('asignatura_video', $this->asignatura_video); // set to template
		if(!empty($this->params->asignatura_id)){
			$this->asignatura = Asignatura::find([
				'conditions' => ['sha1(id) = ?', $this->params->asignatura_id]
			]);
			$this->context('asignatura', $this->asignatura);
		}
		
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->asignatura_video);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'asignatura_id' => array(
				'type' => 'hidden',
				'__default' => $this->asignatura->id
			),
			'descripcion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'enlace' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'trabajador_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_hora' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'ciclo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => array_combine(range(1, $this->COLEGIO->total_notas), range(1, $this->COLEGIO->total_notas)),
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
