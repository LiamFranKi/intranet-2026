<?php
class GruposApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'ALUMNO' => 'horario, imprimir_horario',
			'APODERADO' => 'horario, imprimir_horario',
			'DOCENTE' => 'docente, imprimir_horario_docente, lista_alumnos, asistencia, save_asistencia, asignaturas',
            'ASISTENCIA' => 'index, lista_alumnos, asistencia, save_asistencia',
			'__exclude' => 'imprimir_horario, imprimir_horario_horizontal'
		]);
	}
	
	function index($r){
		$anio = empty($this->get->anio) ? $this->COLEGIO->anio_activo : $this->get->anio;
		//$grupos = $this->COLEGIO->getGrupos($anio);
		$sedes = Sede::all();
		$this->render(array('grupos' => $grupos, 'anio' => $anio, 'sedes' => $sedes));
	}

	function docente(){
		$grupos = $this->USUARIO->personal->getGruposAsignados($this->COLEGIO->anio_activo);
		$this->render(['grupos' => $grupos]);
	}

    function asignaturas(){
        $asignaturas = $this->grupo->getAsignaturas();
        $this->render(['asignaturas' => $asignaturas]);
    }

	function lista_alumnos(){
		$matriculas = $this->grupo->getMatriculas();
		$this->render(array('matriculas' => $matriculas));
	}
	
	function save(){
		$r = -5;
		$this->grupo->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'sede_id' => $this->post->sede_id,
			'nivel_id' => $this->post->nivel_id,
			'grado' => $this->post->grado,
			'seccion' => $this->post->seccion,
			'anio' => $this->post->anio,
			'turno_id' => $this->post->turno_id,
			
			'enlace_archivos' => $this->post->enlace_archivos,
			
			'aula_virtual' => $this->post->aula_virtual,
			'tutor_id' => $this->post->tutor_id,
		));

		$horario = uploadFile('horario_virtual');
		if(!is_null($horario)){
			$this->grupo->horario_virtual = $horario['new_name'];
		}
		
		if($this->grupo->is_valid()){
			$r = $this->grupo->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->grupo->id, 'errors' => $this->grupo->errors->get_all()));
	}

	function borrar($r){
		$r = $this->grupo->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function modificar_costos(){
		$matriculas = $this->grupo->getMatriculas();
		
		$this->render(array('matriculas' => $matriculas, 'form' => $form));
	}

	function save_costos(){
		//print_r($this->post);

		foreach($this->post->costos As $matricula_id => $data){
			$matricula = Matricula::find($matricula_id);
			$costo = Costo::find($data['costo_id']);

			if($costo->tipo == 'PERSONAL'){
				$costo->update_attributes([
					'matricula' => $data['matricula'],
					'pension' => $data['pension'],
					'agenda' => $data['agenda']
				]);
			}else{
				$costo = Costo::create([
					'colegio_id' => $this->COLEGIO->id,
					'descripcion' => 'Costo Personalizado - '.$matricula->alumno->getFullName(),
					'matricula' => $data['matricula'],
					'pension' => $data['pension'],
					'agenda' => $data['agenda'],
					'tipo' => 'PERSONAL'
				]);

				$matricula->costo_id = $costo->id;
				$matricula->save();
			}

		}

		echo json_encode([1]);
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'grupos', true);
		$this->grupo = !empty($this->params->id) ? Grupo::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Grupo();
		$this->context('grupo', $this->grupo); // set to template
		if(in_array($this->params->Action, array('form', 'asistencia'))){
			$this->form = $this->__getForm($this->grupo);
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
			'sede_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array(Sede::all(), 'id', '$object->nombre'),
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nivel_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->niveles, 'id', '$object->nombre'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'grado' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'seccion' => array(
				'type' => 'select',
				'__options' => array_combine($object->SECCIONES, $object->SECCIONES),
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'anio' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__default' => $this->COLEGIO->anio_activo
			),
			'turno_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->turnos, 'id', '$object->nombre'),
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tutor_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'enlace_archivos' => array(
				'class' => 'form-control',
				//'data-bv-notempty' => 'true'
			),
			'registro_habilitado' => array(
				'class' => 'form-control',
				//'data-bv-notempty' => 'true'
			),
			'aula_virtual' => array(
				'class' => 'form-control',
				//'data-bv-notempty' => 'true'
			),
			'horario_virtual' => array(
				'class' => 'form-control',
				'type' => 'file'
				//'data-bv-notempty' => 'true'
			),
			'tutor_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__dataset' => true,
				'type' => 'select',
				'__options' => [Personal::all(['order' => 'apellidos ASC']), 'id', '$object->getFullName()'],
				'__first' => ['', '-- Seleccione --']
			),
		);
		
		$form = new Form($object, $options);
		return $form;
	}



	function imprimir_horario(){
		$grupo = Grupo::find($this->get->grupo_id);
		$horas = Grupo_Horario::all(array(
			'select' => 'DISTINCT hora_inicio, hora_final',
			'conditions' => 'grupo_id="'.$grupo->id.'"',
			'order' => 'STR_TO_DATE(hora_inicio, "%l:%i %p") ASC'
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
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->cell(0,5,'HORARIO DE CLASES',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, $grupo->getNombre(),0, 0, 'R');
		
		$pdf->setFont('helvetica', 'b', 9);
		$pdf->ln(15);
		$pdf->cell(30, 5, '-', 1, 0, 'C', 1);

		$w = 205 / 6;

		foreach($this->COLEGIO->DIAS As $dia){
			$pdf->cell($w, 5, mb_strtoupper($dia, 'utf-8'), 1, 0, 'C', 1);
		}

		foreach($horas As $hora){
			$pdf->ln(5);
			$pdf->cell(30, 5, $hora->hora_inicio.' - '.$hora->hora_final, 1, 0, 'C', 0, 0, 1);
			foreach($this->COLEGIO->DIAS As $key_dia => $dia){
				$conditions = 'grupo_id="'.$grupo->id.'" AND dia="'.$key_dia.'" AND hora_inicio="'.$hora->hora_inicio.'" AND hora_final="'.$hora->hora_final.'"';
				
				if(isset($this->get->asignatura_id)){
					$conditions .= ' AND asignatura_id="'.$this->get->asignatura_id.'"';
				}

				$horario = Grupo_Horario::find(array(
					'conditions' => $conditions
				));

				if($horario->asignatura_id < 0){
					if(isset($this->horarioItems[$horario->asignatura_id])){
						$pdf->cell($w, 5, $this->horarioItems[$horario->asignatura_id], 1, 0, 'C', 0, 0, 1);
					}else{
						$pdf->cell($w, 5, $horario->descripcion, 1, 0, 'C', 0, 0, 1);
					}
				}else{
					$pdf->cell($w, 5, $horario->asignatura->curso->nombre, 1, 0, 'C', 0, 0, 1);	
				}
				
				
			}
		}
		$pdf->ln(10);
		$pdf->setFont('helvetica', 'b', 13);
		$pdf->cell(0, 5, 'DOCENTES');
		$pdf->ln(5);

		foreach($grupo->getAsignaturas() As $asignatura){
			if(isset($this->get->asignatura_id) && $asignatura->id != $this->get->asignatura_id) continue;
			$pdf->ln(5);
			$pdf->setFont('helvetica', 'b', 9);
			$pdf->cell(70, 5, $asignatura->curso->nombre, 1, 0, 'L', 1, 0, 1);
			$pdf->setFont('helvetica', '', 9);
			$pdf->cell(130, 5, $asignatura->personal->getFullName(), 1, 0, 'L', 0, 0, 1);
		}

		$pdf->output();
	}

	function imprimir_horario_horizontal(){
		$grupo = Grupo::find($this->get->grupo_id);
		$horas = Grupo_Horario::all(array(
			'select' => 'DISTINCT hora_inicio, hora_final',
			'conditions' => 'grupo_id="'.$grupo->id.'"',
			'order' => 'STR_TO_DATE(hora_inicio, "%l:%i %p") ASC'
		));
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->setFillColor(240, 240, 240);
		$pdf->ln(120);
		$pdf->addPage('L');
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->cell(0,5,'HORARIO DE CLASES',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, $grupo->getNombre(),0, 0, 'R');
		
		$pdf->setFont('helvetica', 'b', 9);
		$pdf->ln(15);
		$pdf->cell(35, 10, '-', 1, 0, 'C', 1);

		$w = 302 / 6;

		foreach($this->COLEGIO->DIAS As $dia){
			$pdf->cell($w, 10, strtoupper($dia), 1, 0, 'C', 1);
		}

		foreach($horas As $hora){
			$pdf->ln(10);
			$pdf->cell(35, 10, $hora->hora_inicio.' - '.$hora->hora_final, 1, 0, 'C', 0, 0, 1);
			foreach($this->COLEGIO->DIAS As $key_dia => $dia){
				$conditions = 'grupo_id="'.$grupo->id.'" AND dia="'.$key_dia.'" AND hora_inicio="'.$hora->hora_inicio.'" AND hora_final="'.$hora->hora_final.'"';
				
				if(isset($this->get->asignatura_id)){
					$conditions .= ' AND asignatura_id="'.$this->get->asignatura_id.'"';
				}

				$horario = Grupo_Horario::find(array(
					'conditions' => $conditions
				));

				if($horario->asignatura_id < 0){
					if(isset($this->horarioItems[$horario->asignatura_id])){
						$pdf->cell($w, 10, $this->horarioItems[$horario->asignatura_id], 1, 0, 'C', 0, 0, 1);
					}else{
						$pdf->cell($w, 10, mb_strtoupper($horario->descripcion, 'utf-8'), 1, 0, 'C', 0, 0, 1);
					}
				}else{
					$currentX = $pdf->getX();

					$pdf->cell($w, 5, mb_strtoupper($horario->asignatura->curso->nombre, 'utf-8'), 1, 0, 'C', 0, 0, 1);	
					$pdf->setY($pdf->getY() + 5);
					$pdf->setX($currentX);
					$pdf->setFont('helvetica', '', 9);
					$pdf->cell($w, 5, !is_null($horario) ? $horario->asignatura->personal->getFullName() : '', 1, 0, 'C', 0, 0, 1);
					$pdf->setFont('helvetica', 'b', 9);
					$lastX = $pdf->getX();

					$pdf->setY($pdf->getY() - 5);
					$pdf->setX($lastX);
				}
			}
		}


		$pdf->output();
	}

	function setLogo($pdf){
		//$pdf->image('./Static/Image/Insignias/'.$this->COLEGIO->login_insignia, 10, 4, 23, 17);
	}
	

	function imprimir_horario_docente(){
		$docente = Personal::find($this->get->personal_id);
		$horas = Grupo_Horario::all(array(
			'select' => 'DISTINCT hora_inicio, hora_final',
			'conditions' => 'grupos_horarios.anio = "'.$this->COLEGIO->anio_activo.'" AND (asignaturas.personal_id = "'.$this->get->personal_id.'" OR grupos_horarios.personal_id = "'.$this->get->personal_id.'")',
			'joins' => 'LEFT JOIN asignaturas ON asignaturas.id = grupos_horarios.asignatura_id',
			'order' => 'STR_TO_DATE(hora_inicio, "%l:%i %p") ASC'
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
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->cell(0,5,'HORARIO DE CLASES',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, $docente->getFullName(),0, 0, 'R');
		
		$pdf->setFont('helvetica', 'b', 9);
		$pdf->ln(15);
		$pdf->cell(30, 5, '-', 1, 0, 'C', 1);

		foreach($this->COLEGIO->DIAS As $dia){
			$pdf->cell(34, 5, strtoupper($dia), 1, 0, 'C', 1);

		}
		
		
		$pdf->ln(5);
		
		foreach($horas As $hora){
			//print_r($hora->attributes());
		
			$pdf->cell(30, 10, $hora->hora_inicio.' - '.$hora->hora_final, 1, 0, 'C', 0, 0, 1);
			$i = 1;
			foreach($this->COLEGIO->DIAS As $key_dia => $dia){
				$conditions = 'anio = "'.$this->COLEGIO->anio_activo.'" AND dia="'.$key_dia.'" AND hora_inicio="'.$hora->hora_inicio.'" AND hora_final="'.$hora->hora_final.'"';
				if(isset($this->get->personal_id)){
					$conditions .= ' AND asignaturas.personal_id="'.$this->get->personal_id.'"';
				}
				
				$horario = Grupo_Horario::find(array(
					'conditions' => $conditions,
					'joins' => array('asignatura')
				));
				
				if(!$horario){
					$horario = Grupo_Horario::find(array(
						'conditions' => 'anio = "'.$this->COLEGIO->anio_activo.'" AND dia="'.$key_dia.'" AND hora_inicio="'.$hora->hora_inicio.'" AND hora_final="'.$hora->hora_final.'" AND personal_id="'.$this->get->personal_id.'"'
					));
				}
				//if($horario){
					if($horario->asignatura_id < 0){
						if(isset($this->horarioItems[$horario->asignatura_id])){
							$pdf->cell(34, 10, $this->horarioItems[$horario->asignatura_id], 1, 0, 'C', 0, 0, 1);
						}else{
							$pdf->cell(34, 10, $horario->descripcion, 1, 0, 'C', 0, 0, 1);
						}
					}else{
						$asignaturaNombre = isset($horario) ? $horario->asignatura->curso->nombre : '';
						$pdf->cell(34, 5, $asignaturaNombre, 1, 0, 'C', 0, 0, 1);
						$x = $pdf->GetX() - 34; $y = $pdf->GetY() + 5;
						$pdf->SetFont('helvetica', '', 7);
						$grupoNombre = isset($horario) && !is_null($horario->grupo) ? $horario->grupo->getNombreShort2() : '';
						$pdf->MultiCell(34, 5, $grupoNombre, 1, 'C', 0, 0, $x, $y, true, 1, false, false, 5, 'M');
						$pdf->SetFont('helvetica', 'b', 9);
						$pdf->setY($y - 5);
						$pdf->setx($x + 34);
						//$pdf->ln(5);
						//$pdf->cell($i * ($i == 1 ? 30 : 34));
						//$pdf->cell(34, 5, $horario->grupo->getNombreShort2(), 1, 0, 'C', 0, 0, 1);
						//$pdf->ln(-5);
						
						//$pdf->MultiCell(34, 10, $horario->asignatura->curso->nombre.' ('.$horario->grupo->getNombreShort2().')', 1, 'C', 0, 0, null, null, 0, 1);
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


	function imprimir_horario_docente_horizontal(){
		$docente = Personal::find($this->get->personal_id);
		$horas = Grupo_Horario::all(array(
			'select' => 'DISTINCT hora_inicio, hora_final',
			'conditions' => 'grupos_horarios.anio = "'.$this->COLEGIO->anio_activo.'" AND (asignaturas.personal_id = "'.$this->get->personal_id.'" OR grupos_horarios.personal_id = "'.$this->get->personal_id.'")',
			'joins' => 'LEFT JOIN asignaturas ON asignaturas.id = grupos_horarios.asignatura_id',
			'order' => 'STR_TO_DATE(hora_inicio, "%l:%i %p") ASC'
		)); 
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();

		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->setFillColor(240, 240, 240);
		
		$pdf->ln(120);
		$pdf->addPage('L');
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->cell(0,5,'HORARIO DE CLASES',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, $docente->getFullName(),0, 0, 'R');
		
		$pdf->setFont('helvetica', 'b', 9);
		$pdf->ln(15);
		$pdf->cell(35, 10, '-', 1, 0, 'C', 1);

		foreach($this->COLEGIO->DIAS As $dia){
			$pdf->cell(50, 10, strtoupper($dia), 1, 0, 'C', 1);

		}
		
		
		$pdf->ln(10);
		
		foreach($horas As $hora){
			//print_r($hora->attributes());
		
			$pdf->cell(35, 10, $hora->hora_inicio.' - '.$hora->hora_final, 1, 0, 'C', 0, 0, 1);
			$i = 1;
			foreach($this->COLEGIO->DIAS As $key_dia => $dia){
				$conditions = 'anio = "'.$this->COLEGIO->anio_activo.'" AND dia="'.$key_dia.'" AND hora_inicio="'.$hora->hora_inicio.'" AND hora_final="'.$hora->hora_final.'"';
				if(isset($this->get->personal_id)){
					$conditions .= ' AND asignaturas.personal_id="'.$this->get->personal_id.'"';
				}
				
				$horario = Grupo_Horario::find(array(
					'conditions' => $conditions,
					'joins' => array('asignatura')
				));
				
				if(!$horario){
					$horario = Grupo_Horario::find(array(
						'conditions' => 'anio = "'.$this->COLEGIO->anio_activo.'" AND dia="'.$key_dia.'" AND hora_inicio="'.$hora->hora_inicio.'" AND hora_final="'.$hora->hora_final.'" AND personal_id="'.$this->get->personal_id.'"'
					));
				}
				//if($horario){
					if($horario->asignatura_id < 0){
						if(isset($this->horarioItems[$horario->asignatura_id])){
							$pdf->cell(50, 10, $this->horarioItems[$horario->asignatura_id], 1, 0, 'C', 0, 0, 1);
						}else{
							$pdf->cell(50, 10, $horario->descripcion, 1, 0, 'C', 0, 0, 1);
						}
					}else{
						$asignaturaNombre = isset($horario) ? $horario->asignatura->curso->nombre : '';
						$pdf->cell(50, 5, $asignaturaNombre, 1, 0, 'C', 0, 0, 1);
						$x = $pdf->GetX() - 50; $y = $pdf->GetY() + 5;
						$pdf->SetFont('helvetica', '', 7);
						$grupoNombre = isset($horario) ? $horario->grupo->getNombreShort2() : '';
						$pdf->MultiCell(50, 5, $grupoNombre, 1, 'C', 0, 0, $x, $y, true, 1, false, false, 5, 'M');
						$pdf->SetFont('helvetica', 'b', 9);
						$pdf->setY($y - 5);
						$pdf->setx($x + 50);
						//$pdf->ln(5);
						//$pdf->cell($i * ($i == 1 ? 30 : 34));
						//$pdf->cell(34, 5, $horario->grupo->getNombreShort2(), 1, 0, 'C', 0, 0, 1);
						//$pdf->ln(-5);
						
						//$pdf->MultiCell(34, 10, $horario->asignatura->curso->nombre.' ('.$horario->grupo->getNombreShort2().')', 1, 'C', 0, 0, null, null, 0, 1);
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

	function asistencia(){
		$fecha = $this->get->fecha ?? date('Y-m-d');
		$matriculas = $this->grupo->getMatriculas();

		$this->render(['fecha' => $fecha, 'matriculas' => $matriculas]);
	}

	function save_asistencia(){
		
		Matricula_Asistencia::table()->delete(Array(
			'matricula_id' => $this->post->matricula_id,
			'fecha' => date('Y-m-d', strtotime($this->post->fecha))
		));
		
		$asistencia = new Matricula_Asistencia(Array(
            'created_at' => date('Y-m-d H:i:s'),
			'matricula_id' => $this->post->matricula_id,
			'fecha' => date('Y-m-d', strtotime($this->post->fecha)),
			'tipo' => $this->post->asistencia,
		));
		
		$r = $asistencia->save() ? 1 : 0;

		if($r == 1){

			$tiposAlerta = array(
				'TARDANZA_INJUSTIFICADA' => 'TARDANZA',
				'FALTA_INJUSTIFICADA' => 'FALTA',
			);
			
			if(isset($tiposAlerta[$asistencia->tipo])){
				$alertas = $this->COLEGIO->getAlertas(array(
					'tipo' => $tiposAlerta[$asistencia->tipo],
					'estado' => 'ACTIVO'
				));

				$matricula = Matricula::find($this->post->matricula_id);
				foreach($alertas As $alerta){
					foreach($matricula->alumno->familias As $familia){
						//$alerta->sendOther($familia->apoderado, $matricula, date('Y-m-d', strtotime($this->post->fecha)));
						$alerta->sendToDevice($familia->apoderado, $matricula, date('Y-m-d', strtotime($this->post->fecha)));
					}
				}
			}
		}

		echo json_encode(Array($r));
	}


    function codigos_qr(){
		
		
		$grupo = Grupo::find([
            'conditions' => ['sha1(id) = ?', $this->params->id]
        ]);
		
		$matriculas = Matricula::all(Array(
			'conditions' => ['sha1(grupo_id) = ?', $this->params->id],
			'include' => Array('alumno'),
            'joins' => ['alumno'],
			'order' => 'alumnos.apellido_paterno asc, alumnos.apellido_materno asc, alumnos.nombres asc'
		));

		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->addPage();
		$pdf->setFont('Helvetica', '', 9);
		$style = array(
			'position' => '',
			'align' => 'C',
			'stretch' => true,
			'fitwidth' => true,
			'cellfitalign' => '',
			'border' => true,
			'hpadding' => 'auto',
			'vpadding' => 'auto',
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255),
			'text' => true,
			'font' => 'helvetica',
			'fontsize' => 8,
			'stretchtext' => 4
		);

		$pdf->setFillColor(225, 225, 225);

		/* $tutor = Tutor::find(array(
            'conditions' => 'grado="'.$this->get->grado.'" AND seccion="'.$this->get->seccion.'" AND anio_academico="'.$this->get->anio_academico.'" AND turno_id="'.$this->get->turno_id.'" AND nivel_id="'.$this->get->nivel_id.'"'
        )); */

        $tutor = $grupo->tutor;

        $pdf->cell(30, 7, 'TUTOR', 1, 0, 'C', 1, 0, 1);
        $pdf->cell(0, 7, isset($tutor) ? $tutor->getFullName() : '', 1, 1, 'C', 0, 0, 1);
        $pdf->cell(30, 7, 'GRUPO', 1, 0, 'C', 1, 0, 1);
        $pdf->cell(0, 7, $grupo->nivel->nombre.' '.$grupo->getGrado().' '.$grupo->seccion, 1, 0, 'C', 0, 0, 1);
        $pdf->ln(10);

		foreach($matriculas As $key => $matricula){
			$pdf->cell(47, 5, $matricula->alumno->getFullName(), 1, 0, 'C', 0, 0, 1);
			$currentY = $pdf->getY();
			$currentX = $pdf->getX();

			$pdf->setY($pdf->getY() + 5);
			$pdf->setX($currentX - 38);
			//$pdf->write1DBarcode(str_pad($matricula->alumno->id, 5, 0, STR_PAD_LEFT), 'I25', '', '', 47, 15, 0.4, $style, 'N');
			$pdf->write2DBarcode(sha1($matricula->id), 'QRCODE,H', $currentX - 38, $pdf->getY(), 30, 30, $style, 'N');
			
			$pdf->setY($currentY);
			$pdf->setX($currentX);
			if(($key + 1) % 4 == 0){
				$pdf->ln(35);
			}
		}
		$pdf->output();
		//$this->render(array('matriculas' => $matriculas));
	}
}
