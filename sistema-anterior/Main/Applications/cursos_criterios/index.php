<?php
class Cursos_criteriosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$cursos_criterios = $this->curso->getCriterios();
		$this->render(array('cursos_criterios' => $cursos_criterios));
	}
	
	function save(){
		$r = -5;
		$this->curso_criterio->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'descripcion' => $this->post->descripcion,
            'curso_id' => $this->post->curso_id,
            'ciclo' => 0,
            'abreviatura' => $this->post->abreviatura,
            'peso' => $this->post->peso,
		));

		if($this->curso_criterio->is_new_record()){
			$this->curso_criterio->orden = time();
		}
		
		if($this->curso_criterio->is_valid()){
			$r = $this->curso_criterio->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->curso_criterio->id, 'errors' => $this->curso_criterio->errors->get_all()));
	}

	function borrar($r){
		$r = $this->curso_criterio->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function update_orden_criterios(){
        foreach($this->post->data As $orden => $criterio_id){
            Curso_Criterio::table()->update(array(
                'orden' => $orden
            ), array(
                'id' => $criterio_id
            ));
        }
    }
    
    function update_orden(){
        foreach($this->post->data As $orden => $curso_id){
            Curso::table()->update(array(
                'orden' => $orden
            ), array(
                'id' => $curso_id
            ));
        }
    }
	
	function __getObjectAndForm(){
		$this->set('__active', 'cursos_criterios', true);
		$this->curso_criterio = !empty($this->params->id) ? Curso_Criterio::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Curso_Criterio();
		$this->context('curso_criterio', $this->curso_criterio); // set to template
		if(!empty($this->params->curso_id)){
			$this->curso = Curso::find([
				'conditions' => ['sha1(id) = ?', $this->params->curso_id]
			]);
			$this->context('curso', $this->curso);
		}
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->curso_criterio);
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
			'descripcion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'text'
			),
			'abreviatura' => array(
				'class' => 'form-control',
				
			),
			'curso_id' => array(
				'type' => 'hidden',
				'__default' => $this->curso->id
			),
			'ciclo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'orden' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'peso' => array(
				'class' => 'form-control',
				'data-bv-numeric' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
