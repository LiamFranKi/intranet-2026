<?php
class Examenes_bloques_preguntasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => '*'
		]);
	}
	
	function index($r){
		$cursos = $this->examen->getCursos();
		if($this->USUARIO->is('DOCENTE')){
			foreach($cursos As $key => $curso){
				if(!$this->USUARIO->personal->hasCursoAsignadoBloque($curso->id, $this->COLEGIO->anio_activo, $this->examen)){
					unset($cursos[$key]);
				}
			}
		}

		$this->render(array('cursos' => $cursos));
	}
	
	function save(){
		//return print_r($this->post);
		$r = -5;
		$this->examen_bloque_pregunta->set_attributes(array(
			'examen_id' => $this->post->examen_id,
			'curso_id' => $this->post->curso_id,
			'descripcion' => $this->post->descripcion,
		));

		if($this->examen_bloque_pregunta->is_new_record()) 
			$this->examen_bloque_pregunta->orden = time();
		
		if($this->examen_bloque_pregunta->is_valid()){
			$r = $this->examen_bloque_pregunta->save() ? 1 : 0;
			$saved = array();
			if(count($this->post->alternativa) > 0){
				foreach($this->post->alternativa As $x_alternativa){
					$alternativa = isset($x_alternativa['edit']) ? Examen_Bloque_Pregunta_Alternativa::find($x_alternativa['edit']) : new Examen_Bloque_Pregunta_Alternativa();
					
					$alternativa->set_attributes(array(
						'pregunta_id' => $this->examen_bloque_pregunta->id,
						'descripcion' => $x_alternativa['descripcion'],
						'correcta' => isset($x_alternativa['correcta']) ? 'SI' : 'NO'
					));

					$alternativa->save();
					$saved[] = $alternativa->id;
				}
			}

			foreach($this->examen_bloque_pregunta->alternativas As $alternativa){
				if(!in_array($alternativa->id, $saved)){
					$alternativa->delete();
				}
			}
		}
		echo json_encode(array($r, 'id' => $this->examen_bloque_pregunta->id, 'errors' => $this->examen_bloque_pregunta->errors->get_all()));
	}

	function borrar($r){
		$r = $this->examen_bloque_pregunta->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'examenes_bloques_preguntas', true);

		
		if(!empty($this->params->examen_id)){
			$this->examen = Examen_Bloque::find([
				'conditions' => ['sha1(id) = ?', $this->params->examen_id]
			]);

			$this->context('examen', $this->examen, true);
		}

		$this->examen_bloque_pregunta = !empty($this->params->id) ? Examen_Bloque_Pregunta::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Examen_Bloque_Pregunta([
			'examen_id' => $this->examen->id
		]);
		$this->context('examen_bloque_pregunta', $this->examen_bloque_pregunta); // set to template

		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->examen_bloque_pregunta);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		$cursos = $object->examen->getCursos();
		
		if($this->USUARIO->is('DOCENTE')){
			foreach($cursos As $key => $curso){
				if(!$this->USUARIO->personal->hasCursoAsignadoBloque($curso->id, $this->COLEGIO->anio_activo, $this->examen)){
					unset($cursos[$key]);
				}
			}
		}

		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'examen_id' => array(
				'type' => 'hidden',
				'__default' => $this->examen->id
			),
			'curso_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__first' => ['', '-- Seleccione --'],
				'__dataset' => true,
				'__options' => [$cursos, 'id', '$object->nombre']
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
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
