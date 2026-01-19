<?php
class Boletas_subcategoriasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$boletas_subcategorias = Boleta_Subcategoria::all();
		$this->render(array('boletas_subcategorias' => $boletas_subcategorias));
	}
	
	function save(){
		$r = -5;
		$this->boleta_subcategoria->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'categoria_id' => $this->post->categoria_id,
			'nombre' => $this->post->nombre,
			'descripcion' => $this->post->descripcion,
			'concar_igv' => $this->post->concar_igv,
			'concar_cuenta' => $this->post->concar_cuenta,
			'starsoft_cuenta' => $this->post->starsoft_cuenta
		));
		
		if($this->boleta_subcategoria->is_valid()){
			$r = $this->boleta_subcategoria->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->boleta_subcategoria->id, 'errors' => $this->boleta_subcategoria->errors->get_all()));
	}

	function borrar($r){
		$r = $this->boleta_subcategoria->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'boletas_subcategorias', true);
		$this->boleta_subcategoria = !empty($this->params->id) ? Boleta_Subcategoria::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Boleta_Subcategoria();
		$this->context('boleta_subcategoria', $this->boleta_subcategoria); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->boleta_subcategoria);
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
			'categoria_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__dataset' => 'true',
				'__options' => [Boleta_Categoria::all(), 'id', '$object->nombre'],
				'__first' => ['', '-- Seleccione --']
			),
			'nombre' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'descripcion' => array(
				'class' => 'form-control',
			
			),
			'concar_igv' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'concar_cuenta' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'starsoft_cuenta' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
