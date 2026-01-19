<?php
class Asignaturas_tareasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => '*',
			'ALUMNO' => 'detalles, do_enviar_archivo, quitar_archivo_nuevo',
			'APODERADO' => 'detalles'
		]);
	}
	
	function index($r){
		$asignaturas_tareas = Asignatura_Tarea::all();
		$this->render(array('asignaturas_tareas' => $asignaturas_tareas));
	}

	function detalles(){
		if($this->USUARIO->is('ALUMNO')){
			$this->asignatura_tarea->setView($this->USUARIO->alumno_id);
			$matricula = Matricula::find_by_alumno_id_and_grupo_id($this->USUARIO->alumno_id, $this->asignatura_tarea->asignatura->grupo_id);
		}

		$this->render(['matricula' => $matricula]);
	}

    function asignar(){
        
        $asignatura = $this->asignatura_tarea->asignatura;
        $criterios = $asignatura->getCriterios($this->asignatura_tarea->ciclo);

        $this->render(['criterios' => $criterios, 'asignatura' => $asignatura]);
    }

    function do_asignar(){
		$asignatura = Asignatura::find($this->post->asignatura_id);
		$matriculas = $asignatura->grupo->getMatriculas();
        $tarea = Asignatura_Tarea::find($this->post->tarea_id);

        $ids = explode('_', $this->post->criterio_id);

		foreach($matriculas As $matricula){
			$detalles = Nota_Detalle::find_by_matricula_id_and_asignatura_id_and_ciclo($matricula->id, $asignatura->id, $this->post->ciclo);
			//$prueba = $matricula->getPruebaActivaTarea($this->tarea);
            $nota_tarea = Asignatura_Tarea_Nota::find_by_matricula_id_and_tarea_id($matricula->id, $tarea->id);
			if(!$detalles){
				$_detalles = array(
					$this->post->criterio_id => array()
				);
			}else{
				$_detalles = unserialize($detalles->data);
			}

            $_detalles[$ids[0]][$ids[1]][$this->post->cuadro] = $nota_tarea->nota;

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

	function do_enviar_archivo(){
		
		$r = 0;
		if(strtotime(date('Y-m-d')) > strtotime($this->asignatura_tarea->fecha_entrega)){
			echo json_encode(array(0));
			return false;
		}
		
		$entrega = new Asignatura_Tarea_Entrega([
			'alumno_id' => $this->post->alumno_id,
			'tarea_id' => $this->asignatura_tarea->id,
			'url' => $this->post->url,
			'nombre' => '',
			'fecha_hora' => date('Y-m-d H:i:s'),
			'tipo' => 'ALUMNO'
		]);
		
		$r = $entrega->save() ? 1 : 0;
		
		
		echo json_encode(array($r));
	}

	function quitar_archivo_nuevo(){
		$tarea = Asignatura_Tarea_Entrega::find($this->post->tarea_id);
		//@unlink('./Static/Archivos/'.$tarea->archivo);
		$r = $tarea->delete() ? 1 : 0;

		echo json_encode([$r]);
	}
	
	function borrar_archivo(){
		$archivo = Asignatura_Tarea_Archivo::find($this->post->archivo_id);
		$archivo->delete();
		echo json_encode([1]);
	}

	function entrega(){
		$matriculas = $this->asignatura_tarea->asignatura->grupo->getMatriculas();
		$this->render(['matriculas' => $matriculas]);
	}

	function save_entrega(){
		$this->asignatura_tarea->entregas = isset($this->post->entregado) ? serialize($this->post->entregado) : serialize(array());
        
        foreach($this->post->notas As $matricula_id => $nota){
        	Asignatura_Tarea_Nota::table()->delete(['tarea_id' => $this->asignatura_tarea->id, 'matricula_id' => $matricula_id]);
        	if($nota != '')
	        	Asignatura_Tarea_Nota::create([
	        		'tarea_id' => $this->asignatura_tarea->id, 
	        		'matricula_id' => $matricula_id,
	        		'nota' => $nota
	        	]);
        }

        $r = $this->asignatura_tarea->save() ? 1 : 0;
        echo json_encode(array($r));
	}

	function save(){

		$r = -5;
		$this->asignatura_tarea->set_attributes(array(
			'trabajador_id' => $this->USUARIO->personal_id,
			'asignatura_id' => $this->post->asignatura_id,
			
			'ciclo' => $this->post->ciclo,
			//'tematico_id' => $this->post->tematico_id,
			'titulo' => $this->post->titulo,
			'descripcion' => $this->post->descripcion,
			
			'fecha_entrega' => $this->post->fecha_entrega,
            'enlace' => $this->post->enlace,
		));

		$this->asignatura_tarea->fecha_hora = date('Y-m-d H:i:s');
		
		if($this->asignatura_tarea->is_valid()){
			$r = $this->asignatura_tarea->save() ? 1 : 0;

			if($r == 1){
				$archivos = uploadFileMultiple('archivos', ['pdf', 'doc', 'docx', 'png', 'jpg'], './Static/Archivos');
				foreach($archivos As $archivo){
					Asignatura_Tarea_Archivo::create(array(
    					'tarea_id' => $this->asignatura_tarea->id,
    					'nombre' => $archivo['real_name'],
    					'archivo' => $archivo['new_name']
    				));
				}
			}
		}
		echo json_encode(array($r, 'id' => $this->asignatura_tarea->id, 'errors' => $this->asignatura_tarea->errors->get_all()));
	}

	function borrar($r){
		$r = $this->asignatura_tarea->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'asignaturas_tareas', true);
		$this->asignatura_tarea = !empty($this->params->id) ? Asignatura_Tarea::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Asignatura_Tarea();
		$this->context('asignatura_tarea', $this->asignatura_tarea); // set to template
		if(!empty($this->params->asignatura_id)){
			$this->asignatura = Asignatura::find([
				'conditions' => ['sha1(id) = ?', $this->params->asignatura_id]
			]);
			$this->context('asignatura', $this->asignatura);
		}
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->asignatura_tarea);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'titulo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'descripcion' => array(
				'class' => 'form-control',
				
			),
			'fecha_hora' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_entrega' => array(
				'class' => 'form-control calendar',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d')
			),
			'trabajador_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'asignatura_id' => array(
				'type' => 'hidden',
				'__default' => $this->asignatura->id
			),
			'entregas' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'visto' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
            'enlace' => array(
				'class' => 'form-control',
			),
			'archivos' => array(
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
