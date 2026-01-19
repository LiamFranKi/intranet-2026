<?php
class Examenes_bloquesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'ALUMNO' => 'alumno, iniciar_prueba, prueba, save_respuestas, finalizar_prueba, resultados_respuestas',
			'DOCENTE' => 'docente'
		]);
	}
	
	function index($r){
		$examenes_bloques = Examen_Bloque::all([
			'conditions' => 'archivado = "NO"'
		]);

		$archivados = Examen_Bloque::all(array(
			'conditions' => 'archivado = "SI"'
		));

		$this->render(array('examenes_bloques' => $examenes_bloques, 'archivados' => $archivados));
	}

	function docente(){
		$examenes = Examen_Bloque::all(array(
			'conditions' => 'colegio_id="'.$this->COLEGIO->id.'"  AND archivado = "NO"'
		));

		foreach($examenes As $key => $examen){
			$cursos = $examen->getCursos();
			
			$allow = false;
			foreach($cursos As $curso){
				if($this->USUARIO->personal->hasCursoAsignadoBloque($curso->id, $this->COLEGIO->anio_activo, $examen)){
					$allow = true;
				}
			}

			if(!$allow){

				unset($examenes[$key]);
			}
		}

		$this->render(['examenes_bloques' => $examenes]);
	}

	function alumno(){
		$matricula = $this->USUARIO->alumno->getMatriculaByAnio($this->COLEGIO->anio_activo);
		if($matricula){
			$compartidos = $matricula->getExamenesBloquesCompartidos();
			$archivados = $matricula->getExamenesBloquesCompartidos('SI');
		}

		$this->render(array('compartidos' => $compartidos, 'archivados' => $archivados, 'matricula' => $matricula));
	}
	
	function save(){
		$r = -5;
		$this->examen_bloque->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'personal_id' => $this->USUARIO->personal_id,
			'titulo' => $this->post->titulo,

			'puntos_correcta' => $this->post->puntos_correcta,
			'bloque_id' => $this->post->bloque_id,
			'cursos' => serialize($this->post->cursos),
			//'total_preguntas' => $this->post->total_preguntas,
			'estado' => $this->post->estado,
			'grado' => $this->post->grado,
			'preguntas_max' => $this->post->preguntas_max
		));
		
		if($this->examen_bloque->is_valid()){
			$r = $this->examen_bloque->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->examen_bloque->id, 'errors' => $this->examen_bloque->errors->get_all()));
	}

	function borrar($r){
		$r = $this->examen_bloque->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'examenes_bloques', true);
		$this->examen_bloque = !empty($this->params->id) ? Examen_Bloque::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Examen_Bloque();
		$this->context('examen_bloque', $this->examen_bloque); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->examen_bloque);
			$this->context('form', $this->form);
		}
	}

	function archivar(){
		$this->examen_bloque->archivado = 'SI';
		$r = $this->examen_bloque->save() ? 1: 0;
		echo json_encode(array($r));
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'colegio_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'personal_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'titulo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'puntos_correcta' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'bloque_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__dataset' => true,
				'__options' => array(Bloque::all(array('order' => 'nivel_id ASC, nombre ASC')), 'id', '$object->getNombre()')
			),
			'cursos' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'name' => 'cursos[]',
				'multiple' => 'true',
				'style' => 'height: 80px'
			),
			'grado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => $this->COLEGIO->GRADOS,
				'__first' => array('', '-- Seleccione --'),
			),
			'total_preguntas' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'preguntas' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'archivo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'archivado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'preguntas_max' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	

	/** PRUEBAS **/
	function iniciar_prueba(){
		
		if($this->post->password != $this->COLEGIO->clave_bloques){
			echo json_encode(array(-5));
			return false;
		}

		// check if can do the test
		$matricula = Matricula::find($this->post->matricula_id);
		$compartido = Examen_Bloque_Compartido::find($this->post->compartido_id);
		$r = -2;
		if($matricula->canDoTestBloque($compartido)){
			$currentTime = time();
			$currentDate = date('Y-m-d H:i:s');

			$expiracion = $compartido->tiempo > 0 ? ($currentTime + ($compartido->tiempo * 60)) : $currentTime;

			$prueba = new Examen_Bloque_Prueba(array(
				'compartido_id' => $compartido->id,
				'matricula_id' => $matricula->id,
				'fecha_hora' => $currentDate,
				'expiracion' => date('Y-m-d H:i:s', $expiracion),
				'estado' => 'ACTIVO',
				'token' => getToken()
			));
			$r = $prueba->save() ? 1 : 0;
		}

		echo json_encode(array($r, 'prueba_id' => sha1($prueba->id), 'token' => $prueba->token, 'time' => strtotime($prueba->fecha_hora)));
	}


	function prueba(){
		//$prueba = Examen_Bloque_Prueba::find_by_id_and_token_and_fecha_hora($this->params->id, $this->get->token);
		$prueba = Examen_Bloque_Prueba::find([
			'conditions' => ['sha1(id) = ? AND token = ?', $this->params->id, $this->get->token]
		]);

		if(!$prueba || $prueba->checkFinished()){
			return $this->redirect('/examenes_bloques/alumno');
		}

		$compartido = $prueba->compartido;
		$examen = $compartido->examen;
		
		$respuestas = $prueba->getRespuestas();

		$remainingTime = $prueba->getRemainingTime();

		$this->render(array('prueba' => $prueba, 'respuestas' => $respuestas, 'compartido' => $compartido, 'examen' => $examen, 'preguntas' => $preguntas, 'remainingTime' => $remainingTime));
	}

	function save_respuestas(){
		$prueba = Examen_Bloque_Prueba::find_by_id_and_token($this->params->id, $this->post->token);
		$r = $prueba->setRespuestas($this->post->respuestas) ? 1 : 0;

		echo json_encode([$r]);
	}

	function finalizar_prueba(){
		$prueba = Examen_Bloque_Prueba::find_by_id_and_token($this->params->id, $this->post->token);
		$r = 0;
		if($prueba){
			$r = $prueba->setToFinished() ? 1 : 0;
		}

		echo json_encode(array($r));
	}

	function resultados_respuestas(){
		$prueba = Examen_Bloque_Prueba::find([
			'conditions' => ['sha1(id) = ?', $this->params->id]
		]);

		$compartido = $prueba->compartido;
		$examen = $compartido->examen;
		$preguntas = $examen->getPreguntas();
		$respuestas = $prueba->getRespuestas();

		if($examen->total_preguntas == 0)
			return $this->render(array('prueba' => $prueba, 'compartido' => $compartido, 'examen' => $examen, 'preguntas' => $preguntas, 'respuestas' => $respuestas));
		
		// MUESTRA LA VENTANA VIEJA
		if($examen->total_preguntas > 0){
			$preguntas = unserialize($examen->preguntas);
			return $this->render('resultados_respuestas_old', array('prueba' => $prueba, 'compartido' => $compartido, 'examen' => $examen, 'preguntas' => $preguntas, 'respuestas' => $respuestas));
		}
			
	}

	function resultados(){
		$compartido = Examen_Bloque_Compartido::find([
			'conditions' => ['sha1(id) = ?', $this->get->compartido_id]
		]);
		$examen = $compartido->examen;
		$grupo = $compartido->grupo;
		$matriculas = $grupo->getMatriculas();
		$cursos = $examen->getCursos();
		
		$this->render(array('matriculas' => $matriculas, 'compartido' => $compartido, 'examen' => $examen, 'cursos' => $cursos));
	}

	function editar_resultados(){
		$compartido = Examen_Bloque_Compartido::find([
			'conditions' => ['sha1(id) = ?', $this->get->compartido_id]
		]);
		$examen = $compartido->examen;
		$grupo = $compartido->grupo;
		$matriculas = $grupo->getMatriculas();
		$cursos = $examen->getCursos();
		
		$this->render(array('matriculas' => $matriculas, 'compartido' => $compartido, 'examen' => $examen, 'cursos' => $cursos));
	}

	function save_resultados(){
		//
		foreach($this->post->resultados As $matricula_id => $resultado){
			$prueba = Examen_Bloque_Prueba::find_by_compartido_id_and_matricula_id($this->post->compartido_id, $matricula_id, [
				'order' => 'id DESC'
			]);

			if(!$prueba){
				$prueba = new Examen_Bloque_Prueba([
					'compartido_id' => $this->post->compartido_id,
					'matricula_id' => $matricula_id,
					'fecha_hora' => date('Y-m-d H:i:s'),
					'respuestas' => '',
					'expiracion' => date('Y-m-d H:i:s'),
				]);
			}

			$prueba->set_attributes([
				'estado' => 'FINALIZADA',
				'token' => getToken(),
				'resultados' => serialize($resultado)
			]);
			
			$prueba->save();
			$prueba->asignarNotasAsignatura();
		}
		
		echo json_encode([1]);
	}

	function calificar_prueba(){
		$prueba = Examen_Bloque_Prueba::find([
			'conditions' => ['sha1(id) = ?', $this->post->prueba_id]
		]);
		$r = $prueba->calificar() ? 1 : 0;
		echo json_encode(array($r));
	}

	function borrar_prueba(){
		$prueba = Examen_Bloque_Prueba::find([
			'conditions' => ['sha1(id) = ?', $this->post->id]
		]);
		$r = $prueba->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
}
