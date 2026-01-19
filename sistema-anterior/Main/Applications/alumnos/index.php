<?php
class AlumnosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'USUARIO_ID' => 'ver_datos',
			'ALUMNO' => 'perfil, subir_foto, editar_perfil, save_perfil',
            'APODERADO' => 'perfil, subir_foto, editar_perfil, save_perfil',
			'__exclude' => 'matricula_online, save_matricula_online'
		]);
	}

	function x(){
		//file_put_contents('CSRF', 'HACKED');
	}
	
	function index($r){
		
		$alumnos = empty($this->params->query) ? Alumno::all(['limit' => 50]) : $this->COLEGIO->searchAlumnos($this->params->query);

		$this->render(array('alumnos' => $alumnos));
	}

	function editar_perfil(){

		$alumno = isset($this->params->id) ? $this->alumno : $this->USUARIO->alumno;
        $domicilio = array_filter(unserialize(base64_decode($alumno->domicilio)), function($item){return $item['direccion'];});
        
        if(count($domicilio) > 0){
            $alumno->domicilio = implode(' | ', array_filter(array_values(array_shift($domicilio))));
        }


		$form = $this->__getForm($alumno);
		$this->render(['alumno' => $alumno, 'form' => $form]);
	}
	
	function save_perfil(){
		/* if($this->alumno->id != $this->USUARIO->alumno_id){
			$r = 0;
			echo json_encode([$r]);
			return false;
		} */

		$r = -5;
		$this->alumno->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'apellido_paterno' => $this->post->apellido_paterno,
			'apellido_materno' => $this->post->apellido_materno,
			'nombres' => $this->post->nombres,
			
			//'tipo_documento' => $this->post->tipo_documento,
			//'nro_documento' => $this->post->nro_documento,
			'fecha_nacimiento' => $this->post->fecha_nacimiento,
			
			'sexo' => $this->post->sexo,
			'email' => $this->post->email,
			
			'religion' => $this->post->religion,
		
			'domicilio' => $this->post->domicilio
		));

		$foto = uploadFile('foto', ['jpg', 'jpeg', 'png'], './Static/Image/Fotos');
		if(!is_null($foto)){
			$this->alumno->foto = $foto['new_name'];
		}

		if($this->alumno->is_valid()){
			$r = $this->alumno->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->alumno->id, 'errors' => $this->alumno->errors->get_all()));
	}

	function save(){
		$r = -5;
		$this->alumno->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'apellido_paterno' => $this->post->apellido_paterno,
			'apellido_materno' => $this->post->apellido_materno,
			'nombres' => $this->post->nombres,
			'estado_civil' => $this->post->estado_civil,
			'tipo_documento' => $this->post->tipo_documento,
			'nro_documento' => $this->post->nro_documento,
			'fecha_nacimiento' => $this->post->fecha_nacimiento,
			'pais_nacimiento_id' => $this->post->pais_nacimiento_id,
			'departamento_nacimiento_id' => $this->post->departamento_nacimiento_id,
			'provincia_nacimiento_id' => $this->post->provincia_nacimiento_id,
			'distrito_nacimiento_id' => $this->post->distrito_nacimiento_id,
			'sexo' => $this->post->sexo,
			'email' => $this->post->email,
			'fecha_inscripcion' => $this->post->fecha_inscripcion,
			'observaciones' => $this->post->observaciones,
			'nro_hermanos' => $this->post->nro_hermanos,
			'lugar_hermanos' => $this->post->lugar_hermanos,
			'religion' => $this->post->religion,
			'lengua_materna' => $this->post->lengua_materna,
			'segunda_lengua' => $this->post->segunda_lengua,
			'codigo' => $this->post->codigo,
			'discapacidad' => $this->post->discapacidad,
			'domicilio' => $this->post->domicilio
		));

		$foto = uploadFile('foto', ['jpg', 'jpeg', 'png'], './Static/Image/Fotos');
		if(!is_null($foto)){
			$this->alumno->foto = $foto['new_name'];
		}

		if($this->alumno->is_valid()){
			$r = $this->alumno->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->alumno->id, 'errors' => $this->alumno->errors->get_all()));
	}

	function borrar($r){
		$r = $this->alumno->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'alumnos', true);
		$this->alumno = !empty($this->params->id) ? Alumno::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Alumno();
		$this->context('alumno', $this->alumno); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->alumno);
			$this->context('form', $this->form);
		}
	}

	function apoderados(){
		$apoderados = Apoderado::all(array(
            'select' => 'apoderados.*',
            'joins' => array('familias'),
            'conditions' => 'sha1(familias.alumno_id)="'.$this->params->id.'"'
        ));

        $hermanos = array();
        $already = array();
        foreach($apoderados As $apoderado){
            $xhermanos = Alumno::all(array(
                'select' => 'alumnos.*',
                'conditions' => 'familias.apoderado_id="'.$apoderado->id.'"',
                'joins' => 'INNER JOIN familias ON familias.alumno_id=alumnos.id'
            ));
            foreach($xhermanos As $hermano){
                if(!in_array($hermano->id, $already) && sha1($hermano->id) != $this->params->id){
                    $hermanos[] = $hermano;
                    $already[] = $hermano->id;
                }
            }
        }

        $this->render(array('apoderados' => $apoderados, 'hermanos' => $hermanos));
	}

	function matricula_online(){
		if(Config::get('enable_enrollment_form') == "NO"){
			return $this->render('disabled');
		}

		$alumno = new Alumno();
		$form = $this->__getForm($alumno);

		$apoderado = new Apoderado();
		$formApoderado = $this->__getFormApoderadoOnline($apoderado);

		$matricula = new Matricula();
		$formMatricula = $this->__getFormMatricula($matricula);

		$archivos = Documento_Matricula::all();

		$this->render(array('alumno' => $alumno, 'archivos' => $archivos, 'form' => $form, 'fa' => $formApoderado, 'fm' => $formMatricula, 'matricula' => $matricula));
	}

	function matricular(){
		$alumno = new Alumno();
		$form = $this->__getForm($alumno);

		$apoderado = new Apoderado();
		$formApoderado = $this->__getFormApoderado($apoderado);

		$matricula = new Matricula();
		$formMatricula = $this->__getFormMatricula($matricula);

		$this->render(array('alumno' => $alumno, 'form' => $form, 'fa' => $formApoderado, 'fm' => $formMatricula, 'matricula' => $matricula));
	}

	function acceso(){
		$usuario = !is_null($this->alumno->usuario) ? $this->alumno->usuario : new Usuario(array(
			'alumno_id' => $this->alumno->id
		));

		$form = $this->__getFormUsuario($usuario);
		$this->render(array('usuario', $usuario, 'form' => $form));
	}

	function do_acceso(){
		$usuario = !empty($this->post->id) ? Usuario::find_by_id_and_colegio_id($this->post->id, $this->COLEGIO->id) : new Usuario(array(
			'alumno_id' => $this->post->alumno_id,
			'colegio_id' => $this->COLEGIO->id
		));

		$usuario->usuario = $this->post->usuario;
		$usuario->tipo = 'ALUMNO';
		$usuario->estado = $this->post->estado;

		if(!empty($this->post->password)){
			$usuario->password = sha1($this->post->password);
		}

		if(isset($this->post->ms_email)){
			$usuario->ms_email = strtolower($this->post->ms_email);
			$usuario->ms_access_token = getToken();
		}

		$r = -5;
		if($usuario->is_valid() && $usuario->isUniqueInCollege()){
			$r = $usuario->save() ? 1 : 0;
		}

		echo json_encode(array($r, 'errors' => $usuario->errors->get_all()));
	}

	function subir_foto(){
		$foto = uploadFile('foto', ['jpg', 'jpeg', 'png'], './Static/Image/Fotos');
		$r = 0;
		if(!is_null($foto)){
			$this->USUARIO->alumno->foto = $foto['new_name'];
			$r = $this->USUARIO->alumno->save() ? 1 : 0;
		}


		echo json_encode([$r]);
	}

	private function __getFormUsuario($object){
		$this->crystal->load('Form:*');
		$options = array(
			'id' => array(
				'type' => 'hidden'
			),
			'alumno_id' => array(
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
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
	
			'apellido_paterno' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'apellido_materno' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nombres' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado_civil' => array(
				'type' => 'select',
				'__options' => $object->ESTADOS_CIVIL,
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo_documento' => array(
				'type' => 'select',
				'__options' => $object->TIPOS_DOCUMENTO,
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nro_documento' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_nacimiento' => array(
				'class' => 'form-control calendar',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d'),
				'type' => 'date'
			),
			'pais_nacimiento_id' => array(
				'type' => 'select',
				'__options' => array(Pais::all(), 'id', '$object->nombre'),
				'__first' => array('', '-- Seleccione --'),
				'__dataset' => true,
				'class' => 'form-control',
			
			),
			'departamento_nacimiento_id' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				
			),
			'provincia_nacimiento_id' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				
			),
			'distrito_nacimiento_id' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
		
			),
            'seguro_id' => array(
				'type' => 'select',
                '__first' => array('', '-- Seleccione --'),
				'__options' => $object->SEGUROS,
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'sexo' => array(
				'type' => 'select',
				'__options' => $object->SEXOS,
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'email' => array(

				'class' => 'form-control',
				'data-bv-emailaddress' => 'true'
			),
			'foto' => array(
				'class' => 'form-control',
				'type' => 'file'
			),
			'fecha_inscripcion' => array(
				'class' => 'form-control calendar',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d')
			),
			'observaciones' => array(
				'class' => 'form-control',
		
			),
			'nro_hermanos' => array(
				'type' => 'select',
				'__options' => array_combine(range(0, 20), range(0, 20)),
				'class' => 'form-control',
		
			),
			'lugar_hermanos' => array(
				'type' => 'select',
				'class' => 'form-control',

			),
			'religion' => array(
				'type' => 'select',
				'__options' => $object->RELIGIONES,
				'class' => 'form-control',
		
			),
			'lengua_materna' => array(
				'type' => 'select',
				'__options' => $object->LENGUA_MATERNA,
				'class' => 'form-control',
			),
			'segunda_lengua' => array(
				'type' => 'select',
				'__options' => $object->SEGUNDA_LENGUA,
				'class' => 'form-control',
			),
			'codigo' => array(
				'class' => 'form-control',
			),
			'discapacidad' => array(
				'type' => 'select',
				'__options' => $object->DISCAPACIDADES,
				'class' => 'form-control',
			),
			'domicilio' => array(
				'class' => 'form-control',
			),
			'telefonos' => [
				'class' => 'form-control'
			],
			'foto_dni' => [
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'file'
			]
		);
		
		$form = new Form($object, $options);
		return $form;
	}


	private function __getFormApoderado($object){
		$this->crystal->load('Form:*');

		$options = array(
			'id' => array('type' => 'hidden'),
			'__prefix' => 'apoderado_',
			'__sufix' => '[]',
			'tipo_documento' => array(
				'type' => 'select',
				'__options' => $object->TIPOS_DOCUMENTO,
				'onblur'=> 'getApoderado(this)',
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nro_documento' => array(
				'onblur'=> 'getApoderado(this)',
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
			'estado_civil' => array(
				'class' => 'form-control',
				'type' => 'select',
				'__options' => $object->ESTADOS_CIVIL,
				
			),
			'nombres' => [
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			],
			'parentesco' => array(
				'type' => 'select',
				'__options' => $object->APODERADO_PARENTESCOS,
				'class' => 'form-control',
			),
			'email' => [
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			],
			'telefono_celular' => [
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			],
			'direccion' => [
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			],
			
		);

		$form = new Form($object, $options);
		return $form;
	}

	private function __getFormApoderadoOnline($object){
		$this->crystal->load('Form:*');

		$options = array(
			'id' => array('type' => 'hidden'),
			'__prefix' => 'apoderado_',
			'tipo_documento' => array(
				'type' => 'select',
				'__options' => $object->TIPOS_DOCUMENTO,
				
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nro_documento' => array(
			
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
			'estado_civil' => array(
				'class' => 'form-control',
				'type' => 'select',
				'__options' => $object->ESTADOS_CIVIL,
				
			),
			'nombres' => [
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			],
			'parentesco' => array(
				'type' => 'select',
				'__options' => $object->APODERADO_PARENTESCOS,
				'class' => 'form-control',
			),
			'email' => [
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			],
			'telefono_celular' => [
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			],
			'direccion' => [
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			],
			
		);

		$form = new Form($object, $options);
		return $form;
	}


	private function __getFormMatricula($object){
		$this->crystal->load('Form:*');

		$options = array(
			'id' => array('type' => 'hidden'),
			'alumno_id' => array(
				'type' => 'hidden',
				'__default' => $this->get->alumno_id
			),
			'sede_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->sedes, 'id', '$object->nombre'),
				'class' => 'form-control',
				'__first' => array('', '-- Seleccione --'),
				'data-bv-notempty' => 'true'
			),
			'grupo_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->getGrupos($object->grupo->anio), 'id', '$object->getNombre()'),
				'class' => 'form-control',
				'__first' => array('', '-- Seleccione --')
			),
			'costo_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->getCostos(), 'id', '$object->getMontos()'),
				'class' => 'form-control',
				'__first' => array('', '-- Seleccione --')
			),
			'nivel_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->niveles, 'id', '$object->nombre'),
				'class' => 'form-control',
				//'__first' => array('', '-- Seleccione --'),
				'value' => $object->grupo->nivel_id,
				':size' => 'col-sm-4',
				'data-bv-notempty' => 'true'
			),
			'grado' => array(
				'type' => 'select',
				'__options' => array_combine(range(1, 6), range(1, 6)),
				'class' => 'form-control',
				':size' => 'col-sm-4',
				'__first' => array('', '-- Seleccione --'),
				'value' => $object->grupo->grado,
				'data-bv-notempty' => 'true'
			),
			'seccion' => array(
				'type' => 'select',
				'__options' => array_combine($object->SECCIONES, $object->SECCIONES),
				'class' => 'form-control',
				':size' => 'col-sm-4',
				'__first' => array('', '-- Seleccione --'),
				'value' => $object->grupo->seccion,
				'data-bv-notempty' => 'true'
			),
			'anio' => array(
				'type' => 'text',
				'class' => 'form-control',
				'data-bv-integer' => 'true',
				'value' => empty($object->grupo->anio) ? $this->COLEGIO->anio_activo : $object->grupo->anio,
				'data-bv-notempty' => 'true'
			),
			'turno_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->turnos, 'id', '$object->nombre'),
				'class' => 'form-control',
				'__first' => array('', '-- Seleccione --'),
				'value' => $object->grupo->turno_id,
				'data-bv-notempty' => 'true'
			),
			'estado' => array(
				'type' => 'select',
				'__options' => $object->ESTADOS_MATRICULA,
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
			),
			'modalidad' => [
				'class' => 'form-control'
			]
		);

		$form = new Form($object, $options);
		return $form;
	}

	function save_matricular(){
		//print_r($this->post);
		$alumno = new Alumno(array(
			'colegio_id' => $this->COLEGIO->id,
			'codigo' => $this->post->codigo,
			'tipo_documento' => $this->post->tipo_documento,
			'nro_documento' => $this->post->nro_documento,
			'apellido_paterno' => $this->post->apellido_paterno,
			'apellido_materno' => $this->post->apellido_materno,
			'nombres' => $this->post->nombres,
			'sexo' => $this->post->sexo,
			'fecha_inscripcion' => $this->COLEGIO->setFecha($this->post->fecha_inscripcion),
			'fecha_nacimiento' => $this->COLEGIO->setFecha($this->post->fecha_nacimiento),
			'domicilio' => base64_encode(serialize(array(
				array(
					'direccion' => $this->post->domicilio
				)
			)))
		));
		$r = 0;
		if($alumno->is_valid()){
			if($alumno->save()){

				// APODERADO
				foreach($this->post->apoderado_tipo_documento As $keyApoderado => $tipo_documento){
					$apoderado = Apoderado::find(array(
						'conditions' => 'tipo_documento="'.$this->post->apoderado_tipo_documento[$keyApoderado].'" AND nro_documento="'.$this->post->apoderado_nro_documento[$keyApoderado].'"'
					));

					if(!$apoderado) $apoderado = new Apoderado();
					$apoderado->set_attributes(array(
						'colegio_id' => $this->COLEGIO->id,
						'tipo_documento' => $this->post->apoderado_tipo_documento[$keyApoderado],
						'nro_documento' => $this->post->apoderado_nro_documento[$keyApoderado],
						'apellido_paterno' => $this->post->apoderado_apellido_paterno[$keyApoderado],
						'apellido_materno' => $this->post->apoderado_apellido_materno[$keyApoderado],
						'nombres' => $this->post->apoderado_nombres[$keyApoderado],
						'estado_civil' => $this->post->apoderado_estado_civil[$keyApoderado],
						'email' => $this->post->apoderado_email[$keyApoderado],
						'parentesco' => $this->post->apoderado_parentesco[$keyApoderado],
						'telefono_fijo' => $this->post->telefonos,
						'direccion' => $this->post->domicilio
					));

					if($apoderado->save()){
						$familia = Familia::find_by_apoderado_id_and_alumno_id($apoderado->id, $alumno->id);
		                if(!$familia) $familia = Familia::create(array('apoderado_id' => $apoderado->id, 'alumno_id' => $alumno->id));
					}
				}


				// MATRICULA
				$grupo = $this->COLEGIO->getGrupo($this->post, true);
				$matricula = new Matricula();
				$matricula->set_attributes(array(
					'colegio_id' => $this->COLEGIO->id,
					'grupo_id' => $grupo->id,
					'alumno_id' => $alumno->id,
					'fecha_registro' => date('Y-m-d'),
					'personal_id' => $this->USUARIO->personal_id,
					'estado' => $this->post->estado,
					'modalidad' => $this->post->modalidad
					//'costo_id' => $this->post->costo_id,
				));

				// COSTO
				if($this->post->costo_id == -1){
					if(is_null($matricula->costo) || $matricula->costo->tipo == 'GENERAL'){
						$costo = new Costo();
					}

					if(!is_null($matricula->costo) && $matricula->costo->tipo == 'PERSONAL'){
						$costo = $matricula->costo;
					}

					$costo->set_attributes(array(
						'colegio_id' => $this->COLEGIO->id,
						'descripcion' => 'Costo Personalizado - '.$alumno->getFullName(),
						'matricula' => $this->post->costo_matricula,
						'pension' => $this->post->costo_pension,
						'agenda' => $this->post->costo_agenda,
						'tipo' => 'PERSONAL'
					));

					if($costo->save()){
						$matricula->costo_id = $costo->id;
					}

				}else{
					$matricula->costo_id = $this->post->costo_id;
				}

				$r = $matricula->save() ? 1 : 0;
				if($r == 1 && isset($this->post->registrarMatricula)){
					$costo = Costo::find_by_id($matricula->costo_id);
					$pago = new Pago(array(
						'colegio_id' => $this->COLEGIO->id,
						'matricula_id' => $matricula->id,
						'nro_pago' => 1,
						'monto' => $costo->matricula,
						'mora' => 0,
						'fecha_hora' => date('Y-m-d H:i'),
						//'nro_recibo' => '-',
						'tipo' => 0,
						'descripcion' => 'FORMULARIO DE MATRÍCULA',
						'observaciones' => '',
						'personal_id' => $this->USUARIO->personal_id,
						'estado_pago' => 'CANCELADO',
						'fecha_cancelado' => date('Y-m-d')
					));
					if($pago->is_valid()){

						$pago->save();
					}
					//print_r($pago->errors->get_all());
				}
			}
		}

		echo json_encode(array($r));
	}
	

	function save_matricula_online(){
		$alumno = Alumno::find_by_nro_documento($this->post->nro_documento);
		if(!$alumno)
			$alumno = new Alumno();
		/*if($checkAlumno){
			echo json_encode([-1]);
			return false;
		}*/
		
		$alumno->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'codigo' => $this->post->nro_documento,
			'tipo_documento' => $this->post->tipo_documento,
			'nro_documento' => $this->post->nro_documento,
			'apellido_paterno' => $this->post->apellido_paterno,
			'apellido_materno' => $this->post->apellido_materno,
			'nombres' => $this->post->nombres,
			'fecha_nacimiento' => $this->COLEGIO->setFecha($this->post->fecha_nacimiento),
			'registro_desde' => 'ONLINE',
            'seguro_id' => $this->post->seguro_id
		));

		$foto = uploadFile('foto_dni', ['jpg', 'jpeg', 'png']);
		if(!is_null($foto)){
			$alumno->foto_dni = $foto['new_name'];
		}else{
			echo json_encode([-2]);
			return false;
		}
			

		$r = 0;
		if($alumno->is_valid()){
			if($alumno->save()){
				// APODERADO
				$apoderado = Apoderado::find(array(
					'conditions' => 'tipo_documento="'.$this->post->apoderado_tipo_documento.'" AND nro_documento="'.$this->post->apoderado_nro_documento.'"'
				));

				if(!$apoderado) $apoderado = new Apoderado();
				$apoderado->set_attributes(array(
					'colegio_id' => $this->COLEGIO->id,
					'tipo_documento' => $this->post->apoderado_tipo_documento,
					'nro_documento' => $this->post->apoderado_nro_documento,
					'apellido_paterno' => $this->post->apoderado_apellido_paterno,
					'apellido_materno' => $this->post->apoderado_apellido_materno,
					'nombres' => $this->post->apoderado_nombres,
					
					'email' => $this->post->apoderado_email,
					'telefono_celular' => $this->post->apoderado_telefono_celular,
					'direccion' => $this->post->apoderado_direccion,
					'firma_digital' => $this->post->apoderado_firma_digital
				));

				if($apoderado->save()){
					$familia = Familia::find_by_apoderado_id_and_alumno_id($apoderado->id, $alumno->id);
					if(!$familia) $familia = Familia::create(array('apoderado_id' => $apoderado->id, 'alumno_id' => $alumno->id));
				}

				// MATRICULA
				$grupo = $this->COLEGIO->getGrupo((object) [
					'colegio_id' => 1,
					'sede_id' => 1,
					'seccion' => 'ONLINE',
					'anio' => $this->COLEGIO->anio_matriculas, // proximo año
					'turno_id' => 1,
					'grado' => $this->post->grado,
					'nivel_id' => $this->post->nivel_id
				], true);
				$matricula = new Matricula();
				$matricula->set_attributes(array(
					'colegio_id' => $this->COLEGIO->id,
					'grupo_id' => $grupo->id,
					'alumno_id' => $alumno->id,
					'fecha_registro' => date('Y-m-d'),
					'personal_id' => 1, #$this->USUARIO->personal_id,
					'estado' => 4, // temporal
					'registro_desde' => 'ONLINE' // online
				));

				if($this->post->nivel_id == 1)
					$costo_id = 6;
				if($this->post->nivel_id == 2){
					if($this->post->grado <= 3){
						$costo_id = 9;
					}else{
						$costo_id = 9; // 4373
					}
				}
				if($this->post->nivel_id == 3)
					$costo_id = 13;

                $costo = Costo::find($costo_id);
                $new_costo = Costo::create([
                    'colegio_id' => $this->COLEGIO->id,
                    'descripcion' => 'Costo Personalizado - '.$alumno->getFullName(),
                    'matricula' => $costo->matricula,
                    'pension' => $costo->pension,
                    'agenda' => $costo->agenda,
                    'tipo' => 'PERSONAL'
                ]);

				$matricula->costo_id = $new_costo->id;

				$r = $matricula->save() ? 1 : 0;
				if($r == 1){
					enviarEmailMatricula($matricula->id, $apoderado->id);
					enviarEmailMatriculaApoderado($matricula->id);
				}
			}
		}

		echo json_encode(array($r));
	}
}
