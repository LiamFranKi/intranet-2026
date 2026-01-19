<?php
class Asignaturas_examenes_preguntasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => '*'
		]);
	}
	
	function index($r){
		$asignaturas_examenes_preguntas = Asignatura_Examen_Pregunta::all([
			'conditions' => ['examen_id = ? ', $this->examen->id]
		]);
		$this->render(array('asignaturas_examenes_preguntas' => $asignaturas_examenes_preguntas));
	}
	
	function save(){
		$r = -5;
		$this->asignatura_examen_pregunta->set_attributes(array(
			'examen_id' => $this->post->examen_id,
			'descripcion' => $this->post->tipo == "ALTERNATIVAS" ? $this->post->descripcion : strip_tags($this->post->descripcion),
			//'puntos' => $this->post->puntos,
			//'orden' => $this->post->orden,
			'tipo' => $this->post->tipo,
		));

		if($this->asignatura_examen_pregunta->is_new_record()){
			$this->asignatura_examen_pregunta->orden = time();
		}
		if(isset($this->post->puntos)){
			$this->asignatura_examen_pregunta->puntos = $this->post->puntos;
		}
		
		if($this->asignatura_examen_pregunta->is_valid()){
			$r = $this->asignatura_examen_pregunta->save() ? 1 : 0;

			$saved = array();
			if(count($this->post->alternativa) > 0){
				foreach($this->post->alternativa As $x_alternativa){
					$alternativa = isset($x_alternativa['edit']) ? Asignatura_Examen_Pregunta_Alternativa::find($x_alternativa['edit']) : new Asignatura_Examen_Pregunta_Alternativa();
					
					$alternativa->set_attributes(array(
						'pregunta_id' => $this->asignatura_examen_pregunta->id,
						'descripcion' => $x_alternativa['descripcion'],
						'correcta' => isset($x_alternativa['correcta']) ? 'SI' : 'NO'
					));

					$alternativa->save();
					$saved[] = $alternativa->id;
				}
			}

			foreach($this->asignatura_examen_pregunta->alternativas As $alternativa){
				if(!in_array($alternativa->id, $saved)){
					$alternativa->delete();
				}
			}
		}
		echo json_encode(array($r, 'id' => $this->asignatura_examen_pregunta->id, 'errors' => $this->asignatura_examen_pregunta->errors->get_all()));
	}

	function borrar($r){
		$r = $this->asignatura_examen_pregunta->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'asignaturas_examenes_preguntas', true);
		$this->asignatura_examen_pregunta = !empty($this->params->id) ? Asignatura_Examen_Pregunta::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Asignatura_Examen_Pregunta();
		$this->context('asignatura_examen_pregunta', $this->asignatura_examen_pregunta); // set to template7
		if(!empty($this->params->examen_id)){
			$this->examen = Asignatura_Examen::find([
				'conditions' => ['sha1(id) = ?', $this->params->examen_id]
			]);
			$this->context('examen', $this->examen);
		}
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->asignatura_examen_pregunta);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'examen_id' => array(
				'type' => 'hidden',
				'__default' => $this->examen->id
			),
			'descripcion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'textarea'
			),
			'puntos' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'orden' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'imagen_puzzle' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
