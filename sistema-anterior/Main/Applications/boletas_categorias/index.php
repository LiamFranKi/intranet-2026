<?php
class Boletas_categoriasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$boletas_categorias = Boleta_Categoria::all();
		$this->render(array('boletas_categorias' => $boletas_categorias));
	}
	
	function save(){
		$r = -5;
		$this->boleta_categoria->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nombre' => $this->post->nombre,
			'descripcion' => $this->post->descripcion,
		));
		
		if($this->boleta_categoria->is_valid()){
			$r = $this->boleta_categoria->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->boleta_categoria->id, 'errors' => $this->boleta_categoria->errors->get_all()));
	}

	function borrar($r){
		$r = $this->boleta_categoria->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function subcategorias(){
		$boleta_categoria = Boleta_Categoria::find($this->post->id);
		$data = array();
		foreach($boleta_categoria->subcategorias As $subcategoria){
			$data[] = $subcategoria->attributes();
		}
		echo json_encode($data);
		//print_r($this->boleta_categoria->subcategorias);
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'boletas_categorias', true);
		$this->boleta_categoria = !empty($this->params->id) ? Boleta_Categoria::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Boleta_Categoria();
		$this->context('boleta_categoria', $this->boleta_categoria); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->boleta_categoria);
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
			'descripcion' => array(
				'class' => 'form-control',
				'style' => 'height: 70px'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
