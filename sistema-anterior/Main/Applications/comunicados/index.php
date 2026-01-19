<?php
class ComunicadosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'USUARIO_ID' => '*',
            'ALUMNO' => 'home_notices',
            'APODERADO' => 'home_notices'
		]);
	}
	
	function index($r){
		$comunicados = Comunicado::all();
		$this->render(array('comunicados' => $comunicados));
	}

	function publico(){
		$comunicados = Comunicado::all([
			'conditions' => 'estado = "ACTIVO" AND tipo = "ARCHIVO"',
			'order' => 'fecha_hora DESC'
		]);
		$this->render(['comunicados' => $comunicados]);
	}

    function home_notices(){
        $notices = Comunicado::all([
			'conditions' => 'estado = "ACTIVO" AND show_in_home = 1',
			'order' => 'fecha_hora DESC'
		]);

        $this->render(['notices' => $notices]);
    }
	
	function save(){
		$r = -5;
		$this->comunicado->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'descripcion' => $this->post->descripcion,
			'contenido' => $this->post->contenido,
			
			'privacidad' => 'PERSONAL',
			'fecha_hora' => $this->post->fecha_hora,
			'tipo' => $this->post->tipo,
			'estado' => $this->post->estado,
            'show_in_home' => $this->post->show_in_home
		));

		$archivo = uploadFileBlackList('archivo', ['exe', 'php', 'sh'], './Static/Archivos');
		if(!is_null($archivo)){
			$this->comunicado->archivo = $archivo['new_name'];
		}
		
		if($this->comunicado->is_valid()){
			$r = $this->comunicado->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->comunicado->id, 'errors' => $this->comunicado->errors->get_all()));
	}

	function borrar($r){
		$r = $this->comunicado->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'comunicados', true);
		$this->comunicado = !empty($this->params->id) ? Comunicado::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Comunicado();
		$this->context('comunicado', $this->comunicado); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->comunicado);
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
				'data-bv-notempty' => 'true'
			),
			'contenido' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'archivo' => array(
				'class' => 'form-control',
				'type' => 'file'
			),
			'privacidad' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_hora' => array(
                'type' => 'datetime-local',
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d H:i')
			),
			'tipo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
            'show_in_home' => [
                'type' => 'select',
                'class' => 'form-control',
                'data-bv-notempty' => 'true',
                '__options' => [
                    1 => 'SI',
                    0 => 'NO'
                ],
            ]
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
