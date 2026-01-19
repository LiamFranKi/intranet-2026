<?php
class AsignaturasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'ALUMNO' => 'alumno',
			'APODERADO' => 'alumno',
			'DOCENTE' => 'docente, asistencia, save_asistencia, clase_zoom, save_clase_zoom, lista_alumnos, lista_alumnos_pdf, lista_alumnos_excel, lista_apoderados_pdf, lista_apoderados_excel, lista_alumnos_apoderados_pdf, lista_alumnos_apoderados_excel',
            'ASISTENCIA' => 'index'
		]);
	}

    function lista_apoderados_pdf(){
        $grupo = $this->asignatura->grupo;
        $matriculas = $grupo->getMatriculas();

        $this->crystal->load('TCPDF');
        $pdf = new TCPDF('L');
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(10, 10, 10);

        $pdf->AddPage();

        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Lista de Apoderados - ' . $grupo->getNombre(), 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetFillColor(225, 225, 225);

        $pdf->Cell(8, 6, '#', 1, 0, 'C', true);
        $pdf->Cell(35, 6, 'Nº Doc.', 1, 0, 'C', true);
        $pdf->Cell(80, 6, 'Apoderado', 1, 0, 'C', true);
        $pdf->Cell(60, 6, 'Alumno', 1, 0, 'C', true); 
        $pdf->Cell(35, 6, 'Ap. Teléfono', 1, 0, 'C', true);
        $pdf->Cell(60, 6, 'Ap. Email', 1, 1, 'C', true);
        

        $pdf->SetFont('Helvetica', '', 9);
        $i = 1;
        foreach($matriculas as $matricula) {
            $apoderados = $matricula->alumno->getApoderados();
            foreach($apoderados as $apoderado) {
                $pdf->Cell(8, 6, $i, 1);
                $pdf->Cell(35, 6, $apoderado->nro_documento, 1);
                $pdf->Cell(80, 6, $apoderado->getFullName(), 1);
                $pdf->Cell(60, 6, $matricula->alumno->getFullName(), 1);
                $pdf->Cell(35, 6, $apoderado->telefono_celular, 1);
                $pdf->Cell(60, 6, $apoderado->email, 1);
                
                $pdf->Ln();
                $i++;
            }
        }

        $pdf->Output('lista_apoderados.pdf', 'I');
        exit;
    }
    
    function lista_apoderados_excel() {
        $grupo = $this->asignatura->grupo;
        $matriculas = $grupo->getMatriculas();

        $this->crystal->load('PHPExcel');
        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Lista de Apoderados');

        // Set header
        $sheet->setCellValue('A1', 'Lista de Apoderados - ' . $grupo->getNombre());
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        // Set column headers
        $sheet->setCellValue('A2', '#');
        $sheet->setCellValue('B2', 'Nº Doc.');
        $sheet->setCellValue('C2', 'Apoderado');
        $sheet->setCellValue('D2', 'Alumno');
        $sheet->setCellValue('E2', 'Ap. Teléfono');
        $sheet->setCellValue('F2', 'Ap. Email');

        // Style headers
        $sheet->getStyle('A2:F2')->getFont()->setBold(true);
        $sheet->getStyle('A2:F2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('E1E1E1');

        // Fill data
        $row = 3;
        $i = 1;
        foreach($matriculas as $matricula) {
            $apoderados = $matricula->alumno->getApoderados();
            foreach($apoderados as $apoderado) {
                $sheet->setCellValue('A'.$row, $i);
                $sheet->setCellValue('B'.$row, $apoderado->nro_documento);
                $sheet->setCellValue('C'.$row, $apoderado->getFullName());
                $sheet->setCellValue('D'.$row, $matricula->alumno->getFullName());
                $sheet->setCellValue('E'.$row, $apoderado->telefono_celular);
                $sheet->setCellValue('F'.$row, $apoderado->email);
                
                $row++;
                $i++;
            }
        }

        // Add borders
        $lastRow = $row - 1;
        $sheet->getStyle('A1:F'.$lastRow)->applyFromArray([
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ]);

        // Auto size columns
        foreach(range('A','F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="lista_apoderados.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }

    function lista_alumnos_apoderados_pdf(){
        $grupo = $this->asignatura->grupo;
        $matriculas = $grupo->getMatriculas();

        $this->crystal->load('TCPDF');
        $pdf = new TCPDF('L');
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(10, 10, 10);

        $pdf->AddPage();

        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Lista de Alumnos y Apoderados - ' . $grupo->getNombre(), 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetFillColor(225, 225, 225);

        $pdf->Cell(8, 6, '#', 1, 0, 'C', true);
        $pdf->Cell(60, 6, 'Alumno', 1, 0, 'C', true);
        $pdf->Cell(60, 6, 'Apoderado', 1, 0, 'C', true);
        $pdf->Cell(30, 6, 'Ap. Teléfono', 1, 0, 'C', true);
        $pdf->Cell(40, 6, 'Ap. Email', 1, 0, 'C', true);
        $pdf->Cell(20, 6, 'Estrellas', 1, 0, 'C', true);
        $pdf->Cell(50, 6, 'Recomendaciones', 1, 1, 'C', true);

        $pdf->SetFont('Helvetica', '', 8);
        foreach($matriculas as $i => $matricula) {
            $apoderado = $matricula->alumno->getApoderados()[0];
            $recomendaciones = [];
            if ($matricula->recomendaciones) {
                $recomendaciones = array_map(function($item){
                    return $item['descripcion'];
                }, $matricula->getRecomendaciones());
            }
            
            // Get max height for multicell based on recommendations
            $height = 6;
            if (!empty($recomendaciones)) {
                $txt = implode("\n", $recomendaciones);
                $lineCount = count(explode("\n", $txt));
                $height = max($height, $lineCount * 6);
            }

            // Draw all cells with same height
            $pdf->Cell(8, $height, ($i + 1), 1);
            $pdf->Cell(60, $height, $matricula->alumno->getFullName(), 1, 0, 'L');
            $pdf->Cell(60, $height, $apoderado ? $apoderado->getFullName() : '-', 1);
            $pdf->Cell(30, $height, $apoderado ? $apoderado->telefono_celular : '-', 1);
            $pdf->Cell(40, $height, $apoderado ? $apoderado->email : '-', 1);
            $pdf->Cell(20, $height, $matricula->getStarsAmount() ?: '0', 1, 0, 'C');
            
            // MultiCell with same height
            $pdf->MultiCell(50, $height, !empty($recomendaciones) ? implode("\n", $recomendaciones) : '-', 1, 'L');
        }

        $pdf->Output('lista_alumnos_apoderados.pdf', 'I');
        exit;
    }

    function lista_alumnos_apoderados_excel() {
        $grupo = $this->asignatura->grupo;
        $matriculas = $grupo->getMatriculas();

        $this->crystal->load('PHPExcel');
        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Lista de Alumnos y Apoderados');

        // Set header
        $sheet->setCellValue('A1', 'Lista de Alumnos y Apoderados - ' . $grupo->getNombre());
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        // Set column headers
        $sheet->setCellValue('A2', '#');
        $sheet->setCellValue('B2', 'Alumno');
        $sheet->setCellValue('C2', 'Apoderado'); 
        $sheet->setCellValue('D2', 'Ap. Teléfono');
        $sheet->setCellValue('E2', 'Ap. Email');
        $sheet->setCellValue('F2', 'Estrellas');
        $sheet->setCellValue('G2', 'Recomendaciones');

        // Style headers
        $sheet->getStyle('A2:G2')->getFont()->setBold(true);
        $sheet->getStyle('A2:G2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('E1E1E1');

        // Fill data
        $row = 3;
        foreach($matriculas as $i => $matricula) {
            $apoderado = $matricula->alumno->getApoderados()[0];
            $recomendaciones = [];
            if ($matricula->recomendaciones) {
                $recomendaciones = array_map(function($item){
                    return $item['descripcion'];
                }, $matricula->getRecomendaciones());
            }

            $sheet->setCellValue('A'.$row, ($i + 1));
            $sheet->setCellValue('B'.$row, $matricula->alumno->getFullName());
            $sheet->setCellValue('C'.$row, $apoderado ? $apoderado->getFullName() : '-');
            $sheet->setCellValue('D'.$row, $apoderado ? $apoderado->telefono_celular : '-');
            $sheet->setCellValue('E'.$row, $apoderado ? $apoderado->email : '-');
            $sheet->setCellValue('F'.$row, $matricula->getStarsAmount() ?: '0');
            $sheet->setCellValue('G'.$row, !empty($recomendaciones) ? implode("\n", $recomendaciones) : '-');
            
            $row++;
        }

        // Add borders and autosize
        $lastRow = $row - 1;
        $sheet->getStyle('A1:G'.$lastRow)->applyFromArray([
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ]);

        foreach(range('A','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="lista_alumnos_apoderados.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }
    
	
	function index($r){
		if(empty($this->get->nivel_id)){
			$asignaturas = [];	
		}else{
			$grupo = $this->COLEGIO->getGrupo($this->get);
			if($grupo) $asignaturas = $grupo->getAsignaturas();
			
		}
		
		$this->crystal->load('Form:*');
		$form = new Form(null, $this->COLEGIO->searchGrupoForm((array) $this->get));
		$this->render(array('asignaturas' => $asignaturas, 'form' => $form));
	}

	function docente(){
		$asignaturas = Asignatura::all(Array(
			'conditions' => Array('personal_id="'.$this->USUARIO->personal_id.'" AND grupos.anio="'.$this->COLEGIO->anio_activo.'"'),
			'joins' => array('grupo'),
            'order' => 'grupos.nivel_id asc, grupos.grado asc, grupos.seccion asc'

		));
		$this->render(['asignaturas' => $asignaturas]);
	}

    function lista_alumnos(){
        $grupo = $this->asignatura->grupo;  
        $matriculas = $grupo->getMatriculas();
        $this->render(['matriculas' => $matriculas, 'grupo' => $grupo]);
    }

	function alumno(){
		$alumno = !isset($this->params->alumno_id) ? $this->USUARIO->alumno : Alumno::find([
			'conditions' => ['sha1(id) = ?', $this->params->alumno_id]
		]);
		
		$matricula = $alumno->getMatriculaByAnio($this->COLEGIO->anio_activo);
		
		if($matricula){
			$asignaturas = $matricula->grupo->getAsignaturas();
		}

		$this->render(Array('matricula' => $matricula, 'asignaturas' => $asignaturas));
	}

	function save_nuevo(){
		$grupo = $this->COLEGIO->getGrupo($this->post);
		$r = -1;
		if($grupo){
			$r = -5;
			foreach($this->post->curso_id As $curso_id){
				$asignatura = new Asignatura();

				$asignatura->set_attributes(array(
					'colegio_id' => $this->COLEGIO->id,
					'grupo_id' => $grupo->id,
					'curso_id' => $curso_id,
					'personal_id' => $this->post->personal_id,
				));
				
				if($asignatura->is_valid()){
					$r = $asignatura->save() ? 1 : 0;
				}
			}
			
			$r = 1;
		}
		

		echo json_encode(array($r, 'id' => $asignatura->id));
	}
	
	function save(){
		$grupo = $this->COLEGIO->getGrupo($this->post);
		$r = -1;
		if($grupo){
			$r = -5;
			$this->asignatura->set_attributes(array(
				'colegio_id' => $this->COLEGIO->id,
				'grupo_id' => $grupo->id,
				'curso_id' => $this->post->curso_id,
				'personal_id' => $this->post->personal_id,
				'link_libro' => $this->post->link_libro
			));
			
			if($this->asignatura->is_valid()){
				$r = $this->asignatura->save() ? 1 : 0;
			}
		}
		

		echo json_encode(array($r, 'id' => $this->asignatura->id, 'errors' => $this->asignatura->errors->get_all()));
	}

	function save_clase_zoom(){
		$this->asignatura->set_attributes(array(
			'aula_virtual' => $this->post->aula_virtual,
		));

		if($this->asignatura->is_valid()){
			$r = $this->asignatura->save() ? 1 : 0;
		}

		echo json_encode(array($r, 'id' => $this->asignatura->id, 'errors' => $this->asignatura->errors->get_all()));
	}

	function borrar($r){
		$r = $this->asignatura->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function asistencia(){
		$fecha = !empty($this->get->fecha) ? $this->get->fecha : date('Y-m-d');
		$matriculas = $this->asignatura->grupo->getMatriculas();
		$this->render(['fecha' => $fecha, 'matriculas' => $matriculas]);
	}

	function save_asistencia(){
		
		Asignatura_Asistencia::table()->delete(Array(
			'matricula_id' => $this->post->matricula_id,
			'asignatura_id' => $this->post->asignatura_id,
			'fecha' => date('Y-m-d', strtotime($this->post->fecha))
		));
		
		$asistencia = new Asignatura_Asistencia(Array(
			'matricula_id' => $this->post->matricula_id,
			'fecha' => date('Y-m-d', strtotime($this->post->fecha)),
			'tipo' => $this->post->asistencia,
			'asignatura_id' => $this->post->asignatura_id,
		));

		$r = $asistencia->save() ? 1 : 0;
		echo json_encode(Array($r));
	}

    function lista_alumnos_pdf(){
        $grupo = $this->asignatura->grupo;
        $matriculas = $grupo->getMatriculas();

        $this->crystal->load('TCPDF');
        $pdf = new TCPDF('L'); // Set to Landscape orientation
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(10, 10, 10);

        $pdf->AddPage();
        
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Lista de Alumnos - ' . $grupo->getNombre(), 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Helvetica', 'B', 10);
        
        $pdf->SetFillColor(225, 225, 225);
        $pdf->Cell(8, 6, '#', 1, 0, 'C', true, '', 1);
        
        $pdf->Cell(85, 6, 'Nombre', 1, 0, 'C', true, '', 1); 
        $pdf->Cell(30, 6, 'Nº de Doc.', 1, 0, 'C', true, '', 1);
        $pdf->Cell(30, 6, 'Sexo', 1, 0, 'C', true, '', 1);
        $pdf->Cell(40, 6, 'Email', 1, 0, 'C', true, '', 1);
        $pdf->Cell(30, 6, 'F. Nacimiento', 1, 0, 'C', true, '', 1);
        $pdf->Cell(20, 6, 'F. Inscripción', 1, 0, 'C', true, '', 1);
        $pdf->Cell(25, 6, 'Estado', 1, 0, 'C', true, '', 1);
        $pdf->Ln();

        $pdf->SetFont('Helvetica', '', 9);
        $i = 1;
        foreach($matriculas as $matricula) {
            $pdf->Cell(8, 6, $i, 1);
            $pdf->Cell(85, 6, $matricula->alumno->getFullName(), 1, 0, 'L', false, '', 1);
            
            $pdf->Cell(30, 6, $matricula->alumno->nro_documento, 1, 0, 'C', false, '', 1);
            $pdf->Cell(30, 6, $matricula->alumno->getSexo(), 1, 0, 'C');
            $pdf->Cell(40, 6, $matricula->alumno->email, 1, 0, 'C', false, '', 1);
            $pdf->Cell(30, 6, $matricula->alumno->fecha_nacimiento, 1, 0, 'C');
            $pdf->Cell(20, 6, $matricula->alumno->fecha_inscripcion, 1);
            $pdf->Cell(25, 6, $matricula->getEstado(), 1, 0, 'C');
            $pdf->Ln();
            $i++;
        }

        $pdf->Output();
    }

    function lista_alumnos_excel(){
        $grupo = $this->asignatura->grupo;
        $matriculas = $grupo->getMatriculas();

        $this->crystal->load('PHPExcel');
        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Lista de Alumnos');

        // Set header
        $sheet->setCellValue('A1', 'Lista de Alumnos - ' . $grupo->getNombre());
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        // Set column headers
        $sheet->setCellValue('A2', '#');
        $sheet->setCellValue('B2', 'Nombre');
        $sheet->setCellValue('C2', 'Nº de Doc.');
        $sheet->setCellValue('D2', 'Sexo');
        $sheet->setCellValue('E2', 'Email');
        $sheet->setCellValue('F2', 'F. Nacimiento');
        $sheet->setCellValue('G2', 'F. Inscripción');
        $sheet->setCellValue('H2', 'Estado');

        // Style headers
        $sheet->getStyle('A2:H2')->getFont()->setBold(true);
        $sheet->getStyle('A2:H2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('E1E1E1');

        // Fill data
        $row = 3;
        foreach($matriculas as $i => $matricula) {
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $matricula->alumno->getFullName());
            $sheet->setCellValue('C'.$row, $matricula->alumno->nro_documento);
            $sheet->setCellValue('D'.$row, $matricula->alumno->getSexo());
            $sheet->setCellValue('E'.$row, $matricula->alumno->email);
            $sheet->setCellValue('F'.$row, $matricula->alumno->fecha_nacimiento);
            $sheet->setCellValue('G'.$row, $matricula->alumno->fecha_inscripcion);
            $sheet->setCellValue('H'.$row, $matricula->getEstado());
            $row++;
        }

        // Add borders to all cells
        $lastRow = $row - 1;
        $borderStyle = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        $sheet->getStyle('A1:H'.$lastRow)->applyFromArray($borderStyle);

        // Auto size columns
        foreach(range('A','H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="lista_alumnos.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }
	
	function __getObjectAndForm(){
		$this->set('__active', 'asignaturas', true);
		$this->asignatura = !empty($this->params->id) ? Asignatura::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Asignatura();
		$this->context('asignatura', $this->asignatura); // set to template
		if(in_array($this->params->Action, array('form', 'clase_zoom', 'registrar'))){
			$this->form = $this->__getForm($this->asignatura);
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
			'grupo_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'curso_id' => array(
				'type' => 'select',
				'__first' => ['', '-- Seleccione --'],
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'personal_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__dataset' => true,
				'type' => 'select',
				'__options' => [Personal::all(['order' => 'apellidos ASC']), 'id', '$object->getFullName()'],
				'__first' => ['', '-- Seleccione --']
			),
			'aula_virtual' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'habilitar_aula' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),

			// GRUPO
			'sede_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array(Sede::all(), 'id', '$object->nombre'),
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->sede_id
			),
			'nivel_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->niveles, 'id', '$object->nombre'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->nivel_id
			),
			'grado' => array(
				'type' => 'select',
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->grado
			),
			'seccion' => array(
				'type' => 'select',
				'__options' => array_combine($object->SECCIONES, $object->SECCIONES),
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->seccion
			),
			'anio' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__default' => $this->COLEGIO->anio_activo,
				'rel' => 'curso',
				'value' => empty($object->grupo->anio) ? $this->COLEGIO->anio_activo : $object->grupo->anio,
			),
			'turno_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->turnos, 'id', '$object->nombre'),
				'__first' => array('', '-- Seleccione --'),
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'rel' => 'curso',
				'value' => $object->grupo->turno_id
			),
			'aula_virtual' => [
				'class' => 'form-control'
			],
			'link_libro' => [
				'type' => 'text',
				'class' => 'form-control'
			]
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
