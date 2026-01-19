<?php
class NivelesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$niveles = Nivel::all();
		$this->render(array('niveles' => $niveles));
	}
	
	function save(){
		$r = -5;
		$this->nivel->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nombre' => $this->post->nombre,
			'abreviatura' => $this->post->abreviatura,
			'definicion_grado' => $this->post->definicion_grado,
			'grado_minimo' => $this->post->grado_minimo,
			'grado_maximo' => $this->post->grado_maximo,
			'nota_aprobatoria' => $this->post->nota_aprobatoria,
			'tipo_calificacion' => $this->post->tipo_calificacion,
			'tipo_calificacion_final' => $this->post->tipo_calificacion_final,
			'nota_maxima' => $this->post->nota_maxima,
			'nota_minima' => $this->post->nota_minima,
			'codigo_modular' => $this->post->codigo_modular,
			
		));
		
		if($this->nivel->is_valid()){
			$r = $this->nivel->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->nivel->id, 'errors' => $this->nivel->errors->get_all()));
	}

	function borrar($r){
		$r = $this->nivel->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'niveles', true);
		$this->nivel = !empty($this->params->id) ? Nivel::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Nivel();
		$this->context('nivel', $this->nivel); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->nivel);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'colegio_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nombre' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'abreviatura' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'definicion_grado' => array(
				'type' => 'select',
				'__options' => $object->DEFINICIONES_GRADO,
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'grado_minimo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'grado_maximo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nota_aprobatoria' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo_calificacion' => array(
				'type' => 'select',
				'__options' => $object->TIPOS_CALIFICACION,
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo_calificacion_final' => array(
				'type' => 'select',
				'__options' => $object->TIPOS_CALIFICACION_FINAL,
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nota_maxima' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nota_minima' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'codigo_modular' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'avanzada' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'monto_adelanto_matricula' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
