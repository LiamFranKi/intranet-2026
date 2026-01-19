<?php
class SedesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$sedes = Sede::all();
		$this->render(array('sedes' => $sedes));
	}
	
	function save(){
		$r = -5;
		$this->sede->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nombre' => $this->post->nombre,
			'codigo_sunat' => $this->post->codigo_sunat,
			'prefijo_boleta' => $this->post->prefijo_boleta,
			'direccion' => $this->post->direccion,
		));
		
		if($this->sede->is_valid()){
			$r = $this->sede->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->sede->id, 'errors' => $this->sede->errors->get_all()));
	}

	function configuracion(){
		$config = Boleta_Configuracion::find_by_sede_id($this->sede->id);
		if(!$config) $config = new Boleta_Configuracion();

		$this->render(['config' => $config]);
	}

	function save_configuracion(){

		$config = Boleta_Configuracion::find_by_sede_id($this->sede->id);
		if(!$config) $config = new Boleta_Configuracion();

		$config->set_attributes(array(
			'sede_id' => $this->sede->id,
			'serie' => $this->post->serie,
			'numero' => $this->post->numero,
			'serie_2' => $this->post->serie_2,
			'numero_2' => $this->post->numero_2,
			'serie_3' => $this->post->serie_3,
			'numero_3' => $this->post->numero_3,
			'serie_mora' => $this->post->serie_mora,
			'numero_mora' => $this->post->numero_mora,
		));

		$r = $config->save() ? 1 : 0;
		echo json_encode(array($r));
	}

	function borrar($r){
		$r = $this->sede->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'sedes', true);
		$this->sede = !empty($this->params->id) ? Sede::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Sede();
		$this->context('sede', $this->sede); // set to template
		if(in_array($this->params->Action, array('form', 'configuracion'))){
			$this->form = $this->__getForm($this->sede);
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
			'codigo_sunat' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'prefijo_boleta' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'direccion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
