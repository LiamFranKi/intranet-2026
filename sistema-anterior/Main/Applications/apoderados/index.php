<?php
class ApoderadosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'APODERADO' => 'hijos',
			'__exclude' => 'firma_digital'
		]);
	}
	
	function index($r){

		$apoderados = $this->COLEGIO->searchApoderados($this->get->query);
		$this->render(array('apoderados' => $apoderados));
	}

	function hijos(){
		$alumnos = Alumno::find_by_sql('
            SELECT alumnos.* FROM alumnos
            INNER JOIN familias ON familias.alumno_id = alumnos.id
            WHERE familias.apoderado_id = "'.$this->USUARIO->apoderado_id.'"
        ');
        
        $this->render(array('alumnos' => $alumnos));
	}
	
	function save(){
		$r = -5;
		$this->apoderado = !empty($this->post->id) ? Apoderado::find([
			'conditions' => ['sha1(id) = ?', $this->post->id]
		]) : Apoderado::find(array(
			'conditions' => 'tipo_documento="'.$this->post->tipo_documento.'" AND nro_documento="'.$this->post->nro_documento.'"'
		));

        if(is_null($this->apoderado)){
            $this->apoderado = new Apoderado();
        }

		$this->apoderado->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nombres' => $this->post->nombres,
			'apellido_paterno' => $this->post->apellido_paterno,
			'apellido_materno' => $this->post->apellido_materno,
			'vive' => $this->post->vive,
			'tipo_documento' => $this->post->tipo_documento,
			'nro_documento' => $this->post->nro_documento,
			'estado_civil' => $this->post->estado_civil,
			'telefono_fijo' => $this->post->telefono_fijo,
			'telefono_celular' => $this->post->telefono_celular,
			'direccion' => $this->post->direccion,
			'centro_trabajo_direccion' => $this->post->centro_trabajo_direccion,
			'grado_instruccion' => $this->post->grado_instruccion,
			'ocupacion' => $this->post->ocupacion,
			'parentesco' => $this->post->parentesco,
			'vive_con_estudiante' => $this->post->vive_con_estudiante,
			'fecha_nacimiento' => date('Y-m-d', strtotime($this->post->fecha_nacimiento)),
			'pais_nacimiento_id' => $this->post->pais_nacimiento_id,
			'email' => $this->post->email,
		));
		
		if($this->apoderado->is_valid()){
			$r = $this->apoderado->save() ? 1 : 0;
			if(!empty($this->post->alumno_id)){
				$familia = Familia::find_by_apoderado_id_and_alumno_id($this->apoderado->id, $this->post->alumno_id);
                if(!$familia) $familia = Familia::create(array('apoderado_id' => $this->apoderado->id, 'alumno_id' => $this->post->alumno_id));
			}
		}
		echo json_encode(array($r, 'id' => $this->apoderado->id, 'errors' => $this->apoderado->errors->get_all()));
	}

	function borrar($r){
		$r = $this->apoderado->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function acceso(){
		$usuario = !is_null($this->apoderado->usuario) ? $this->apoderado->usuario : new Usuario(array(
			'apoderado_id' => $this->apoderado->id
		));
	
		$form = $this->__getFormUsuario($usuario);
		$this->render(array('usuario', $usuario, 'form' => $form));
	}
	
	function do_acceso(){
		$usuario = !empty($this->post->id) ? Usuario::find_by_id_and_colegio_id($this->post->id, $this->COLEGIO->id) : new Usuario(array(
			'apoderado_id' => $this->post->apoderado_id,
			'colegio_id' => $this->COLEGIO->id
		));
		
		$usuario->usuario = $this->post->usuario;
		$usuario->tipo = 'APODERADO';
		$usuario->estado = $this->post->estado;
		
		if(!empty($this->post->password)){
			$usuario->password = sha1($this->post->password);
		}
		
		$r = -5;
		if($usuario->is_valid() && $usuario->isUniqueInCollege()){
			$r = $usuario->save() ? 1 : 0;
		}
		
		echo json_encode(array($r, 'errors' => $usuario->errors->get_all()));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'apoderados', true);
		$this->apoderado = !empty($this->params->id) ? Apoderado::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Apoderado();
		$this->context('apoderado', $this->apoderado); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->apoderado);
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
			'nombres' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'apellido_paterno' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'apellido_materno' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'vive' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo_documento' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => $object->TIPOS_DOCUMENTO,
			),
			'nro_documento' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado_civil' => array(
				'class' => 'form-control',
				'type' => 'select',
				'__options' => $object->ESTADOS_CIVIL,
			),
			'telefono_fijo' => array(
				'class' => 'form-control',
				
			),
			'telefono_celular' => array(
				'class' => 'form-control',
				
			),
			'direccion' => array(
				'class' => 'form-control',
				
			),
			'centro_trabajo_direccion' => array(
				'class' => 'form-control',
				
			),
			'grado_instruccion' => array(
				'class' => 'form-control',
				'type' => 'select',
				'__options' => $object->GRADOS_INSTRUCCION,
			),
			'ocupacion' => array(
				'class' => 'form-control',
				
			),
			'parentesco' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => $object->APODERADO_PARENTESCOS,
			),
			'vive_con_estudiante' => array(
				'class' => 'form-control',
				
			),
			'fecha_nacimiento' => array(
				'class' => 'form-control calendar',	
				
			),
			'pais_nacimiento_id' => array(
				'class' => 'form-control',
				'__options' => array(Pais::all(), 'id', '$object->nombre'),
				'__dataset' => true,
				'type' => 'select',
			),
			'email' => array(
				'class' => 'form-control',
				
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}


	private function __getFormUsuario($object){
		$this->crystal->load('Form:*');
		$options = array(
			'id' => array(
				'type' => 'hidden'
			),
			'apoderado_id' => array(
				'type' => 'hidden'
			),
			'tipo' => array(
				':size' => 'col-sm-4'
			),
			'usuario' => array(
				'__label' => 'Nombre de Usuario',
				'data-bv-notempty' => 'true',
				'class' => 'form-control'
			),
			'password' => array(
				'type' => 'password',
				'__label' => 'Contraseña',
				'value' => '',
				'data-bv-notempty' => $object->is_new_record() ? 'true' : 'false',
				'class' => 'form-control'
			),
			'estado' => array(
				':size' => 'col-sm-3',
				'class' => 'form-control'
			),
			'ms_email' => array(
				'__label' => 'Cuenta Office365',
				'data-bv-notempty' => 'true'
				//':size' => 'col-sm-4'
			)
		);
		return new Form($object, $options);
	}
	

	function firma_digital(){
		header('Content-Type: image/png');
		$apoderado = Apoderado::find([
			'conditions' => ['sha1(id) = ?', $this->params->id]
		]);
		$firma = str_replace('data:image/png;base64,', '', $apoderado->firma_digital);
		echo base64_decode($firma);
	}
}
