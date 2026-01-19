<?php
class TutoriaApplication extends Core\Application{

	function initialize(){
		$this->security('SecureSession', array(
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => '*'
		));
		
		if(isset($this->params->grupo_id)){
			$this->grupo = Grupo::find([
                'conditions' => ['sha1(id) = ?', $this->params->grupo_id]
            ]);
			$this->context('grupo', $this->grupo);
		}
	}
	
	function index($r){        
        $grupos = $this->USUARIO->personal->getGruposAsignados($this->COLEGIO->anio_activo);
        //echo $this->USUARIO->personal_id;
        $grupos_tutor = Grupo::all(array(
			'conditions' => 'colegio_id="'.$this->COLEGIO->id.'" AND anio="'.$this->COLEGIO->anio_activo.'" AND tutor_id="'.$this->USUARIO->personal_id.'"',
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
        ));
        //$grupos = array_merge($grupos, $grupos_tutor);
		$this->render(array('grupos' => $grupos, 'grupos_tutor' => $grupos_tutor));
	}
    
    function conducta(){
    	$grupo = Grupo::find([
            'conditions' => ['sha1(id) = ?', $this->params->grupo_id]
        ]);
 
        $matriculas = $grupo->getMatriculas();
 
        $this->render(array('matriculas' => $matriculas, 'grupo' => $grupo));
    }

    function save_conducta(){
    	$r = 0;
    	if(count($this->post->notas) > 0){
    		
    		foreach($this->post->notas As $matricula_id => $nota){
    			Promedio::table()->delete(array(
    				'matricula_id' => $matricula_id,
    				'ciclo' => $this->post->ciclo,
    				'asignatura_id' => $this->post->asignatura_id
    			));

    			Promedio::create(array(
    				'matricula_id' => $matricula_id,
    				'ciclo' => $this->post->ciclo,
    				'asignatura_id' => $this->post->asignatura_id,
    				'promedio' => $nota
    			));
    		}
    		$r = 1;
    	}

    	echo json_encode(array($r));
    }

    function recomendaciones(){
        $matriculas = $this->grupo->getMatriculas();
        $this->render(array('matriculas' => $matriculas));
    }
    
    function do_recomendaciones(){
        if(count($this->post->recomendaciones) > 0){
            foreach($this->post->recomendaciones As $matricula_id => $recomendacion){

                if(!empty($recomendacion)){
					$matricula = Matricula::find($matricula_id);
					$matricula->setRecomendacion($this->post->ciclo, array(
						'descripcion' => $recomendacion,
						'personal' => $this->USUARIO->personal_id
					));
					$matricula->save();
                }
            }
        }
        echo json_encode(array(1));
    }
    
 
    
    function lista_alumnos(){
       $matriculas = $this->grupo->getMatriculas();
       $this->render(array('matriculas' => $matriculas));
    }

    function lista_alumnos_apoderados(){
       $matriculas = $this->grupo->getMatriculas();
       $this->render(array('matriculas' => $matriculas));
    }


