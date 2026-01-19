<?php
class Grupos_horariosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$grupos_horarios = Grupo_Horario::all();
		$this->render(array('grupos_horarios' => $grupos_horarios));
	}
	
	function save(){
		$r = -5;
		$this->grupo_horario->set_attributes(array(
			'grupo_id' => !isset($this->post->grupo_id) ? 0 : $this->post->grupo_id,
			'asignatura_id' => !isset($this->post->asignatura_id) ? 0 : $this->post->asignatura_id,
			'personal_id' => !isset($this->post->personal_id) ? 0 : $this->post->personal_id,
			'dia' => $this->post->dia,
			'hora_inicio' => $this->post->hora_inicio,
			'hora_final' => $this->post->hora_final,
			'descripcion' => $this->post->descripcion,
			'tipo' => $this->post->tipo,
			'anio' => date('Y')
		));
		
		if($this->grupo_horario->is_valid()){
			$r = $this->grupo_horario->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->grupo_horario->id, 'errors' => $this->grupo_horario->errors->get_all()));
	}

	function borrar($r){
		$r = $this->grupo_horario->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'grupos_horarios', true);
		$this->grupo_horario = !empty($this->params->id) ? Grupo_Horario::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Grupo_Horario();
		$this->context('grupo_horario', $this->grupo_horario); // set to template
		if(!empty($this->params->grupo_id)){

			$this->grupo = Grupo::find([
				'conditions' => ['sha1(id) = ?', $this->params->grupo_id]
			]);

			$this->context('grupo', $this->grupo);
		}

		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->grupo_horario);
			$this->context('form', $this->form);
		}
	}
	
	function form(){
		$horario = new Grupo_Horario(array(
			'grupo_id' => $this->grupo->id,
			'tipo' => 'GRUPO'
		));
		$form = $this->__getForm($horario);
		$this->render(array('horario' => $horario, 'form' => $form));
	}

	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'grupo_id' => array(
				'type' => 'hidden',
				'__default' => $this->grupo->id
			),
			
			'personal_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'descripcion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'dia' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => $object->DIAS,
			),
			'hora_inicio' => array(
				'class' => 'form-control hora',
				'data-bv-notempty' => 'true'
			),
			'hora_final' => array(
				'class' => 'form-control hora',
				'data-bv-notempty' => 'true'
			),
			'tipo' => array(
				'type' => 'hidden'
			),
			'anio' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);

		if($object->grupo){
			$options['asignatura_id'] = array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($object->grupo->getAsignaturas(), 'id', function($o){return $o->curso->nombre;}),
				
				'data-bv-notempty' => 'true',
				'class' => 'form-control'
			);
		}
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
