<?php
class Asignaturas_examenesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => '*',
			'ALUMNO' => 'iniciar_prueba, prueba, save_respuestas, finalizar_prueba, resultados_detalles'
		]);
	}
	
	function index($r){
		$asignaturas_examenes = Asignatura_Examen::all();
		$this->render(array('asignaturas_examenes' => $asignaturas_examenes));
	}

    function asignar(){
        
        $asignatura = $this->asignatura_examen->asignatura;
        $criterios = $asignatura->getCriterios($this->asignatura_examen->ciclo);

        $this->render(['criterios' => $criterios, 'asignatura' => $asignatura]);
    }

    function do_asignar(){
		$asignatura = Asignatura::find($this->post->asignatura_id);
		$matriculas = $asignatura->grupo->getMatriculas();
        //$tarea = Asignatura_Tarea::find($this->post->tarea_id);
        $examen = Asignatura_Examen::find($this->post->examen_id);

        $ids = explode('_', $this->post->criterio_id);

		foreach($matriculas As $matricula){
			$detalles = Nota_Detalle::find_by_matricula_id_and_asignatura_id_and_ciclo($matricula->id, $asignatura->id, $this->post->ciclo);
			//$prueba = $matricula->getPruebaActivaTarea($this->tarea);
            //$nota_tarea = Asignatura_Tarea_Nota::find_by_matricula_id_and_tarea_id($matricula->id, $tarea->id);
            $prueba = $matricula->getBestTestAula($examen);

			if(!$detalles){
				$_detalles = array(
					$this->post->criterio_id => array()
				);
			}else{
				$_detalles = unserialize($detalles->data);
			}

            $_detalles[$ids[0]][$ids[1]][$this->post->cuadro] = $prueba->puntaje;

			//$indicador = Asignatura_Indicador::find_by_criterio_id($this->post->criterio_id);
            //$_detalles[$this->post->criterio->id][$indicador->id]
			/*
			foreach($_detalles[$this->post->criterio_id] As $indicador_id => $nota){
				$_detalles[$this->post->criterio_id][$indicador_id][$this->post->cuadro] = $prueba->puntaje > 20 ? 20 : $prueba->puntaje;
				break;
			}
			*/

			Nota_Detalle::table()->delete(array(
                'asignatura_id' => $asignatura->id,
                'matricula_id' => $matricula->id,
                'ciclo' => $this->post->ciclo,
            ));

			Nota_Detalle::create(array(
                'asignatura_id' => $asignatura->id,
                'matricula_id' => $matricula->id,
                'ciclo' => $this->post->ciclo,
                'data' => serialize($_detalles)
            ));
		}

        echo json_encode([1]);
	}

	function resultados(){
		
		$matriculas = $this->asignatura_examen->asignatura->grupo->getMatriculas();
		

		$this->render(array('matriculas' => $matriculas));
	}

	function resultados_detalles(){
		$prueba = Asignatura_Examen_Prueba::find([
			'conditions' => ['sha1(id) = ?', $this->params->id]
		]);
		$examen = $prueba->examen;

		$preguntas = $prueba->getPreguntas();

		$this->render(['preguntas' => $preguntas, 'examen' => $examen, 'prueba' => $prueba]);
	}
	
	function save(){
		$r = -5;
		$this->asignatura_examen->set_attributes(array(
			'trabajador_id' => $this->USUARIO->personal_id,
			'titulo' => $this->post->titulo,
			'tipo_puntaje' => $this->post->tipo_puntaje,
			'puntos_correcta' => $this->post->puntos_correcta,
			'penalizar_incorrecta' => $this->post->penalizar_incorrecta,
			'penalizacion_incorrecta' => $this->post->penalizacion_incorrecta,
			'tiempo' => $this->post->tiempo,
			'intentos' => $this->post->intentos,
			'estado' => $this->post->estado,
			'orden_preguntas' => $this->post->orden_preguntas,
			/* 'fecha_desde' => $this->post->fecha_desde,
			'fecha_hasta' => $this->post->fecha_hasta,
			'hora_desde' => date('H:i:s', strtotime($this->post->hora_desde)),
			'hora_hasta' => date('H:i:s', strtotime($this->post->hora_hasta)), */
 			'asignatura_id' => $this->post->asignatura_id,
			
			'ciclo' => $this->post->ciclo,
			'preguntas_max' => $this->post->preguntas_max,
            'tipo' => $this->post->tipo
		));
		
        $archivo = uploadFile('archivo_pdf', ['pdf']);
        if(!is_null($archivo)){
            $this->asignatura_examen->archivo_pdf = $archivo['new_name'];
        }


		if($this->asignatura_examen->is_valid()){
			$r = $this->asignatura_examen->save() ? 1 : 0;
		}


		echo json_encode(array($r, 'id' => $this->asignatura_examen->id, 'errors' => $this->asignatura_examen->errors->get_all()));
	}

	function borrar($r){
		$r = $this->asignatura_examen->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

    function cambiar_estado($r){
		$this->asignatura_examen->estado = $this->asignatura_examen->estado == "ACTIVO" ? "INACTIVO" : "ACTIVO";
        $r = $this->asignatura_examen->save() ? 1 : 0;
		echo json_encode(array($r));
	}

	function iniciar_prueba(){
		// check if can do the test
		$matricula = Matricula::find($this->post->matricula_id);
		$examen = $this->asignatura_examen;
		$r = -2;
		

		if($matricula->canDoTestAula($examen)){
			$currentTime = time();
			$currentDate = date('Y-m-d H:i:s');
			
			$expiracion = $examen->tiempo > 0 ? ($currentTime + ($examen->tiempo * 60)) : $currentTime;

			$prueba = new Asignatura_Examen_Prueba(array(
				'examen_id' => $examen->id,
				'matricula_id' => $matricula->id,
				'fecha_hora' => $currentDate,
				'expiracion' => date('Y-m-d H:i:s', $expiracion),
				'estado' => 'ACTIVO',
				'token' => getToken()
			));
			$r = $prueba->save() ? 1 : 0;
			if($r == 1)
				$prueba->createPreguntas();
		}

		echo json_encode(array($r, 'prueba_id' => sha1($prueba->id), 'token' => $prueba->token, 'time' => strtotime($prueba->fecha_hora)));
	}


	function prueba(){
		$prueba = Asignatura_Examen_Prueba::find([
			'conditions' => ['sha1(id) = ? AND token = ?', $this->params->id, $this->get->token]
		]);


		if(!$prueba || $prueba->checkFinished()){
			$finished = true;
            return $this->render('test_finished');
		}
		
		$examen = $prueba->examen;

        //print_r($prueba->getPreguntasId());

		$preguntas = $prueba->getPreguntas();
		$respuestas = $prueba->getRespuestas();

		$remainingTime = $prueba->getRemainingTime();

		$this->render(array('prueba' => $prueba, 'respuestas' => $respuestas, 'finished' => $finished, 'compartido' => $compartido, 'examen' => $examen, 'preguntas' => $preguntas, 'remainingTime' => $remainingTime));
	}

	function finalizar_prueba(){
		$prueba = Asignatura_Examen_Prueba::find_by_id_and_token($this->params->id, $this->post->token);
		$r = 0;
		if($prueba){
			$r = $prueba->setToFinished() ? 1 : 0;
		}

		echo json_encode(array($r));
	}

	function save_respuestas(){
		$prueba = Asignatura_Examen_Prueba::find_by_id_and_token($this->params->id, $this->post->token);
        $r = 0;
        if($prueba->estado != "FINALIZADA"){
            $r = $prueba->setRespuestas($this->post->respuestas) ? 1 : 0;    
        }

		
		
		echo json_encode([$r]);
	}

	function borrar_prueba(){
		$prueba = Asignatura_Examen_Prueba::find($this->post->id);
		$r = $prueba->delete() ? 1 : 0;
		echo json_encode([$r]);
	}

	function editar_prueba(){
		if(!empty($this->get->prueba_id)){
			$prueba = Asignatura_Examen_Prueba::find([
				'conditions' => ['sha1(id) = ?', $this->get->prueba_id]
			]);
		}else{
			$prueba = Asignatura_Examen_Prueba::create([
				'examen_id' => $this->get->examen_id,
				'matricula_id' => $this->get->matricula_id,
				'fecha_hora' => date('Y-m-d H:i:s'),
				'expiracion' => date('Y-m-d H:i:s'),
				'puntaje' => 0,
				'correctas' => 0,
				'incorrectas' => 0,
				'respuestas' => '',
				'preguntas' => '',
				'estado' => 'FINALIZADA'
			]);
		}
		


		$this->render(['prueba' => $prueba]);
	}

	function save_prueba(){
		$prueba = Asignatura_Examen_Prueba::find($this->post->id);
		$prueba->set_attributes([
			'puntaje' => $this->post->puntaje,
			'correctas' => $this->post->correctas,
			'incorrectas' => $this->post->incorrectas,
			//'respuestas' => ''
		]);

		$r = $prueba->save() ? 1 : 0;
		echo json_encode([$r]);
	}

	function calificar(){
		$examen = $this->asignatura_examen;
		$pruebas = Asignatura_Examen_Prueba::all([
			'conditions' => 'examen_id = "'.$examen->id.'"'
		]);
		foreach($pruebas As $prueba){
			$prueba->calificar();
		}
		echo json_encode([1]);
	}

	function resultados_excel(){
		$examen = $this->asignatura_examen;
		$matriculas = $examen->asignatura->grupo->getMatriculas();
		
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/resultados_examen.xlsx');
		$s1 = $excel->getSheet(0);

		$currentRow = 3;
		$i = 1;
		foreach($matriculas AS $key => $matricula){
			$prueba = $matricula->getBestTestAula($examen);
			if(!$matricula->alumno) continue;

			$s1->setCellValue('A'.$currentRow, $i);
			$s1->setCellValue('B'.$currentRow, $matricula->alumno->getFullName());
			$s1->setCellValue('C'.$currentRow, isset($prueba) ? $prueba->puntaje : '-');
			$s1->setCellValue('D'.$currentRow, isset($prueba) ? $prueba->correctas : '-');
			$s1->setCellValue('E'.$currentRow, isset($prueba) ? $prueba->incorrectas : '-');
			++$currentRow;
			++$i;
		}
		writeExcel($excel);
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'asignaturas_examenes', true);
		$this->asignatura_examen = !empty($this->params->id) ? Asignatura_Examen::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Asignatura_Examen();
		$this->context('asignatura_examen', $this->asignatura_examen); // set to template
		if(!empty($this->params->asignatura_id)){
			$this->asignatura = Asignatura::find([
				'conditions' => ['sha1(id) = ?', $this->params->asignatura_id]
			]);
			$this->context('asignatura', $this->asignatura);
		}
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->asignatura_examen);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		$total_ciclos = $this->COLEGIO->total_notas;
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'tipo_puntaje' => array(
				'class' => 'form-control'
			),
            'tipo' => array(
				'class' => 'form-control'
			),
            'archivo_pdf' => array(
				'class' => 'form-control',
                'type' => 'file',
                'accept' => '.pdf',
                
			),
			'preguntas_max' => array(
				'class' => 'form-control',
				'min' => 1,
				'__default' => 1
			),
			'puntos_correcta' => array(
				'class' => 'tip number input-small form-control',
				'title' => 'Puntos obtenidos por una respuesta correcta.',
				'data-bv-numeric' => 'true'
			),
			'penalizacion_incorrecta' => array(
				'class' => 'tip number input-small form-control',
				'title' => 'Puntos que se restarán por respuesta incorrecta.',
				'data-bv-numeric' => 'true',
			),
			'titulo' => array(
				'data-bv-notempty' => 'true',
				'class' => 'required form-control'
			),
            'estado' => array(
				'class' => 'form-control'
			),
			
			'orden_preguntas' => array(
				'__label' => 'Orden de Preguntas',
				'class' => 'form-control'
			),
			'tiempo' => array(
				'class' => 'number required input-small form-control tip',
				'title' => 'Tiempo en minutos',
				'data-bv-notempty' => 'true'
			),
			'intentos' => array(
				'class' => 'number required input-small form-control',
				'min' => 1,
				'__default' => 1
			),
			'penalizar_incorrecta' => array(
				'class' => 'input-small form-control'
			),

			'fecha_desde' => array(
	
				'__default' => date('Y-m-d'),
				'class' => 'tip required form-control calendar',
				'title' => 'Indica la fecha en la que se activará el examen.',
				'data-bv-notempty' => 'true'
			),

			'hora_desde' => array(
				
				'__default' => date('h:i A'),
				'class' => 'tip required form-control hora',
				'title' => 'Indica la hora en la que se activará el examen.',
				'data-bv-notempty' => 'true'
			),
			'fecha_hasta' => array(
		
				'__default' => '',
				'class' => 'tip required form-control calendar',
				'title' => 'Indica la fecha en la que finalizará el examen.',
				'data-bv-notempty' => 'true'
			),

			'hora_hasta' => array(
				
				'__default' => '',
				'class' => 'tip required form-control hora',
				'title' => 'Indica la hora en la que finalizará el examen.',
				'data-bv-notempty' => 'true'
			),

			'asignatura_id' => array('type' => 'hidden', '__default' => $this->asignatura->id),
			'ciclo' => array(
				'type' => 'select',
				'__options' => array_combine(range(1, $total_ciclos), range(1, $total_ciclos)),
				'class' => 'form-control'
			),
			
		);

        if($object->is_new_record()){
            $options['archivo_pdf']['data-bv-notempty'] = 'true';
        }
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