    function imprimir_lista_alumnos(){
        $grupo = Grupo::find($this->get->grupo_id);

        $matriculas = $grupo->getMatriculas();

        $this->crystal->load('TCPDF');

        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 5, PDF_MARGIN_RIGHT);
        $pdf->setPrintFooter(false);
        $pdf->setPrintHeader(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 10, 5);
        $pdf->SetAutoPageBreak(TRUE, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setLanguageArray($l);
        $pdf->AddPage('L');
        //$this->setLogo($pdf);
        $pdf->SetFont('helvetica', 'b', 13);
        $pdf->setFillColor(220, 220, 220);
        $pdf->cell(0,10,'LISTA DE ALUMNOS - '.$grupo->getNombre(),0,0,'R');
        $pdf->ln(17);
        $pdf->setFont('Helvetica','b',10);
        $pdf->cell(7,5,'Nº',1,0,'C', 1);
        $pdf->cell(60,5,'Apellidos y Nombres',1,0,'C', 1);
        $pdf->cell(25,5,'Parentesco',1,0,'C', 1);
        
        $pdf->cell(20,5,'Nº Doc.',1,0,'C', 1);
        
        $pdf->cell(50,5,'Teléfono',1,0,'C', 1);
        $pdf->cell(50,5,'Email',1,0,'C', 1);
        $pdf->cell(75,5,'Dirección',1,0,'C', 1);


        
        $pdf->setFont('Helvetica','',9);
        $i = 1;
        $hombres = 0;
        $mujeres = 0;
        foreach($matriculas As $matricula){
            $pdf->ln(5);

            $alumno = $matricula->alumno;
            $domicilio = $alumno->getDomicilio();
            $apoderado = $alumno->getFirstApoderado();

            $pdf->cell(7,5,$i,1,0,'C');
            $pdf->setFont('Helvetica','b',9);
            $pdf->cell(60,5, $alumno->getFullName(), 1, 0,'L', 0, '', 1);
            $pdf->setFont('Helvetica','',9);
            $pdf->cell(25,5, 'Alumno',1,0,'C');
            //$pdf->cell(60,5, !is_null($apoderado) ? $apoderado->getFullName() : '',1,0,'L', 0, '', 1);
            
            $pdf->cell(20,5, $alumno->nro_documento,1,0,'C',  0, '', 1);
           

            //$apoderado = $matricula->alumno->getFirstApoderado();
            $telefonos = array();
            if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
            if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

            $pdf->cell(50, 5, implode(' - ', $telefonos), 1, 0, 'C', 0, 0, 1);
            $pdf->cell(50, 5, $apoderado->email, 1, 0, 'C', 0, 0, 1);
            $pdf->cell(75, 5, $apoderado->direccion, 1, 0, 'C', 0, 0, 1);

            $apoderados = $alumno->getApoderados();
            foreach($apoderados As $apoderado){
                $pdf->ln(5);
                $pdf->cell(7,5, '',1,0,'C');
                $pdf->cell(60,5, $apoderado->getFullName(),1,0,'L', 0, '', 1);
                $pdf->cell(25,5, $apoderado->getParentesco(),1,0,'C');
                $pdf->cell(20,5, $apoderado->nro_documento,1,0,'C',  0, '', 1);
                $telefonos = array();
                if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
                if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

                $pdf->cell(50, 5, implode(' - ', $telefonos), 1, 0, 'C', 0, 0, 1);
                $pdf->cell(50, 5, $apoderado->email, 1, 0, 'C', 0, 0, 1);
                $pdf->cell(75, 5, $apoderado->direccion, 1, 0, 'C', 0, 0, 1);
            }

            //$pdf->cell(20,5, $apoderado->telefono_fijo,1,0,'C',  0, '', 1);
            //$direccion = reset($domicilio);

            //$pdf->cell(50,5, !$direccion ? '' : $direccion['direccion'],1,0,'C', 0, '', 1);

            //$pdf->cell(42,5,$alumno->getFirstApoderado()->direccion,1,0,'C', 0, '', 1);
            
            if($alumno->sexo == 0) $hombres++;
            if($alumno->sexo == 1) $mujeres++;
            $i++;
        }

        $pdf->ln(10);
        $pdf->cell(30,5,'Hombres',1,0,'C', 1);
        $pdf->cell(30, 5, $hombres, 1, 0, 'C', 0, 0, 1);

        $pdf->cell(30,5,'Mujeres',1,0,'C', 1);
        $pdf->cell(30, 5, $mujeres, 1, 0, 'C', 0, 0, 1);

        $pdf->cell(30,5,'Total',1,0,'C', 1);
        $pdf->cell(30, 5, count($matriculas), 1, 0, 'C', 0, 0, 1);

        $pdf->output('asistencia.pdf','I');
    }

    function asignaturas_grupo(){
        $grupo = Grupo::find([
            'conditions' => ['sha1(id) = ?', $this->params->id]
        ]);

        $this->render(['grupo' => $grupo]);
    }
}
