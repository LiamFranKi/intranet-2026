<?php
class MatriculasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'ALUMNO' => 'alumno',
			'APODERADO' => 'alumno, fotocheck'
		]);
	}
	
	function index($r){
		$alumno = Alumno::find([
			'conditions' => ['sha1(id) = ?', $this->get->alumno_id]
		]);

		$matriculas = Matricula::all([
			'conditions' => ['alumno_id = ?', $alumno->id]
		]);
		$this->render(array('matriculas' => $matriculas, 'alumno' => $alumno));
	}

	function alumno(){
		$alumno = !isset($this->params->alumno_id) ? $this->USUARIO->alumno : Alumno::find([
			'conditions' => ['sha1(id) = ?', $this->params->alumno_id]
		]);
		$matriculas = $alumno->getMatriculas();

		$this->render(['matriculas' => $matriculas]);
	}

    function fotocheck(){
        $nivel = ucwords(mb_strtolower($this->matricula->grupo->getNombreShort4(), 'utf-8'));
        $this->render(['nivel' => $nivel]);
    }
	
	function save(){
		$r = -5;
		$grupo = $this->COLEGIO->getGrupo($this->post, true);
		
		$this->matricula->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'grupo_id' => $grupo->id,
			'alumno_id' => $this->post->alumno_id,
			//'fecha_registro' => date('Y-m-d'),
			'personal_id' => $this->USUARIO->personal_id,
			'estado' => $this->post->estado,
			'ocultar' => $this->post->ocultar,
			'descontar' => $this->post->descontar,
			'modalidad' => $this->post->modalidad
			//'costo_id' => $this->post->costo_id,
		));
		if($this->matricula->is_new_record()){
			$this->matricula->fecha_registro = date('Y-m-d');
		}

		// ES UN COSTO PERSONALIZADO
		
		if($this->post->costo_id == -1){
			if(is_null($this->matricula->costo) || $this->matricula->costo->tipo == 'GENERAL'){
				$costo = new Costo();
			}

			if(!is_null($this->matricula->costo) && $this->matricula->costo->tipo == 'PERSONAL'){
				$costo = $this->matricula->costo;
			}

			$costo->set_attributes(array(
				'colegio_id' => $this->COLEGIO->id,
				'descripcion' => 'Costo Personalizado - '.$this->matricula->alumno->getFullName(),
				'matricula' => $this->post->costo_matricula,
				'pension' => $this->post->costo_pension,
				'agenda' => $this->post->costo_agenda,
				'tipo' => 'PERSONAL'
			));

			if($costo->save()){
				$this->matricula->costo_id = $costo->id;
			}

		}else{
			if($this->matricula->costo->tipo == 'PERSONAL'){
				$this->matricula->costo->delete();
			}
			$this->matricula->costo_id = $this->post->costo_id;
		}
		
		if($this->matricula->is_valid()){
			$r = $this->matricula->save() ? 1 : 0;
			if($r == 1 && isset($this->post->registrarMatricula)){
				$costo = Costo::find_by_id($this->matricula->costo_id);
				$pago = new Pago(array(
					'colegio_id' => $this->COLEGIO->id,
					'matricula_id' => $this->matricula->id,
					'nro_pago' => 1,
					'monto' => $costo->matricula,
					'mora' => 0,
					'fecha_hora' => date('Y-m-d H:i'),
					//'nro_recibo' => '-',
					'tipo' => 0,
					'descripcion' => 'FORMULARIO DE MATRÍCULA',
					'observaciones' => '',
					'personal_id' => $this->USUARIO->personal_id,
					'estado_pago' => 'PENDIENTE',
					'fecha_cancelado' => date('Y-m-d H:i:s')
				));
				if($pago->is_valid()){

					$pago->save();
				}
				//print_r($pago->errors->get_all());
			}
		}
		echo json_encode(array($r, 'id' => $this->matricula->id, 'errors' => $this->matricula->errors->get_all()));
	}

	function borrar($r){
		$r = $this->matricula->delete() ? 1 : 0;
		echo json_encode(array($r));
	}


	
	function __getObjectAndForm(){
		$this->set('__active', 'matriculas', true);
		$this->matricula = !empty($this->params->id) ? Matricula::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Matricula();
		$this->context('matricula', $this->matricula); // set to template
		
		if(in_array($this->params->Action, array('form', 'online'))){
			$this->form = $this->__getForm($this->matricula);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'alumno_id' => [
				'type' => 'hidden'
			],
			'colegio_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'grupo_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
	
			'fecha_registro' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'personal_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado' => array(
				'type' => 'select',
				'__options' => $object->ESTADOS_MATRICULA,
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control'
			),
			'costo_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->getCostos(), 'id', '$object->getMontos()'),
				'class' => 'form-control',
				'__first' => array('', '-- Seleccione --')
			),
			'recomendaciones' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'ocultar' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'descontar' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'modalidad' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),


			// GRUPO
			'sede_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array(Sede::all(), 'id', '$object->nombre'),
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->sede_id
			),
			'nivel_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->niveles, 'id', '$object->nombre'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->nivel_id
			),
			'grado' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->grado
			),
			'seccion' => array(
				'type' => 'select',
				'__options' => array_combine($object->SECCIONES, $object->SECCIONES),
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->seccion
			),
			'anio' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__default' => $this->COLEGIO->anio_activo,
				'rel' => 'curso',
				'value' => empty($object->grupo->anio) ? $this->COLEGIO->anio_activo : $object->grupo->anio,
			),
			'turno_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->turnos, 'id', '$object->nombre'),
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->turno_id
			),
			
		);

		if($object->is_new_record() && !empty($this->get->alumno_id)){
			$alumno = Alumno::find([
				'conditions' => ['sha1(id) = ?', $this->get->alumno_id]
			]);
			$options['alumno_id']['value'] = $alumno->id;
		}
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
