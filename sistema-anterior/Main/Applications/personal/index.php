<?php
class PersonalApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => 'perfil, subir_foto',
			'__exclude' => 'asistencia_publico, save_asistencia_publico'
		]);
	}

	function save_asistencia_publico(){
		
		$personal = Personal::find_by_colegio_id_and_nro_documento($this->COLEGIO->id, $this->post->dni);
	
		if(!$personal){
			
			$this->render(['personal' => null]);
			return false;
		}

		$hora = date('H:i');

		$registros = Trabajador_Asistencia::find_all_by_fecha_and_trabajador_id(date('Y-m-d'), $personal->id);

		$data = array();

		if(count($registros) == 0){

			$asistencia = new Trabajador_Asistencia(array(
				'trabajador_id' => $personal->id,
				'fecha' => date('Y-m-d'),
				'descuento_minuto' => $this->COLEGIO->descuento_minuto,
				'hora_permitida' => $personal->hora_entrada,
				'hora_real' => $hora,
				'tipo' => 'ENTRADA'
			));

			$minutos = abs(minuteDifference($asistencia->hora_permitida, $asistencia->hora_real));
			$asistencia->minutos_tardanza = (strtotime($asistencia->hora_real) > strtotime($asistencia->hora_permitida)) ? $minutos : 0;
			$asistencia->descuento = $asistencia->minutos_tardanza * $asistencia->descuento_minuto;
			$asistencia->save();
		}

		if(count($registros) == 1 && $registros[0]->tipo == 'ENTRADA'){
			$asistencia = new Trabajador_Asistencia(array(
				'trabajador_id' => $personal->id,
				'fecha' => date('Y-m-d'),
				'descuento_minuto' => $this->COLEGIO->descuento_minuto,
				'hora_permitida' => $personal->hora_salida,
				'hora_real' => $hora,
				'tipo' => 'SALIDA'
			));

			$minutos = abs(minuteDifference($asistencia->hora_permitida, $asistencia->hora_real));
			$asistencia->minutos_tardanza = (strtotime($asistencia->hora_real) < strtotime($asistencia->hora_permitida)) ? $minutos : 0;
			$asistencia->descuento = $asistencia->minutos_tardanza * $asistencia->descuento_minuto;
			$asistencia->save();
		}

		if(count($registros) >= 2){
			$data['tipo'] = 'NINGUNO';
		}

		$this->render(array('personal' => $personal, 'asistencia' => $asistencia));
	}
	
	function index($r){
		$personal = Personal::all(array(
			'conditions' => 'colegio_id="'.$this->COLEGIO->id.'"'
		));
		$this->render(array('personal' => $personal));
	}
	
	function subir_foto(){
		$foto = uploadFile('foto', ['jpg', 'jpeg', 'png'], './Static/Image/Fotos');
		$r = 0;
		if(!is_null($foto)){
			$this->USUARIO->personal->foto = $foto['new_name'];
			$r = $this->USUARIO->personal->save() ? 1 : 0;
		}


		echo json_encode([$r]);
	}

	function save(){
		$r = -5;
		$this->personal->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'sexo' => $this->post->sexo,
			'telefono_fijo' => $this->post->telefono_fijo,
			'telefono_celular' => $this->post->telefono_celular,
			'linea_celular' => $this->post->linea_celular,
			'direccion' => $this->post->direccion,
			
			'nombres' => $this->post->nombres,
			'apellidos' => $this->post->apellidos,
			'tipo_documento' => $this->post->tipo_documento,
			'nro_documento' => $this->post->nro_documento,
			'grado_instruccion' => $this->post->grado_instruccion,
			'profesion' => $this->post->profesion,
			'cargo' => $this->post->cargo,
			'email' => $this->post->email,
			'fecha_nacimiento' => $this->post->fecha_nacimiento,
			'pais_nacimiento_id' => $this->post->pais_nacimiento_id,
			'estado_civil' => $this->post->estado_civil,
			'tipo_contrato' => $this->post->tipo_contrato,
			'observaciones' => $this->post->observaciones,
			'departamento_nacimiento_id' => $this->post->departamento_nacimiento_id,
			'provincia_nacimiento_id' => $this->post->provincia_nacimiento_id,
			'distrito_nacimiento_id' => $this->post->distrito_nacimiento_id,
			'domicilio_pais_id' => $this->post->domicilio_pais_id,
			'domicilio_departamento_id' => $this->post->domicilio_departamento_id,
			'domicilio_provincia_id' => $this->post->domicilio_provincia_id,
			'domicilio_distrito_id' => $this->post->domicilio_distrito_id,
			'fecha_ingreso' => $this->post->fecha_ingreso,
			'hora_entrada' => $this->post->hora_entrada,
			'hora_salida' => $this->post->hora_salida
		));
		
		$foto = uploadFile('foto', ['jpg', 'jpeg', 'png'], './Static/Image/Fotos');
		if(!is_null($foto)){
			$this->personal->foto = $foto['new_name'];
		}

		if($this->personal->is_valid()){
			$r = $this->personal->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->personal->id, 'errors' => $this->personal->errors->get_all()));
	}

	function borrar($r){
		$r = $this->personal->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function acceso(){
		$usuario = !is_null($this->personal->usuario) ? $this->personal->usuario : new Usuario(array(
			'personal_id' => $this->personal->id
		));
		
		$permisos = array(
			'ALUMNOS' => 'Alumnos',
			'MATRICULAS' => 'Matriculas',
			'APODERADOS' => 'Apoderados',
			'GRUPOS' => 'Grupos',
			'CURSOS' => 'Cursos',
			'PERSONAL' => 'Personal',
			'PAGOS' => 'Pagos',
			'CAJA' => 'Caja',
			'FACTURACION' => 'Facturación',
			'REPORTES' => 'Reportes',
			'CANCHA' => 'Alquiler Cancha',
			'ARCHIVOS_GENERAL' => 'Archivos',
			'COMPENDIOS' => 'Compendios',
			'SYLABUS' => 'Sylabus'
		);

		$form = $this->__getFormUsuario($usuario);
		$this->render(array('usuario' => $usuario, 'form' => $form, 'permisos' => $permisos));
	}
	
	function do_acceso(){
		$usuario = !empty($this->post->id) ? Usuario::find_by_id_and_colegio_id($this->post->id, $this->COLEGIO->id) : new Usuario(array(
			'personal_id' => $this->post->personal_id,
			'colegio_id' => $this->COLEGIO->id
		));
		
		$usuario->usuario = $this->post->usuario;
		$usuario->tipo = $this->post->tipo;
		$usuario->estado = $this->post->estado;
		$usuario->cambiar_password = $this->post->cambiar_password;

		if(!empty($this->post->password)){
			$usuario->password = sha1($this->post->password);
		}
		

		$usuario->setPermisos($this->post->permisos);
		
		
		$r = -5;
		if($usuario->is_valid() && $usuario->isUniqueInCollege()){
			$r = $usuario->save() ? 1 : 0;
		}
		
		echo json_encode(array($r, 'errors' => $usuario->errors->get_all()));
	}

	function horario_json(){
		$horas = Personal_Horario::find_all_by_personal_id_and_colegio_id_and_anio($this->params->id, $this->COLEGIO->id, $this->COLEGIO->anio_activo);
		$data = array();
		foreach($horas As $hora){
			$data[] = array(
				'id' => $hora->id,
				'start' => $hora->fecha.' '.$hora->inicio,
				'end' => $hora->fecha.' '.$hora->fin,
				'title' => $hora->titulo."\n".$hora->grupo,
			);
		}
		echo json_encode($data);
	}

	function horario(){
		$this->crystal->load('Form:*');
		$horario = isset($this->params->id) ? Personal_Horario::find($this->params->id) : new Personal_Horario();
		if(!empty($this->get->personal_id))
			$personal = Personal::find([
				'conditions' => ['sha1(id) = ?', $this->get->personal_id]
			]);


		$form = new Form($horario, array(
			'id' => array(
				'type' => 'hidden'
			),
			'titulo' => [
				'class' => 'form-control'
			],
			'grupo' => [
				'class' => 'form-control'
			],
			'personal_id' => array(
				'type' => 'hidden',
				'__default' => $personal->id
			),
			'inicio' => array(
				'type' => 'hidden',
				'__default' => $this->get->inicio
			),
			'fin' => array(
				'type' => 'hidden',
				'__default' => $this->get->fin
			),
			
		));

		$this->render(array('horario' => $horario, 'form' => $form));
	}

	function save_horario(){
		$horario = !empty($this->params->id) ? Personal_Horario::find($this->params->id) : new Personal_Horario();
		
		
		if($this->post->update == 'true'){
			$horario->set_attributes(array(
				'fecha' => date('Y-m-d', strtotime($this->post->inicio)),
				'inicio' => date('H:i:s', strtotime($this->post->inicio)),
				'fin' => date('H:i:s', strtotime($this->post->fin)),
				'dia' => date('w', strtotime($this->post->inicio)),
			));
		}else{
			if($horario->is_new_record()){
				$horario->set_attributes(array(
					'colegio_id' => $this->COLEGIO->id,
					'personal_id' => $this->post->personal_id,
					'anio' => $this->COLEGIO->anio_activo,
					'titulo' => $this->post->titulo,
					'grupo' => $this->post->grupo,
					'fecha' => date('Y-m-d', strtotime($this->post->inicio)),
					'inicio' => date('H:i:s', strtotime($this->post->inicio)),
					'fin' => date('H:i:s', strtotime($this->post->fin)),
					'dia' => date('w', strtotime($this->post->inicio)),
				));
			}else{
				$horario->set_attributes(array(
					'titulo' => $this->post->titulo,
					'grupo' => $this->post->grupo,
				));
			}
		}
		
	
		$r = 0;
		if($horario->is_valid()){
			$r = $horario->save() ? 1 : 0;
		}

		echo json_encode(array($r));
	}

	function horario_pdf(){
		
		$horas = Personal_Horario::all(array(
			'select' => 'DISTINCT inicio, fin',
			'conditions' => 'anio = "'.$this->COLEGIO->anio_activo.'" AND personal_id = "'.$this->personal->id.'"',
			'order' => 'inicio ASC'
		)); 
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();

		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->setFillColor(240, 240, 240);
		
		$pdf->ln(120);
		$pdf->addPage();
		//$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->cell(0,5,'HORARIO DE CLASES',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, $this->personal->getFullName(),0, 0, 'R');
		
		$pdf->setFont('helvetica', 'b', 9);
		$pdf->ln(15);
		$pdf->cell(30, 5, '-', 1, 0, 'C', 1);

		foreach($this->COLEGIO->DIAS As $dia){
			$pdf->cell(34, 5, strtoupper($dia), 1, 0, 'C', 1);

		}
		
		
		$pdf->ln(5);
		
		foreach($horas As $hora){
			//print_r($hora->attributes());
		
			$pdf->cell(30, 10, date('h:i A', strtotime($hora->inicio)).' - '.date('h:i A', strtotime($hora->fin)), 1, 0, 'C', 0, 0, 1);
			$i = 1;
			foreach($this->COLEGIO->DIAS As $key_dia => $dia){
				$conditions = 'personal_id="'.$this->personal->id.'" AND anio = "'.$this->COLEGIO->anio_activo.'" AND dia="'.($key_dia + 1).'" AND inicio="'.$hora->inicio.'" AND fin="'.$hora->fin.'"';
				
				
				$horario = Personal_Horario::find(array(
					'conditions' => $conditions,
				));

				//if($horario){
					if(empty($horario->grupo)){
						$pdf->cell(34, 10, $horario->titulo, 1, 0, 'C', 0, 0, 1);
					}else{
						
						$pdf->cell(34, 5, $horario->titulo, 1, 0, 'C', 0, 0, 1);
						$x = $pdf->GetX() - 34; $y = $pdf->GetY() + 5;
						$pdf->SetFont('helvetica', '', 7);
						
						$pdf->MultiCell(34, 5, $horario->grupo, 1, 'C', 0, 0, $x, $y, true, 1, false, false, 5, 'M');
						//$pdf->cell(34, 5, $horario->titulo, 1, 0, 'C', 0, 0, 1);
						$pdf->SetFont('helvetica', 'b', 9);
						$pdf->setY($y - 5);
						$pdf->setx($x + 34);

					}
				//}
				++$i;
			}
			$data .= '</tr>';$pdf->ln(10);
		}

	
		//$pdf->ln(10);
		//$pdf->writeHTML($data);


		$pdf->output();
	}

	function borrar_horario(){
		$horario = !empty($this->params->id) ? Personal_Horario::find($this->params->id) : new Personal_Horario();
		$r = $horario->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'personal', true);
		$this->personal = !empty($this->params->id) ? Personal::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Personal();
		$this->context('personal', $this->personal); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->personal);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
		
			'sexo' => array(
				'type' => 'select',
				'__options' => $object->SEXOS,
				'class' => 'form-control',
				
			),
			'telefono_fijo' => array(
				'class' => 'form-control',
				
			),
			'telefono_celular' => array(
				'class' => 'form-control',
				
			),
			'linea_celular' => array(
				'type' => 'select',
				'__options' => $object->LINEAS_CELULAR,
				'class' => 'form-control',
				
			),
			'direccion' => array(
				'class' => 'form-control',
				
			),
			'foto' => array(
				'class' => 'form-control',
				'type' => 'file'
				//
			),
			'nombres' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'apellidos' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo_documento' => array(
				'type' => 'select',
				'__options' => $object->TIPOS_DOCUMENTO,
				'class' => 'form-control',
				
			),
			'nro_documento' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'grado_instruccion' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'__options' => $object->GRADOS_INSTRUCCION,
				'class' => 'form-control',
				
			),
			'profesion' => array(
				'class' => 'form-control',
				
			),
			'cargo' => array(
				'class' => 'form-control',
				
			),
			'email' => array(
				'class' => 'form-control',
				
			),
			'fecha_nacimiento' => array(
				'class' => 'form-control calendar',
				'__default' => date('Y-m-d')
				
			),
			'pais_nacimiento_id' => array(
				'type' => 'select',
				'__options' => array(Pais::all(), 'id', '$object->nombre'),
				'__first' => array('', '-- Seleccione --'),
				'__dataset' => true,
				'class' => 'form-control',
				
			),
			'estado_civil' => array(
				'type' => 'select',
				'__options' => $object->ESTADOS_CIVIL,
				'class' => 'form-control',
				
			),
			'tipo_contrato' => array(
				'type' => 'select',
				'__options' => $object->TIPOS_CONTRATO,
				'class' => 'form-control',
				
			),
			'observaciones' => array(
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
			'domicilio_pais_id' => array(
				'type' => 'select',
				'__options' => array(Pais::all(), 'id', '$object->nombre'),
				'__first' => array('', '-- Seleccione --'),
				'__dataset' => true,
				'class' => 'form-control',
				
			),
			'domicilio_departamento_id' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				
			),
			'domicilio_provincia_id' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				
			),
			'domicilio_distrito_id' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				
			),
			'fecha_ingreso' => array(
				'class' => 'form-control calendar',
				'__default' => date('Y-m-d')
			),
			'hora_entrada' => array(
				'class' => 'form-control',
				'type' => 'time'
				
			),
			'hora_salida' => array(
				'class' => 'form-control',
				'type' => 'time'
				
			),
			'resena' => array(
				'class' => 'form-control',
				
			),
			'mostrar_app' => array(
				'class' => 'form-control',
				
			),
			'link_aula' => array(
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
			'personal_id' => array(
				'type' => 'hidden'
			),
			'tipo' => array(
				'class' => 'form-control',
                'type' => 'select',
                '__options' => array_combine(TraitConstants::ALLOWED_USER_TYPES, TraitConstants::ALLOWED_USER_TYPES)
			),
			'usuario' => array(
				'__label' => 'Nombre de Usuario',
				'data-bv-notEmpty' => 'true',
				'class' => 'form-control'
			),
			'password' => array(
				'type' => 'password',
				'__label' => 'Contraseña',
				'value' => '',
				'data-bv-notempty' => $object->is_new_record() ? 'true' : 'false',
				'class' => 'form-control'
			),
			'cambiar_password' => array(
				'class' => 'form-control'
			),
			'ms_email' => array(
				'__label' => 'Cuenta Office365',
				'data-bv-notempty' => 'true'
				//':size' => 'col-sm-4'
			),
			'estado' => array(
				'class' => 'form-control'
			),
		);
		return new Form($object, $options);
	}
	
}
