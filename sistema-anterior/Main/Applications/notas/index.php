<?php
/**
 *	Administra el registro de notas por parte de los docente
 */
class NotasApplication extends Core\Application {

	/**
	 * 	Permite inicializar variables y operaciones para todos los métodos
	 */
	function initialize(){
        $permisos = array(
            'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
            'DOCENTE' => 'detallado, imprimir_cuantitativa, registrar, save_cualitativa, save_cuantitativa, registrar_detalles, full_example, save_detalles, do_cargar_archivo_full, registrar_examen_mensual, save_examen_mensual, imprimir, imprimir_grupo, registrar_examen_mensual_bloque, save_examen_mensual_bloque',
            'ALUMNO' => 'imprimir, detallado',
            'APODERADO' => 'imprimir, detallado',
            'AUXILIAR' => 'imprimir',
            'COORDINADOR' => '*',
            'DIRECTOR' => '*',
            'SECRETARIA' => '*',
            'CAJERO' => '*'
        );

        if(!is_null($this->USUARIO) && $this->USUARIO->hasPermiso('CURSOS')){
            $permisos['PERSONALIZADO'] = '*';
        }

		$this->security('SecureSession', $permisos);
	}

	function full_example(){
        $this->crystal->load('PHPExcel');
        $asignatura = Asignatura::find($this->get->asignatura_id);
        $curso = $asignatura->curso;
        $matriculas = $asignatura->grupo->getMatriculas();

        $excel = PHPExcel_IOFactory::load('./Static/Templates/notas_full.xlsx');

        $s1 = $excel->getSheet(0);
        $s1->setCellValue('A2', $this->get->title);
        $currentCol = 3;
		$criterios = $asignatura->getCriterios($this->get->ciclo);
		foreach($criterios As $criterio){
			$indicadores = $criterio->getIndicadores();
			$criterioCell = getNameFromNumber($currentCol).'5';
			$s1->setCellValue($criterioCell, mb_strtoupper($criterio->descripcion));


			if(count($indicadores) > 0){
				$indicadorGeneral = $indicadores[0];
				$s1->mergeCells($criterioCell.':'.getNameFromNumber($currentCol + $indicadorGeneral->cuadros - 1).'5');

				for($i = 1; $i <= $indicadorGeneral->cuadros; $i++){
					$s1->setCellValue(getNameFromNumber($currentCol).'6', $i);

					++$currentCol;
				}



				//$currentCol += $indicadorGeneral->cuadros;
			}else{

				$s1->mergeCells(getNameFromNumber($currentCol).'5:'.getNameFromNumber($currentCol).'6');
				++$currentCol;
			}


			$s1->getColumnDimension(getNameFromNumber($currentCol))->setWidth(20);
			$s1->getColumnDimension(getNameFromNumber($currentCol))->setAutoSize(true);
			/*
			$s1->getStyle($criterioCell)->applyFromArray(
				array(
					'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array('rgb' => 'D7E4BC')
					)
				)
			);
			*/

		}


        $currentRow = 7;
        foreach($matriculas As $key => $matricula){
            $alumno = $matricula->alumno;
            $s1->setCellValue('A'.$currentRow, $key + 1);
            $s1->setCellValue('B'.$currentRow, $alumno->getFullName());


			$currentCol = 3;
			foreach($criterios As $criterio){
				$criterioCell = getNameFromNumber($currentCol).$currentRow;
				$nota = $matricula->getNota($asignatura->id, $criterio->id, $this->get->ciclo);
				$s1->setCellValue($criterioCell, $nota);
				++$currentCol;
			}

            ++$currentRow;
        }

        $lastCol = $currentCol - 1;

        $s1->getStyle('A5:'.getNameFromNumber($lastCol).(5 + count($matriculas)))->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
            ),
        ));

        writeExcel($excel);
    }

    function do_cargar_archivo_full(){
		$this->crystal->load('PHPExcel');
        $archivo = $_FILES['archivo'];
        $info = pathinfo($archivo['name']);

        $r = 0;
        $data = array();
        if($archivo['error'] == UPLOAD_ERR_OK && $info['extension'] == 'xlsx'){
            $name = getToken().'.xlsx';
            if(move_uploaded_file($archivo['tmp_name'], './Static/Temp/'.$name)){
                $excel = PHPExcel_IOFactory::load('./Static/Temp/'.$name);
                $s1 = $excel->getSheet(0);
                //echo $s1->getCell('A2')->getValue();

                $currentRow = 6;
                while(true){
                    $nro = $s1->getCell('A'.$currentRow)->getValue();
                    $nombres = $s1->getCell('B'.$currentRow)->getValue();
                    $notas = array();
                    for($i=3; $i<=3 + ($this->post->total_criterios - 1); $i++){
						$criterioCell = getNameFromNumber($i).$currentRow;
						$nota = $s1->getCell($criterioCell)->getValue();
						if(empty($nota)) $nota = 0;
						$notas[] = $nota;
					}

                    if(empty($nro) || empty($nombres)) break;

                    $data[] = array(
                        'nro' => $nro,
                        'nombres' => $nombres,
                        'notas' => $notas
                    );
                    ++$currentRow;

                }
                @unlink('./Static/Temp/'.$name);
                $r = 1;
            }
        }


        echo response(array($r, $data));
        //print_r($this->post);
	}


	/**
	 * Muestra la lista de alumnos de una asignatura determinada
	 */

	function registrar($r){
		$asignatura = Asignatura::find([
            'conditions' => ['sha1(id) = ?', $this->get->asignatura_id]
        ]);
        $this->context('asignatura', $asignatura);
        $this->context('curso', $asignatura->curso);
        $this->context('criterios', $asignatura->getCriterios($this->get->ciclo));

        $matriculas = $asignatura->grupo->getMatriculas();

        if($asignatura->grupo->nivel->calificacionCualitativa()){
			$view = 'registrar_cualitativa';
		}else{
			$view = 'registrar_cuantitativa';
		}

        $readonly = $this->get->readonly == "true";
        if($asignatura->grupo->anio != $this->COLEGIO->anio_activo && !$this->USUARIO->is('ADMINISTRADOR')){
            $readonly = true;
        }

        $this->render($view, array('matriculas' => $matriculas, 'readonly' => $readonly));
	}

    function detallado(){
        $matricula = Matricula::find([
            'conditions' => ['sha1(id) = ?', $this->get->matricula_id]
        ]);


        $this->render(array('matricula' => $matricula));
    }


    function registrar_examen_mensual_bloque(){
        //$asignatura = Asignatura::find($this->get->asignatura_id);
        //$this->context('asignatura', $asignatura);
        //$this->context('curso', $asignatura->curso);
        $bloque = Bloque::find($this->get->bloque_id);
        $grupo = Grupo::find($this->get->grupo_id);

        $matriculas = $grupo->getMatriculas();
        $this->render(array('matriculas' => $matriculas, 'bloque' => $bloque, 'grupo' => $grupo));
    }

    function save_examen_mensual_bloque(){
        //print_r($this->post);

        //$asignatura = Asignatura::find($this->post->asignatura_id);
        $matriculas = array();

        /*

        */

        foreach($this->post->notas_examen As $matricula_id => $dataAsignatura){
            if(!in_array($matricula_id, $matriculas)) $matriculas[] = $matricula_id;
            foreach($dataAsignatura As $asignatura_id => $data){

                for($i=1; $i<=2;$i++){
                    Nota_Examen_Mensual::table()->delete(array(
                        'asignatura_id' => $asignatura_id,
                        'nro' => $i,
                        'ciclo' => $this->post->ciclo,
                        'matricula_id' => $matricula_id
                    ));
                }

                foreach($data As $nro => $nota){
                    if($nota != ''){
                        $nota = Nota_Examen_Mensual::create(array(
                            'matricula_id' => $matricula_id,
                            'asignatura_id' => $asignatura_id,
                            'nro' => $nro,
                            'ciclo' => $this->post->ciclo,
                            'nota' => $nota
                        ));
                    }
                }

                $matricula = Matricula::find($matricula_id);
                $matricula->updatePromedioFromCriterios($asignatura_id, $this->post->ciclo);
            }
        }

        /*
        foreach($matriculas As $matricula_id){


        }
        */

        echo json_encode(array(1));
    }

    function registrar_examen_mensual(){
        $asignatura = Asignatura::find($this->get->asignatura_id);
        $this->context('asignatura', $asignatura);
        $this->context('curso', $asignatura->curso);
        $matriculas = $asignatura->grupo->getMatriculas();
        $this->render(array('matriculas' => $matriculas));
    }

    function save_examen_mensual(){
        //print_r($this->post);
        $asignatura = Asignatura::find($this->post->asignatura_id);
        $matriculas = array();

        for($i=1; $i<=2;$i++){
            Nota_Examen_Mensual::table()->delete(array(
                'asignatura_id' => $this->post->asignatura_id,
                'nro' => $i,
                'ciclo' => $this->post->ciclo,
            ));
        }

        foreach($this->post->notas_examen As $matricula_id => $data){
            if(!in_array($matricula_id, $matriculas)) $matriculas[] = $matricula_id;
            foreach($data As $nro => $nota){
                if($nota != ''){
                    $nota = Nota_Examen_Mensual::create(array(
                        'matricula_id' => $matricula_id,
                        'asignatura_id' => $this->post->asignatura_id,
                        'nro' => $nro,
                        'ciclo' => $this->post->ciclo,
                        'nota' => $nota
                    ));
                }
            }
        }

        foreach($matriculas As $matricula_id){
            $matricula = Matricula::find($matricula_id);
            $matricula->updatePromedioFromCriterios($asignatura, $this->post->ciclo);
        }

        echo json_encode(array(1));
    }

    function save_cuantitativa(){
		$asignatura = Asignatura::find($this->post->asignatura_id);
		$matriculas = array();
		foreach($this->post->notas As $criterio_id => $data){
			Nota::table()->delete(array(
				'asignatura_id' => $this->post->asignatura_id,
				'criterio_id' => $criterio_id,
				'ciclo' => $this->post->ciclo,
			));

			$criterio = Asignatura_Criterio::find($criterio_id);
			foreach($data As $matricula_id => $nota){
				if(!in_array($matricula_id, $matriculas)) $matriculas[] = $matricula_id;
				if($nota != ''){
					$nota = Nota::create(array(
						'matricula_id' => $matricula_id,
						'asignatura_id' => $this->post->asignatura_id,
						'criterio_id' => $criterio_id,
						'ciclo' => $this->post->ciclo,
						'nota' => $nota
					));
				}
			}
		}

		foreach($matriculas As $matricula_id){
			$matricula = Matricula::find($matricula_id);


			// indicadores
			Nota_Detalle::table()->delete(array(
                'asignatura_id' => $asignatura->id,
                'matricula_id' => $matricula_id,
                'ciclo' => $this->post->ciclo,
            ));

			Nota_Detalle::create(array(
                'asignatura_id' => $asignatura->id,
                'matricula_id' => $matricula_id,
                'ciclo' => $this->post->ciclo,
                'data' => serialize($this->post->nota[$matricula_id])
            ));
			// EXAMEN MENSUAL
			Nota_Examen_Mensual::table()->delete(array(
                'asignatura_id' => $asignatura->id,
                'matricula_id' => $matricula_id,
                'ciclo' => $this->post->ciclo,
            ));
			$notasExamenMensual = $this->post->notas_examen[$matricula_id];
			if(count($notasExamenMensual) > 0){
				foreach($notasExamenMensual As $nro => $nota){
	                if($nota != ''){
	                    $nota = Nota_Examen_Mensual::create(array(
	                        'matricula_id' => $matricula_id,
	                        'asignatura_id' => $this->post->asignatura_id,
	                        'nro' => $nro,
	                        'ciclo' => $this->post->ciclo,
	                        'nota' => $nota
	                    ));
	                }
	            }
			}


			$matricula->updatePromedioFromCriterios($asignatura, $this->post->ciclo);

		}

        echo json_encode(array(1));
    }

    /**
     * Muestra el formulario para registrar las notas con los criterios desglosados en subcriterios
     */

    function registrar_detalles(){
        $asignatura = Asignatura::find($this->get->asignatura_id);
        $matricula = Matricula::find($this->get->matricula_id);

        $this->render(array(
            'asignatura' => $asignatura,
            'matricula' => $matricula,
            'criterios' => $asignatura->getCriterios($this->get->ciclo)
        ));
    }

    function save_detalles(){
        //print_r($this->post);
        $asignatura = Asignatura::find($this->post->asignatura_id);
        $matricula = Matricula::find($this->post->matricula_id);
        $notas = $this->post->nota[$this->post->matricula_id];
        $r = 0;
        if(count($notas) > 0){
            $promedios = array();

            foreach($notas As $criterio_id => $notas_criterio){
                //echo $criterio_id."\n";
                foreach($notas_criterio As $indicador_id => $notas_indicador){
                    //if(!isset($promedios[$criterio_id][$subcriterio_id])) $promedios[$criterio_id][$subcriterio_id] = array();
                   for($i=0;$i<count($notas_indicador);$i++){
                       $nota = $notas_indicador[$i];
                       if(!empty($nota)) $promedios[$criterio_id][$indicador_id][] = $nota;
                   }

                   $promedios[$criterio_id][$indicador_id] = count($promedios[$criterio_id][$indicador_id]) > 0 ?  round(array_sum($promedios[$criterio_id][$indicador_id]) / count($promedios[$criterio_id][$indicador_id])) : null;
                   //array_push(, $nota_subcriterio);
                }

                $promedios[$criterio_id] = array_filter($promedios[$criterio_id]);

                // PROMEDIOS PARA LOS CRITERIOS PRINCIPALES
                // SE GUARDAN EN LA BASE DE DATOS
                $promedios[$criterio_id] = count($promedios[$criterio_id]) > 0 ? round(array_sum($promedios[$criterio_id]) / count($promedios[$criterio_id])) : null;
            }
            // CLEAR DATA
            Nota_Detalle::table()->delete(array(
                'asignatura_id' => $this->post->asignatura_id,
                'matricula_id' => $this->post->matricula_id,
                'ciclo' => $this->post->ciclo,
            ));

            // SET DATA

            $xnotas = new Nota_Detalle(array(
                'asignatura_id' => $this->post->asignatura_id,
                'matricula_id' => $this->post->matricula_id,
                'ciclo' => $this->post->ciclo,
                'data' => serialize($notas)
            ));

            $r = $xnotas->save() ? 1 : 0;
        }

        if($r == 1){
            $promedios = array_filter($promedios);
            // GUARDA LOS PROMEDIOS DE CRITERIOS

            foreach($asignatura->getCriterios($this->post->ciclo) As $criterio){
                $promedio = $promedios[$criterio->id];
                // DELETE ONLY IF HAVE SUB CRITERIOS
                if(isset($notas[$criterio->id])){
                    Nota::table()->delete(array(
                        'matricula_id' => $this->post->matricula_id,
                        'criterio_id' => $criterio->id,
                        'ciclo' => $this->post->ciclo,
                        'asignatura_id' => $this->post->asignatura_id,
                    ));
                }
                // SET
                if(isset($promedio)){
                    Nota::create(array(
                        'matricula_id' => $this->post->matricula_id,
                        'criterio_id' => $criterio->id,
                        'ciclo' => $this->post->ciclo,
                        'asignatura_id' => $this->post->asignatura_id,
                        'nota' => $promedio
                    ));
                }
            }

            // GUARDA EL PROMEDIO DEL CURSO
            $matricula->updatePromedioFromCriterios($asignatura, $this->post->ciclo);

        }

        echo json_encode(array($r));

    }

    // GUARDA LAS NOTAS - TIPO CUALITATIVA
    function save_cualitativa(){

        $promedios = array();

        foreach($this->post->notas As $criterio_id => $data){
            Nota::table()->delete(array(
                'asignatura_id' => $this->post->asignatura_id,
                'criterio_id' => $criterio_id,
                'ciclo' => $this->post->ciclo,
            ));

            foreach($data As $matricula_id => $nota){
                if($nota != ''){
                    $nota = Nota::create(array(
                        'matricula_id' => $matricula_id,
                        'asignatura_id' => $this->post->asignatura_id,
                        'criterio_id' => $criterio_id,
                        'ciclo' => $this->post->ciclo,
                        'nota' => strtoupper($nota)
                    ));
                }
            }
        }

		foreach($this->post->promedio As $matricula_id => $promedio){
			Promedio::table()->delete(array(
                'asignatura_id' => $this->post->asignatura_id,
                'matricula_id' => $matricula_id,
                'ciclo' => $this->post->ciclo,
            ));

            Promedio::create(array(
                'asignatura_id' => $this->post->asignatura_id,
                'matricula_id' => $matricula_id,
                'ciclo' => $this->post->ciclo,
                'promedio' => strtoupper($promedio)
            ));
		}

        echo json_encode(array(1));
    }


	/**
	 * Genera un archivo xls a partir de las notas de los alumnos
	 */
    function consolidado(){

        // - - DATA
        $this->asignaturas = Asignatura::all(array(
            'conditions' => 'asignaturas.nivel_id="'.$this->get->nivel_id.'" AND grado="'.$this->get->grado.'" AND seccion="'.$this->get->seccion.'" AND turno_id="'.$this->get->turno_id.'" AND anio_academico="'.$this->get->anio_academico.'"',
            'order' => 'cursos.orden ASC',
            'joins' => array('curso'),
        ));

        $this->matriculas = Matricula::all(Array(
			'conditions' => Array('nivel_id="'.$this->get->nivel_id.'" AND grado="'.$this->get->grado.'" AND seccion="'.$this->get->seccion.'" AND turno_id="'.$this->get->turno_id.'" AND anio_academico="'.$this->get->anio_academico.'"'),
			'include' => Array('alumno'),
			'order' => 'REPLACE(REPLACE(alumnos.apellido_paterno, "ñ", "nz"), "Ñ", "NZ") ASC',
			'joins' => Array('alumno')
		));

        /*switch($this->get->nivel_id){
            case 1;
                $this->consolidado_inicial();
            break;
            case 2;
                $this->consolidado_primaria();
            break;
            case 3;
                $this->consolidado_secundaria();
            break;
        }*/
        $this->consolidado_primaria();
    }

    function consolidado_inicial(){
        $this->crystal->load('PHPExcel');
        //$excel = new PHPExcel();
        $matriculas = $this->matriculas;
        $asignaturas = $this->asignaturas;
        $excel = PHPExcel_IOFactory::load('./Static/Templates/consolidado_inicial.xlsx');

        // - - - - SHEET 1 - - - - //

        $s1 = $excel->getSheet(0);
        $currentRow = 5;
        foreach($matriculas As $key => $matricula){
            $s1->setCellValue('A'.$currentRow, $key + 1);
            $s1->setCellValue('B'.$currentRow, $matricula->alumno->codigo);
            $s1->setCellValue('C'.$currentRow, mb_strtoupper($matricula->alumno->apellido_paterno.' '.$matricula->alumno->apellido_materno, 'UTF-8'));
            $s1->setCellValue('D'.$currentRow, mb_strtoupper($matricula->alumno->nombres, 'UTF-8'));

            // NOTA

            $asignaturaCol = 5;
            foreach($asignaturas As $asignatura){
                $criterioCol = $asignaturaCol;

                foreach($asignatura->curso->getCriteriosCategorias() As $categoria){
                    foreach($categoria->getCriterios($this->get->ciclo) As $criterio){
                        $criterioCell = getNameFromNumber($criterioCol).$currentRow;
                        $nota = $matricula->getNota($asignatura->id, $criterio->id, $this->get->ciclo);
                        $s1->getColumnDimension(getNameFromNumber($criterioCol))->setWidth(5);
                        $s1->setCellValue($criterioCell, strtoupper($nota));

                        ++$criterioCol;
                    }
                }
                $asignaturaCol += $asignatura->curso->getCountCriterios($this->get->ciclo);
            }

            $recomendacion = $matricula->getObservacion($this->get->ciclo);

            $s1->setCellValue(getNameFromNumber($asignaturaCol).$currentRow, $recomendacion);

            ++$currentRow;
        }

        $s1->getRowDimension(3)->setRowHeight(300);

        $asignaturaCol = 5;
        foreach($asignaturas As $asignatura){
            $cell = getNameFromNumber($asignaturaCol).'2';
            if($asignatura->curso->getCountCriterios($this->get->ciclo) > 0){
                $s1->setCellValue($cell, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'));
                $s1->getStyle($cell)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                    'font' => array(
                        'size' => 10,
                        'name' => 'Arial',
                        'bold' => true
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => array(
                          'argb' => 'FFCCFF',
                        ),
                    )
                ));

                $s1->mergeCells($cell.':'.getNameFromNumber($asignaturaCol + $asignatura->curso->getCountCriterios($this->get->ciclo) - 1).'2');
            }

            $criterioCol = $asignaturaCol;
            foreach($asignatura->curso->getCriteriosCategorias() As $categoria_key => $categoria){
                foreach($categoria->getCriterios($this->get->ciclo) As $criterio){
                    $criterioCell = getNameFromNumber($criterioCol).'3';
                    $s1->getStyle($criterioCell)->applyFromArray(array(
                        'alignment' => array(
                            'rotation' => 90,
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array(
                              'argb' => empty($categoria->color) ? 'FFFFFF' : $categoria->color,
                            ),
                        )
                    ));

                    $s1->setCellValue($criterioCell, $criterio->descripcion);
                    //$s1->setCellValue($criterioCell, 'A');
                    ++$criterioCol;
                }
            }

            $asignaturaCol += $asignatura->curso->getCountCriterios($this->get->ciclo);
        }

        // Recomendaciones
        $s1->getColumnDimension(getNameFromNumber($asignaturaCol))->setWidth(100);
        $s1->getStyle('E4:'.getNameFromNumber($asignaturaCol).'4')->applyFromArray(array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                  'argb' => 'FFFF99',
                ),
            )
        ));
        $s1->setCellValue(getNameFromNumber($asignaturaCol).'4', 'RECOMENDACIONES');

        $currentCol = $asignaturaCol + 1;
        $cols = 165;

        // delete remant cols
        /*for($col = $cols;$col >= $currentCol; $col--){
            $s1->removeColumn(getNameFromNumber($col), 1);
        }*/

        $this->writeExcel($excel);
    }

    function consolidado_primaria(){
        $matriculas = $this->matriculas;
        $asignaturas = $this->asignaturas;
        // selecciona la nota aprobatoria de acuerdo al nivel
        $nota_aprobatoria = $this->get->nivel_id == 2 ? $this->__config->nota_aprobatoria_primaria : ($this->get->nivel_id == 3 ? $this->__config->nota_aprobatoria_secundaria : 0);

        $cols = 82;

        // EXCEL

        $this->crystal->load('PHPExcel');
        //$excel = new PHPExcel();

        $excel = PHPExcel_IOFactory::load('./Static/Templates/consolidado_template.xlsx');

        // - - - - SHEET 1 - - - - //

        $s1 = $excel->getSheet(0);
        $s1->setTitle($this->SYSTEM->getCicloNotasDescription($this->get->ciclo));
        $s1->setCellValue('B2', mb_strtoupper($this->__config->nombre_colegio, 'UTF-8'));


        $ekey = 5;
        foreach($matriculas As $key => $matricula){
            $s1->setCellValue('A'.$ekey, $key + 1);
            $s1->setCellValue('B'.$ekey, $matricula->alumno->codigo);
            $s1->setCellValue('C'.$ekey, mb_strtoupper($matricula->alumno->apellido_paterno.' '.$matricula->alumno->apellido_materno, 'UTF-8'));
            $s1->setCellValue('D'.$ekey, mb_strtoupper($matricula->alumno->nombres, 'UTF-8'));

            // COLOCA LAS NOTAS EN EL ARCHIVO
            $currentCol = 5;
            foreach($asignaturas As $key => $asignatura){
                $criterios = $asignatura->curso->getCriterios($this->get->ciclo);


                    foreach($criterios As $criterio){
                        $cellName = getNameFromNumber($currentCol).$ekey;
                        $nota = $matricula->getNota($asignatura->id, $criterio->id, $this->get->ciclo);
                        $s1->setCellValue($cellName, $nota);

                        $color = $nota < $nota_aprobatoria ? 'FF0000' : '000000';
                        $s1->getStyle($cellName)->getFont()->getColor()->setRGB($color);

                        $currentCol++;
                    }

                    $cellName = getNameFromNumber($currentCol).$ekey; // promedio final
                    $promedio = $matricula->getPromedio($asignatura->id, $this->get->ciclo);
                    $s1->setCellValue($cellName, $promedio);

                    $color = $promedio < $nota_aprobatoria ? 'FF0000' : '000000';
                    $s1->getStyle($cellName)->getFont()->getColor()->setRGB($color);
                    $s1->getStyle($cellName)->getFill()->applyFromArray(array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'CCFFCC')
                    ));

                    $currentCol++;

            }

            // ADDITIONAL DATA

            $ciclo = $this->get->ciclo;

            $data = array(
                'PROMEDIO GENERAL' => function($s, $cellName) use($matricula, $asignaturas, $ciclo){
                    $s->setCellValue($cellName, $matricula->getPromedioGeneral($ciclo, $asignaturas));
                    $s->getStyle($cellName)->getFill()->applyFromArray(array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '99CCFF')
                    ));
                },
                'PROMEDIO DE AULA' => function($s, $cellName) use($matricula, $matriculas, $ciclo){
                    $s->setCellValue($cellName, $matricula->getPromedioAula($ciclo, $asignaturas));
                    $s->getStyle($cellName)->getFill()->applyFromArray(array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '99CCFF')
                    ));
                },
                'Inasistencias Injustificadas' => function($s, $cellName) use ($matricula, $ciclo){
                    $s->setCellValue($cellName, $matricula->getTotalFaltasInjustificadas($ciclo));
                    $s->getStyle($cellName)->getFill()->applyFromArray(array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFFF99')
                    ));
                },
                'Inasistencias Justificadas' => function($s, $cellName) use ($matricula, $ciclo){
                    $s->setCellValue($cellName, $matricula->getTotalFaltasJustificadas($ciclo));
                    $s->getStyle($cellName)->getFill()->applyFromArray(array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'CCFFCC')
                    ));
                },
                'Tardanzas' => function($s, $cellName) use ($matricula, $ciclo){
                    $s->setCellValue($cellName, $matricula->getTotalTardanzas($ciclo));
                    $s->getStyle($cellName)->getFill()->applyFromArray(array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFFF99')
                    ));
                },
            );

            //$currentCol = $currentCol + 1;

            foreach($data As $data_key => $callback){

                if($callback instanceOf Closure){
                    $callback($s1, getNameFromNumber($currentCol). $ekey);
                }

                $currentCol++;
            }

            $ekey++;
        }

        // SET THE HEADER ASIGNATURAS

        $currentCol = 5;
        foreach($asignaturas As $key => $asignatura){
            $criterios = $asignatura->curso->getCriterios($this->get->ciclo);
            $cellName = getNameFromNumber($currentCol).'3';
            $s1->setCellValue($cellName, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'));
            if(count($criterios) > 0){
                $coffset = $currentCol; // offset from curso
                foreach($criterios As $ckey => $criterio){
                    $criterioCell = getNameFromNumber($coffset).'4';
                    $title = !empty($criterio->abreviatura) ? $criterio->abreviatura : 'C'.($ckey+1);

                    $s1->setCellValue($criterioCell, $title);
                    $s1->getComment($criterioCell)->getText()->createTextRun($criterio->descripcion);
                    $s1->getStyle($criterioCell)->getFill()->applyFromArray(array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'CCFFCC')
                    ));
                    $coffset++;
                }
                $s1->getColumnDimension(getNameFromNumber($coffset))->setWidth('15');
                $s1->setCellValue(getNameFromNumber($coffset).'4', 'Prom. Final.'); // Prom Final
                $s1->mergeCells($cellName.':'.getNameFromNumber($currentCol + count($criterios)).'3');
                $s1->getStyle(getNameFromNumber($coffset).'4')->getFill()->applyFromArray(array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'CCFFCC')
                ));
            }else{
               $s1->mergeCells($cellName.':'.getNameFromNumber($currentCol).'4');
            }

            $currentCol = ($currentCol + count($criterios)) + 1;
        }

        // promedios inician desde aqui

        $headers = array(
            'PROMEDIO GENERAL' => array('width' => 20),
            'PROMEDIO DEL AULA' => array('width' => 20),


            'Inasistencias Injustificadas' => array('width' => 25),
            'Inasistencias Justificadas' => array('width' => 25),
            'Tardanzas',
            //'Observaciones' => array('width' => 100),
        );

        foreach($headers As $key => $head){
            if($head instanceOf Closure){
                $head(getNameFromNumber($currentCol).'3', $currentCol);
            }else{
                $title = $head;
                $width = 10;

                if(is_array($head)){
                    $title = $key;
                    $width = $head['width'];
                }

                $s1->setCellValue(getNameFromNumber($currentCol).'3', $title);
                $s1->mergeCells(getNameFromNumber($currentCol).'3:'.getNameFromNumber($currentCol).'4');
                $s1->getColumnDimension(getNameFromNumber($currentCol))->setWidth($width);
            }
            $currentCol++;
        }

        // elimina columnas sobrantes
        // ni idea porque el +3
        for($col = 117;$col >= $currentCol; $col--){
            $s1->removeColumn(getNameFromNumber($col), 1);
        }

        $this->writeExcel($excel);


    }

    function getLibretaInicialPage(Matricula $matricula, $pdf){
        // fetch all data

        $alumno = $matricula->alumno;
        $nota_aprobatoria = $matricula->grupo->nivel->nota_aprobatoria;
        $_height = 4.3;
        if($matricula->grupo->grado == 5){
            $_height = 6;
        }
        $total_width = 120;
        // end data

        $this->setHeaderLibreta($pdf, $matricula, $alumno);

        //$pdf->cell(20,$_height,'PROM',1,0,'C',1);

        $asignaturas = $matricula->getAsignaturas();
        //$pdf->ln(2);
        foreach($asignaturas As $keyAsignatura => $asignatura){
            $criterios = $asignatura->getCriterios($this->get->ciclo);
            if(count($criterios) == 0) continue;
            $c_height = count($criterios) * $_height;
            $promedio = $matricula->getPromedio($asignatura->id, $this->get->ciclo);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->cell(70,$c_height, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'),1,0,'L',0);

            //$pdf->setY($pdf->getY());
            $currentX = $pdf->getX();
            $currentY = $pdf->getY();
            foreach($criterios As $keyCriterio => $criterio){

                $nota = $matricula->getNota($asignatura->id, $criterio->id, $this->get->ciclo);

                $pdf->setX($currentX);
                $pdf->cell(100,$_height, $criterio->descripcion,1,0,'L',0, 0, 1);

                $pdf->cell(15,$_height, is_null($nota) ? '-' : strtoupper($nota),1,0,'C',0, 0, 1);
                // SET PROMEDIO HERE
                if($keyCriterio == 0){
                    $pdf->cell(15,$c_height, is_null($promedio) ? '-' : strtoupper($promedio),1,0,'C',0, 0, 1);
                }

                $pdf->ln($_height);
            }


            $pdf->SetFont('helvetica', '', 8);
            //$pdf->ln(1);

        }
        $pdf->ln(1);
        $areasY = $pdf->getY();

        $pdf->cell(50,$_height*2, 'AREAS',1,0,'C',0, 0, 1);
        $pdf->cell(50,$_height, 'BIMESTRE',1,0,'C',0, 0, 1);
        $pdf->ln($_height);
        $pdf->cell(50);
        for($i = 1 ; $i<= $this->COLEGIO->total_notas; ++$i){
            $pdf->cell(50/($this->COLEGIO->total_notas + 1),$_height, $this->COLEGIO->roman($i),1,0,'C',0, 0, 1);
        }
        $pdf->cell(50/($this->COLEGIO->total_notas + 1),$_height, 'PF',1,0,'C',0, 0, 1);

        $totalCursos = 0;
        $asignaturas[] = (object) array(
            'id' => -101, // CONDUCTA
            'curso' => new Curso(array(
                'nombre' => 'Conducta'
            ))
        );

        $ALLOWED = array('AD','A', 'B', 'C');
        $VALUES = array('AD' => 4, 'A' => 3, 'B' => 2, 'C' => 1);
        $MAP = array(1 => 'C', 2 => 'B', 3 => 'A', 4 => 'AD');
        $totaln = 0;
        foreach($asignaturas As $asignatura){
            if($asignatura->id != -101){
                $criterios = $asignatura->getCriterios($this->get->ciclo);
                if(count($criterios) == 0) continue;
            }
            


            $pdf->ln($_height);
            $pdf->cell(50,$_height, $asignatura->curso->nombre,1,0,'L',0, 0, 1);
            $total = array();
            for($i=1 ; $i<= $this->COLEGIO->total_notas; ++$i){
                $promedio = null;
                if($i <= $this->get->ciclo) $promedio = $matricula->getPromedio($asignatura->id, $i);
                if($i <= $this->get->ciclo && !is_null($promedio)) ++$totalCursos;
                if(!is_null($promedio)) 
                    $total[] = $VALUES[strtoupper($promedio)];
                //$total[] = $promedio;

                $pdf->cell(50/($this->COLEGIO->total_notas + 1),$_height, is_null($promedio) ? '-' : strtoupper($promedio),1,0,'C',0, 0, 1);
            }
            $promedioFinal = '-';
            if($this->get->ciclo == $this->COLEGIO->total_notas)
                $promedioFinal = count($total) > 0 ? $MAP[round(array_sum($total) / count($total))] : 'B';
                //$promedioFinal = count($total) > 0  ? $MAP[round(array_sum($total) / count($total))].$total[0].'_'.count($total) : '';
            //

            $pdf->cell(50/($this->COLEGIO->total_notas + 1),$_height, $promedioFinal,1,0,'C',0, 0, 1);
           // $pdf->cell(0, 5, print_r($total, true));
            $totaln += 1;
        }




        $pdf->setY($areasY);
        $pdf->setX(110);
        $pdf->cell(95,$_height, 'OBSERVACIONES DEL TUTOR(A)',1,0,'C',0, 0, 1);
        $pdf->ln($_height);
        $pdf->setX(110);
        //$pdf->multicell(95, 30, $matricula->getRecomendacion($this->get->ciclo)->descripcion, 1, 'L', 0, 1, '', '', true, 0, false, true, 0, 'M');
        $promedioCiclo = $matricula->getPromedioCiclo($this->get->ciclo, $totalCursos);
        $mensaje = $this->COLEGIO->getRangoMensajeByNota($promedioCiclo);
        $recomendacion = $matricula->getRecomendacion($this->get->ciclo);
        $pdf->multicell(95, 30, trim($mensaje."\n".$recomendacion->descripcion), 1, 'L', 0, 1, '', '', true, 0, false, true, 0, 'M');

        $pdf->setY($areasY);

        $pdf->ln(($totaln) * ($_height + 3));
        

        $pdf->cell(15);
        $pdf->cell(70, 4, '_______________________________________', 0, 0, 'C');
        $pdf->cell(30);
        $pdf->cell(70, 4, '_______________________________________', 0, 0, 'C');

        $tutor = $matricula->grupo->tutor;
        $pdf->ln(5);
        $pdf->cell(15);
        $pdf->cell(70, 5, 'Profesor/Tutor: '.(isset($tutor) ? $tutor->getFullName() : ''), 0, 0, 'C', 0, 0, 1);
        $pdf->cell(30);

        $director = $this->COLEGIO->getCurrentDirector();
        $pdf->cell(70, 5, 'Dirección', 0, 0, 'C', 0, 0, 1);

    }

    function getLibretaPage(Matricula $matricula, $pdf){
        // fetch all data

        $alumno = $matricula->alumno;
        $nota_aprobatoria = $matricula->grupo->nivel->nota_aprobatoria;
        $_height = 5;
        $total_width = 120;
        // end data

        $this->setHeaderLibreta($pdf, $matricula, $alumno);
        $pdf->SetFont('helvetica', 'b', 9);
        $pdf->cell(80,$_height,'ÁREAS CURRICULARES',1,0,'C',1);

        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $description = $this->COLEGIO->getCicloNotasSingleShort($i);
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, mb_strtoupper($description, 'utf-8'),1,0,'C',1, 0, 1);
        }

        $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, 'PF',1,0,'C',1, 0, 1);

        //$pdf->cell(20,$_height,'PROM',1,0,'C',1);

        $areas = $this->COLEGIO->getAreasByNivel($matricula->grupo->nivel_id);
        $total_cursos = array();

        $already = array();
        foreach($areas As $area){
            $asignaturas = $matricula->getAsignaturasByArea($area->id);
            if(count($asignaturas) > 0){
                $pdf->ln($_height);
                $pdf->SetFont('helvetica', 'b', 9);
                $pdf->cell(80,$_height,mb_strtoupper($area->nombre, 'UTF-8'),1,0,'L',1);

                for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
                    $promedio = null;
                    if($i <= $this->get->ciclo){
                        $promedio = $matricula->getPromedioArea($area->id, $i);
                    }

                    if(is_null($promedio)){
                        $promedio = '-';
                    }else{
                        if(is_numeric($promedio)){
                            if($promedio >= $nota_aprobatoria) $pdf->setTextColor(0, 0, 255);
                            if($promedio < $nota_aprobatoria) $pdf->setTextColor(255, 0, 0);
                            //$total_cursos[$i] += 1;
                        }
                    }

                    $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio, 1,0,'C',1);
                    $pdf->setTextColor(0, 0, 0);
                }

                $promedio_final = ($this->get->ciclo == $this->COLEGIO->total_notas) ? $matricula->getPromedioFinalArea($area->id) : '-';
                $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio_final, 1,0,'C',1);

                $pdf->SetFont('helvetica', '', 8);
                //$pdf->cell(20,$_height, '',1,0,'C',0);
                // PRINT ASIGNATURAS
                foreach($asignaturas As $asignatura){
                    $already[] = $asignatura->id;
                    $pdf->ln($_height);
                    $pdf->SetFont('helvetica', '', 8);
                    $pdf->cell(80,$_height,mb_strtoupper($asignatura->curso->nombre, 'UTF-8'),1,0,'L',0);
                    $pdf->SetFont('helvetica', '', 8);
                    for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
                        $promedio = null;
                        if($i <= $this->get->ciclo){
                            $promedio = $matricula->getPromedio($asignatura->id, $i);
                        }
                        //$promedio = 0;
                        if(is_null($promedio)){
                            $promedio = '-';
                        }else{
                            if(is_numeric($promedio)){
                                if($promedio >= $nota_aprobatoria) $pdf->setTextColor(0, 0, 255);
                                if($promedio < $nota_aprobatoria) $pdf->setTextColor(255, 0, 0);
                                $total_cursos[$i] += 1;
                            }
                        }

                        //if($asignatura->grupo->nivel->calificacionPorcentual() and $asignatura->curso->examenMensual()){
                        //    $pdf->cell(($total_width / ($this->COLEGIO->total_notas + 1))/2, $_height, $promedio, 1,0,'C',0);
                        //    $pdf->setTextColor(0, 0, 0);
                        //    $promedioExamenMensual = $matricula->getPromedioExamenMensual($asignatura, $i, false);
                        //    $pdf->cell(($total_width / ($this->COLEGIO->total_notas + 1))/2, $_height, empty($promedioExamenMensual) ? '-' : $promedioExamenMensual, 1,0,'C',0);
                        //}else{
                            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio, 1,0,'C',0);
                        //}

                        $pdf->setTextColor(0, 0, 0);
                    }

                    $promedio_final = ($this->get->ciclo == $this->COLEGIO->total_notas) ? $matricula->getPromedioFinalAsignatura($asignatura->id) : '-';
                    $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio_final ,1,0,'C',0);
                }
            }
        }

        if(count($areas) > 0) $pdf->ln($_height);


        $asignaturas = $matricula->getAsignaturas();

        foreach($asignaturas As $asignatura){
            if(in_array($asignatura->id, $already)) continue;
            $pdf->ln($_height);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->cell(80,$_height, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'),1,0,'L',0);
            $pdf->SetFont('helvetica', '', 8);

            for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
               $promedio = null;
                if($i <= $this->get->ciclo){
                    $promedio = $matricula->getPromedio($asignatura->id, $i);
                }

                if(is_null($promedio)){
                    $promedio = '-';
                }else{
                    if(is_numeric($promedio)){
                        if($promedio >= $nota_aprobatoria) $pdf->setTextColor(0, 0, 255);
                        if($promedio < $nota_aprobatoria) $pdf->setTextColor(255, 0, 0);
                        $total_cursos[$i] += 1;
                    }
                }

                //$pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio, 1,0,'C',0);
                //$pdf->setTextColor(0, 0, 0);

                //if($asignatura->grupo->nivel->calificacionPorcentual() and $asignatura->curso->examenMensual()){
                //    $pdf->cell(($total_width / ($this->COLEGIO->total_notas + 1))/2, $_height, $promedio, 1,0,'C',0);
                //    $pdf->setTextColor(0, 0, 0);
                //    $promedioExamenMensual = $matricula->getPromedioExamenMensual($asignatura, $i, false);
                //    $pdf->cell(($total_width / ($this->COLEGIO->total_notas + 1))/2, $_height, empty($promedioExamenMensual) ? '-' : $promedioExamenMensual, 1,0,'C',0);
                //}else{
                    $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio, 1,0,'C',0);
                //}
                $pdf->setTextColor(0, 0, 0);


            }
            $promedio_final = ($this->get->ciclo == $this->COLEGIO->total_notas) ? $matricula->getPromedioFinalAsignatura($asignatura->id) : '-';
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio_final ,1,0,'C',0);
            //$pdf->cell(20,$_height, $matricula->getPromedioFinalAsignatura($asignatura->id, $config->ciclos),1,0,'C',0);
        }

        $pdf->ln(6);
        $_height = 4;
        $pdf->cell(80 + $total_width - ($total_width / ($this->COLEGIO->total_notas + 1)), $_height, 'CUADRO RESUMEN', 1, 0, 'C', 1, 0, 1);
        $pdf->Ln($_height);

        $pdf->cell(80, $_height, 'CANTIDAD DE CURSOS',1, 0, 'L', 1, 0, 1);

        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $total_cursosx = null;
            if($i <= $this->get->ciclo){
                $total_cursosx = $total_cursos[$i];
            }

            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($total_cursosx) ? $total_cursosx : '-', 1,0,'C',0);
        }

        $pdf->Ln($_height);

        $pdf->cell(80, $_height, 'SUMA DE NOTAS',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $puntaje = 0;
            if($i <= $this->get->ciclo){
                $puntaje = $matricula->getPuntajeCiclo($i);
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $puntaje > 0 ? $puntaje : '-', 1,0,'C',0);
        }

        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'PROMEDIO',1, 0, 'L', 1, 0, 1);
        $total = 0;
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $promedio = null;
            if($i <= $this->get->ciclo){
                $promedio = $matricula->getPromedioCiclo($i, $total_cursos[$i]);
                $total += $promedio;
            }

            if($i == $this->get->ciclo){
                $promedioFinalCiclo = $promedio;
            }

            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($promedio) ? round($promedio) : '-', 1,0,'C',0);
        }

        if($this->get->ciclo == $this->COLEGIO->total_notas){
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, round($total / $this->COLEGIO->total_notas), 1,0,'C',0);
        }



        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'PROMEDIO CONDUCTA',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $promedio = null;
            if($i <= $this->get->ciclo){
                // OVERWRITE
                $promedio = $matricula->getPromedio(-101, $i);
                // MERITOS
                if(!$promedio)
                    $promedio = $matricula->getPromedioConducta($i);
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($promedio) ? $promedio : '-', 1,0,'C',0);
        }

        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'ORDEN DE MÉRITO',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $orden = null;
            if($i <= $this->get->ciclo){
                $orden = $matricula->getOrdenMeritoCiclo($i);
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($orden) ? 'Puesto: '.$orden : '-', 1,0,'C',0);
        }

        $rangeCiclos = $this->COLEGIO->getRangosCiclosNotas();

        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'Nº DE INASISTENCIAS',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $faltas = null;
            if($i <= $this->get->ciclo){
                $faltas = $matricula->getRangeAsistencia($rangeCiclos[$i]['inicio'], $rangeCiclos[$i]['final'], 'FALTA_INJUSTIFICADA');
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($faltas) ? $faltas : '-', 1,0,'C',0);
        }
        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'Nº DE TARDANZAS',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $faltas = null;
            if($i <= $this->get->ciclo){
                $faltas = $matricula->getRangeAsistencia($rangeCiclos[$i]['inicio'], $rangeCiclos[$i]['final'], 'TARDANZA_INJUSTIFICADA');
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($faltas) ? $faltas : '-', 1,0,'C',0);
        }

        $pdf->ln(6);


        $this->setFooterLibreta($pdf, $matricula, $promedioFinalCiclo);
    }

    function getLibretaPageLetras(Matricula $matricula, $pdf){
        // fetch all data

        $alumno = $matricula->alumno;
        $nota_aprobatoria = $matricula->grupo->nivel->nota_aprobatoria;
        $_height = 5;
        $total_width = 120;
        // end data

        $this->setHeaderLibreta($pdf, $matricula, $alumno);
        $pdf->SetFont('helvetica', 'b', 9);
        $pdf->cell(80,$_height,'ÁREAS CURRICULARES',1,0,'C',1);

        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $description = $this->COLEGIO->getCicloNotasSingleShort($i);
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, mb_strtoupper($description, 'utf-8'),1,0,'C',1, 0, 1);
        }

        $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, 'PF',1,0,'C',1, 0, 1);

        //$pdf->cell(20,$_height,'PROM',1,0,'C',1);

        $areas = $this->COLEGIO->getAreasByNivel($matricula->grupo->nivel_id);
        $total_cursos = array();

        $already = array();
        foreach($areas As $area){
            $asignaturas = $matricula->getAsignaturasByArea($area->id);
            if(count($asignaturas) > 0){
                $pdf->ln($_height);
                $pdf->SetFont('helvetica', 'b', 9);
                $pdf->cell(80,$_height,mb_strtoupper($area->nombre, 'UTF-8'),1,0,'L',1);

                for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
                    $promedio = null;
                    if($i <= $this->get->ciclo){
                        $promedio = $matricula->getPromedioArea($area->id, $i);
                    }

                    if(is_null($promedio)){
                        $promedio = '-';
                    }else{
                        if(is_numeric($promedio)){
                            if($promedio >= $nota_aprobatoria) $pdf->setTextColor(0, 0, 255);
                            if($promedio < $nota_aprobatoria) $pdf->setTextColor(255, 0, 0);
                            //$total_cursos[$i] += 1;
                        }
                    }

                    $letra = $this->COLEGIO->getLetraNotaPrimariaByNota($promedio);
                    $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $letra, 1,0,'C',1);
                    $pdf->setTextColor(0, 0, 0);
                }

                $promedio_final = ($this->get->ciclo == $this->COLEGIO->total_notas) ? $matricula->getPromedioFinalArea($area->id) : '-';
                $letra = $this->COLEGIO->getLetraNotaPrimariaByNota($promedio_final);
                $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $letra, 1,0,'C',1);

                $pdf->SetFont('helvetica', '', 8);
                //$pdf->cell(20,$_height, '',1,0,'C',0);
                // PRINT ASIGNATURAS
                foreach($asignaturas As $asignatura){
                    $already[] = $asignatura->id;
                    $pdf->ln($_height);
                    $pdf->SetFont('helvetica', '', 8);
                    $pdf->cell(80,$_height,mb_strtoupper($asignatura->curso->nombre, 'UTF-8'),1,0,'L',0);
                    $pdf->SetFont('helvetica', '', 8);
                    for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
                        $promedio = null;
                        if($i <= $this->get->ciclo){
                            $promedio = $matricula->getPromedio($asignatura->id, $i);
                        }
                        //$promedio = 0;
                        if(is_null($promedio)){
                            $promedio = '-';
                        }else{
                            if(is_numeric($promedio)){
                                if($promedio >= $nota_aprobatoria) $pdf->setTextColor(0, 0, 255);
                                if($promedio < $nota_aprobatoria) $pdf->setTextColor(255, 0, 0);
                                $total_cursos[$i] += 1;
                            }
                        }

                        //if($asignatura->grupo->nivel->calificacionPorcentual() and $asignatura->curso->examenMensual()){
                        //    $pdf->cell(($total_width / ($this->COLEGIO->total_notas + 1))/2, $_height, $promedio, 1,0,'C',0);
                        //    $pdf->setTextColor(0, 0, 0);
                        //    $promedioExamenMensual = $matricula->getPromedioExamenMensual($asignatura, $i, false);
                        //    $pdf->cell(($total_width / ($this->COLEGIO->total_notas + 1))/2, $_height, empty($promedioExamenMensual) ? '-' : $promedioExamenMensual, 1,0,'C',0);
                        //}else{
                            $letra = $this->COLEGIO->getLetraNotaPrimariaByNota($promedio);
                            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $letra, 1,0,'C',0);
                        //}

                        $pdf->setTextColor(0, 0, 0);
                    }

                    $promedio_final = ($this->get->ciclo == $this->COLEGIO->total_notas) ? $matricula->getPromedioFinalAsignatura($asignatura->id) : '-';
                    $letra = $this->COLEGIO->getLetraNotaPrimariaByNota($promedio_final);
                    $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $letra ,1,0,'C',0);
                }
            }
        }

        if(count($areas) > 0) $pdf->ln($_height);


        $asignaturas = $matricula->getAsignaturas();

        foreach($asignaturas As $asignatura){
            if(in_array($asignatura->id, $already)) continue;
            $pdf->ln($_height);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->cell(80,$_height, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'),1,0,'L',0);
            $pdf->SetFont('helvetica', '', 8);

            for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
               $promedio = null;
                if($i <= $this->get->ciclo){
                    $promedio = $matricula->getPromedio($asignatura->id, $i);
                }

                if(is_null($promedio)){
                    $promedio = '-';
                }else{
                    if(is_numeric($promedio)){
                        if($promedio >= $nota_aprobatoria) $pdf->setTextColor(0, 0, 255);
                        if($promedio < $nota_aprobatoria) $pdf->setTextColor(255, 0, 0);
                        $total_cursos[$i] += 1;
                    }
                }

                //$pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio, 1,0,'C',0);
                //$pdf->setTextColor(0, 0, 0);

                //if($asignatura->grupo->nivel->calificacionPorcentual() and $asignatura->curso->examenMensual()){
                //    $pdf->cell(($total_width / ($this->COLEGIO->total_notas + 1))/2, $_height, $promedio, 1,0,'C',0);
                //    $pdf->setTextColor(0, 0, 0);
                //    $promedioExamenMensual = $matricula->getPromedioExamenMensual($asignatura, $i, false);
                //    $pdf->cell(($total_width / ($this->COLEGIO->total_notas + 1))/2, $_height, empty($promedioExamenMensual) ? '-' : $promedioExamenMensual, 1,0,'C',0);
                //}else{
                    $letra = $this->COLEGIO->getLetraNotaPrimariaByNota($promedio);
                    $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $letra, 1,0,'C',0);
                //}
                $pdf->setTextColor(0, 0, 0);


            }
            $promedio_final = ($this->get->ciclo == $this->COLEGIO->total_notas) ? $matricula->getPromedioFinalAsignatura($asignatura->id) : '-';
            $letra = $this->COLEGIO->getLetraNotaPrimariaByNota($promedio_final);
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $letra ,1,0,'C',0);
            //$pdf->cell(20,$_height, $matricula->getPromedioFinalAsignatura($asignatura->id, $config->ciclos),1,0,'C',0);
        }

        $pdf->ln(6);
        $_height = 4;
        $pdf->cell(80 + $total_width - ($total_width / ($this->COLEGIO->total_notas + 1)), $_height, 'CUADRO RESUMEN', 1, 0, 'C', 1, 0, 1);
        $pdf->Ln($_height);

        $pdf->cell(80, $_height, 'CANTIDAD DE CURSOS',1, 0, 'L', 1, 0, 1);

        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $total_cursosx = null;
            if($i <= $this->get->ciclo){
                $total_cursosx = $total_cursos[$i];
            }

            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($total_cursosx) ? $total_cursosx : '-', 1,0,'C',0);
        }

        $pdf->Ln($_height);

        $pdf->cell(80, $_height, 'SUMA DE NOTAS',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $puntaje = 0;
            if($i <= $this->get->ciclo){
                $puntaje = $matricula->getPuntajeCiclo($i);
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $puntaje > 0 ? $puntaje : '-', 1,0,'C',0);
        }

        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'PROMEDIO',1, 0, 'L', 1, 0, 1);
        $total = 0;
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $promedio = null;
            if($i <= $this->get->ciclo){
                $promedio = $matricula->getPromedioCiclo($i, $total_cursos[$i]);
                $total += $promedio;
            }

            if($i == $this->get->ciclo){
                $promedioFinalCiclo = $promedio;
            }

            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($promedio) ? round($promedio) : '-', 1,0,'C',0);
        }

        if($this->get->ciclo == $this->COLEGIO->total_notas){
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, round($total / $this->COLEGIO->total_notas), 1,0,'C',0);
        }



        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'PROMEDIO CONDUCTA',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $promedio = null;
            if($i <= $this->get->ciclo){
                // OVERWRITE
                $promedio = $matricula->getPromedio(-101, $i);
                // MERITOS
                if(!$promedio)
                    $promedio = $matricula->getPromedioConducta($i);
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($promedio) ? $promedio : '-', 1,0,'C',0);
        }

        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'ORDEN DE MÉRITO',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $orden = null;
            if($i <= $this->get->ciclo){
                $orden = $matricula->getOrdenMeritoCiclo($i);
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($orden) ? 'Puesto: '.$orden : '-', 1,0,'C',0);
        }

        $rangeCiclos = $this->COLEGIO->getRangosCiclosNotas();

        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'Nº DE INASISTENCIAS',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $faltas = null;
            if($i <= $this->get->ciclo){
                $faltas = $matricula->getRangeAsistencia($rangeCiclos[$i]['inicio'], $rangeCiclos[$i]['final'], 'FALTA_INJUSTIFICADA');
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($faltas) ? $faltas : '-', 1,0,'C',0);
        }
        $pdf->Ln($_height);
        $pdf->cell(80, $_height, 'Nº DE TARDANZAS',1, 0, 'L', 1, 0, 1);
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $faltas = null;
            if($i <= $this->get->ciclo){
                $faltas = $matricula->getRangeAsistencia($rangeCiclos[$i]['inicio'], $rangeCiclos[$i]['final'], 'TARDANZA_INJUSTIFICADA');
            }
            $pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, isset($faltas) ? $faltas : '-', 1,0,'C',0);
        }

        $pdf->ln(6);


        $this->setFooterLibreta($pdf, $matricula, $promedioFinalCiclo);
    }

    function imprimir(){
        $matricula = Matricula::find([
            'conditions' => ['sha1(id) = ?', $this->get->matricula_id]
        ]);
        //echo $matricula->grupo->nivel->nombre;
        //if(preg_match('/inicial/i', $matricula->grupo->nivel->nombre)){
        if($matricula->grupo->nivel->tipo_calificacion == 0){
            //echo 'INICIAL';
            $this->imprimir_inicial($matricula);
        }else{
            $this->imprimir_otros($matricula);
        }
    }

    function imprimir_letras(){
        $matricula = Matricula::find([
            'conditions' => ['sha1(id) = ?', $this->get->matricula_id]
        ]);
        //echo $matricula->grupo->nivel->nombre;
        //if(preg_match('/inicial/i', $matricula->grupo->nivel->nombre)){
        if($matricula->grupo->nivel->tipo_calificacion == 0){
            //echo 'INICIAL';
            $this->imprimir_inicial($matricula);
        }else{
            $this->imprimir_otros_letras($matricula);
        }
    }

    function imprimir_inicial($matricula){
        $this->crystal->load('TCPDF');
        $pdf = new TCPDF();
        $this->getLibretaInicialPage($matricula, $pdf);
        $pdf->output();
    }

    function imprimir_otros($matricula){
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
        $this->getLibretaPage($matricula, $pdf);
		$pdf->output();
	}

    function imprimir_otros_letras($matricula){
        $this->crystal->load('TCPDF');
        $pdf = new TCPDF();
        $this->getLibretaPageLetras($matricula, $pdf);
        $pdf->output();
    }

   // HEADER - FOOTER

   function setHeaderLibreta($pdf, $matricula, $alumno){
        $pdf->SetFillColor(229,229,229);
		$pdf->setMargins(5,10,5);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(5, 5, 5);
		$pdf->SetAutoPageBreak(true, 0);
		$pdf->addPage();

        // water mark
        $pdf->SetAlpha(0.2);
        $pdf->image('./Static/Image/Fondos/'.Config::get('libreta_fondo'), 57, 60, 102, 100);
        $pdf->SetAlpha(1);
        // header
		$pdf->image('./Static/Image/Fondos/'.Config::get('libreta_logo'), 20, 3, 21.7, 20.9);


        $pdf->SetFont('helvetica', 'b', 14);
		$pdf->cell(0,5, 'BOLETA DE INFORMACIÓN ACADÉMICA',0,0,'C');
        $pdf->ln(10);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->cell(0,5,mb_strtoupper($this->COLEGIO->titulo_intranet, 'utf-8'),0,0,'C');


		//$pdf->image('.'.$alumno->getFoto(), 165, 5, 20, 23);
        $h_height = 5;
		$pdf->Ln(10);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(40,$h_height,'ALUMNO',1,0,'C',1);
		//$pdf->SetFillColor(255,255,255);
		$pdf->cell(95,$h_height, $alumno->getFullName(),1,0,'C', 0, 0, 1);
        $pdf->cell(30,$h_height,'CÓDIGO',1,0,'C',1);
        $pdf->cell(35,$h_height, $alumno->codigo,1,0,'C',0);

		$pdf->Ln($h_height);
        $pdf->cell(40,$h_height,'GRADO / SECCIÓN',1,0,'C',1);
        $pdf->cell(30,$h_height, $matricula->grupo->getGrado().' '.strtoupper($matricula->grupo->seccion),1,0,'C',0, 0, 1);
		$pdf->cell(30,$h_height,'NIVEL',1,0,'C',1);
		$pdf->cell(35,$h_height,strtoupper($matricula->grupo->nivel->nombre),1,0,'C',0);


		$pdf->cell(30,$h_height,'TURNO',1,0,'C',1);
		$pdf->cell(35,$h_height,$matricula->grupo->turno->nombre,1,0,'C',0);
        $pdf->ln($h_height);
        $pdf->cell(40,5,'PROFESOR / TUTOR',1,0,'C',1);
        $tutor = $matricula->grupo->tutor;
        $pdf->cell(95,5, !isset($tutor) ? 'NO SE ASIGNÓ TUTOR' : mb_strtoupper($tutor->apellidos.' '.$tutor->nombres, 'utf-8'),1,0,'C',0, 0, 1);
        $pdf->cell(30,5,'AÑO',1,0,'C',1);
        $pdf->cell(35,5, $matricula->grupo->anio,1,0,'C',0);

		$pdf->ln(6);
    }

    function setFooterLibreta($pdf, $matricula, $promedioFinalCiclo){
        $config = $this->__config;

        $pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5,'Apreciaciones / Recomendaciones del Tutor',0,0,'L');
        $pdf->ln(3);
		$pdf->setFont('helvetica', '', 9);
        $pdf->cell(0, 10, $matricula->getRecomendacion($this->get->ciclo)->descripcion, 0, 0, 'L', 0, 0, 1);
        //$mensaje = $this->COLEGIO->getRangoMensajeByNota($promedioFinalCiclo);

        //$pdf->cell(0, 10, $mensaje, 0, 0, 'L', 0, 0, 1);
        //$pdf->cell(0, 7, $matricula->getLastObservacion(), 0, 0, 'L');


        $pdf->Ln(15);
        $pdf->cell(15);
        $pdf->cell(70, 5, '_______________________________________', 0, 0, 'C');
        $pdf->cell(30);
        $pdf->cell(70, 5, '_______________________________________', 0, 0, 'C');

        $tutor = $matricula->grupo->tutor;
        $pdf->ln(6);
        $pdf->cell(15);
        $pdf->cell(70, 5, 'Profesor/Tutor: '.(isset($tutor) ? $tutor->getFullName() : ''), 0, 0, 'C', 0, 0, 1);
        $pdf->cell(30);

        $director = $this->COLEGIO->getCurrentDirector();
        $pdf->cell(70, 5, 'Dirección', 0, 0, 'C', 0, 0, 1);
    }

    function imprimir_grupo(){
        $grupo = Grupo::find($this->get->grupo_id);
        //echo $matricula->grupo->nivel->nombre;
        if(preg_match('/inicial/i', $grupo->nivel->nombre)){
            //echo 'INICIAL';
            $this->imprimir_grupo_inicial($grupo);
        }else{
            $this->imprimir_grupo_otros($grupo);
        }
    }

    function imprimir_grupo_inicial($grupo){
        $this->crystal->load('TCPDF');
        $pdf = new TCPDF();


        $matriculas = $grupo->getMatriculas();
        foreach($matriculas As $matricula){
            //header('Location: /notas/imprimir?ciclo='.$this->get->ciclo.'&matricula_id='.$matricula->id);
            $this->getLibretaInicialPage($matricula, $pdf);
        }

        $pdf->output();
    }

    function imprimir_grupo_otros($grupo){
        $this->crystal->load('TCPDF');
        $pdf = new TCPDF();


        $matriculas = $grupo->getMatriculas();
        foreach($matriculas As $matricula){
            //header('Location: /notas/imprimir?ciclo='.$this->get->ciclo.'&matricula_id='.$matricula->id);
            $this->getLibretaPage($matricula, $pdf);
        }

        $pdf->output();
    }

    function imprimir_excel(){
         $matricula = Matricula::find([
            'conditions' => ['sha1(id) = ?', $this->get->matricula_id]
        ]);
        //echo $matricula->grupo->nivel->nombre;
        if(preg_match('/inicial/i', $matricula->grupo->nivel->nombre)){
            //echo 'INICIAL';
            $this->imprimir_excel_inicial($matricula);
        }else{
            $this->imprimir_excel_otros($matricula);
        }
    }

    function imprimir_excel_inicial(){
        $this->crystal->load('PHPExcel:PHPExcel');
        $excel = PHPExcel_IOFactory::load('./Static/Templates/libreta_inicial.xlsx');
        $s1 = $excel->getSheet(0);

         $matricula = Matricula::find([
            'conditions' => ['sha1(id) = ?', $this->get->matricula_id]
        ]);
        $alumno = $matricula->alumno;
        $tutor = $matricula->grupo->tutor;

        $normalStyle = array(
            'font'  => array(
                //'bold'  => true,
                'color' => array('rgb' => '000000'),
                'size'  => 9,
                'name'  => 'Calibri'
            ),
            'alignment' => array(
                'wrap' => true,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
        );

        $borderBottom = array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
        );

        $leftStyle = array(
            'alignment' => array(
                'wrap' => true,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
        );

        $centerStyle = array(
            'alignment' => array(
                'wrap' => true,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'font'  => array(
                //'bold'  => true,
                'color' => array('rgb' => '000000'),
                'size'  => 9,
                'name'  => 'Calibri'
            ),
        );

        $boldStyle = array(
            'font'  => array(
                'bold'  => true,
            ),
        );

        $fillStyle = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'D8D8D8',
                ),
            ),
        );


        $s1->setCellValue('C3', mb_strtoupper($this->COLEGIO->titulo_intranet, 'utf-8'));
        $s1->setCellValue('C6', $alumno->getFullName());
        $s1->setCellValue('C7', $matricula->grupo->getGrado().' '.strtoupper($matricula->grupo->seccion));
        $s1->setCellValue('C8', !isset($tutor) ? 'NO SE ASIGNÓ TUTOR' : $tutor->getFullName());
        $s1->setCellValue('F7', strtoupper($matricula->grupo->nivel->nombre));
        $s1->setCellValue('I6', $alumno->codigo);
        $s1->setCellValue('I7', $matricula->grupo->turno->nombre);
        $s1->setCellValue('I8', $matricula->grupo->anio);


        $asignaturas = $matricula->getAsignaturas();
        $currentRow = 10;
        $totalCursos = 0;

        foreach($asignaturas As $keyAsignatura => $asignatura){
            $criterios = $asignatura->getCriterios($this->get->ciclo);
            if(count($criterios) <= 0) continue;

            $s1->setCellValue('A'.$currentRow, $asignatura->curso->nombre);
            $s1->mergeCells('A'.$currentRow.':C'.($currentRow + count($criterios) - 1));

            $promedio = $matricula->getPromedio($asignatura->id, $this->get->ciclo);
            if(!is_null($promedio)) ++$totalCursos;

            $s1->setCellValue('J'.$currentRow, is_null($promedio) ? '-' : strtoupper($promedio));
            $s1->mergeCells('J'.$currentRow.':J'.($currentRow + count($criterios) - 1));

            foreach($criterios As $criterio){
                $s1->setCellValue('D'.$currentRow, $criterio->descripcion);
                $s1->mergeCells('D'.$currentRow.':H'.$currentRow);

                $nota = $matricula->getNota($asignatura->id, $criterio->id, $this->get->ciclo);
                $s1->setCellValue('I'.$currentRow, is_null($nota) ? '-' : strtoupper($nota));
                ++$currentRow;
            }
        }

        --$currentRow;
        if($currentRow > 10){
            $s1->getStyle('A10:J'.$currentRow)->applyFromArray($normalStyle);
            $s1->getStyle('A10:H'.$currentRow)->applyFromArray($leftStyle);
        }
        $currentRow += 2;

        $s1->setCellValue('A'.$currentRow, 'Apreciaciones / Recomendaciones del Tutor');
        $s1->mergeCells('A'.$currentRow.':J'.$currentRow);
        $s1->getStyle('A'.$currentRow)->applyFromArray($boldStyle);
        ++$currentRow;
        //$recomendacion = $matricula->getRecomendacion($this->get->ciclo)->descripcion;
        //$mensaje = $this->COLEGIO->getRangoMensajeByNota($promedioFinalCiclo);
        $promedioCiclo = $matricula->getPromedioCiclo($this->get->ciclo, $totalCursos);
        $mensaje = $this->COLEGIO->getRangoMensajeByNota($promedioCiclo);
        $recomendacion = $matricula->getRecomendacion($this->get->ciclo);


        $s1->setCellValue('A'.$currentRow, trim($mensaje."\n".$recomendacion->descripcion));
        $s1->mergeCells('A'.$currentRow.':J'.($currentRow + 1));
        $s1->getStyle('A'.$currentRow.':J'.($currentRow + 1))->applyFromArray($normalStyle);


        $s1->getStyle('A'.($currentRow + 4).':D'.($currentRow + 4))->applyFromArray($borderBottom);
        $s1->setCellValue('A'.($currentRow + 5), 'Tutor: '.(isset($tutor) ? $tutor->getFullName() : ''));
        $s1->getStyle('A'.($currentRow + 5))->applyFromArray($centerStyle);
        $s1->mergeCells('A'.($currentRow + 5).':D'.($currentRow + 5));

        //$director = $this->COLEGIO->getCurrentDirector();
        //$pdf->cell(70, 5, 'Director(a): '.(!is_null($director) ? $director->getFullName() : ''), 0, 0, 'C', 0, 0, 1);

        $s1->getStyle('G'.($currentRow + 4).':J'.($currentRow + 4))->applyFromArray($borderBottom);
        //$s1->setCellValue('G'.($currentRow + 5), 'Director(a): '.(!is_null($director) ? $director->getFullName() : ''));
        $s1->setCellValue('G'.($currentRow + 5), 'Dirección');
        $s1->getStyle('G'.($currentRow + 5))->applyFromArray($centerStyle);
        $s1->mergeCells('G'.($currentRow + 5).':J'.($currentRow + 5));

        writeExcel($excel);
    }

    function imprimir_excel_otros(){
        $this->crystal->load('PHPExcel:PHPExcel');
        $excel = PHPExcel_IOFactory::load('./Static/Templates/libreta_general.xlsx');
        $s1 = $excel->getSheet(0);

         $matricula = Matricula::find([
            'conditions' => ['sha1(id) = ?', $this->get->matricula_id]
        ]);
        $alumno = $matricula->alumno;
        $tutor = $matricula->grupo->tutor;

        $normalStyle = array(
            'font'  => array(
                //'bold'  => true,
                'color' => array('rgb' => '000000'),
                'size'  => 9,
                'name'  => 'Calibri'
            ),
            'alignment' => array(
                'wrap' => true,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
        );

        $borderBottom = array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
        );

        $leftStyle = array(
            'alignment' => array(
                'wrap' => true,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
        );

        $centerStyle = array(
            'alignment' => array(
                'wrap' => true,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'font'  => array(
                //'bold'  => true,
                'color' => array('rgb' => '000000'),
                'size'  => 9,
                'name'  => 'Calibri'
            ),
        );

        $boldStyle = array(
            'font'  => array(
                'bold'  => true,
            ),
        );

        $fillStyle = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'D8D8D8',
                ),
            ),
        );


        $s1->setCellValue('C3', mb_strtoupper($this->COLEGIO->titulo_intranet, 'utf-8'));
        $s1->setCellValue('C6', $alumno->getFullName());
        $s1->setCellValue('C7', $matricula->grupo->getGrado().' '.strtoupper($matricula->grupo->seccion));
        $s1->setCellValue('C8', !isset($tutor) ? 'NO SE ASIGNÓ TUTOR' : $tutor->getFullName());
        $s1->setCellValue('F7', strtoupper($matricula->grupo->nivel->nombre));
        $s1->setCellValue('I6', $alumno->codigo);
        $s1->setCellValue('I7', $matricula->grupo->turno->nombre);
        $s1->setCellValue('I8', $matricula->grupo->anio);


        $areas = $this->COLEGIO->getAreasByNivel($matricula->grupo->nivel_id);
        $total_cursos = array();
        $already = array();

        $currentRow = 11;
        foreach($areas As $area){
            $asignaturas = $matricula->getAsignaturasByArea($area->id);
            if(count($asignaturas) > 0){
                $s1->setCellValue('A'.$currentRow, mb_strtoupper($area->nombre, 'UTF-8'));
                $s1->mergeCells('A'.$currentRow.':E'.$currentRow);
                $s1->getStyle('A'.$currentRow.':J'.$currentRow)->applyFromArray($normalStyle);
                $s1->getStyle('A'.$currentRow)->applyFromArray($leftStyle);
                $s1->getStyle('A'.$currentRow)->applyFromArray($boldStyle);
                $s1->getRowDimension($currentRow)->setRowHeight(12.5);

                $currentCol = 6;
                for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
                    $promedio = null;
                    if($i <= $this->get->ciclo){
                        $promedio = $matricula->getPromedioArea($area->id, $i);
                    }

                    if(is_null($promedio)){
                        $promedio = '-';
                    }else{
                        if(is_numeric($promedio)){
                            //if($promedio >= $nota_aprobatoria) $pdf->setTextColor(0, 0, 255);
                            //if($promedio < $nota_aprobatoria) $pdf->setTextColor(255, 0, 0);
                            //$total_cursos[$i] += 1;
                        }
                    }

                    $s1->setCellValue(numToChar($currentCol).$currentRow, $promedio);

                    ++$currentCol;
                }

                $promedioFinal = ($this->get->ciclo == $this->COLEGIO->total_notas) ? $matricula->getPromedioFinalArea($area->id) : '-';
                //$pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio_final, 1,0,'C',0);
                $s1->setCellValue(numToChar($currentCol).$currentRow, $promedioFinal);

                ++$currentRow;
                // PRINT ASIGNATURAS
                foreach($asignaturas As $asignatura){
                    $already[] = $asignatura->id;
                    $s1->setCellValue('A'.$currentRow, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'));

                    $currentCol = 6;
                    for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
                        $promedio = null;
                        if($i <= $this->get->ciclo){
                            $promedio = $matricula->getPromedio($asignatura->id, $i);
                        }
                        //$promedio = 0;
                        if(is_null($promedio)){
                            $promedio = '-';
                        }else{
                            if(is_numeric($promedio)){
                                //if($promedio >= $nota_aprobatoria) $pdf->setTextColor(0, 0, 255);
                                //if($promedio < $nota_aprobatoria) $pdf->setTextColor(255, 0, 0);
                                $total_cursos[$i] += 1;
                            }
                        }
                        $s1->setCellValue(numToChar($currentCol).$currentRow, $promedio);
                        ++$currentCol;
                    }

                    $promedioFinal = ($this->get->ciclo == $this->COLEGIO->total_notas) ? $matricula->getPromedioFinalAsignatura($asignatura->id) : '-';
                    //$pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio_final ,1,0,'C',0);

                    $s1->setCellValue(numToChar($currentCol).$currentRow, $promedioFinal);

                    $s1->mergeCells('A'.$currentRow.':E'.$currentRow);
                    $s1->getStyle('A'.$currentRow.':J'.$currentRow)->applyFromArray($normalStyle);
                    $s1->getStyle('A'.$currentRow)->applyFromArray($leftStyle);
                    $s1->getRowDimension($currentRow)->setRowHeight(12.5);

                    ++$currentRow;
                }
            }
        }
        if(count($areas) > 0){
            $s1->getRowDimension($currentRow)->setRowHeight(5);
            ++$currentRow;
        }

        $asignaturas = $matricula->getAsignaturas();

        foreach($asignaturas As $asignatura){
            if(in_array($asignatura->id, $already)) continue;
            $s1->setCellValue('A'.$currentRow, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'));

            $currentCol = 6;
            for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
                $promedio = null;
                if($i <= $this->get->ciclo){
                    $promedio = $matricula->getPromedio($asignatura->id, $i);
                }
                //$promedio = 0;
                if(is_null($promedio)){
                    $promedio = '-';
                }else{
                    if(is_numeric($promedio)){
                        //if($promedio >= $nota_aprobatoria) $pdf->setTextColor(0, 0, 255);
                        //if($promedio < $nota_aprobatoria) $pdf->setTextColor(255, 0, 0);
                        $total_cursos[$i] += 1;
                    }
                }
                $s1->setCellValue(numToChar($currentCol).$currentRow, $promedio);
                ++$currentCol;
            }

            $promedioFinal = ($this->get->ciclo == $this->COLEGIO->total_notas) ? $matricula->getPromedioFinalAsignatura($asignatura->id) : '-';
            //$pdf->cell($total_width / ($this->COLEGIO->total_notas + 1), $_height, $promedio_final ,1,0,'C',0);
            $s1->setCellValue(numToChar($currentCol).$currentRow, $promedioFinal);

            $s1->mergeCells('A'.$currentRow.':E'.$currentRow);
            $s1->getStyle('A'.$currentRow.':J'.$currentRow)->applyFromArray($normalStyle);
            $s1->getStyle('A'.$currentRow)->applyFromArray($leftStyle);
            $s1->getRowDimension($currentRow)->setRowHeight(12.5);

            ++$currentRow;
        }

        $s1->getRowDimension($currentRow)->setRowHeight(5);
        ++$currentRow;

        $s1->setCellValue('A'.$currentRow, 'CUADRO RESUMEN');
        $s1->mergeCells('A'.$currentRow.':J'.$currentRow); ++$currentRow;

        $s1->setCellValue('A'.$currentRow, 'CANTIDAD DE CURSOS');
        $s1->mergeCells('A'.$currentRow.':E'.$currentRow);

        $currentCol = 6;
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $total_cursosx = null;
            if($i <= $this->get->ciclo){
                $total_cursosx = $total_cursos[$i];
            }
            $s1->setCellValue(numToChar($currentCol).$currentRow, isset($total_cursosx) ? $total_cursosx : '-');
            ++$currentCol;
        }
        ++$currentRow;

        $s1->setCellValue('A'.$currentRow, 'SUMA DE NOTAS');
        $s1->mergeCells('A'.$currentRow.':E'.$currentRow);
        $currentCol = 6;
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $puntaje = 0;
            if($i <= $this->get->ciclo){
                $puntaje = $matricula->getPuntajeCiclo($i);
            }
            $s1->setCellValue(numToChar($currentCol).$currentRow, $puntaje > 0 ? $puntaje : '-');
            ++$currentCol;
        }
        ++$currentRow;

        $s1->setCellValue('A'.$currentRow, 'PROMEDIO');
        $s1->mergeCells('A'.$currentRow.':E'.$currentRow);
        $currentCol = 6;
        $total = 0;
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $promedio = null;
            if($i <= $this->get->ciclo){
                $promedio = $matricula->getPromedioCiclo($i, $total_cursos[$i]);
                $total += $promedio;
            }

            if($i == $this->get->ciclo){
                $promedioFinalCiclo = $promedio;
            }

            $s1->setCellValue(numToChar($currentCol).$currentRow, isset($promedio) ? $promedio : '-');
            ++$currentCol;
        }
        if($this->get->ciclo == $this->COLEGIO->total_notas){
            $s1->setCellValue(numToChar($currentCol).$currentRow, round($total / $this->COLEGIO->total_notas));
        }
        ++$currentRow;

        $s1->setCellValue('A'.$currentRow, 'PROMEDIO CONDUCTA');
        $s1->mergeCells('A'.$currentRow.':E'.$currentRow);
        $currentCol = 6;
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $promedio = null;
            if($i <= $this->get->ciclo){

                //$promedio = $matricula->getPromedioConducta($i);
                $promedio = $matricula->getPromedio(-101, $i);
                // MERITOS
                if(!$promedio)
                    $promedio = $matricula->getPromedioConducta($i);
            }
            $s1->setCellValue(numToChar($currentCol).$currentRow, isset($promedio) ? $promedio : '-');
            ++$currentCol;
        }
        ++$currentRow;

        $s1->setCellValue('A'.$currentRow, 'ORDEN DE MÉRITO');
        $s1->mergeCells('A'.$currentRow.':E'.$currentRow);
        $currentCol = 6;
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $orden = null;
            if($i <= $this->get->ciclo){
                $orden = $matricula->getOrdenMeritoCiclo($i);
            }
            $s1->setCellValue(numToChar($currentCol).$currentRow, isset($orden) ? 'Puesto: '.$orden : '-');
            ++$currentCol;
        }
        ++$currentRow;

        $rangeCiclos = $this->COLEGIO->getRangosCiclosNotas();

        $s1->setCellValue('A'.$currentRow, 'Nº DE INASISTENCIAS');
        $s1->mergeCells('A'.$currentRow.':E'.$currentRow);
        $currentCol = 6;
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $faltas = null;
            if($i <= $this->get->ciclo){
                $faltas = $matricula->getRangeAsistencia($rangeCiclos[$i]['inicio'], $rangeCiclos[$i]['final'], 'FALTA_INJUSTIFICADA');
            }
            $s1->setCellValue(numToChar($currentCol).$currentRow, isset($faltas) ? $faltas : '-');
            ++$currentCol;
        }
        ++$currentRow;

        $s1->setCellValue('A'.$currentRow, 'Nº DE TARDANZAS');
        $s1->mergeCells('A'.$currentRow.':E'.$currentRow);
        $currentCol = 6;
        for($i=1; $i<=$this->COLEGIO->total_notas; $i++){
            $faltas = null;
            if($i <= $this->get->ciclo){
                $faltas = $matricula->getRangeAsistencia($rangeCiclos[$i]['inicio'], $rangeCiclos[$i]['final'], 'TARDANZA_INJUSTIFICADA');
            }
            $s1->setCellValue(numToChar($currentCol).$currentRow, isset($faltas) ? $faltas : '-');
            ++$currentCol;
        }


        $s1->getStyle('A'.($currentRow - 7).':J'.$currentRow)->applyFromArray($normalStyle);
        $s1->getStyle('A'.($currentRow - 6).':A'.$currentRow)->applyFromArray($leftStyle);
        $s1->getStyle('A'.($currentRow - 7).':A'.$currentRow)->applyFromArray($fillStyle);
        $s1->getStyle('A'.($currentRow - 7).':A'.$currentRow)->applyFromArray($boldStyle);

        ++$currentRow;

        $s1->getRowDimension($currentRow)->setRowHeight(5);
        ++$currentRow;

        $s1->setCellValue('A'.$currentRow, 'Apreciaciones / Recomendaciones del Tutor');
        $s1->mergeCells('A'.$currentRow.':J'.$currentRow);
        $s1->getStyle('A'.$currentRow)->applyFromArray($boldStyle);

        ++$currentRow;

        //$recomendacion = $matricula->getRecomendacion($this->get->ciclo)->descripcion;
        $mensaje = $this->COLEGIO->getRangoMensajeByNota($promedioFinalCiclo);
        $s1->setCellValue('A'.$currentRow, $mensaje);
        $s1->mergeCells('A'.$currentRow.':J'.($currentRow + 1));
        $s1->getStyle('A'.$currentRow.':J'.($currentRow + 1))->applyFromArray($normalStyle);


        $s1->getStyle('A'.($currentRow + 4).':D'.($currentRow + 4))->applyFromArray($borderBottom);
        $s1->setCellValue('A'.($currentRow + 5), 'Tutor: '.(isset($tutor) ? $tutor->getFullName() : ''));
        $s1->getStyle('A'.($currentRow + 5))->applyFromArray($centerStyle);
        $s1->mergeCells('A'.($currentRow + 5).':D'.($currentRow + 5));

        //$director = $this->COLEGIO->getCurrentDirector();
        //$pdf->cell(70, 5, 'Director(a): '.(!is_null($director) ? $director->getFullName() : ''), 0, 0, 'C', 0, 0, 1);

        $s1->getStyle('G'.($currentRow + 4).':J'.($currentRow + 4))->applyFromArray($borderBottom);
        //$s1->setCellValue('G'.($currentRow + 5), 'Director(a): '.(!is_null($director) ? $director->getFullName() : ''));
        $s1->setCellValue('G'.($currentRow + 5), 'Dirección');
        $s1->getStyle('G'.($currentRow + 5))->applyFromArray($centerStyle);
        $s1->mergeCells('G'.($currentRow + 5).':J'.($currentRow + 5));

        writeExcel($excel);
    }

    function imprimir_cuantitativa(){
        $asignatura = Asignatura::find($this->params->id);
        $this->crystal->load('TCPDF');
        $pdf = new TCPDF();
        $pdf->SetFillColor(241, 247, 226);
        $pdf->setMargins(5,10,5);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(true, 0);

         $this->get_registro_cuantitativa($pdf, $asignatura);

         $pdf->output();
    }

    function imprimir_cuantitativa_grupo(){
        set_time_limit(0);
        $grupo = Grupo::find([
            'conditions' => ['sha1(id) = ?', $this->get->grupo_id]
        ]);
        $this->crystal->load('TCPDF');
        $pdf = new TCPDF();
        
        $pdf->setMargins(5,10,5);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(true, 0);
        $asignaturas = $grupo->getAsignaturas();
        foreach($asignaturas AS $key => $asignatura){
            $pdf->SetFillColor(241, 247, 226);
            $this->get_registro_cuantitativa($pdf, $asignatura);
            //if($key == 1) break;
        }
        

        $pdf->output();
    }

    function get_registro_cuantitativa($pdf, $asignatura){
        
        $pdf->addPage('L');

        // data
       
        $grupo = $asignatura->grupo;
        $matriculas = $grupo->getMatriculas();
        $criterios = $asignatura->getCriterios();
        $notaAprobatoria = $asignatura->grupo->nivel->nota_aprobatoria;
        // header
        $pdf->image('.'.$this->COLEGIO->getLoginInsignia(), 20, 3, 21.7, 20.9);

        $pdf->SetFont('helvetica', 'b', 14);
        $pdf->cell(0,5, 'REGISTRO DE NOTAS - '.$asignatura->curso->nombre.' - '.$this->COLEGIO->getCicloNotas().' '.$this->get->ciclo,0,0,'R');
        $pdf->ln(10);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->cell(0,5, mb_strtoupper($grupo->getNombreShort(), 'utf-8'),0,0,'R');

        
        $pdf->ln(15);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->cell(10, 10, 'N°', 1, 0, 'C', 1);
        $pdf->cell(100, 10, 'APELLIDOS Y NOMBRES', 1, 0, 'C', 1);
        $totalWidth = 165;
        $totalCriterios = count($criterios);
        if($asignatura->grupo->nivel->calificacionPorcentual() && $asignatura->curso->examenMensual()){
            $totalCriterios++;
        }
        $pdf->setFont('Helvetica', '', 9);
        foreach($criterios As $criterio){
            $pdf->cell($totalWidth / $totalCriterios, 5, $criterio->descripcion, 1, 0, 'C', 1, 0, 1);
            $currentX = $pdf->getX();
            $pdf->setY($pdf->getY() + 5);
            $pdf->setX($currentX - $totalWidth / $totalCriterios);
            $pdf->cell($totalWidth / $totalCriterios, 5, $criterio->peso.'%', 1, 0, 'C', 1, 0, 1);
            $pdf->setY($pdf->getY() - 5);
            $pdf->setX($currentX);

        }
        if($asignatura->grupo->nivel->calificacionPorcentual() && $asignatura->curso->examenMensual()){
            $pdf->cell($totalWidth / $totalCriterios, 5, 'EXAMEN MENSUAL', 1, 0, 'C', 1, 0, 1);
            $currentX = $pdf->getX();
            $pdf->setY($pdf->getY() + 5);
            $pdf->setX($currentX - $totalWidth / $totalCriterios);
            $pdf->cell($totalWidth / $totalCriterios, 5, $asignatura->curso->peso_examen_mensual.'%', 1, 0, 'C', 1, 0, 1);
            $pdf->setY($pdf->getY() - 5);
            $pdf->setX($currentX);
        }


        $pdf->cell(10, 10, 'PF', 1, 0, 'C', 1);
        $pdf->setY($pdf->getY() + 5);
        $pdf->setFont('Helvetica', '', 9);

        


        $pdf->SetFillColor(253, 233, 217);
        foreach($matriculas As $key => $matricula){
            $pdf->ln(5);
            $pdf->cell(10, 5, $key + 1, 1, 0, 'C', 0);
            $pdf->cell(100, 5, $matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
            foreach($criterios As $criterio){
                $indicadores = $criterio->getIndicadores();
                $width = $totalWidth / $totalCriterios;
                if(count($indicadores) > 0){
                    $indicadorGeneral = $indicadores[0];
                    foreach($indicadores As $indicador){
                        for($i = 0; $i < $indicador->cuadros; ++$i){
                            $nota = $matricula->getNotaDetalle($asignatura->id, $this->get->ciclo, $criterio->id, $indicador->id, $i);
                            if($nota >= $notaAprobatoria) $pdf->setTextColor(0, 0, 255);
                            if($nota < $notaAprobatoria) $pdf->setTextColor(255, 0, 0);

                            $pdf->cell($width / ($indicadorGeneral->cuadros + 1), 5, $nota, 1, 0, 'C', 0);
                            $pdf->setTextColor(0, 0, 0);
                        }
                    }

                    $nota = $matricula->getNota($asignatura->id, $criterio->id, $this->get->ciclo);
                    if($nota >= $notaAprobatoria) $pdf->setTextColor(0, 0, 255);
                    if($nota < $notaAprobatoria) $pdf->setTextColor(255, 0, 0);
                    $pdf->cell($width / ($indicadorGeneral->cuadros + 1), 5, $nota, 1, 0, 'C', 1);
                    $pdf->setTextColor(0, 0, 0);
                }else{

                    $nota = $matricula->getNota($asignatura->id, $criterio->id, $this->get->ciclo);
                    if($nota >= $notaAprobatoria) $pdf->setTextColor(0, 0, 255);
                    if($nota < $notaAprobatoria) $pdf->setTextColor(255, 0, 0);
                    $pdf->cell($width, 5, $nota, 1, 0, 'C', 0);
                    $pdf->setTextColor(0, 0, 0);
                }
            }

            if($asignatura->grupo->nivel->calificacionPorcentual() && $asignatura->curso->examenMensual()){
                $width = $totalWidth / $totalCriterios;
                for($i=1; $i <= 2; ++$i){
                    $nota = $matricula->getNotaExamenMensual($asignatura->id, $i, $this->get->ciclo);
                    if($nota >= $notaAprobatoria) $pdf->setTextColor(0, 0, 255);
                    if($nota < $notaAprobatoria) $pdf->setTextColor(255, 0, 0);
                    $pdf->cell($width / 3, 5, $nota, 1, 0, 'C', 0);
                    $pdf->setTextColor(0, 0, 0);
                }
                $notaExamenMensual = $matricula->getPromedioExamenMensual($asignatura, $this->get->ciclo, false);
                if($notaExamenMensual >= $notaAprobatoria) $pdf->setTextColor(0, 0, 255);
                if($notaExamenMensual < $notaAprobatoria) $pdf->setTextColor(255, 0, 0);
                $pdf->cell($width / 3, 5, $notaExamenMensual, 1, 0, 'C', 1);
                $pdf->setTextColor(0, 0, 0);
            }

            $promedio = $matricula->getPromedio($asignatura->id, $this->get->ciclo);
            if($promedio >= $notaAprobatoria) $pdf->setTextColor(0, 0, 255);
            if($promedio < $notaAprobatoria) $pdf->setTextColor(255, 0, 0);
            $pdf->setFont('Helvetica', 'b', 9);
            $pdf->cell(10, 5, $promedio, 1, 0, 'C', 1);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFont('Helvetica', '', 9);
        }

        
    }
}
