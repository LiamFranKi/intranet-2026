<?php
class Examenes_bloques_compartidosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$examenes_bloques_compartidos = Examen_Bloque_Compartido::all([
			'conditions' => ['examen_id = ?', $this->examen->id]
		]);
		$this->render(array('examenes_bloques_compartidos' => $examenes_bloques_compartidos));
	}
	
	function save_multiple(){
		foreach($this->post->grupos As $grupo_id){
			$compartido = new Examen_Bloque_Compartido([
				'examen_id' => $this->post->examen_id,
				'tiempo' => $this->post->tiempo,
				'intentos' => $this->post->intentos,
				'expiracion' => $this->post->expiracion_fecha.' '.date('H:i', strtotime($this->post->expiracion_hora)),
				'ciclo' => $this->post->ciclo,
				'nro' => $this->post->nro,
				'grupo_id' => $grupo_id
			]);

			$compartido->save();
		}

		echo json_encode([1]);
	}

	function save(){
		if(isset($this->post->grupos)){
			return $this->save_multiple();
		}

		$r = -5;
		$this->examen_bloque_compartido->set_attributes(array(
			'examen_id' => $this->post->examen_id,
			'tiempo' => $this->post->tiempo,
			'intentos' => $this->post->intentos,
			'expiracion' => $this->post->expiracion_fecha.' '.date('H:i', strtotime($this->post->expiracion_hora)),
			'ciclo' => $this->post->ciclo,
			'nro' => $this->post->nro
		));
		
		if($this->examen_bloque_compartido->is_valid()){
			$r = $this->examen_bloque_compartido->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->examen_bloque_compartido->id, 'errors' => $this->examen_bloque_compartido->errors->get_all()));
	}

	function borrar($r){
		$r = $this->examen_bloque_compartido->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'examenes_bloques_compartidos', true);

		if(!empty($this->params->examen_id)){
			$this->examen = Examen_Bloque::find([
				'conditions' => ['sha1(id) = ?', $this->params->examen_id]
			]);

			$this->context('examen', $this->examen, true);
		}

		$this->examen_bloque_compartido = !empty($this->params->id) ? Examen_Bloque_Compartido::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Examen_Bloque_Compartido();
		$this->context('examen_bloque_compartido', $this->examen_bloque_compartido); // set to template

		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->examen_bloque_compartido);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');

		$grupos = Grupo::all(array(
			'conditions' => 'nivel_id = "'.$this->examen->bloque->nivel_id.'" AND anio = "'.$this->COLEGIO->anio_activo.'" AND grado = "'.$this->examen->grado.'"',
			'order' => 'grupos.nivel_id ASC, grupos.grado ASC, grupos.seccion ASC'
		));
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'examen_id' => array(
				'__default' => $this->examen->id,
				'type' => 'hidden'
			),
			'grupos' => array(
				'class' => 'form-control',
				'type' => 'select',
				'name' => 'grupos[]',
				'multiple' => 'true',
				'size' => 5,
				'style' => 'height: 100px',
				'__dataset' => 'true',
				'__options' => array($grupos, 'id', '$object->getNombreShortSede()')
			),
			'grupo_id' => array(
				'type' => 'hidden'
			),
			'tiempo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__default' => 60
			),
			'intentos' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__default' => 1
			),
			'expiracion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'ciclo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => $this->COLEGIO->getOptionsCicloNotas()
			),
			'nro' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => array(1 => 1, 2 => 2)
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
