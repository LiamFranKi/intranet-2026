<?php
class ReportesApplication extends Core\Application{

	function index($r){
		$this->crystal->load('Form:*');
		$form = new Form(null, $this->COLEGIO->searchGrupoForm());
		$costos = $this->COLEGIO->getCostos();
		$personal = $this->COLEGIO->getPersonal();
		$this->render(array('FORM' => $form, 'costos' => $costos, 'personal' => $personal));
	}

	function setLogo($pdf){
		//$pdf->image('./Static/Image/Insignias/'.$this->COLEGIO->login_insignia, 10, 4, 23, 17);
	}

	function estadisticas_notas_bloques(){
		$bloque = Bloque::find($this->get->bloque_id);
		$cursos = $bloque->cursos;

		$grupos = $this->COLEGIO->getGrupos($this->get->anio);

		$this->render(array('bloque' => $bloque, 'cursos' => $cursos, 'grupos' => $grupos));
	}

	function estadisticas_notas_bloques_pdf_image($data, $timestamp, $rotation = 0){
		/*echo '<pre>';
		print_r($data['cursos']);
		echo '</pre>';*/

    	$this->crystal->load('Graph:pData.class');
    	$this->crystal->load('Graph:pDraw.class');
    	$this->crystal->load('Graph:pImage.class');

		$MyData = new pData();

		$MyData->addPoints($data[1],"Bloque 1");
		$MyData->addPoints($data[2],"Bloque 2");
		//$MyData->addPoints($d[2],"Bloque 2");
		//$MyData->setAxisName(0,"Hits");

		$MyData->addPoints($data['cursos'], "Cursos");

		
		$MyData->setSerieDescription("Cursos","Curso");
		$MyData->setAbscissa("Cursos");

		/* Create the pChart object */
		$myPicture = new pImage(800, 430, $MyData);

		/* Turn of Antialiasing */
		$myPicture->Antialias = FALSE;

		/* Add a border to the picture */
		$myPicture->drawGradientArea(0,0,800,430,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
		$myPicture->drawGradientArea(0,0,800,430,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20));
		$myPicture->drawRectangle(0,0,799,429,array("R"=>0,"G"=>0,"B"=>0));

		/* Set the default font */
		$myPicture->setFontProperties(array("FontName"=>"./Crystals/Graph/fonts/verdana.ttf","FontSize"=>10));

		/* Define the chart area */
		$myPicture->setGraphArea(60,40,750,300);

		/* Draw the scale */
		$scaleSettings = array(
			"GridR"=>200,
			"GridG"=>200,
			"GridB"=>200,
			"DrawSubTicks"=>TRUE,
			"CycleBackground"=>TRUE, 
			
			'Mode' => SCALE_MODE_START0,
			"InnerTickWidth"=>0,
			"OuterTickWidth"=>0
		);

		if($rotation > 0){
			$scaleSettings["LabelRotation"] = $rotation;
		}
		

		$myPicture->drawScale($scaleSettings);

		/* Write the chart legend */
		$myPicture->drawLegend(590,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

		/* Turn on shadow computing */ 
		$myPicture->setShadow(TRUE, array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

		/* Draw the chart */
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
		$settings = array("Gradient"=>TRUE,"GradientMode"=>GRADIENT_EFFECT_CAN, "Surrounding"=>  0, "DisplayValues"=>TRUE, "DisplayPos"=>LABEL_POS_INSIDE);
		$myPicture->drawBarChart($settings);

		/* Render the picture (choose the best way) */
		//$myPicture->autoOutput("pictures/example.drawBarChart.spacing.png");
		$myPicture->render("./Static/Temp/".$timestamp.".png");
		//$myPicture->render();
		//$myPicture->autoOutput();
    }

    function estadisticas_notas_bloques_pdf(){
    	$bloque = Bloque::find($this->get->bloque_id);
		$cursos = $bloque->cursos;

		$grupos = $this->COLEGIO->getGrupos($this->get->anio);

		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$graphGruposData = array('cursos' => array(), 1 => array(), 2 => array());

		foreach($grupos As $grupo){
			if($grupo->nivel_id != $bloque->nivel_id) continue;

			$bloqueCursosTotal = 0;
			$graphCursosData = array('cursos' => array(), 1 => array(), 2 => array());

			// GET LENGTH
			foreach($bloque->cursos As $bc){
				if($grupo->hasCurso($bc->curso->id)){
					++$bloqueCursosTotal;
					$graphCursosData['cursos'][] = $bc->curso->nombre;
				}
			}

			for($nro=1; $nro <= $bloque->total_notas; ++$nro){
				// START SORT DATA
				$matriculas = $grupo->getMatriculas();
				$sortedMatriculas = array();
				foreach($matriculas As $sortKeyMatricula => $sortMatricula){
					$total = 0;
					$totalCursos = 0;

					$sortedMatricula = array(
						'matricula' => $sortMatricula,
					);

					foreach($bloque->cursos As $bc){
						if($grupo->hasCurso($bc->curso->id)){
							$asignatura = $grupo->getAsignaturaByCurso($bc->curso_id);
							$notaExamenMensual = $sortMatricula->getNotaExamenMensual($asignatura->id, $nro, $this->get->ciclo);
							$total += $notaExamenMensual;
							$puntajeCurso = $bloque->puntaje($bc->curso_id, $grupo->id, $nro, $notaExamenMensual);
							++$totalCursos;
						}
					}

					$promedio = $totalCursos > 0 ? ($total / $totalCursos) : 0;

					$sortedMatricula['promedio'] = round($promedio);
					$sortedMatriculas[] = $sortedMatricula;

					// GUARDA EL PROMEDIO PARA EL FINAL DE AULA
					$promedioBloque = $bloque->puntaje(-1, $grupo->id, $nro, $promedio);
				}

				// END SORT DATA
				$pdf->addPage('P');
				$pdf->SetFont('helvetica', 'b', 13);
				$pdf->setFillColor(240, 240, 240);
				$this->setLogo($pdf);
				$pdf->cell(0, 5,'CUADRO DE DATOS - BLOQUE '.$nro, 0, 1,'R');
				$pdf->SetFont('helvetica', '', 9);
				$pdf->cell(0, 5, 'BLOQUE - '.$bloque->nombre, 0, 1,'R');
				$pdf->ln(15);
				$pdf->SetFont('helvetica', '', 10);

	
				$pdf->cell(40, 7, 'TUTOR', 1, 0, 'C', 1);
				$pdf->cell(0, 7, $grupo->tutor->getFullName(), 1, 0, 'C', 0, 0, 1);
				$pdf->ln(7);
				$pdf->cell(40, 7, 'AULA', 1, 0, 'C', 1);
				$pdf->cell(0, 7, $grupo->getNombreShort(), 1, 0, 'C', 0, 0, 1);
				$pdf->ln(10);

				$totalWidth = 200;
				$nameWidth = 70;
				$onlyNameWidth = 65;

				$pdf->cell($nameWidth, 7, '', 1, 0, 'C', 1);
				foreach($bloque->cursos As $bc){
					if($grupo->hasCurso($bc->curso->id)){
						$pdf->cell(($totalWidth - $nameWidth)/ ($bloqueCursosTotal + 1), 7, !empty($bc->curso->abreviatura) ? $bc->curso->abreviatura : substr($bc->curso->nombre, 0, 3), 1, 0, 'C', 1, 0, 1);
					}
				}
				$pdf->cell(($totalWidth - $nameWidth) / ($bloqueCursosTotal + 1), 7, 'PROM', 1, 0, 'C', 1);

				// ORDENA LAS MATRICULAS
				usort($sortedMatriculas, function($a, $b){
					if($a['promedio'] == $b['promedio']) return 0;

					if($a['promedio'] < $b['promedio']){
						return -1;
					}

					if($a['promedio'] > $b['promedio']){
						return 1;
					}
				});

				$sortedMatriculas = array_reverse($sortedMatriculas);

				foreach($sortedMatriculas As $keyMatricula => $sortedMatricula){
					$sortedMatricula = (object) $sortedMatricula;
					$matricula = $sortedMatricula->matricula;

					$pdf->ln(7);
					$pdf->cell($nameWidth - $onlyNameWidth, 7, $keyMatricula + 1, 1, 0, 'C', 0, 0, 1);
					$pdf->cell($onlyNameWidth, 7, $matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
					foreach($bloque->cursos As $bc){
						if($grupo->hasCurso($bc->curso->id)){
							$asignatura = $grupo->getAsignaturaByCurso($bc->curso_id);
							$notaExamenMensual = $matricula->getNotaExamenMensual($asignatura->id, $nro, $this->get->ciclo);
							$pdf->cell(($totalWidth - $nameWidth)/ ($bloqueCursosTotal + 1), 7, $notaExamenMensual, 1, 0, 'C', 0, 0, 1);
						}
					}
					$pdf->cell(($totalWidth - $nameWidth) / ($bloqueCursosTotal + 1), 7, $sortedMatricula->promedio, 1, 0, 'C', 0);
				}

				// PROMEDIO
				$pdf->ln(7);
				$pdf->cell($nameWidth, 7, 'PROMEDIO', 1, 0, 'C', 1, 0, 1);
				
				foreach($bloque->cursos As $bc){
					if($grupo->hasCurso($bc->curso->id)){
						$puntajeCurso = $bloque->puntaje($bc->curso_id, $grupo->id, $nro);
						$puntajeCurso = round(count($matriculas) > 0 ? ($puntajeCurso->total / count($matriculas)) : 0);
						$pdf->cell(($totalWidth - $nameWidth) / ($bloqueCursosTotal + 1), 7, $puntajeCurso, 1, 0, 'C', 1, 0, 1);
						
						$graphCursosData[$nro][] = $puntajeCurso;
						//$pdf->cell(($totalWidth - $nameWidth) / 2 / ($bloqueCursosTotal + 1), 5, print_r($puntajeCurso, true), 1, 0, 'C', 1, 0, 1);
					}
				}

				$promedioBloque = $bloque->puntaje(-1, $grupo->id, $nro);
				$promedioBloque = round(count($matriculas) > 0 ? ($promedioBloque->total / count($matriculas)) : 0);
				$pdf->cell(($totalWidth - $nameWidth) / ($bloqueCursosTotal + 1), 7, $promedioBloque, 1, 0, 'C', 1, 0, 1);
			}

			// GRAFICO

			$pdf->addPage('L');
			$pdf->SetFont('helvetica', 'b', 13);
			$this->setLogo($pdf);
			$pdf->cell(0, 5,'COMPARATIVO ENTRE BLOQUES 1 Y 2', 0, 1,'R');
			$pdf->SetFont('helvetica', '', 8);
			$pdf->cell(0, 5, $grupo->getNombreShort(), 0, 1,'R');
			$pdf->ln(15);
			$pdf->SetFont('helvetica', '', 8);

			//echo '<pre>';
			//print_r($graphCursosData);
			//echo '</pre>';
			$timestamp = getToken();
			$this->estadisticas_notas_bloques_pdf_image($graphCursosData, $timestamp, 45);
			$pdf->image('./Static/Temp/'.$timestamp.'.png', 10, 25, 275, 100);
			@unlink('./Static/Temp/'.$timestamp.'.png');

			
			$graphGruposData['cursos'][] = $grupo->getNombreShort3();

			$promedioBloque1 = $bloque->puntaje(-1, $grupo->id, 1);
			$promedioBloque1 = round(count($matriculas) > 0 ? ($promedioBloque1->total / count($matriculas)) : 0);
			$promedioBloque2 = $bloque->puntaje(-1, $grupo->id, 2);
			$promedioBloque2 = round(count($matriculas) > 0 ? ($promedioBloque2->total / count($matriculas)) : 0);

			$graphGruposData[1][] = $promedioBloque1;
			$graphGruposData[2][] = $promedioBloque2;
		}

		$pdf->addPage('L');
		$pdf->SetFont('helvetica', 'b', 13);
		$this->setLogo($pdf);
		$pdf->cell(0, 5,'COMPARATIVO ENTRE AULAS BLOQUES 1 Y 2', 0, 1,'R');
		$pdf->SetFont('helvetica', '', 8);
		$pdf->cell(0, 5, 'COMPARATIVO PROMEDIOS', 0, 1,'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 8);

		$timestamp = getToken();
		$this->estadisticas_notas_bloques_pdf_image($graphGruposData, $timestamp, 0);
		$pdf->image('./Static/Temp/'.$timestamp.'.png', 10, 25, 275, 100);
		@unlink('./Static/Temp/'.$timestamp.'.png');

		$pdf->output();
    }

	function estadisticas_notas_bloques_pdfx(){

		$bloque = Bloque::find($this->get->bloque_id);
		$cursos = $bloque->cursos;

		$grupos = $this->COLEGIO->getGrupos($this->get->anio);

		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$graphGruposData = array('cursos' => array(), 1 => array(), 2 => array());

		foreach($grupos As $grupo){
			if($grupo->nivel_id != $bloque->nivel_id) continue;
				
				$pdf->addPage('L');
				$pdf->SetFont('helvetica', 'b', 13);
				$pdf->setFillColor(240, 240, 240);
				$this->setLogo($pdf);
				$pdf->cell(0, 5,'CUADRO DE DATOS', 0, 1,'R');
				$pdf->SetFont('helvetica', '', 8);
				$pdf->cell(0, 5, $grupo->getNombreShort(), 0, 1,'R');
				$pdf->ln(15);
				$pdf->SetFont('helvetica', '', 8);

				$pdf->cell(30, 5, 'BLOQUE', 1, 0, 'C', 1);
				$pdf->cell(40, 5, $bloque->nombre, 1, 0, 'C', 0, 0, 1);
				$pdf->cell(30, 5, 'TUTOR', 1, 0, 'C', 1);
				$pdf->cell(85, 5, $grupo->tutor->getFullName(), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(30, 5, 'AULA', 1, 0, 'C', 1);
				$pdf->cell(70, 5, $grupo->getNombreShort(), 1, 0, 'C', 0, 0, 1);
				$pdf->ln(7);
				
				$totalWidth = 285;
				$nameWidth = 60;
				$onlyNameWidth = 55;

				$pdf->cell($nameWidth, 10, '', 1, 0, 'C', 1);
				for($nro=1; $nro <= 2; ++$nro){
					$pdf->cell(($totalWidth - $nameWidth) / 2, 5, $this->COLEGIO->roman($nro), 1, 0, 'C', 1);
				}
				$pdf->ln(5);
				$pdf->cell($nameWidth);
				$bloqueCursosTotal = 0;
				$graphCursosData = array('cursos' => array(), 1 => array(), 2 => array());

				// GET LENGTH
				foreach($bloque->cursos As $bc){
					if($grupo->hasCurso($bc->curso->id)){
						++$bloqueCursosTotal;
						$graphCursosData['cursos'][] = $bc->curso->nombre;
					}
				}

				// PRINT
				for($nro=1; $nro <= 2; ++$nro){
					foreach($bloque->cursos As $bc){
						if($grupo->hasCurso($bc->curso->id)){
							$pdf->cell(($totalWidth - $nameWidth)/ 2 / ($bloqueCursosTotal + 1), 5, !empty($bc->curso->abreviatura) ? $bc->curso->abreviatura : substr($bc->curso->nombre, 0, 3), 1, 0, 'C', 1, 0, 1);

						}
					}
					$pdf->cell(($totalWidth - $nameWidth) / 2 / ($bloqueCursosTotal + 1), 5, 'PROM', 1, 0, 'C', 1);
				}

				$matriculas = $grupo->getMatriculas();
				foreach($matriculas As $key_matricula => $matricula){
					$pdf->ln(5);
					$pdf->cell($nameWidth - $onlyNameWidth, 5, ($key_matricula + 1), 1, 0, 'C', 0, 0, 1);
					$pdf->cell($onlyNameWidth, 5, $matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
					for($nro=1; $nro <= 2; ++$nro){
						$total = 0;
						$totalCursos = 0;
						foreach($bloque->cursos As $bc){
							if($grupo->hasCurso($bc->curso->id)){
								$asignatura = $grupo->getAsignaturaByCurso($bc->curso_id);
								$notaExamenMensual = $matricula->getNotaExamenMensual($asignatura->id, $nro, $this->get->ciclo);
								$pdf->cell(($totalWidth - $nameWidth) / 2 / ($bloqueCursosTotal + 1), 5, $notaExamenMensual, 1, 0, 'C', 0, 0, 1);
								$total += $notaExamenMensual;
								$puntajeCurso = $bloque->puntaje($bc->curso_id, $grupo->id, $nro, $notaExamenMensual);

								++$totalCursos;
							}
						}

						$promedio = round($totalCursos > 0 ? ($total / $totalCursos) : 0);

						// GUARDA EL PROMEDIO PARA EL FINAL DE AULA
						$promedioBloque = $bloque->puntaje(-1, $grupo->id, $nro, $promedio);


						$pdf->cell(($totalWidth - $nameWidth) / 2 / ($bloqueCursosTotal + 1), 5, $promedio, 1, 0, 'C', 0, 0, 1);
					}
				}

				$pdf->ln(5);
				$pdf->cell($nameWidth, 5, 'PROMEDIO', 1, 0, 'C', 1, 0, 1);
				
				for($nro=1; $nro <= 2; ++$nro){
					foreach($bloque->cursos As $bc){
						if($grupo->hasCurso($bc->curso->id)){
							$puntajeCurso = $bloque->puntaje($bc->curso_id, $grupo->id, $nro);
							$puntajeCurso = round(count($matriculas) > 0 ? ($puntajeCurso->total / count($matriculas)) : 0);
							$pdf->cell(($totalWidth - $nameWidth) / 2 / ($bloqueCursosTotal + 1), 5, $puntajeCurso, 1, 0, 'C', 1, 0, 1);
							
							$graphCursosData[$nro][] = $puntajeCurso;
							//$pdf->cell(($totalWidth - $nameWidth) / 2 / ($bloqueCursosTotal + 1), 5, print_r($puntajeCurso, true), 1, 0, 'C', 1, 0, 1);
						}
					}

					$promedioBloque = $bloque->puntaje(-1, $grupo->id, $nro);
					$promedioBloque = round(count($matriculas) > 0 ? ($promedioBloque->total / count($matriculas)) : 0);
					$pdf->cell(($totalWidth - $nameWidth) / 2 / ($bloqueCursosTotal + 1), 5, $promedioBloque, 1, 0, 'C', 1, 0, 1);
				}

				
				// GRAFICO

				$pdf->addPage('L');
				$pdf->SetFont('helvetica', 'b', 13);
				$this->setLogo($pdf);
				$pdf->cell(0, 5,'COMPARATIVO ENTRE BLOQUES 1 Y 2', 0, 1,'R');
				$pdf->SetFont('helvetica', '', 8);
				$pdf->cell(0, 5, $grupo->getNombreShort(), 0, 1,'R');
				$pdf->ln(15);
				$pdf->SetFont('helvetica', '', 8);

				//echo '<pre>';
				//print_r($graphCursosData);
				//echo '</pre>';
				$timestamp = getToken();
				$this->estadisticas_notas_bloques_pdf_image($graphCursosData, $timestamp);
				$pdf->image('./Static/Temp/'.$timestamp.'.png', 10, 25, 275, 100);
				@unlink('./Static/Temp/'.$timestamp.'.png');

				
				$graphGruposData['cursos'][] = $grupo->getNombreShort2();

				$promedioBloque1 = $bloque->puntaje(-1, $grupo->id, 1);
				$promedioBloque1 = count($matriculas) > 0 ? ($promedioBloque1->total / count($matriculas)) : 0;
				$promedioBloque2 = $bloque->puntaje(-1, $grupo->id, 2);
				$promedioBloque2 = count($matriculas) > 0 ? ($promedioBloque2->total / count($matriculas)) : 0;

				$graphGruposData[1][] = $promedioBloque1;
				$graphGruposData[2][] = $promedioBloque2;
		}
		
		$pdf->addPage('L');
		$pdf->SetFont('helvetica', 'b', 13);
		$this->setLogo($pdf);
		$pdf->cell(0, 5,'COMPARATIVO ENTRE AULAS BLOQUES 1 Y 2', 0, 1,'R');
		$pdf->SetFont('helvetica', '', 8);
		$pdf->cell(0, 5, 'COMPARATIVO PROMEDIOS', 0, 1,'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 8);

		$timestamp = getToken();
		$this->estadisticas_notas_bloques_pdf_image($graphGruposData, $timestamp);
		$pdf->image('./Static/Temp/'.$timestamp.'.png', 10, 25, 275, 100);
		@unlink('./Static/Temp/'.$timestamp.'.png');
		
		
		$pdf->output();
	}


	function resumen_lista_alumnos(){
		$this->crystal->load('TCPDF');
		
		$grupos = $this->COLEGIO->getGrupos($this->get->anio);
		$niveles = $this->COLEGIO->getNiveles();

		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage();
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		$this->setLogo($pdf);
		$pdf->cell(0, 5,'Alumnos Matriculados '.$this->get->anio, 0, 1,'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 10);
		$totalGeneral = 0;
		foreach($niveles As $nivel){
			$pdf->SetFont('helvetica', 'b', 12);
			$pdf->cell(100, 10, strtoupper($nivel->nombre), 0, 1, 'L', 0);
			$pdf->SetFont('helvetica', '', 10);
			$pdf->cell(100, 5, 'GRUPO', 1, 0, 'C', 1);
			$pdf->cell(50, 5, 'ALUMNOS', 1, 0, 'C', 1);
			$pdf->ln(5);
			$total = 0;
			foreach($grupos As $grupo){
				if($grupo->nivel_id != $nivel->id) continue;
				$matriculas = count($grupo->getMatriculas());
				$pdf->cell(100, 5, $grupo->getNombre(), 1, 0, 'L', 0, 0, 1);
				$pdf->cell(50, 5, $matriculas, 1, 0, 'C', 0, 0, 1);
				$pdf->ln(5);
				$total += $matriculas;
			}
			$pdf->cell(70);
			$pdf->cell(30, 5, 'TOTAL', 1, 0, 'C', 1);
			$pdf->cell(50, 5, $total, 1, 0, 'C', 0, 0, 1);
			$pdf->ln(5);
			$totalGeneral += $total;
		}

		$pdf->ln(5);
		$pdf->cell(100, 5, 'TOTAL GENERAL', 1, 0, 'C', 1);
		$pdf->cell(50, 5, $totalGeneral, 1, 0, 'C', 0, 0, 1);

		$pdf->output();
	}
	
	function alumnos_costo(){
		$this->crystal->load('TCPDF');
		$costo = Costo::find($this->get->costo_id);
		$matriculas = Matricula::all(Array(
			'conditions' => Array('matriculas.colegio_id="'.$this->COLEGIO->id.'" AND costo_id="'.$this->get->costo_id.'" AND grupos.nivel_id="'.$this->get->nivel_id.'" AND grupos.anio="'.$this->get->anio.'"'),
			'order' => 'grupo_id ASC',
			'joins' => array('grupo')
		));
		
		$nivel = Nivel::find($this->get->nivel_id);
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage();
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		$this->setLogo($pdf);
		$pdf->cell(0, 5,'Lista de Alumnos '.$this->get->anio.' - '.$costo->descripcion, 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, 'Alumnos de nivel: '.$nivel->nombre,0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->cell(100, 5, 'APELLIDOS Y NOMBRES', 1, 0, 'C', 1);
		$pdf->cell(70, 5, 'GRADO - SECCIÓN', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'Nº DOCUMENTO', 1, 0, 'C', 1);
		foreach($matriculas As $matricula){
			$alumno = $matricula->alumno;
			$pdf->ln(5);
			$pdf->cell(100, 5, $alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
			$pdf->cell(70, 5, $matricula->grupo->getNombre(), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, $alumno->nro_documento, 1, 0, 'C', 0, 0, 1);
		}
		$pdf->output();
	}

	function alumnos_retirados(){
		$this->crystal->load('TCPDF');
		$matriculas = Matricula::all(Array(
			'conditions' => Array('matriculas.colegio_id="'.$this->COLEGIO->id.'" AND grupos.nivel_id="'.$this->get->nivel_id.'" AND grupos.anio="'.$this->get->anio.'" AND matriculas.estado="2"'),
			'order' => 'grupo_id ASC',
			'joins' => array('grupo')
		));
		$nivel = Nivel::find($this->get->nivel_id);
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0, 5,'Lista de alumnos retirados - '.$this->get->anio, 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, 'Alumnos de nivel: '.$nivel->nombre,0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->cell(100, 5, 'APELLIDOS Y NOMBRES', 1, 0, 'C', 1);
		$pdf->cell(70, 5, 'GRADO - SECCIÓN', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'Nº DOCUMENTO', 1, 0, 'C', 1, 0, 1);
		foreach($matriculas As $matricula){
			$alumno = $matricula->alumno;
			$pdf->ln(5);
			$pdf->cell(100, 5, $alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
			$pdf->cell(70, 5, $matricula->grupo->getNombre(), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, $alumno->nro_documento, 1, 0, 'C', 0, 0, 1);
		}
		$pdf->output();
	}
	
	/**
	 * Imprime la asistencia de una asignatura
	 * @param stdClass $r Almacena el id correspondiente
	 */ 
	function imprimir_asistencia_asignatura($r){
		$this->crystal->load('TCPDF');
		$asignatura = Asignatura::find($this->get->id);
		$fecha = $this->get->fecha;
		$matriculas = $asignatura->grupo->getMatriculas();
		
		
		$pdf = new TCPDF();
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
		$pdf->SetAutoPageBreak(TRUE, 5);
		$pdf->setLanguageArray($l);
		
		$pdf->AddPage();
		$this->setLogo($pdf);
		$pdf->setFillColor(234, 234, 234);
		$pdf->SetFont('helvetica', 'b', 13);
		//$pdf->image('./Static/Image/logo.jpg', 10, 2, 45, 17);
		$pdf->cell(0,10,'ASISTENCIA REGISTRADA',0,0,'R');
		$pdf->ln(15);
		$pdf->setFont('Helvetica','b',10);
		$pdf->cell(20,5,'Nº',1,0,'C', 1);
		$pdf->cell(120,5,'APELLIDOS Y NOMBRES',1,0,'C', 1);
		$pdf->cell(40,5,'REGISTRO',1,0,'C', 1);
		$pdf->ln(5);
		$pdf->setFont('Helvetica','',9);
		$i = 1;
		foreach($matriculas As $matricula){
			$pdf->setTextColor(0,0,0);
			$alumno = $matricula->alumno;
			$asistencia = $matricula->getAsistenciaAsignatura($asignatura->id, $fecha);
			$pdf->cell(20,5,$i,1,0,'C');
			$pdf->cell(120,5, $alumno->getFullName(),1,0,'L');
			if($asistencia->tipo == 'TARDANZA' | $asistencia->tipo == 'FALTA'){
				$pdf->setTextColor(255,0,0);
			}
			$pdf->cell(40,5,str_replace('_',' ', $asistencia->tipo),1,0,'C');
			$pdf->ln(5);
			$i++;
		}
		$pdf->setTextColor(0,0,0);
        
		$pdf->ln(5);
		$pdf->cell(30, 5, 'CURSO', 1, 0, 'L', 1);
		$pdf->cell(0, 5, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'), 1);
		$pdf->ln(5);
		$docente = $asignatura->personal;
		$pdf->cell(30, 5, 'DOCENTE', 1, 0, 'L', 1);
		$pdf->cell(0, 5, $docente->getFullName(), 1);
		$pdf->ln(5);
		$pdf->cell(30, 5, 'GRUPO', 1, 0, 'L', 1);
		$pdf->cell(0, 5,  $asignatura->grupo->getNombre(), 1);
		$pdf->ln(5);
		$pdf->cell(30, 5, 'FECHA', 1, 0, 'L', 1);
		$pdf->cell(0, 5, $asignatura->parseFecha($fecha), 1);
		$pdf->output('asistencia.pdf','I');
	}
	
	function imprimir_asistencia_asignatura_matricula($r){
		
		
		// fetch all data
		$matricula = Matricula::find($this->get->matricula_id);
		$asignatura = Asignatura::find($this->get->asignatura_id);
		$asistencias = Asignatura_Asistencia::find_all_by_matricula_id_and_asignatura_id($matricula->id, $asignatura->id, Array(
			'order' => 'fecha ASC'
		));
		
		$alumno = $matricula->alumno;

		$meses = $matricula->MESES;
		// end data
		
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->SetFillColor(229,229,229);
		$pdf->setMargins(5,10,5);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(5, 5, 5);
		
		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		//$pdf->image('./Static/Image/logo.jpg', 5, 5, 40, 15);
		$pdf->cell(0,10,'ASISTENCIA REGISTRADA EN EL PRESENTE AÑO ACADÉMICO - '.$matricula->grupo->anio,0,0,'R');
		$pdf->ln(10);
		$pdf->cell(0,10,mb_strtoupper($asignatura->curso->nombre, 'UTF-8'),0,0,'R');
		$pdf->SetFont('helvetica', '', 10);
		$pdf->Ln(15);
		
		$pdf->cell(40,7,'ALUMNO',1,0,'C',1);
		//$pdf->SetFillColor(255,255,255);
		$pdf->cell(0,7, $alumno->getFullName(),1,0,'C');
		$pdf->Ln(7);
		$pdf->cell(40,7,'NIVEL',1,0,'C',1);
		$pdf->cell(40,7, $matricula->grupo->nivel->nombre,1,0,'C',0);
		$pdf->cell(40,7,'GRADO / SECCIÓN',1,0,'C',1);
		$pdf->cell(20,7,$matricula->grupo->getGrado().' '.strtoupper($matricula->grupo->seccion),1,0,'C',0, 0, 1);
		$pdf->cell(30,7,'TURNO',1,0,'C',1);
		$pdf->cell(30,7,$matricula->grupo->turno->nombre,1,0,'C',0);
		$pdf->ln(10);
		$pdf->cell(50,7,'FECHA',1,0,'C',1);
		$pdf->cell(50,7,'REGISTRO',1,0,'C',1);
		$pdf->cell(50,7,'FECHA',1,0,'C',1);
		$pdf->cell(50,7,'REGISTRO',1,0,'C',1);
		$pdf->ln(7);
		$i = 1;
		foreach($asistencias As $asistencia){
			$pdf->setTextColor(0,0,0);
			$fecha = explode('-', $asistencia->fecha);
			$pdf->cell(50,7,$fecha[2].' - '.$meses[$fecha[1]-1],1,0,'C',1);
			if($asistencia->tipo == 'TARDANZA' | $asistencia->tipo == 'FALTA_INJUSTIFICADA'){
				$pdf->setTextColor(255,0,0);
			}
			$pdf->cell(50,7,str_replace('_',' ', $asistencia->tipo),1,0,'C',0);
			if($i%2 == 0){
				$pdf->ln(7);
			}
			$i++;
			
		}
		$pdf->output();
		
		return $pdf;
	}
	
	function imprimir_asistencia_matricula($r){

		// fetch all data
		$matricula = Matricula::find($this->params->id);

		$asistencias = Matricula_Asistencia::find_all_by_matricula_id($matricula->id, Array(
			'order' => 'fecha ASC'
		));
		
		$alumno = $matricula->alumno;
		
		//$config = $this->get('__config');
		$meses = $matricula->MESES;
		// end data
		
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->SetFillColor(229,229,229);
		$pdf->setMargins(5,10,5);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(5, 5, 5);
		
		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		//$pdf->image('./Static/Image/logo.jpg', 5, 5, 40, 15);
		$pdf->cell(200,10,'ASISTENCIA REGISTRADA EN EL PRESENTE AÑO ACADÉMICO - '.$matricula->grupo->anio,0,0,'R');
		$pdf->SetFont('helvetica', '', 10);
		$pdf->Ln(20);
		
		$pdf->cell(40,7,'ALUMNO',1,0,'C',1);
		//$pdf->SetFillColor(255,255,255);
		$pdf->cell(0,7, $alumno->getFullName(),1,0,'C');
		$pdf->Ln(7);
		$pdf->cell(40,7,'NIVEL',1,0,'C',1);
		$pdf->cell(40,7, $matricula->grupo->nivel->nombre,1,0,'C',0);
		$pdf->cell(40,7,'GRADO / SECCIÓN',1,0,'C',1);
		$pdf->cell(20,7,$matricula->grupo->getGrado().' '.strtoupper($matricula->grupo->seccion),1,0,'C',0, 0, 1);
		$pdf->cell(30,7,'TURNO',1,0,'C',1);
		$pdf->cell(30,7,$matricula->grupo->turno->nombre,1,0,'C',0);
		$pdf->ln(10);
		$pdf->cell(50,7,'FECHA',1,0,'C',1);
		$pdf->cell(50,7,'REGISTRO',1,0,'C',1);
		$pdf->cell(50,7,'FECHA',1,0,'C',1);
		$pdf->cell(50,7,'REGISTRO',1,0,'C',1);
		$pdf->ln(7);
		$i = 1;
		foreach($asistencias As $asistencia){
			$pdf->setTextColor(0,0,0);
			$fecha = explode('-', $asistencia->fecha);
			$pdf->cell(50,7,$fecha[2].' - '.$meses[$fecha[1]-1],1,0,'C',1);
			
			if($asistencia->tipo == 'TARDANZA_INJUSTIFICADA' | $asistencia->tipo == 'FALTA_INJUSTIFICADA'){
				$pdf->setTextColor(255,0,0);
			}
			$pdf->cell(50,7,str_replace('_',' ', $asistencia->tipo),1,0,'C',0);
			if($i%2 == 0){
				$pdf->ln(7);
			}
			$i++;
		}
        
		$pdf->output();
		
		return $pdf;
	}
	
	function imprimir_asistencia($r){
		$this->crystal->load('TCPDF');
		
		$grupo = Grupo::find($this->get->id);
		$fecha = $this->get->fecha;
		$matriculas = $grupo->getMatriculas();
		
		
		$pdf = new TCPDF();
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
		$pdf->SetAutoPageBreak(TRUE, 5);
		$pdf->setLanguageArray($l);
		
		$pdf->AddPage();
		$this->setLogo($pdf);
		$pdf->setFillColor(234, 234, 234);
		$pdf->SetFont('helvetica', 'b', 13);
		//$pdf->image('./Static/Image/logo.jpg', 10, 2, 45, 17);
		$pdf->cell(0,10,'ASISTENCIA REGISTRADA',0,0,'R');
		$pdf->ln(15);
		$pdf->setFont('Helvetica','b',10);
		$pdf->cell(20,5,'Nº',1,0,'C', 1);
		$pdf->cell(120,5,'APELLIDOS Y NOMBRES',1,0,'C', 1);
		$pdf->cell(40,5,'REGISTRO',1,0,'C', 1);
		$pdf->ln(5);
		$pdf->setFont('Helvetica','',9);
		$i = 1;
		foreach($matriculas As $matricula){
			$pdf->setTextColor(0,0,0);
			$alumno = $matricula->alumno;
			$asistencia = $matricula->getAsistencia($fecha);
			$pdf->cell(20,5,$i,1,0,'C');
			$pdf->cell(120,5, $alumno->getFullName(),1,0,'L');
			if($asistencia->tipo != 'PRESENTE'){
				$pdf->setTextColor(255,0,0);
			}
			$pdf->cell(40,5,str_replace('_',' ', $asistencia->tipo),1,0,'C', 0, 0, 1);
			$pdf->ln(5);
			$i++;
		}
		$pdf->setTextColor(0,0,0);
        
		$pdf->ln(5);
		
		$pdf->cell(30, 5, 'GRUPO', 1, 0, 'L', 1);
		$pdf->cell(0, 5,  $grupo->getNombre(), 1);
		$pdf->ln(5);
		$pdf->cell(30, 5, 'FECHA', 1, 0, 'L', 1);
		$pdf->cell(0, 5, $grupo->parseFecha($fecha), 1);
		$pdf->output('asistencia.pdf','I');
	}
	
	function imprimir_lista_alumnos(){
		if(!isset($this->get->grupo_id)){
			$grupo = $this->COLEGIO->getGrupo($this->get);
		}else{
			$grupo = Grupo::find($this->get->grupo_id);
		}
		
		if(!$grupo){
			$grupo = new Grupo((array) $this->get);
		}
		
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
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(220, 220, 220);
		$pdf->cell(0,10,'LISTA DE ALUMNOS - '.$grupo->getNombre(),0,0,'R');
		$pdf->ln(17);
		$pdf->setFont('Helvetica','b',10);
		$pdf->cell(7,5,'Nº',1,0,'C', 1);
		$pdf->cell(60,5,'Apellidos y Nombres',1,0,'C', 1);
		$pdf->cell(60,5,'Apoderado',1,0,'C', 1);
		$pdf->cell(17,5,'Sexo',1,0,'C', 1);
		$pdf->cell(17,5,'Nº Doc.',1,0,'C', 1);
		$pdf->cell(10,5,'Edad',1,0,'C', 1);
		$pdf->cell(35,5,'Teléfono',1,0,'C', 1);
		$pdf->cell(40,5,'Email',1,0,'C', 1);
		$pdf->cell(40,5,'Dirección',1,0,'C', 1);
	
		
		$pdf->ln(5);
		$pdf->setFont('Helvetica','',9);
		$i = 1;
		
		foreach($matriculas As $matricula){
			$alumno = $matricula->alumno;
			$domicilio = $alumno->getDomicilio();
			$apoderado = $alumno->getFirstApoderado();

			$pdf->cell(7,5,$i,1,0,'C');
			$pdf->cell(60,5, $alumno->getFullName(),1,0,'L', 0, '', 1);
			$pdf->cell(60,5, !is_null($apoderado) ? $apoderado->getFullName() : '',1,0,'L', 0, '', 1);
			$pdf->cell(17,5, $alumno->getSexo(),1,0,'C',  0, '', 1);
			$pdf->cell(17,5, $alumno->nro_documento,1,0,'C',  0, '', 1);
			$pdf->cell(10,5, $alumno->getEdad(),1,0,'C',  0, '', 1);
			
			$apoderado = $matricula->alumno->getFirstApoderado();
			$telefonos = array();
			if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
			if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

			$pdf->cell(35, 5, implode(' - ', $telefonos), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(40, 5, $apoderado->email, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(40, 5, $apoderado->direccion, 1, 0, 'C', 0, 0, 1);

			//$pdf->cell(20,5, $apoderado->telefono_fijo,1,0,'C',  0, '', 1);
			//$direccion = reset($domicilio);

			//$pdf->cell(50,5, !$direccion ? '' : $direccion['direccion'],1,0,'C', 0, '', 1);
			
			//$pdf->cell(42,5,$alumno->getFirstApoderado()->direccion,1,0,'C', 0, '', 1);
			$pdf->ln(5);
			$i++;
		}
		
			$pdf->output('asistencia.pdf','I');
	}
	
	function resumen_ingresos(){
		$this->crystal->load('TCPDF');
		set_time_limit(0);
		ini_set('display_errors', 'true');
        
        $fecha1 = date('Y-m-d', strtotime($this->get->fecha1));
        $fecha2 = date('Y-m-d', strtotime($this->get->fecha2));
        
		$ingresos = Pago::all(Array(
			'conditions' => 'pagos.colegio_id="'.$this->COLEGIO->id.'" AND pagos.estado="ACTIVO" AND DATE(fecha_hora) BETWEEN DATE("'.$fecha1.'") AND DATE("'.$fecha2.'")',
			'order' => 'fecha_hora ASC',
			'joins' => 'INNER JOIN matriculas ON matriculas.id = pagos.matricula_id
			INNER JOIN alumnos ON alumnos.id = matriculas.alumno_id'
		));
		
		
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0,5,'Resumen de Ingresos',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, $this->get->fecha1.($this->get->fecha1 != $this->get->fecha2 ? ' - '.$this->get->fecha2 : ''),0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->cell(0,5,'INGRESOS',0,0,'L');$pdf->Ln(10);
		$pdf->setFont('helvetica', '', 9);
		$pdf->cell(30, 5, 'Nº DE RECIBO', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'MONTO S/.', 1, 0, 'C', 1);
		$pdf->cell(42, 5, 'DESCRIPCIÓN', 1, 0, 'C', 1);
		$pdf->cell(73, 5, 'ALUMNO', 1, 0, 'C', 1);
		$pdf->cell(35, 5, 'FECHA - HORA', 1, 0, 'C', 1);
		$total_ingresos = 0;
		foreach($ingresos As $ingreso){
			$alumno = $ingreso->matricula->alumno;
			$pdf->ln(5);
			$pdf->cell(30, 5, $ingreso->nro_recibo, 1, 0, 'C', 0);
			$pdf->cell(20, 5, number_format($ingreso->monto + $ingreso->mora, 2), 1, 0, 'C', 0);
			$pdf->cell(42, 5, $ingreso->getTipoDescription(), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(73, 5, $alumno->getFullName(), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(35, 5, $ingreso->getFechaHora(), 1, 0, 'C', 0, 0, 1);
			$total_ingresos += ($ingreso->monto + $ingreso->mora); 
		}
		
		
		
		$pdf->Ln(10);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->cell(0,5,'BALANCE',0,0,'L');$pdf->Ln(10);
		$pdf->setFont('helvetica', '', 9);
		$pdf->cell(50, 5, 'TOTAL INGRESOS S/.', 1, 0, 'C', 1);


		$pdf->ln(5);
		$pdf->cell(50, 5, number_format($total_ingresos, 2), 1, 0, 'C', 0, 0);

		
		
		$pdf->output();
		
		//$this->render(Array('ingresos' => $ingresos, 'egresos' => $egresos, 'agendas' => $agendas));
		//echo date('h:i', strtotime('16:20'));
	}

	function estadisticas_pagos_image($data){
    	$this->crystal->load('Graph:pData.class');
    	$this->crystal->load('Graph:pDraw.class');
    	$this->crystal->load('Graph:pImage.class');

		$MyData = new pData();
		$total_neto = array();
		$total_pagado = array();
		$total_saldo = array();
		foreach($data As $d){
			$total_neto[] = $d['a'];
			$total_pagado[] = $d['b'];
			$total_saldo[] = $d['c'];
		}
		$MyData->addPoints($total_neto,"Total Neto");
		$MyData->addPoints($total_pagado,"Total Pagado");
		$MyData->addPoints($total_saldo,"Total Saldo");
		//$MyData->setAxisName(0,"Hits");

		$MyData->addPoints(array_slice($this->COLEGIO->MESES, 2), "Meses");
		
		$MyData->setSerieDescription("Meses","Month");
		$MyData->setAbscissa("Meses");

		/* Create the pChart object */
		$myPicture = new pImage(800,330,$MyData);

		/* Turn of Antialiasing */
		$myPicture->Antialias = FALSE;

		/* Add a border to the picture */
		$myPicture->drawGradientArea(0,0,800,330,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
		$myPicture->drawGradientArea(0,0,800,330,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20));
		$myPicture->drawRectangle(0,0,799,329,array("R"=>0,"G"=>0,"B"=>0));

		/* Set the default font */
		$myPicture->setFontProperties(array("FontName"=>"./Crystals/Graph/fonts/pf_arma_five.ttf","FontSize"=>7));

		/* Define the chart area */
		$myPicture->setGraphArea(60,40,750,300);

		/* Draw the scale */
		$scaleSettings = array(
			"GridR"=>200,
			"GridG"=>200,
			"GridB"=>200,
			"DrawSubTicks"=>TRUE,
			"CycleBackground"=>TRUE, 
			'Mode' => SCALE_MODE_START0);
		$myPicture->drawScale($scaleSettings);

		/* Write the chart legend */
		$myPicture->drawLegend(580,12,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

		/* Turn on shadow computing */ 
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

		/* Draw the chart */
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
		$settings = array("Surrounding"=>-30,"InnerSurrounding"=>30,"Interleave"=>0);
		$myPicture->drawBarChart($settings);

		/* Render the picture (choose the best way) */
		//$myPicture->autoOutput("pictures/example.drawBarChart.spacing.png");
		$myPicture->render("./Static/Temp/reporte_pagos.png");
    }

	function estadisticas_pagos(){
		$this->crystal->load('TCPDF');
		$matriculas = Matricula::all(array(
			'conditions' => 'matriculas.colegio_id="'.$this->COLEGIO->id.'" AND estado="0" AND grupos.anio="'.$this->get->anio.'"',
			'joins' => array('grupo')
		));

		$optionsPago = $this->COLEGIO->getOptionsNroPago();
		$data = array();
		
		foreach($optionsPago As $key => $option){
			$total_neto = 0;
			$total_pagado = 0;

			foreach($matriculas As $matricula){
				$total_neto += $matricula->costo->pension;
				$total_pagos = Pago::find(array(
					'select' => 'SUM(monto+mora) As monto_total',
					'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND matricula_id="'.$matricula->id.'" AND tipo="1" AND nro_pago="'.$key.'" AND YEAR(fecha_hora)="'.$this->get->anio.'" AND DATE(fecha_hora) <= DATE("'.date('Y-m-d', strtotime($this->get->fecha)).'")'
				));

				//if($total_pagos->monto_total >= $matricula->costo->pension){
					$total_pagado += $total_pagos->monto_total;
					//$total_pagado += $matricula->costo->pension;
				//}
			}

			$data[] = array(
				'mes' => $option,
				'a' => $total_neto,
				'b' => $total_pagado,
				'c' => ($total_pagado > $total_neto) ? 0 : $total_neto - $total_pagado
			);
		}
		
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage('L');
		$this->setLogo($pdf);
		$pdf->cell(0,5,'COMPORTAMIENTO DE COBRANZA DE MARZO A DICIEMBRE (al '.$this->get->fecha.')',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->ln(120);
		$this->estadisticas_pagos_image($data);

		$pdf->image('./Static/Temp/reporte_pagos.png', 10, 25, 275, 100);

		$pdf->setFillColor(240, 240, 240);
		$pdf->cell(5);
		$pdf->cell(50, 5, 'AL '.($this->get->fecha), 1, 0, 'C', 1);
		$pdf->cell(75, 5, 'S/. TOTAL NETO', 1, 0, 'C', 1);
		$pdf->cell(75, 5, 'S/. TOTAL PAGADO', 1, 0, 'C', 1);
		$pdf->cell(75, 5, 'S/. TOTAL SALDO', 1, 0, 'C', 1);
		foreach($data As $d){
			$pdf->ln(5);
			$pdf->cell(5);
			$pdf->cell(50, 5, $d['mes'], 1, 0, 'C', 0);
			$pdf->cell(75, 5, number_format($d['a'], 2), 1, 0, 'C', 0);
			$pdf->cell(75, 5, number_format($d['b'], 2), 1, 0, 'C', 0);
			$pdf->cell(75, 5, number_format($d['c'], 2), 1, 0, 'C', 0);
		}
		$pdf->output();
		//$this->render(array('data' => $data));
	}

	function alumnos_deudores(){
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage('L');
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0,5,'Alumnos Deudores',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$options = $this->COLEGIO->getOptionsNroPago();
		
		if($this->get->nro_pago >= 0){
			$pdf->cell(0,5, ($this->get->nro_pago == 0 ? 'Matrícula' : 'Mensualidad '.$options[$this->get->nro_pago]).' - '.$this->get->anio,0, 0, 'R');
		}else{
			$pdf->cell(0,5, 'Todo el año',0, 0, 'R');
		}
		
		$pdf->ln(15);

		$grupos = $this->COLEGIO->getGrupos($this->get->anio);

		/*
		$matriculas = Matricula::all(array(
			'conditions' => 'matriculas.colegio_id="'.$this->COLEGIO->id.'" AND estado="0" AND grupos.anio="'.$this->get->anio.'"',
			'joins' => array('grupo'),
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		));
		*/

		$pdf->setFont('helvetica', 'b', 9);
		//$pdf->cell(10, 5, 'Nº', 1, 0, 'C', 1);
		$pdf->cell(60, 5, 'ALUMNO', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'GRUPO', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'CONCEPTO', 1, 0, 'C', 1);
		$pdf->cell(25, 5, 'A PAGAR S/.', 1, 0, 'C', 1);
		$pdf->cell(25, 5, 'PAGADO S/.', 1, 0, 'C', 1);
		$pdf->cell(35, 5, 'TELF. APODERADO', 1, 0, 'C', 1);
		$pdf->cell(45, 5, 'EMAIL APODERADO', 1, 0, 'C', 1);
		$pdf->cell(45, 5, 'DIRECCIÓN', 1, 0, 'C', 1);

		$pdf->setFont('helvetica', '', 9);
		$total = 0;
		$numero = 0;
		foreach($grupos As $grupo){
			$matriculas = $grupo->getMatriculas();
			foreach($matriculas As $key_matricula => $matricula){
				for($x = 0; $x<=$this->COLEGIO->total_pensiones; $x++){
					if($this->get->nro_pago != -1 && $x != $this->get->nro_pago) continue;
					if($x == 0){
						$tipo_pago = 0;
						$nro_pago = 1;
					}else{
						$tipo_pago = 1;
						$nro_pago = $x;
					}

					
					$total_pagos = Pago::find(array(
						'select' => 'SUM(monto+mora) As monto_total',
						'conditions' => 'estado_pago="CANCELADO" AND estado = "ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND tipo="'.$tipo_pago.'" AND nro_pago="'.$nro_pago.'"'
					));
					
					//if($tipo_pago == 0 && $total_pagos->monto_total >= $matricula->costo->matricula) continue;
					//if($tipo_pago == 1 && $total_pagos->monto_total >= $matricula->costo->pension) continue;
					// PAGO ALMENOS 1 SOL
					if($total_pagos->monto_total > 0) continue;
					
					/*$pago = Pago::find(array(
						'conditions' => 'estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="'.$nro_pago.'" AND tipo="'.$tipo_pago.'"'
					));*/

					if($pago) continue;
					$monto = $tipo_pago == 0 ? $matricula->costo->matricula : $matricula->costo->pension;
					if($monto <= 0) continue; // becado

					$pdf->ln(5);
					//$pdf->cell(10, 5, $numero, 1, 0, 'C', 0, 0, 1);
					$pdf->cell(60, 5, $matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
					$pdf->cell(30, 5, $matricula->grupo->nivel->nombre.' - '.$matricula->grupo->getGrado().' '.$matricula->grupo->seccion, 1, 0, 'C', 0, 0, 1);

					if($tipo_pago == 0){
						$pdf->cell(20, 5, 'Matrícula', 1, 0, 'C', 0, 0, 1);
						$pdf->cell(25, 5, number_format($matricula->costo->matricula, 2), 1, 0, 'C', 0);
						
						$total += ($matricula->costo->matricula - $total_pagos->monto_total);
					}else{
						$pdf->cell(20, 5, $this->COLEGIO->getCicloPensionesSingle($x), 1, 0, 'C', 0, 0, 1);
						$pdf->cell(25, 5, number_format($matricula->costo->pension, 2), 1, 0, 'C', 0);
					
						$total += ($matricula->costo->pension - $total_pagos->monto_total);
					}

					
					++$numero;
					$pdf->cell(25, 5, number_format($total_pagos->monto_total, 2), 1, 0, 'C', 0);

					$apoderado = $matricula->alumno->getFirstApoderado();
					$telefonos = array();
					if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
					if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

					$pdf->cell(35, 5, implode(' - ', $telefonos), 1, 0, 'C', 0, 0, 1);
					$pdf->cell(45, 5, $apoderado->email, 1, 0, 'C', 0, 0, 1);
					$pdf->cell(45, 5, $apoderado->direccion, 1, 0, 'C', 0, 0, 1);
				}
			}
		}

		$pdf->ln(5);
		$pdf->cell(40);
		$pdf->cell(30, 5, "REGISTROS", 1, 0, 'C', 1);
		$pdf->cell(30, 5, $numero, 1, 0, 'C', 0);
		$pdf->cell(30, 5, "TOTAL", 1, 0, 'C', 1);
		$pdf->cell(30, 5, number_format($total, 2), 1, 0, 'C', 0);

		$pdf->output();
	}

	function alumnos_pagadores(){
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0,5,'Alumnos Pagadores',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$options = $this->COLEGIO->getOptionsNroPago();
		
		if($this->get->nro_pago >= 0){
			$pdf->cell(0,5, ($this->get->nro_pago == 0 ? 'Matrícula' : 'Mensualidad '.$options[$this->get->nro_pago]).' - '.$this->get->anio,0, 0, 'R');
		}else{
			$pdf->cell(0,5, 'Todo el año',0, 0, 'R');
		}
		
		$pdf->ln(15);

		

		$matriculas = Matricula::all(array(
			'conditions' => 'matriculas.colegio_id="'.$this->COLEGIO->id.'" AND estado="0" AND grupos.anio="'.$this->get->anio.'"',
			'joins' => array('grupo'),
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		));

		$pdf->setFont('helvetica', 'b', 9);
		$pdf->cell(60, 5, 'ALUMNO', 1, 0, 'C', 1);
		$pdf->cell(40, 5, 'GRUPO', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'CONCEPTO', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'A PAGAR S/.', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'PAGADO S/.', 1, 0, 'C', 1);

		$pdf->setFont('helvetica', '', 9);
		$total = 0;
		$totalWithMora = 0;
		$numero = 0;
		foreach($matriculas As $key_matricula => $matricula){
			for($x = 0; $x<=$this->COLEGIO->total_pensiones; $x++){
				if($this->get->nro_pago != -1 && $x != $this->get->nro_pago) continue;
				if($x == 0){
					$tipo_pago = 0;
					$nro_pago = 1;
				}else{
					$tipo_pago = 1;
					$nro_pago = $x;
				}

				$total_pagos = Pago::find(array(
					'select' => 'SUM(monto+mora) As monto_total',
					'conditions' => 'estado_pago="CANCELADO" AND estado = "ACTIVO" AND matricula_id="'.$matricula->id.'" AND tipo="'.$tipo_pago.'" AND nro_pago="'.$nro_pago.'"'
				));

				if($tipo_pago == 0 && $total_pagos->monto_total < $matricula->costo->matricula) continue;
				if($tipo_pago == 1 && $total_pagos->monto_total < $matricula->costo->pension) continue;

				$pdf->ln(5);
				
				$pdf->cell(60, 5, $matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
				$pdf->cell(40, 5, $matricula->grupo->nivel->nombre.' - '.$matricula->grupo->getGrado().' '.$matricula->grupo->seccion, 1, 0, 'C', 0, 0, 1);

				if($tipo_pago == 0){
					$pdf->cell(30, 5, 'Matrícula', 1, 0, 'C', 0, 0, 1);
					$pdf->cell(30, 5, number_format($matricula->costo->matricula, 2), 1, 0, 'C', 0);
					$total += $matricula->costo->matricula;
				}else{
					$pdf->cell(30, 5, $this->COLEGIO->getCicloPensionesSingle($x), 1, 0, 'C', 0, 0, 1);
					$pdf->cell(30, 5, number_format($matricula->costo->pension, 2), 1, 0, 'C', 0);
					$total += $matricula->costo->pension;
				}

				$totalWithMora += $total_pagos->monto_total;

				++$numero;
				$pdf->cell(30, 5, number_format($total_pagos->monto_total, 2), 1, 0, 'C', 0);
			}
		}

		$pdf->ln(5);
		$pdf->cell(40);
		$pdf->cell(30, 5, "REGISTROS", 1, 0, 'C', 1);
		$pdf->cell(30, 5, $numero, 1, 0, 'C', 0);
		$pdf->cell(30, 5, "TOTAL", 1, 0, 'C', 1);
		$pdf->cell(30, 5, number_format($total, 2), 1, 0, 'C', 0);
		$pdf->cell(30, 5, number_format($totalWithMora, 2), 1, 0, 'C', 0);

		$pdf->output();
	}

	function lista_profesores_aula(){
		
		$grupo = $this->COLEGIO->getGrupo($this->get);
		if(!$grupo){
			$grupo = new Grupo((array) $this->get);
		}
		
		$asignaturas = $grupo->getAsignaturas();
		
		$nivel = $grupo->nivel;
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0, 5,'Lista de Profesores', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, 'Profesores del: '.$grupo->getGrado().' '.$this->get->seccion.' - '.$nivel->nombre,0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->cell(100, 5, 'NOMBRES Y APELLIDOS PROFESOR', 1, 0, 'C', 1);
		$pdf->cell(100, 5, 'CURSO', 1, 0, 'C', 1);
		//$pdf->cell(30, 5, 'DNI', 1, 0, 'C', 1);
		foreach($asignaturas As $asignatura){
			$personal = $asignatura->personal;
			$pdf->ln(5);
			$pdf->cell(100, 5, $personal->getFullName(), 1, 0, 'L', 0);
			$pdf->cell(100, 5, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'), 1, 0, 'L');

		}
		$pdf->output();
	}
	
	function personal_administrativo(){
		$personal = Personal::all(Array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'"',
			'order' => $this->get->order.' '.$this->get->direction
		));
		
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage('L');
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0, 5,'Personal Administrativo', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 9);
		//$pdf->cell(0,5, 'Alumnos de: '.$this->get->grado.'º '.$this->get->seccion.' - '.$nivel->nombre,0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(70, 5, 'APELLIDOS Y NOMBRES', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'CARGO', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'FECHA DE NACIMIENTO', 1, 0, 'C', 1, 0, 1);
		$pdf->cell(30, 5, 'TELÉFONO', 1, 0, 'C', 1);
		$pdf->cell(55, 5, 'DIRECCIÓN', 1, 0, 'C', 1);
		$pdf->cell(40, 5, 'EMAIL', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'FECHA INGRESO', 1, 0, 'C', 1);
		//$pdf->cell(100, 5, 'PROFESIÓN', 1, 0, 'C', 1);
		//$pdf->cell(30, 5, 'DNI', 1, 0, 'C', 1);

		foreach($personal As $persona){
			$pdf->ln(5);
			$telefonos = array();
			if(!empty($persona->telefono_fijo)) $telefonos[] = $persona->telefono_fijo;
			if(!empty($persona->telefono_celular)) $telefonos[] = $persona->telefono_celular;

			$pdf->cell(70, 5, $persona->getFullName(), 1, 0, 'L', 0, 0, 1);
			$pdf->cell(30, 5, $persona->cargo, 1, 0, 'C', 0, null, 1);
			if($persona->fecha_nacimiento == '1969-12-31') $persona->fecha_nacimiento = '';
			$pdf->cell(30, 5, $persona->fecha_nacimiento, 1, 0, 'C', 0, null, 1);
			$pdf->cell(30, 5, implode(' - ', $telefonos), 1, 0, 'L', 0, null, 1);
			$pdf->cell(55, 5, $persona->direccion, 1, 0, 'L', 0, null, 1);
			$pdf->cell(40, 5, $persona->email, 1, 0, 'L', 0, null, 1);
			$pdf->cell(30, 5, $persona->fecha_ingreso, 1, 0, 'C', 0, null, 1);
			//$pdf->cell(100, 5, $persona->profesion, 1, 0, 'L', 0, null, 1);
			//$pdf->cell(50, 5, $matricula->grado.($this->get->nivel == 1 ? ' años' : 'º').' - '.strtoupper($matricula->seccion), 1, 0, 'C', 0);
			//$pdf->cell(30, 5, $alumno->dni, 1, 0, 'C', 0);
		}
		$pdf->output();
	}
	
	function asistencia_mensual(){
		
		$grupo = $this->COLEGIO->getGrupo($this->get);
		if(!$grupo){
			$data = (array) $this->get;
			unset($data['mes']);
			$grupo = new Grupo($data);
		}
		$matriculas = $grupo->getMatriculas();
		
		$start_day = '01-'.$this->get->mes.'-'.$this->get->anio;
		$total_days = date('t', strtotime($start_day));
		//----------------
		
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage('L');
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0, 5,'REPORTE DE ASISTENCIA - '.$grupo->getGrado().' '.$this->get->seccion, 0, 1,'R');
		$pdf->cell(0, 5,'Mes de '.ucwords(strtolower($this->COLEGIO->MESES[$this->get->mes-1])).' - '.$this->get->anio, 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		//$pdf->cell(0,5, 'Alumnos de: '.$this->get->grado.'º '.$this->get->seccion.' - '.$nivel->nombre,0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->cell(85, 10, 'APELLIDOS Y NOMBRES', 1, 0, 'C', 1);
		
		setlocale(LC_TIME, 'spanish');
		$pdf->SetFont('helvetica', '', 8);
		for($i=1; $i<= $total_days; $i++){
			$current = $i.'-'.$this->get->mes.'-'.$this->get->anio;
			if(date('N', strtotime($current)) < 6){
				$pdf->cell(8.9, 5, mb_strtoupper(utf8_encode(strftime('%a', strtotime($current))), 'UTF-8'), 1, 0, 'C', 1);	
			}
		}
		$pdf->ln(5);
		$pdf->cell(85);
		for($i=1; $i<= $total_days; $i++){
			$current = $i.'-'.$this->get->mes.'-'.$this->get->anio;
			if(date('N', strtotime($current)) < 6){
				$pdf->cell(8.9, 5, $i, 1, 0, 'C', 1);	
			}
		}
		//$pdf->SetFont('helvetica', '', 10);
		//$pdf->cell(30, 5, 'DNI', 1, 0, 'C', 1);
		$x = array('PRESENTE' => 'P', 'FALTA_JUSTIFICADA' => 'F', 'FALTA_INJUSTIFICADA' => 'FI', 'TARDANZA_JUSTIFICADA' => 'T', 'TARDANZA_INJUSTIFICADA' => 'TI');
		foreach($matriculas As $matricula){
			$alumno = $matricula->alumno;
			$pdf->Ln(5);
			$pdf->cell(85, 5, $alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
			for($i=1; $i<= $total_days; $i++){
				$current = $i.'-'.$this->get->mes.'-'.$this->get->anio;
				if(date('N', strtotime($current)) < 6){
					$asistencia = Matricula_Asistencia::find(array(
						'conditions' => array('matricula_id="'.$matricula->id.'" AND DATE(fecha)=DATE("'.date('Y-m-d', strtotime($current)).'")')
					));
		
					$tipo = $x[$asistencia->tipo];
					if(!$asistencia) $tipo = '';
					if($tipo == 'P') $pdf->setTextColor(0, 0, 255);
					if($tipo == 'T' || $tipo == 'TI') $pdf->setTextColor(255, 128, 0);
					if($tipo == 'F' | $tipo == 'FI') $pdf->setTextColor(255, 0, 0);
					$pdf->cell(8.9, 5, $tipo, 1, 0, 'C', 0);	
					
					$pdf->setTextColor(0, 0, 0);
				}
			}
		}
		
		$pdf->output();
	}

	
	function imprimir_syllabus($r){
		$asignatura = Asignatura::find($r->id);
		$fechas = $asignatura->getHorarioFechas();

		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0, 5,'SYLLABUS - AVANCE ACADÉMICO', 0, 1,'R');
		$pdf->SetFont('helvetica', '', 12);
		$pdf->cell(0, 5, $asignatura->curso->nombre.' - '.$asignatura->grupo->getNombreShort2(), 0, 1,'R');
		
		//$pdf->cell(0,5, 'Alumnos de: '.$this->get->grado.'º '.$this->get->seccion.' - '.$nivel->nombre,0, 0, 'R');
		$pdf->ln(15);
		
		foreach($fechas As $monthKey => $data){
			$pdf->SetFont('helvetica', 'b', 11);
			$pdf->cell(85, 5, mb_strtoupper($this->COLEGIO->MESES[$monthKey], 'utf-8'), 0, 0, 'L', 0);
			$pdf->SetFont('helvetica', '', 10);
			$pdf->ln(7);

			$pdf->cell(60, 5, 'FECHA', 1, 0, 'C', 1);
			$pdf->cell(0, 5, 'TEMA', 1, 0, 'C', 1);
			
			foreach($data As $fecha){
				$pdf->ln(5);
				$pdf->cell(60, 5, $fecha['weekDay'].' - '.$this->COLEGIO->parseFecha($fecha['fecha']), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(0, 5, $fecha['tema']->tema, 1, 0, 'L', 0, 0, 1);	
			}

			$pdf->ln(10);

		}
	
		
		$pdf->output();
	}

	function nomina_matricula(){
		$grupo = $this->COLEGIO->getGrupo($this->get);
		if(!$grupo) $grupo = new Grupo((array) $this->get);
        // DATA
        $nivel = $grupo->nivel;
        $turno = $grupo->turno;
        // DATA
        $this->crystal->load('PHPExcel:PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/nomina_matricula.xls');
		$s1 = $excel->getSheet(0);
        $s2 = $excel->getSheet(1);
        
        $currentCol = 4;
        for($i=0;$i<6;$i++){
            $s1->setCellValue(getNameFromNumber($currentCol).'12', $this->COLEGIO->ugel_codigo[$i]);
            $currentCol += 2;
        }
        $s1->setCellValue('D13', $this->COLEGIO->ugel_nombre);
        $s1->setCellValue('AT3', $this->get->anio);
        
        $s1->setCellValue('V11', $this->COLEGIO->titulo_intranet);
        $s1->setCellValue('V13', $this->COLEGIO->resolucion_creacion);
        
        $currentCol = 22;
        for($i=0;$i<7;$i++){
            $s1->setCellValue(getNameFromNumber($currentCol).'12', $this->COLEGIO->codigo_modular[$i]);
            ++$currentCol;
        }
        $s1->setCellValue('V15', $nivel->abreviatura);
        $s1->setCellValue('AC15', $this->get->grado);
        $s1->setCellValue('AI15', $this->get->seccion == 'UNICA' ? '-' : $this->get->seccion);
        $s1->setCellValue('AO15', $turno->abreviatura);
        
        $s1->setCellValue('AR11', '01/03/'.$this->get->anio);
        $s1->setCellValue('AX11', '28/12/'.$this->get->anio);
        
        $s1->setCellValue('BE11', $this->COLEGIO->departamento->nombre);
        $s1->setCellValue('BE12', $this->COLEGIO->provincia->nombre);
        $s1->setCellValue('BE13', $this->COLEGIO->distrito->nombre);
        $s1->setCellValue('BB16', $this->COLEGIO->centro_poblado);
        
        
        
		$matriculas = $grupo->getMatriculas();
        
        $currentRow = 23;
        $s = $s1;
        foreach($matriculas As $matricula){
            $alumno = $matricula->alumno;
            //for($i=1;$i<=50;$i++){
            $currentCol = 2;
            for($x=0;$x<14;$x++){
                $s->setCellValue(getNameFromNumber($currentCol).$currentRow, $alumno->codigo[$x]);
                ++$currentCol;
            }
            $s->setCellValue('P'.$currentRow, $alumno->getFullName());
            $s->setCellValue('AM'.$currentRow, date('d', strtotime($alumno->fecha_nacimiento)));
            $s->setCellValue('AN'.$currentRow, date('m', strtotime($alumno->fecha_nacimiento)));
            $s->setCellValue('AO'.$currentRow, date('Y', strtotime($alumno->fecha_nacimiento)));
            $s->setCellValue('AP'.$currentRow, $alumno->sexo == 0 ? 'H' : 'M');
            $s->setCellValue('AR'.$currentRow, $alumno->pais_nacimiento->nombre[0]);
            $s->setCellValue('AS'.$currentRow, $alumno->getApoderadoByParentesco('PADRE')->vive);
            $s->setCellValue('AT'.$currentRow, $alumno->getApoderadoByParentesco('MADRE')->vive);
            $segunda_lengua = $alumno->getSegundaLengua();
            $s->setCellValue('AU'.$currentRow, $segunda_lengua[0]);
            $lengua_materna = $alumno->getLenguaMaterna();
            $s->setCellValue('AV'.$currentRow, $lengua_materna[0]);
            $discapacidad = $alumno->getDiscapacidad();
            $s->setCellValue('BA'.$currentRow, $alumno->discapacidad);
            
            $currentCol = 54;
            for($i=0;$i<6;$i++){
            	$s->setCellValue(numToChar($currentCol).$currentRow, $matricula->grupo->nivel->codigo_modular[$i]);
            	++$currentCol;
            }

            if($currentRow == 43){
                $currentRow = 13;
                $s = $s2;
            }else{
                ++$currentRow;
            }
            //}
        }
        
        writeExcel($excel);

    }
	
	function ficha_matricula($r){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/ficha_matricula.xlsx');
		$s1 = $excel->getSheet(0);
		 
		$alumno = Alumno::find($r->id);
		$currentCol = 45;
		for($i=0;$i<=13;$i++){
			$s1->setCellValue(getNameFromNumber($currentCol).'10', $alumno->codigo[$i]);
			++$currentCol;
		}
		
		//$s1->setCellValue('AS10', getNameFromNumber($currentCol));
		
		$s1->setCellValue('A13', $alumno->apellido_paterno);
		$s1->setCellValue('I13', $alumno->apellido_materno);
		$s1->setCellValue('R13', $alumno->nombres);
		 
		 if($alumno->sexo == 'MASCULINO'){
			 $s1->setCellValue('AF13', 'X');
		 }else{
			 $s1->setCellValue('AD13', 'X');
		 }
		 
		 $s1->setCellValue('AG13', $alumno->getEstadoCivil());
		 
		 // fecha nacimiento
		 $s1->setCellValue('G17', date('d', strtotime($alumno->fecha_nacimiento)));
		 $s1->setCellValue('I17', date('m', strtotime($alumno->fecha_nacimiento)));
		 $s1->setCellValue('L17', date('Y', strtotime($alumno->fecha_nacimiento)));
		 
		 $s1->setCellValue('G18', $alumno->pais_nacimiento->nombre);
		 
		 $s1->setCellValue('G19', $alumno->departamento_nacimiento->nombre);
		 $s1->setCellValue('G20', $alumno->provincia_nacimiento->nombre);
		 $s1->setCellValue('G21', $alumno->distrito_nacimiento->nombre);
		 
		 $s1->setCellValue('U16', $alumno->getLenguaMaterna());
		 $s1->setCellValue('U18', $alumno->getSegundaLengua());
		 
		 $s1->setCellValue('U19', $alumno->nro_hermanos);
		 $s1->setCellValue('AC19', $alumno->lugar_hermanos);
		 
		 $s1->setCellValue('U20', $alumno->getReligion());
		 
		 switch($alumno->discapacidad){
			 case 'DI':
				$s1->setCellValue('V21', 'X');
			 break;
			 case 'DA':
				$s1->setCellValue('Y21', 'X');
			 break;
			 case 'DV':
				$s1->setCellValue('AA21', 'X');
			 break;
			 case 'DM':
				$s1->setCellValue('AC21', 'X');
			 break;
			 case 'OT':
				$s1->setCellValue('AE21', 'X');
			 break;
		 }
		 
		 $s1->setCellValue('U22', $alumno->nro_documento);
		 
		 if($alumno->estado_nacimiento == 0){
			 $s1->setCellValue('AL17', 'X');
		 }else{
			 $s1->setCellValue('AR17', 'X');
		 }
		 
		 $s1->setCellValue('AG19', $alumno->observaciones_nacimiento);
		 
		 $currentRow = 17;
		 $actividades_nacimiento = $alumno->getActividadesNacimiento();
		 foreach($alumno->ACTIVIDADES_NACIMIENTO As $key => $val){
			 $s1->setCellValue('BE'.$currentRow, $actividades_nacimiento[$key]);
			 ++$currentRow;
		 }
		 
		 // PESO TALLA
		 $currentRow = 28;
		 
		 foreach($alumno->getControlesPesoTalla() As $control){
			 if(empty($control['peso']) || empty($control['talla'])) continue;
			 
			 $s1->setCellValue('A'.$currentRow, date('d', strtotime($control['fecha'])));
			 $s1->setCellValue('C'.$currentRow, date('m', strtotime($control['fecha'])));
			 $s1->setCellValue('E'.$currentRow, date('Y', strtotime($control['fecha'])));
			 $s1->setCellValue('G'.$currentRow, $control['peso']);
			 $s1->setCellValue('I'.$currentRow, $control['talla']);
			 $s1->setCellValue('K'.$currentRow, $control['observaciones']);
			 ++$currentRow;
		 }
		 
		 $currentRow = 28;
		 foreach($alumno->getOtrosControles() As $control){
			 if(empty($control['tipo_control']) || empty($control['resultado'])) continue;
			 
			 $s1->setCellValue('W'.$currentRow, date('d', strtotime($control['fecha'])));
			 $s1->setCellValue('Y'.$currentRow, date('m', strtotime($control['fecha'])));
			 $s1->setCellValue('AA'.$currentRow, date('Y', strtotime($control['fecha'])));
			 $s1->setCellValue('AC'.$currentRow, $control['tipo_control']);
			 $s1->setCellValue('AF'.$currentRow, $control['resultado']);
			 ++$currentRow;
		 }
		 
		 $currentRow = 28;
		 foreach($alumno->getEnfermedadesSufridas() As $enfermedad){
			 $s1->setCellValue('AJ'.$currentRow, $enfermedad['edad']);
			 $s1->setCellValue('AL'.$currentRow, $enfermedad['enfermedad']);
			 ++$currentRow;
		 }
		 
		 $currentRow = 28;
		 foreach($alumno->getVacunas() As $vacuna){
			 $s1->setCellValue('AQ'.$currentRow, $vacuna['edad']);
			 $s1->setCellValue('AS'.$currentRow, $vacuna['vacuna']);
			 ++$currentRow;
		 }
		 
		 $s1->setCellValue('AW26', $alumno->alergias);
		 $s1->setCellValue('AW30', $alumno->experiencias_traumaticas);
		 $s1->setCellValue('BB32', $alumno->tipo_sangre);
		 
		 $currentRow = 36;
		 foreach($alumno->getDomicilio() As $domicilio){
			  $s1->setCellValue('A'.$currentRow, $domicilio['anio']);
			  $s1->setCellValue('C'.$currentRow, $domicilio['direccion']);
			  $s1->setCellValue('O'.$currentRow, $domicilio['lugar']);
			  $s1->setCellValue('S'.$currentRow, $domicilio['departamento']);
			  $s1->setCellValue('W'.$currentRow, $domicilio['provincia']);
			  $s1->setCellValue('AB'.$currentRow, $domicilio['distrito']);
			  $s1->setCellValue('AG'.$currentRow, $domicilio['telefono']);
			  ++$currentRow;
		 }
		 // PADRE
		 $padre = $alumno->getApoderadoByParentesco(0);
		 if(isset($padre)){
			 $s1->setCellValue('AQ36', $padre->apellido_paterno);
			 $s1->setCellValue('AQ37', $padre->apellido_materno);
			 $s1->setCellValue('AQ38', $padre->nombres);
			 $s1->setCellValue('AQ41', date('d', strtotime($padre->fecha_nacimiento)));
			 $s1->setCellValue('AS41', date('m', strtotime($padre->fecha_nacimiento)));
			 $s1->setCellValue('AV41', date('Y', strtotime($padre->fecha_nacimiento)));
			 $s1->setCellValue('AQ42', $padre->getGradoInstruccion());
			 $s1->setCellValue('AQ43', $padre->ocupacion);
			 if($padre->vive == 'SI'){
				 $s1->setCellValue('AS39', 'X');
			 }else{
				 $s1->setCellValue('AW39', 'X');
			 }
			 
			 if($padre->vive_con_estudiante == 'SI'){
				 $s1->setCellValue('AS44', 'X');
			 }else{
				 $s1->setCellValue('AW44', 'X');
			 }
		 }
		 // MADRE
		 $madre = $alumno->getApoderadoByParentesco(1);
		 if(isset($madre)){
			 $s1->setCellValue('AY36', $madre->apellido_paterno);
			 $s1->setCellValue('AY37', $madre->apellido_materno);
			 $s1->setCellValue('AY38', $madre->nombres);
			 $s1->setCellValue('AY41', date('d', strtotime($madre->fecha_nacimiento)));
			 $s1->setCellValue('BA41', date('m', strtotime($madre->fecha_nacimiento)));
			 $s1->setCellValue('BD41', date('Y', strtotime($madre->fecha_nacimiento)));
			 $s1->setCellValue('AY42', $madre->getGradoInstruccion());
			 $s1->setCellValue('AY43', $madre->ocupacion);
			 if($madre->vive == 'SI'){
				 $s1->setCellValue('BA39', 'X');
			 }else{
				 $s1->setCellValue('BE39', 'X');
			 }
			 
			 if($madre->vive_con_estudiante == 'SI'){
				 $s1->setCellValue('BA44', 'X');
			 }else{
				 $s1->setCellValue('BE44', 'X');
			 }
		 }
		 // TRABAJOS
		 
		 $currentRow = 50;
		 foreach($alumno->getTrabajos() As $trabajo){
			 $s1->setCellValue('A'.$currentRow, $trabajo['anio']);
			 $s1->setCellValue('C'.$currentRow, $trabajo['edad']);
			 $s1->setCellValue('E'.$currentRow, $trabajo['descripcion']);
			 $s1->setCellValue('Y'.$currentRow, $trabajo['horas']);
			 ++$currentRow;
		 }
		 
		 writeExcel($excel);
	}
	
	function imprimir_matricula($r){
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$matricula = Matricula::find($r->id);
		$alumno = $matricula->alumno;
        $apoderado = $alumno->getFirstApoderado();
		if(!$apoderado) $apoderado = new Apoderado();
		
        $apoderados = Apoderado::all(array(
            'select' => 'apoderados.*',
            'joins' => array('familias'),
            'conditions' => 'familias.alumno_id="'.$alumno->id.'"'
        ));
        
		$foto = '.'.$alumno->getFoto();
		//$ubicacion = ubicacion($alumno->lugar_nacimiento);

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Zarkiel');
		$pdf->SetTitle('Ficha de Matrícula');
		$pdf->SetSubject('Ficha');

		//set margins
		$pdf->SetMargins(5, 5, PDF_MARGIN_RIGHT);

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 5);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$pdf->setLanguageArray($l);
		
		$pdf->AddPage('P', 'A5');
		$pdf->SetFont('helvetica', 'b', 16);
		$pdf->ln(10);
		$pdf->cell(50);
		//$pdf->image('./Static/Image/logo.jpg', 20, 5, 20, 22);
		$pdf->cell(0,0,'FICHA DE MATRÍCULA');

		$pdf->image($foto,110,25,30,35);
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(0,7,'1.- MATRICULA EN:');
		$pdf->ln(7);
		$pdf->cell(50,5, strtoupper($matricula->grupo->nivel->nombre).' - '.$matricula->grupo->getGrado().' '.strtoupper($matricula->grupo->seccion),1,0,'C');
		$pdf->cell(10);
		$pdf->cell(30,5,$alumno->nro_documento,1,0,'C');
		
		$pdf->ln(5);
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(50,5,'NIVEL - GRADO - SECCIÓN',1,0,'C');
		
		$pdf->cell(10);
		$pdf->cell(30,5,'Nº Documento',1,0,'C');
		
		$pdf->ln(10);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(50,5,strtoupper($matricula->grupo->turno->nombre),1,0,'C'); // turno
		$pdf->ln(5);
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(50,5,'TURNO',1,0,'C');
		
		$pdf->ln(7);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(0,7,'2.- DATOS PERSONALES DEL ESTUDIANTE');
		$pdf->ln(7);
		
		$pdf->cell(46,5,$alumno->apellido_paterno,1,0,'C', 0, 0, 1);
		$pdf->cell(46,5,$alumno->apellido_materno,1,0,'C', 0, 0, 1);
		$pdf->cell(46,5,$alumno->nombres,1,0,'C', 0, 0, 1);
		$pdf->ln(5);
		
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(46,5,'APELLIDO PATERNO',1,0,'C');
		$pdf->cell(46,5,'APELLIDO MATERNO',1,0,'C');
		$pdf->cell(46,5,'NOMBRES COMPLETOS',1,0,'C');
		$pdf->SetFont('helvetica', '', 9);
		//-----------------------------------------------------------//
		$pdf->ln(7);
		$fecha_nacimiento = explode('-',$alumno->fecha_nacimiento);

		$pdf->SetFont('helvetica', '', 6);
		$pdf->cell(5,1,'Día:');
		$pdf->ln(5);
		$pdf->ln(-5);
		$pdf->SetFont('helvetica', '', 9);
		
		$pdf->cell(15,5, $fecha_nacimiento[2],1,0,'C');
		
		$pdf->SetFont('helvetica', '', 6);
		$pdf->cell(5,1,'Mes:');
		$pdf->ln(5);
		$pdf->ln(-5);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(15);
		
		$pdf->cell(15,5,$fecha_nacimiento[1],1,0,'C');
		
		$pdf->SetFont('helvetica', '', 6);
		$pdf->cell(5,1,'Año:');
		$pdf->ln(5);
		$pdf->ln(-5);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(30);
		
		$pdf->cell(15,5,$fecha_nacimiento[0],1,0,'R');
		$pdf->cell(3);
		
		$pdf->SetFont('helvetica', '', 6);
		$pdf->cell(5,1,'Dist:');
		$pdf->ln(5);
		$pdf->ln(-5);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(48);
		
		$pdf->cell(25,5,$alumno->distrito_nacimiento->nombre,1,0,'C', 0, 0, 1);
		
		$pdf->SetFont('helvetica', '', 6);
		$pdf->cell(5,1,'Prov:');
		$pdf->ln(5);
		$pdf->ln(-5);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(73);
		
		$pdf->cell(25,5,$alumno->provincia_nacimiento->nombre,1,0,'C', 0, 0, 1);
		
		$pdf->SetFont('helvetica', '', 6);
		$pdf->cell(5,1,'Dpto:');
		$pdf->ln(5);
		$pdf->ln(-5);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(98);
		
		$pdf->cell(25,5,$alumno->departamento_nacimiento->nombre,1,0,'C', 0, 0, 1);
		
		$pdf->cell(1);
		$pdf->cell(14,5,(date('Y')-$fecha_nacimiento[0]),1,0,'C');
		
		$pdf->ln(5);
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(45,5,'FECHA DE NACIMIENTO',1,0,'C');
		$pdf->cell(3);
		$pdf->cell(75,5,'LUGAR DE NACIMIENTO',1,0,'C');
		$pdf->cell(1);
		$pdf->cell(14,5,'EDAD',1,0,'C');
		$pdf->SetFont('helvetica', '', 9);
		//-----------------------------------------------------------//
		$pdf->ln(7);
		
		$pdf->cell(25,5,$alumno->getSexo(),1,0,'C');
		$pdf->cell(3);
		
		$domicilio = $alumno->getLastDomicilio();
		
		$pdf->SetFont('helvetica', '', 6);
		$pdf->cell(5,1,'Calle, Av:');
		$pdf->ln(5);
		$pdf->ln(-5);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(28);
		
		$pdf->cell(83,5,$domicilio['direccion'],1,0,'C', 0, 0, 1);
		
		$pdf->SetFont('helvetica', '', 6);
		$pdf->ln(5);
		$pdf->ln(-5);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(111);
		$pdf->cell(3);
		$pdf->cell(24,5,$domicilio['telefono'],1,0,'C', 0, 0, 1);
		
		$pdf->ln(5);
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(25,5,'SEXO',1,0,'C');
		$pdf->cell(3);
		$pdf->cell(83,5,'DOMICILIO',1,0,'C');
		$pdf->cell(3);
		$pdf->cell(24,5,'TELEFONO',1,0,'C');
		$pdf->SetFont('helvetica', '', 9);

		$pdf->SetFont('helvetica', '', 9);
		
		
		//------------------ ALUMNO --------------------//
		$pdf->ln(7);
		$pdf->cell(0,7,'3.- DATOS DEL PADRE O APODERADO');
		//------------------------------------------------------//
		$pdf->ln(7);
		$i = 1;
		foreach($apoderados As $apoderado){
			$pdf->SetFont('helvetica', '', 8);
			$pdf->cell(39,5,$apoderado->apellido_paterno,1,0,'C', 0, 0, 1);
			$pdf->cell(39,5,$apoderado->apellido_materno,1,0,'C', 0, 0, 1);
			$pdf->cell(40,5,$apoderado->nombres,1,0,'C', 0, 0, 1);
			$pdf->cell(20,5,$apoderado->getParentesco(),1,0,'C', 0, 0, 1);
			$pdf->ln(5);
			$i++;
		}
		
		$pdf->SetFont('helvetica', 'b', 7);
		if(count($apoderados) > 0){
		$pdf->cell(39,5,'APELLIDO PATERNO',1,0,'C');
		$pdf->cell(39,5,'APELLIDO MATERNO',1,0,'C');
		$pdf->cell(40,5,'NOMBRES COMPLETOS',1,0,'C');
		$pdf->cell(20,5,'PARENTESCO',1,0,'C');
		$pdf->SetFont('helvetica', '', 9);
		}
		
		$pdf->ln((3 - count($apoderados)) * 5);
		
		$pdf->ln(7);

		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 10);
		$fecha_i = explode('-',$alumno->fecha_inscripcion);
		$pdf->cell(60);
		$meses = $this->get('__meses');
        $pdf->SetFont('helvetica', '', 8);
		$pdf->cell(100,7, mb_strtoupper($this->COLEGIO->distrito->nombre, 'utf-8').', '.mb_strtoupper($this->COLEGIO->parseFecha($alumno->fecha_inscripcion), 'utf-8'),0,0,'C');
		
		$pdf->ln(15);
		
		$pdf->line(5,165,65,165);
		$pdf->cell(5);
		$pdf->cell(50,7,'FIRMA DEL PADRE DE FAMILIA',0,0,'C');$pdf->ln(5);
		$pdf->cell(50,7,'          '.$apoderado->getTipoDocumento().': '.$apoderado->nro_documento,0,0,'L');
		
		//$pdf->ln(10);
        
        //$pdf->cell(30, 7,'Usuario: '.$alumno->usuario->usuario,0,0,'L');
        //$pdf->cell(50, 7,'Contraseña: '.$alumno->nro_documento,0,0,'L');
        
		$pdf->addPage();
		
	
		$html = '
		<style>
		table{
			margin: 20px 0 0 0;
			width: 100%;
		}
		h2{
			font-size: 40px;
		}
		th{
			text-align: center;
		}
		td, li{
			text-align: justify;
			font-size: 30px;
			
		}
		li{
			margin-bottom: 10px;
		}
		</style>
		<table>
			<tr>
				<th>
				<br />
				<h2>COMPROMISO CON EL '.mb_strtoupper($this->COLEGIO->titulo_intranet, 'utf-8').'</h1></th>
			</tr>
			<tr>
				<td><br /><br />
				Yo, '.$apoderado->getFullName().'<br />
				Con DNI, '.$apoderado->nro_documento.'<br />
				Matriculado(a), '.$alumno->getFullName().'<br />
				ACEPTO LAS DISPOSICIONES GENERALES DETALLADAS A CONTINUACIÓN:
				</td>
			</tr>
			<tr>
				<td><br />
					<H2>BASES LEGALES:</H2>
					<ul>
						<li>Constitución política del Perú</li>
						<li>Ley General de Educación</li>
					</ul>
					<ol>
						<li>El Educando tiene derecho a recibir formación integral en cada semestre académico.</li>
						<li>Recibir estímulos en méríto al cumplimiento de sus deberes.</li>
						<li>Respetar a sus profesores, no utilizar el nombre de la institución en actividades y/o en acciones no utilizadas por la Dirección.</li>
						<li>Participar en forma responsable en las actividades educativas del instituto absteniéndose de intervenir en actividades políticas partidarias dentro del Instituto, en actos reñidos por la moral, y en las buenas costumbres que atenten contra la salud física y mental.
						</li>
						<li>Cuidar los ambientes, equipos mobiliarios, y demás instalaciones del Instituto.</li>
						<li>Cumplir con las disposiciones que emita la Dirección del Instituto o programa Educativo.</li>
						<li>Los viajes de Estudion son de forma obligatoria, cuyo rol y costos aproximados serán entregados al inicio del semestre correspondiente.</li>
						<li>El uso de uniforme es obligatorio para asistir a actos y comisiones oficiales.</li>
						<li>Cancelar en su oportunidad las mensualidades de acuerdo al cronograma. (Adjunto)</li>
						<li>En caso de incumplir con el pago de las mensualidades se cobrará una mora de S/. 0.50 por día.</li>
						<li>Los alumnos, al no cumplir serán sometidos al Reclamento Interno del Instituto.</li>
						<li>Los Padres de Familia serán responsables de los actos que contravengan al Reglamento del Instituto.</li>
						<li>El Padre o apoderado de no estar de acuerdo con el presente Reglamento, no insistir en matricular o ratificar su matrícula en nuestra Institución por el bien de una sociedad conciente.</li>
					</ol>
				</td>
			</tr>
			<tr>
				<td><br /><br />Manifiesto de igual manera conocer que la transgresión del presente compromiso da lugar a sanción prevista en el Reglamiento Interno del instituto, teniendo inclusive la potestad de sancionarme, en casos extremos, con mi separación definitiva.</td>
			</tr>
			<tr>
				<td>En prueba de conformidad con el contenido del presente compromiso, firmo a continuación para efectos de su validez.</td>
			</tr>
		</table>
		<br /><br /><br /><br /><br /><br />
		<table>
			<tr>
				<td style="text-align: center">________________________________</td>
				<td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;________________________________</td>
			</tr>
			<tr>
				<td style="text-align: left;">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				FIRMA DEL ALUMNO<br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				'.$apoderado->getTipoDocumento().': '.$alumno->nro_documento.'</td>
				<td style="text-align: left">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				FIRMA DEL PADRE O APODERADO<br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				'.$apoderado->getTipoDocumento().':  '.$apoderado->nro_documento.'
				</td>
				
			</tr>
		</table>
		';
		
		$pdf->WriteHTML($html);
		$pdf->Output();
		//print_r($apoderado);
		;
	}
    
    function topico_atenciones(){
        $this->crystal->load('TCPDF');
        $pdf = new TCPDF();
        $pdf->setPrintFooter(false);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $alumno = Alumno::find($this->get->alumno_id);
        
        $atenciones = Topico_Atencion::find_all_by_alumno_id($alumno->id, array(
            'order' => 'id DESC'
        ));
        
        //$pdf->SetHeaderData('../../../Static/Image/Insignias/f1e4bdfc226f4f5b52a000d404c380dc6dd191a1.png', PDF_HEADER_LOGO_WIDTH, $this->COLEGIO->titulo_intranet, 'ATENCIONES EN TÓPICO - '.$alumno->getFullName());
        $pdf->SetHeaderData(null, 0, $this->COLEGIO->titulo_intranet, 'ATENCIONES EN PSICOLOGÍA - '.$alumno->getFullName());
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFillColor(232, 232, 232);
        $pdf->AddPage('L');
		
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(7,5,'Nº',1, 0, 'C', 1, '', 0);
		$pdf->cell(40,5,'FECHA / HORA',1, 0, 'C', 1, '', 0);

		$pdf->cell(80,5,'MOTIVO',1, 0, 'C', 1, '', 0);
		
		$pdf->cell(80,5,'TRATAMIENTO',1, 0, 'C', 1, '', 0);
		$pdf->cell(60,5,'ATENDIDO POR',1, 0, 'C', 1, '', 0);
		
		$pdf->ln(5);
		$pdf->SetFont('helvetica', '', 8);
		
		$i = 1;
		foreach($atenciones As $atencion){
			$pdf->cell(7,5,$i,1, 0, 'C', 0, '', 0);
			$pdf->cell(40,5,$atencion->getFechaHora(),1, 0, 'C', 0, '', 1);
			
			$pdf->cell(80,5,$atencion->motivo,1, 0, 'L', 0, '', 1);
			
			$pdf->cell(80,5,$atencion->tratamiento,1, 0, 'L', 0, '', 1);
			$pdf->cell(60,5,$atencion->personal->getFullName(),1, 0, 'L', 0, '', 1);
		
			$pdf->ln(5);
			$i++;
		}
		
		$pdf->SetFont('helvetica', '', 8);
		$pdf->cell(207);
		$pdf->cell(60,5,'SE REGISTRARON '.count($atenciones).' ATENCION(ES)',1, 0, 'C', 0, '', 0);
        
        
        $pdf->output();
    }


	function reporte_final(){
		$this->crystal->load('PHPExcel:PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/reporte_final.xlsx');
		$normalStyle = array(
		    'font'  => array(
		        'bold'  => true,
		        'color' => array('rgb' => '000000'),
		        'size'  => 11,
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

		$fillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'FDE9D9',
				),
			),
		);

		$sheet = $excel->getSheet(0);
		$grupos = $this->COLEGIO->getGrupos($this->get->anio);
		//$grupos = Grupo::find_all_by_id(31);

		$sheets = array();
		foreach($grupos As $key_grupo => $grupo){
			$sheets[$key_grupo] = clone $sheet;
			$sheets[$key_grupo]->setTitle(substr($grupo->getNombre(), 0, 30));
			$excel->addSheet($sheets[$key_grupo]);

			$s = $sheets[$key_grupo];

			$s->setCellValue('O1', 'BALANCE - '.$grupo->getNombre());

			$matriculas = $grupo->getMatriculas();
			$asignaturas = $grupo->getAsignaturas();

			$currentRow = 2;
			$rendimiento = array();
			$promediosAsignatura = array();
			foreach($matriculas As $key_matricula => $matricula){
				$s->setCellValue('A'.$currentRow, $key_matricula + 1);
				$s->setCellValue('B'.$currentRow, $matricula->alumno->getFullName());
				$currentColLeftPromedio = 3;
				$totalPromedios = array();
				for($ciclo = 1; $ciclo<=$this->COLEGIO->total_notas;$ciclo++){
					$promedioCiclo = $matricula->getPromedioCiclo($ciclo, count($asignaturas));
					$s->setCellValue(numToChar($currentColLeftPromedio).$currentRow, round($promedioCiclo));
					//$s->setCellValue(numToChar($currentColLeftPromedio).$currentRow, 'A');
					$totalPromedios[] = round($promedioCiclo);
					++$currentColLeftPromedio;
				}
				$totalPromedios = array_sum($totalPromedios);
				$promedioFinal = $totalPromedios > 0 ? round($totalPromedios / $this->COLEGIO->total_notas) : '';
				$s->setCellValue('G'.$currentRow, $promedioFinal);

				// Rendimiento
				$rendimientoFinal = $promedioFinal < 14 ? 'Bajo': ($promedioFinal<17?'Regular':($promedioFinal < 19 ? 'Bueno':'Muy Bueno') );
				$s->setCellValue('H'.$currentRow, $rendimientoFinal);
				$rendimiento[] = $rendimientoFinal;
				
				// asignaturas
				$s->setCellValue('X'.$currentRow, $key_matricula + 1);
				$s->setCellValue('Y'.$currentRow, $matricula->alumno->getFullName());
				$currentRowAsignatura = 3;
				$currentColAsignatura = 26;
				foreach($asignaturas As $asignatura){
					// RIGHT DETALLES
					for($ciclo = 1; $ciclo<=$this->COLEGIO->total_notas;$ciclo++){
						$promedioCiclo = $matricula->getPromedio($asignatura->id, $ciclo);
						$s->setCellValue(numToChar($currentColAsignatura).$currentRow, $promedioCiclo);
						$s->getColumnDimension(numToChar($currentColAsignatura))->setWidth(5.71);
						++$currentColAsignatura;
					}

					$promedioFinalAsignatura = $matricula->getPromedioFinalAsignatura($asignatura->id);
					$s->setCellValue(numToChar($currentColAsignatura).$currentRow, $promedioFinalAsignatura);

					if(is_null($promediosAsignatura[$asignatura->id]))
						$promediosAsignatura[$asignatura->id] = array();

					$promediosAsignatura[$asignatura->id][] = $promedioFinalAsignatura;
					++$currentColAsignatura;
				}
				$s->getRowDimension($currentRow)->setRowHeight(30);
				++$currentRow;
			}

			// RESUMEN STYLE
			$s->getStyle('O2:Q'.(count($asignaturas) + 2))->applyFromArray($normalStyle);
			$s->getStyle('O2:O'.(count($asignaturas) + 2))->applyFromArray($fillStyle);
			
			// ALUMNOS STYLE
			$s->getStyle('A1:I'.(count($matriculas) + 1))->applyFromArray($normalStyle);

			// HEADER ASIGNATURAS
			
			$currentRowAsignatura = 3;
			$currentColAsignatura = 26;
			foreach($asignaturas As $asignatura){
				$mergeStep = $this->COLEGIO->total_notas - 1;

				$s->setCellValue(numToChar($currentColAsignatura).'1', $asignatura->curso->nombre);
				$s->mergeCells(numToChar($currentColAsignatura).'1:'.numToChar($currentColAsignatura + ($this->COLEGIO->total_notas - 1)).'1');
				
				$currentColAsignatura += $this->COLEGIO->total_notas;
				

				$s->setCellValue(numToChar($currentColAsignatura).'1', 'PROM');
				$s->getColumnDimension(numToChar($currentColAsignatura))->setWidth(7);
				++$currentColAsignatura;
				
				// LEFT RESUMEN
				$s->setCellValue('O'.$currentRowAsignatura, $asignatura->curso->nombre);
				$promedioAsignaturaGrupo = count($promediosAsignatura[$asignatura->id]) == 0 ? 0 : array_sum($promediosAsignatura[$asignatura->id]) / count($promediosAsignatura[$asignatura->id]);
				$s->setCellValue('P'.$currentRowAsignatura, $promedioAsignaturaGrupo);
				$s->setCellValue('Q'.$currentRowAsignatura, $promedioAsignaturaGrupo >= 19 ? 20 : $promedioAsignaturaGrupo + 2);
				++$currentRowAsignatura;
			}

			// ASIGNATURAS STYLE
			$s->getStyle('X1:Y'.(count($matriculas) + 1))->applyFromArray($normalStyle);
			$s->getStyle('Z1:'.numToChar($currentColAsignatura - 1).(count($matriculas) + 1))->applyFromArray($normalStyle);
			$s->getStyle('X1:'.numToChar($currentColAsignatura - 1).'1')->applyFromArray($fillStyle);

			// RESUMEN RENDIMIENTO
			$items = array('Bajo', 'Regular', 'Bueno', 'Muy Bueno');
			$totales = array_count_values($rendimiento);
			$currentRow = 3;
			foreach(array_reverse($items) As $item){
				$s->setCellValue('L'.$currentRow, $totales[$item] > 0 ? $totales[$item] : 0);

				$s->setCellValue('M'.$currentRow, count($rendimiento) > 0 ? ((100*$totales[$item])/(count($rendimiento)))/100 : 0);
				++$currentRow;
			}
		}


		
		$excel->removeSheetByIndex(0);
		$excel->removeSheetByIndex(0);

		writeExcel($excel);
    }

    function estadisticas_finales(){

    	//$grupos = $this->COLEGIO->getGrupos($this->get->anio);
		//$grupos = Grupo::find_all_by_id(31);
	
		//$grupo = Grupo::find_by_id(31);
		$grupo = $this->COLEGIO->getGrupo($this->get);
		
		
		//foreach($grupos As $key_grupo => $grupo){
		$matriculas = $grupo->getMatriculas();
		$asignaturas = $grupo->getAsignaturas();
	

		$rendimiento = array();
		$promediosAsignatura = array();
		
		foreach($matriculas As $key_matricula => $matricula){
			$totalPromedios = array();
			for($ciclo = 1; $ciclo<=$this->get->ciclo;$ciclo++){
				$promedioCiclo = $matricula->getPromedioCiclo($ciclo, count($asignaturas));
				$totalPromedios[] = round($promedioCiclo);
			}

			$sumaPromedios = array_sum($totalPromedios);
			$promedioFinal = count($totalPromedios) > 0 ? round($sumaPromedios / count($totalPromedios)) : 0;
			$rendimientoFinal = $promedioFinal < 14 ? 'Bajo': ($promedioFinal<17?'Regular':($promedioFinal < 19 ? 'Bueno':'Muy Bueno') );
			$rendimiento[] = $rendimientoFinal;
		}
		
		//print_r($totalPromedios);

		// rendimiento
		$items = array('Bajo', 'Regular', 'Bueno', 'Muy Bueno');
		$totales = array_count_values($rendimiento);
		$currentRow = 3;
		foreach($items As $item){
			$data[] = array(
				'x_key' => $item,
				'value' => (count($rendimiento) > 0 ? ((100*$totales[$item])/(count($rendimiento))) : 0).'%'
			);
		}

		
		$promediosFinales = array();
		$promediosAsignatura = array();
		foreach($asignaturas As $asignatura){
			foreach($matriculas As $matricula){
				$promedioFinalAsignatura = $matricula->getPromedioFinalAsignatura($asignatura->id);
				$promedioFinalAsignatura = is_null($promedioFinalAsignatura) ? 0 : $promedioFinalAsignatura;
				$promediosFinales[$asignatura->id][$matricula->id] = $promedioFinalAsignatura;
			}

			$promedioAsignaturaGrupo = count($promediosFinales[$asignatura->id]) == 0 ? 0 : array_sum($promediosFinales[$asignatura->id]) / count($promediosFinales[$asignatura->id]);
			$promediosAsignatura[$asignatura->id] = $promedioAsignaturaGrupo;
		}


		
		$this->render(array('rendimiento' => array(
			'data' => $data,
			'labels' => $items
		), 'asignaturas' => $asignaturas, 'matriculas' => $matriculas, 
		'promediosFinales' => $promediosFinales,
		'promediosAsignatura' => $promediosAsignatura, 'grupo' => $grupo));

		/*
		echo '<pre>';
		print_r($data);
		print_r($totales);
		print_r($rendimiento);
		echo '</pre>';
		*/
    }


    function estadisticas_balance_general(){
    	$grupos = $this->COLEGIO->getGrupos($this->get->anio);
    	$items = array('Bajo', 'Regular', 'Bueno', 'Muy Bueno');
    	$data = array();
    	$rendimiento = array();
    	$promediosAsignatura = array();
    	$total_alumnos = array();
    	$totales = array();
    	foreach($grupos AS $grupo){

    		if(is_null($rendimiento[$grupo->id])){
    			$rendimiento[$grupo->id] = array();
    		}

    		$matriculas = $grupo->getMatriculas();
			$asignaturas = $grupo->getAsignaturas();
		
			
			

			foreach($matriculas As $key_matricula => $matricula){
				$totalPromedios = array();
				for($ciclo = 1; $ciclo<=$this->get->ciclo;$ciclo++){
					$promedioCiclo = $matricula->getPromedioCiclo($ciclo, count($asignaturas));
					$totalPromedios[] = round($promedioCiclo);
				}

				$sumaPromedios = array_sum($totalPromedios);
				$promedioFinal = count($totalPromedios) > 0 ? round($sumaPromedios / count($totalPromedios)) : 0;
				$rendimientoFinal = $promedioFinal < 14 ? 'Bajo': ($promedioFinal<17?'Regular':($promedioFinal < 19 ? 'Bueno':'Muy Bueno') );
				$rendimiento[$grupo->id][] = $rendimientoFinal;
				$total_alumnos[] = $matricula->id;
			}

			$totales[$grupo->id] = array_count_values($rendimiento[$grupo->id]);
			$currentRow = 3;
			foreach($items As $item){
				$data[$grupo->id][$item] = count($rendimiento[$grupo->id]) > 0 ? ((100*$totales[$grupo->id][$item])/(count($rendimiento[$grupo->id]))) : 0;
			}
    	}

    	$data_general = array();
    	foreach($grupos As $grupo){
    		foreach($items As $item){
    			$data_general[$grupo->id][$item] = count($total_alumnos) > 0 ? ((100*$totales[$grupo->id][$item])/(count($total_alumnos))) : 0;
    		}
    	}

    	//$totales = array_count_values($rendimiento);
    	//echo '<pre>';
    	//   	print_r($rendimiento);
    	//echo '</pre>';
    	$this->render(array('grupos' => $grupos, 'items' => $items, 'data_aula' => $data, 'data_general' => $data_general));
    }

    
    function objetivos_matriculas($r){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/cuadro_matriculas.xlsx');
		$niveles = $this->COLEGIO->getNiveles();

		$normalStyle = array(
		    'font'  => array(
		        'bold'  => true,
		        'color' => array('rgb' => '000000'),
		        'size'  => 11,
		        'name'  => 'Arial'
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

	    $headerStyle = array(
	    	'font'  => array(
		        'bold'  => true,
		        'color' => array('rgb' => '000000'),
		        'size'  => 12,
		        'name'  => 'Arial'
		    ),
	    );

	    $nivelStyle = array(
	    	'font'  => array(
		        'bold'  => true,
		        'color' => array('rgb' => '000000'),
		        'size'  => 14,
		        'name'  => 'Arial'
		    ),
	    );

	    $textStyle = array(
	    	'font'  => array(
		        'color' => array('rgb' => '000000'),
		        'size'  => 18,
		        'name'  => 'Arial'
		    ),
	    );

		$fillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'FDE9D9',
				),
			),
		);

		$fillTotalStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'B6DDE8',
				),
			),
		);

		$s1 = $excel->getSheet(0);
		$s1->setCellValue('A1', 'OBJETIVOS MATRÍCULAS - '.$this->get->anio);

		$currentRow = 3;
		$totales = array();
		$letras = array('B', 'C', 'H', 'I', 'J', 'K', 'L');
		foreach($niveles As $key => $nivel){
			$grupos = $this->COLEGIO->getGruposByNivel($nivel->id, $this->get->anio);
			$s1->setCellValue('A'.$currentRow, mb_strtoupper($nivel->nombre, 'utf-8'));
			$s1->setCellValue('B'.$currentRow, 'SE ESPERAN ANTIGUOS');
			$s1->setCellValue('C'.$currentRow, 'OBJETIVOS VACANTES');
			$s1->setCellValue('H'.$currentRow, 'ALUMNOS MATRICULADOS ANTIGUOS');
			$s1->setCellValue('I'.$currentRow, 'ALUMNOS MATRICULADOS NUEVOS');
			$s1->setCellValue('J'.$currentRow, 'ALUMNOS ANTIGUOS FALTA CAPTAR');
			$s1->setCellValue('K'.$currentRow, 'ALUMNOS NUEVOS FALTA CAPTAR');
			$s1->setCellValue('L'.$currentRow, 'TOTAL DE MATRICULADOS');
			$s1->setCellValue('M'.$currentRow, 'ESTADO');

			$s1->getStyle('A'.$currentRow.':M'.$currentRow)->applyFromArray($normalStyle);
			$s1->getStyle('A'.$currentRow.':M'.$currentRow)->applyFromArray($fillStyle);
			$s1->getStyle('A'.$currentRow.':M'.$currentRow)->applyFromArray($headerStyle);
			$s1->getStyle('A'.$currentRow)->applyFromArray($nivelStyle);
			$s1->getRowDimension($currentRow)->setRowHeight(70);
			$totalStart = ($currentRow + 1);
			foreach($grupos As $grupo){
				++$currentRow;
				$s1->setCellValue('A'.$currentRow, mb_strtoupper($grupo->getGradoDescribed().' '.$grupo->seccion, 'utf-8'));
				$s1->setCellValue('J'.$currentRow, '=B'.$currentRow.'-H'.$currentRow);
				$s1->setCellValue('K'.$currentRow, '=C'.$currentRow.'-(J'.$currentRow.'+L'.$currentRow.')');
				$s1->setCellValue('L'.$currentRow, '=I'.$currentRow.'+H'.$currentRow);
				$s1->setCellValue('M'.$currentRow, '=IF(L'.$currentRow.'=C'.$currentRow.',"NO VACANTE","")');
				$s1->getStyle('A'.$currentRow.':M'.$currentRow)->applyFromArray($normalStyle);
				$s1->getStyle('B'.$currentRow.':M'.$currentRow)->applyFromArray($textStyle);
				$s1->getStyle('A'.$currentRow)->applyFromArray($fillStyle);
				$s1->getStyle('A'.$currentRow)->applyFromArray($headerStyle);
			}
			$totalEnd = $currentRow;
			++$currentRow;
			// total
			$s1->setCellValue('A'.$currentRow, 'TOTAL');
			foreach($letras As $letra){
				$s1->setCellValue($letra.$currentRow, '=SUM('.$letra.$totalStart.':'.$letra.$totalEnd.')');
				$totales[$letra][] = $letra.$currentRow;
			}

			/*
			$s1->setCellValue('B'.$currentRow, '=SUM(B'.$totalStart.':B'.$totalEnd.')');
			$s1->setCellValue('C'.$currentRow, '=SUM(C'.$totalStart.':C'.$totalEnd.')');
			$s1->setCellValue('H'.$currentRow, '=SUM(H'.$totalStart.':H'.$totalEnd.')');
			$s1->setCellValue('I'.$currentRow, '=SUM(I'.$totalStart.':I'.$totalEnd.')');
			$s1->setCellValue('J'.$currentRow, '=SUM(J'.$totalStart.':J'.$totalEnd.')');
			$s1->setCellValue('K'.$currentRow, '=SUM(K'.$totalStart.':K'.$totalEnd.')');
			$s1->setCellValue('L'.$currentRow, '=SUM(L'.$totalStart.':L'.$totalEnd.')');

			$totales['B'][] = 'B'.$currentRow;
			*/
			$s1->getStyle('A'.$currentRow.':L'.$currentRow)->applyFromArray($normalStyle);
			$s1->getStyle('A'.$currentRow.':L'.$currentRow)->applyFromArray($fillTotalStyle);
			$s1->getStyle('A'.$currentRow.':L'.$currentRow)->applyFromArray($textStyle);
			++$currentRow;
			// advance
			++$currentRow;
		}

		//++$currentRow;

		//$s1->setCellValue('A'.$currentRow, mb_strtoupper($nivel->nombre, 'utf-8'));
		$s1->setCellValue('B'.$currentRow, 'SE ESPERAN ANTIGUOS');
		$s1->setCellValue('C'.$currentRow, 'OBJETIVOS VACANTES');
		$s1->setCellValue('H'.$currentRow, 'ALUMNOS MATRICULADOS ANTIGUOS');
		$s1->setCellValue('I'.$currentRow, 'ALUMNOS MATRICULADOS NUEVOS');
		$s1->setCellValue('J'.$currentRow, 'ALUMNOS ANTIGUOS FALTA CAPTAR');
		$s1->setCellValue('K'.$currentRow, 'ALUMNOS NUEVOS FALTA CAPTAR');
		$s1->setCellValue('L'.$currentRow, 'TOTAL DE MATRICULADOS');
		$s1->setCellValue('M'.$currentRow, 'TOTAL ALUMNOS FALTANTES');
		$s1->getStyle('A'.$currentRow.':M'.$currentRow)->applyFromArray($normalStyle);
		$s1->getStyle('A'.$currentRow.':M'.$currentRow)->applyFromArray($fillStyle);
		$s1->getStyle('A'.$currentRow.':M'.$currentRow)->applyFromArray($headerStyle);
		$s1->getRowDimension($currentRow)->setRowHeight(70);
		++$currentRow;
		$s1->setCellValue('A'.$currentRow, 'TOTAL GENERAL');
		$s1->getStyle('A'.$currentRow)->applyFromArray($normalStyle);
		$s1->getStyle('A'.$currentRow)->applyFromArray($fillStyle);
		$s1->getStyle('A'.$currentRow)->applyFromArray($headerStyle);

		foreach($letras As $letra){
			$s1->setCellValue($letra.$currentRow, '=SUM('.implode(',', $totales[$letra]).')');
			//$totales['B'][] = $currentRow;
		}

		$s1->setCellValue('M'.$currentRow, '=C'.$currentRow.'-L'.$currentRow);

		$s1->getStyle('B'.$currentRow.':M'.$currentRow)->applyFromArray($normalStyle);
		$s1->getStyle('B'.$currentRow.':M'.$currentRow)->applyFromArray($textStyle);

		writeExcel($excel);
	}




	function ubicacion_docentes($r){
		$this->crystal->load('TCPDF');
		
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		

		$pdf = new TCPDF();
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->SetMargins(5, 10, 5);
		$pdf->SetAutoPageBreak(TRUE, 5);
		$pdf->setLanguageArray($l);
		
		$pdf->AddPage('L');
		$this->setLogo($pdf);
		$pdf->setFillColor(234, 234, 234);
		$pdf->SetFont('helvetica', 'b', 13);
		//$pdf->image('./Static/Image/logo.jpg', 10, 2, 45, 17);
		$pdf->cell(0,5,'Ubicación de Docentes',0 , 1,'R');

		
		$pdf->ln(10);
		$fecha = $from;
		
		while(true){

			$keyDia = date('N', strtotime($fecha)) - 1;
			
			if($keyDia <= 4){

				$pdf->SetFont('helvetica', 'b', 13);
				$pdf->cell(0,5, $this->COLEGIO->DIAS[$keyDia].', '.$this->COLEGIO->parseFecha($fecha),0,0,'L');
				$pdf->ln(10);

				$pdf->setFont('Helvetica','b',10);
				$pdf->cell(30,5,'HORA',1,0,'C', 1);
				$pdf->cell(65,5,'DOCENTE',1,0,'C', 1);
				$pdf->cell(50,5,'GRUPO',1,0,'C', 1);
				$pdf->cell(50,5,'CURSO',1,0,'C', 1);
				$pdf->cell(90,5,'TEMA',1,0,'C', 1);
				$pdf->ln(5);
				$pdf->setFont('Helvetica','',9);
				$i = 1;

				$horarios = Grupo_Horario::all(array(
					'conditions' => 'dia="'.$keyDia.'"',
					'order' => 'STR_TO_DATE(hora_inicio, "%l:%i %p") ASC'
				));

				foreach($horarios As $horario){
					$personal = $horario->tipo == 'DOCENTE' ? $horario->personal : $horario->asignatura->personal;

					if(!$personal) continue;
					if(!empty($this->get->personal_id) && $personal->id != $this->get->personal_id) continue;
					if($horario->tipo == 'GRUPO' && is_null($horario->grupo)) continue;
					if($horario->tipo == 'GRUPO' && is_null($horario->asignatura)) continue;
					
					$pdf->cell(30,5, $horario->hora_inicio.' - '.$horario->hora_final,1,0,'C', 0, 0, 1);
					$pdf->cell(65,5, $personal->getFullName(),1,0,'L', 0, 0, 1);
					$pdf->cell(50,5, $horario->tipo == 'GRUPO' ? $horario->grupo->getNombreShort2() : '-',1,0,'C', 0, 0, 1);
					$pdf->cell(50,5, $horario->tipo == 'GRUPO' ? $horario->asignatura->curso->nombre : '-',1,0,'C', 0, 0, 1);

					if($horario->tipo == 'GRUPO'){
						$tema = Asignatura_Tema::find_by_asignatura_id_and_fecha($horario->asignatura_id, $fecha);
						$tema = $tema->tema;
					}else{
						$tema = $horario->descripcion;
					}

					$pdf->cell(90,5, $tema, 1, 0, 'C', 0, 0, 1);
					$pdf->ln(5);
				}
			}


			if($fecha == $to) break;
			$fecha = date('Y-m-d', strtotime($fecha.' +1 day'));
			$pdf->ln(5);
		}

		
		

		$pdf->output();
	}


	function alquiler_cancha(){
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$alquileres = Alquiler_Cancha::all(array(
			'conditions' => 'DATE(inicio) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND colegio_id="'.$this->COLEGIO->id.'"',
			'order' => 'inicio ASC'
		));
		
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0, 5,'Alquiler de Cancha', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(0,5, 'Del '.$this->COLEGIO->parseFecha($from).' al '.$this->COLEGIO->parseFecha($to),0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(60, 5, 'NOMBRE', 1, 0, 'C', 1);
		$pdf->cell(60, 5, 'REGISTRADO POR', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'FECHA', 1, 0, 'C', 1);
		$pdf->cell(40, 5, 'HORA(S)', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'PRECIO S/.', 1, 0, 'C', 1, 0, 1);
		
		$total = 0;
		foreach($alquileres As $alquiler){
			$por = !is_null($alquiler->personal) ? $alquiler->personal->getFullName() : '';
			$pdf->ln(5);
			

			$pdf->cell(60, 5, $alquiler->nombre, 1, 0, 'L', 0, 0, 1);
			$pdf->cell(60, 5, $por, 1, 0, 'C', 0, null, 1);
			$pdf->cell(20, 5, $this->COLEGIO->getFecha($alquiler->inicio), 1, 0, 'C', 0, null, 1);
			$pdf->cell(40, 5, date('h:i A', strtotime($alquiler->inicio)).' - '.date('h:i A', strtotime($alquiler->fin)), 1, 0, 'C', 0, null, 1);
			$pdf->cell(20, 5, number_format($alquiler->precio, 2), 1, 0, 'C', 0, null, 1);
		
			$total += $alquiler->precio;
		}

		$descuento = $total * $this->COLEGIO->descuento_alquiler_cancha / 100;
		$total = $total - $descuento;

		$pdf->ln(5);

		$pdf->cell(120 + 40);
		$pdf->cell(20, 5, 'TOTAL', 1, 0, 'C', 1, null, 1);
		$pdf->cell(20, 5, number_format($total, 2), 1, 0, 'C', 0, null, 1);

		

		$pdf->output();
	}

	function morasx(){
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$pagos = Pago::all(array(
			'conditions' => 'DATE(pagos.fecha_hora) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.colegio_id="'.$this->COLEGIO->id.'" AND pagos.estado="ACTIVO" AND pagos.estado_pago="CANCELADO"',
			'joins' => array('matricula')
		));
		
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0, 5,'Reporte de Moras', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(0,5, 'Del '.$this->COLEGIO->parseFecha($from).' al '.$this->COLEGIO->parseFecha($to),0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(70, 5, 'ALUMNO', 1, 0, 'C', 1);
		$pdf->cell(40, 5, 'DESCRIPCIÓN', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'Nº RECIBO', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'FECHA', 1, 0, 'C', 1, 0, 1);
		$pdf->cell(20, 5, 'MONTO', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'MORA', 1, 0, 'C', 1);
		
		
		$total = 0;
		$totalMora = 0;
		foreach($pagos As $pago){
			
			$pdf->ln(5);
			

			$pdf->cell(70, 5, $pago->matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
			$pdf->cell(40, 5, $pago->getDescription(), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, $pago->nro_recibo, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(20, 5, $this->COLEGIO->setFecha($pago->fecha_hora), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(20, 5, number_format($pago->monto, 2), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(20, 5, number_format($pago->mora, 2), 1, 0, 'C', 0, 0, 1);
			
			
		
			$total += $pago->monto;
			$totalMora += $pago->mora;
		}

		$pdf->ln(5);

		$pdf->cell(120 + 20);
		$pdf->cell(20, 5, 'TOTAL', 1, 0, 'C', 1, null, 1);
		$pdf->cell(20, 5, number_format($total, 2), 1, 0, 'C', 0, null, 1);
		$pdf->cell(20, 5, number_format($totalMora, 2), 1, 0, 'C', 0, null, 1);

		

		$pdf->output();
	}

	function moras(){
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));
		/*
		$pagos = Pago::all(array(
			'conditions' => 'DATE(pagos.fecha_hora) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.colegio_id="'.$this->COLEGIO->id.'" AND pagos.estado="ACTIVO"  AND nro_recibo != "-"',
			'order' => 'nro_recibo ASC'
			//'joins' => array('matricula')
		));
		*/

		$pagos = Pago::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND fecha_cancelado BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND estado_pago = "CANCELADO" AND estado = "ACTIVO" AND mora > 0'
		));

		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/moras.xlsx');
		$normalStyle = array(
		    'font'  => array(
		        'color' => array('rgb' => '000000'),
		        'size'  => 10,
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

		$s1 = $excel->getSheet(0);
		$s1->setCellValue('A3', 'Del '.$this->COLEGIO->parseFecha($from).' al '.$this->COLEGIO->parseFecha($to));

		$currentRow = 6;

		$total = 0;
		$totalMora = 0;
		foreach($pagos As $pago){
			$alumno = $pago->matricula->alumno;
			if(!isset($alumno)) continue;

			$s1->setCellValue('A'.$currentRow, $alumno->getFullName());
			$s1->setCellValue('B'.$currentRow, $pago->getDescription());
			$s1->setCellValue('C'.$currentRow, $pago->nro_recibo);
			$s1->setCellValue('D'.$currentRow, $this->COLEGIO->setFecha($pago->fecha_cancelado));
			$s1->setCellValue('E'.$currentRow, number_format($pago->monto, 2));
			$s1->setCellValue('F'.$currentRow, number_format($pago->mora, 2));
		
			
		
			$total += $pago->monto;
			$totalMora += $pago->mora;
			++$currentRow;
		}

		$s1->setCellValue('D'.$currentRow, 'TOTAL');
		$s1->setCellValue('E'.$currentRow, number_format($total, 2));
		$s1->setCellValue('F'.$currentRow, number_format($totalMora, 2));

		if(count($pagos) > 6)
			$s1->getStyle('A6:F'.$currentRow)->applyFromArray($normalStyle);

		writeExcel($excel);
	}


	function historial_moras(){
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		/*
		$historiales = Pago_Historial::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND fecha BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
		));
		*/

		$pagos = Pago::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND fecha_cancelado BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND estado_pago = "CANCELADO" AND estado = "ACTIVO" AND mora > 0'
		));
		
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		
		$pdf->addPage('L');
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0, 5,'Historial de Moras', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(0,5, 'Del '.$this->COLEGIO->parseFecha($from).' al '.$this->COLEGIO->parseFecha($to),0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(60, 5, 'NOMBRE', 1, 0, 'C', 1);
		$pdf->cell(45, 5, 'GRUPO', 1, 0, 'C', 1);
		$pdf->cell(50, 5, 'CONCEPTO', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'FECHA', 1, 0, 'C', 1);
		$pdf->cell(40, 5, 'Nº DE RECIBO', 1, 0, 'C', 1);
		//$pdf->cell(30, 5, 'ARCHIVO', 1, 0, 'C', 1);

		$pdf->cell(20, 5, 'MONTO S/.', 1, 0, 'C', 1, 0, 1);
		$pdf->cell(20, 5, 'MORA S/.', 1, 0, 'C', 1, 0, 1);
		
		$total = 0;
		$totalMora = 0;
		//foreach($historiales As $historial){
			//$pagos = $historial->getPagos();
			foreach($pagos As $pago){
				if($pago->mora <= 0) continue;
				$pdf->ln(5);
				$pdf->cell(60, 5, $pago->matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
				$pdf->cell(45, 5, $pago->matricula->grupo->getNombreShort2(), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(50, 5, $pago->getDescription(), 1, 0, 'C', 0, null, 1);
				$pdf->cell(20, 5, $this->COLEGIO->getFecha($pago->fecha_cancelado), 1, 0, 'C', 0, null, 1);
				$pdf->cell(40, 5, $pago->getNroBoleta(), 1, 0, 'C', 0, null, 1);
				//$pdf->cell(30, 5, $historial->archivo, 1, 0, 'C', 0, null, 1);
				$pdf->cell(20, 5, number_format($pago->monto, 2), 1, 0, 'C', 0, null, 1);
				$pdf->cell(20, 5, number_format($pago->mora, 2), 1, 0, 'C', 0, null, 1);
			
				$total += $pago->monto;
				$totalMora += $pago->mora;
			}

			
		//}

		$pdf->ln(5);

		$pdf->cell(120 + 50+10+5);
		$pdf->cell(30, 5, 'TOTAL', 1, 0, 'C', 1, null, 1);
		$pdf->cell(20, 5, number_format($total, 2), 1, 0, 'C', 0, null, 1);
		$pdf->cell(20, 5, number_format($totalMora, 2), 1, 0, 'C', 0, null, 1);

		

		$pdf->output();
	}

	function pensiones(){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/pension_alumnos.xlsx');
		$s1 = $excel->getSheet(0);

		$grupos = $this->COLEGIO->getGrupos($this->get->anio);

		$currentRow = 5;
		foreach($grupos AS $grupo){
			$matriculas = $grupo->getMatriculas();
			foreach($matriculas AS $matricula){
				$apoderado = $matricula->alumno->getFirstApoderado();
				$telefonos = array();
				if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
				if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

				$s1->setCellValue('B'.$currentRow, $matricula->alumno->getFullName());
				$s1->setCellValue('C'.$currentRow, $grupo->getNombreShort2());

				$s1->setCellValue('E'.$currentRow, number_format($matricula->costo->pension, 2));
				$s1->setCellValue('G'.$currentRow, implode(', ', $telefonos));
				$s1->setCellValue('H'.$currentRow, $apoderado->email);
				$s1->setCellValue('I'.$currentRow, $apoderado->direccion);

				++$currentRow;
			}
		}

		$normalStyle = array(
		    'font'  => array(
		        //'bold'  => true,
		        'color' => array('rgb' => '000000'),
		        'size'  => 10,
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

	    if($currentRow > 5) $s1->getStyle('B5:I'.($currentRow - 1))->applyFromArray($normalStyle);

		writeExcel($excel);
	}

	function datos_alumnos(){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load("./Static/Templates/datos_alumnos.xlsx");
		$s1 = $excel->getSheet(0);
		$s1->setCellValue('A1', 'Lista de Alumnos - '.$this->get->anio);
		$matriculas = Matricula::all(array(
			'conditions' => 'grupos.anio = "'.$this->get->anio.'"',
			'joins' => array('grupo', 'alumno'),
			'order' => $this->COLEGIO->ALUMNOS_ORDER
		));

		$currentRow = 5;
		foreach($matriculas As $matricula){
			$alumno = $matricula->alumno;
			$apoderado = $alumno->getFirstApoderado();

			$s1->setCellValue('A'.$currentRow, $alumno->nro_documento);
			$s1->setCellValue('B'.$currentRow, $alumno->getFullName());

			$s1->setCellValue('C'.$currentRow, $apoderado->direccion);
			$s1->setCellValue('D'.$currentRow, $apoderado->telefono_fijo.' - '.$apoderado->telefono_celular);

			++$currentRow;
		}

		$normalStyle = array(
		    'font'  => array(
		        //'bold'  => true,
		        'color' => array('rgb' => '000000'),
		        'size'  => 10,
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

	    if($currentRow > 5) $s1->getStyle('A5:D'.($currentRow - 1))->applyFromArray($normalStyle);

		writeExcel($excel);
	}

	function concar(){
		$this->crystal->load('PHPExcel');
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$excel = PHPExcel_IOFactory::load('./Static/Templates/concar.xlsx');
		$s1 = $excel->getSheet(0);

		// BOLETAS - FACTURACIÓN

		$boletas = Boleta::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND fecha BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
		));

		// PENSIONES
		$pagos = Pago::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND DATE(fecha_hora) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
		));

		// MORAS
		$moras = Pago::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND DATE(fecha_boleteo_mora) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND tipo_mora = "BOLETA" AND estado_pago = "CANCELADO"'
		));

		$registros = array();
		foreach($boletas As $boleta)
			$registros[] = new xBoleta($boleta, 'BOLETA');

		foreach($pagos As $pago)
			$registros[] = new xBoleta($pago, 'PAGO');

		foreach($moras As $mora)
			$registros[] = new xBoleta($mora, 'MORA_BOLETA');
		
		usort($registros, function($a, $b){
			$nro1 = $a->getSerieNumero();
			$nro2 = $b->getSerieNumero();
			return strcmp($nro1, $nro2);
		});


		$currentRow = 1;
		$autoNumeric = 1;
		foreach($registros As $registro){
			//$s1->getStyle('A'.$currentRow.':B'.$currentRow)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			/** MORAS **/

			/** MATRICULA / PENSIÓN **/
			/*
			if($registro instanceOf Pago){
				if($registro->estado == 'ACTIVO'){
					// PRIMERA FILA
					++$currentRow;
					$s1->setCellValue('A'.$currentRow, '05 ');
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha_hora)));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$alumno = $registro->matricula->alumno;
					$s1->setCellValue('E'.$currentRow, mb_strtoupper($alumno->apellido_paterno.' '.$alumno->apellido_materno, 'utf-8').', BV '.$registro->getCurrentSerie().'-'.intval($registro->getNumero()));
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'S');

					$s1->setCellValue('J'.$currentRow, '121201');
					$s1->setCellValue('K'.$currentRow, '70966381');
					$s1->setCellValue('L'.$currentRow, '');
					$s1->setCellValue('M'.$currentRow, 'D');
					$s1->setCellValue('N'.$currentRow, number_format($registro->monto, 2));

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getCurrentSerie().'-'.intval($registro->getNumero()));
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha_hora)));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha_hora)));

					$s1->setCellValue('V'.$currentRow, $registro->getDescription());

					// SEGUNDA FILA
					++$currentRow;
					$s1->setCellValue('A'.$currentRow, '05 ');
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha_hora)));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$alumno = $registro->matricula->alumno;
					$s1->setCellValue('E'.$currentRow, mb_strtoupper($alumno->apellido_paterno.' '.$alumno->apellido_materno, 'utf-8').', BV '.$registro->getCurrentSerie().'-'.intval($registro->getNumero()));
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'S');

					$s1->setCellValue('J'.$currentRow, '704103');
					$s1->setCellValue('K'.$currentRow, '70966381');
					$s1->setCellValue('L'.$currentRow, '300');
					$s1->setCellValue('M'.$currentRow, 'H');
					$s1->setCellValue('N'.$currentRow, number_format($registro->monto, 2));

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getCurrentSerie().'-'.intval($registro->getNumero()));
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha_hora)));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha_hora)));

					$s1->setCellValue('V'.$currentRow, $registro->getDescription());
				// ANULADO
				}else{
					++$currentRow;
					$s1->setCellValue('A'.$currentRow, '05 ');
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha_hora)));
					$s1->setCellValue('D'.$currentRow, 'MN');
			
					$s1->setCellValue('E'.$currentRow, 'ANULADO BV '.$registro->getCurrentSerie().'-'.intval($registro->getNumero()));
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'N');

					$s1->setCellValue('J'.$currentRow, '121201');
					$s1->setCellValue('K'.$currentRow, '0001 ');
					$s1->setCellValue('L'.$currentRow, '');
					$s1->setCellValue('M'.$currentRow, 'D');
					$s1->setCellValue('N'.$currentRow, '0');

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getCurrentSerie().'-'.intval($registro->getNumero()));
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha_hora)));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha_hora)));

					$s1->setCellValue('V'.$currentRow, 'ANULADO BV '.$registro->getCurrentSerie().'-'.intval($registro->getNumero()));
				}
				
			}*/

			/** BOLETAS **/
			if($registro instanceOf Boleta){
				$subcategoria = $registro->getSubcategoria();
				if(!$subcategoria) continue;

				// ACTIVOS
				if($registro->estado == 'ACTIVO'){
					// PRIMERA FILA
					++$currentRow;
					$s1->setCellValue('A'.$currentRow, '05 ');
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$apellidos = explode(' ', $registro->nombre);
					$s1->setCellValue('E'.$currentRow, mb_strtoupper($apellidos[0].' '.$apellidos[1], 'utf-8').', BV '.$registro->getCurrentSerie().'-'.$registro->numero);
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'S');

					$s1->setCellValue('J'.$currentRow, '121201');
					$s1->setCellValue('K'.$currentRow, '70966381');
					$s1->setCellValue('L'.$currentRow, '');
					$s1->setCellValue('M'.$currentRow, 'D');
					$s1->setCellValue('N'.$currentRow, number_format($registro->getMontoTotal(), 2));

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getCurrentSerie().'-'.$registro->numero);
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));

					$s1->setCellValue('V'.$currentRow, $subcategoria->nombre);

					// SEGUNDA FILA
					if($subcategoria->concar_igv  == 'NO'){
						++$currentRow;
						$s1->setCellValue('A'.$currentRow, '05 ');
						$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
						$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
						$s1->setCellValue('D'.$currentRow, 'MN');
						$apellidos = explode(' ', $registro->nombre);
						$s1->setCellValue('E'.$currentRow, mb_strtoupper($apellidos[0].' '.$apellidos[1], 'utf-8').', BV '.$registro->getCurrentSerie().'-'.$registro->numero);
						$s1->setCellValue('F'.$currentRow, 0);
						$s1->setCellValue('G'.$currentRow, 'V');
						$s1->setCellValue('H'.$currentRow, 'S');

						$s1->setCellValue('J'.$currentRow, $subcategoria->concar_cuenta);
						$s1->setCellValue('K'.$currentRow, '70966381');
						$s1->setCellValue('L'.$currentRow, '300');
						$s1->setCellValue('M'.$currentRow, 'H');
						$s1->setCellValue('N'.$currentRow, number_format($registro->getMontoTotal(), 2));

						$s1->setCellValue('Q'.$currentRow, 'BV');
						$s1->setCellValue('R'.$currentRow, $registro->getCurrentSerie().'-'.$registro->numero);
						$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
						$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));

						$s1->setCellValue('V'.$currentRow, $subcategoria->nombre);
					}

					// SEGUNDA FILA
					if($subcategoria->concar_igv  == 'SI'){
						++$currentRow;
						$igv = $registro->getMontoTotal() * 18 / 100;
						$importe = $registro->getMontoTotal() - $igv;

						$s1->setCellValue('A'.$currentRow, '05 ');
						$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
						$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
						$s1->setCellValue('D'.$currentRow, 'MN');
						$apellidos = explode(' ', $registro->nombre);
						$s1->setCellValue('E'.$currentRow, mb_strtoupper($apellidos[0].' '.$apellidos[1], 'utf-8').', BV '.$registro->getCurrentSerie().'-'.$registro->numero);
						$s1->setCellValue('F'.$currentRow, 0);
						$s1->setCellValue('G'.$currentRow, 'V');
						$s1->setCellValue('H'.$currentRow, 'S');

						$s1->setCellValue('J'.$currentRow, '401111');
						$s1->setCellValue('K'.$currentRow, '70966381');
						$s1->setCellValue('L'.$currentRow, '');
						$s1->setCellValue('M'.$currentRow, 'H');
						$s1->setCellValue('N'.$currentRow, number_format($igv, 2));

						$s1->setCellValue('Q'.$currentRow, 'BV');
						$s1->setCellValue('R'.$currentRow, $registro->getCurrentSerie().'-'.$registro->numero);
						$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
						$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));

						$s1->setCellValue('V'.$currentRow, $subcategoria->nombre);

						// TERCERA FILA
						++$currentRow;
						$s1->setCellValue('A'.$currentRow, '05 ');
						$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
						$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
						$s1->setCellValue('D'.$currentRow, 'MN');
						$apellidos = explode(' ', $registro->nombre);
						$s1->setCellValue('E'.$currentRow, mb_strtoupper($apellidos[0].' '.$apellidos[1], 'utf-8').', BV '.$registro->getCurrentSerie().'-'.$registro->numero);
						$s1->setCellValue('F'.$currentRow, 0);
						$s1->setCellValue('G'.$currentRow, 'V');
						$s1->setCellValue('H'.$currentRow, 'S');

						$s1->setCellValue('J'.$currentRow, $subcategoria->concar_cuenta);
						$s1->setCellValue('K'.$currentRow, '70966381');
						$s1->setCellValue('L'.$currentRow, '300');
						$s1->setCellValue('M'.$currentRow, 'H');
						$s1->setCellValue('N'.$currentRow, number_format($importe, 2));

						$s1->setCellValue('Q'.$currentRow, 'BV');
						$s1->setCellValue('R'.$currentRow, $registro->getCurrentSerie().'-'.$registro->numero);
						$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
						$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));

						$s1->setCellValue('V'.$currentRow, $subcategoria->nombre);
					}

				// ANULADOS
				}else{
					++$currentRow;
					$s1->setCellValue('A'.$currentRow, '05 ');
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$apellidos = explode(' ', $registro->nombre);
					$s1->setCellValue('E'.$currentRow, 'ANULADO BV '.$registro->getCurrentSerie().'-'.$registro->numero);
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'N');

					$s1->setCellValue('J'.$currentRow, '121201');
					$s1->setCellValue('K'.$currentRow, '0001 ');
					$s1->setCellValue('L'.$currentRow, '');
					$s1->setCellValue('M'.$currentRow, 'D');
					$s1->setCellValue('N'.$currentRow, '0');

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getCurrentSerie().'-'.$registro->numero);
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha)));

					$s1->setCellValue('V'.$currentRow, 'ANULADO BV '.$registro->getCurrentSerie().'-'.$registro->numero);
				}
				
			}

			++$autoNumeric;
			
		}

		$normalStyle = array(
		    'font'  => array(
		        //'bold'  => true,
		        'color' => array('rgb' => '000000'),
		        'size'  => 10,
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

	    if($currentRow > 2) $s1->getStyle('A2:AJ'.($currentRow))->applyFromArray($normalStyle);

		writeExcel($excel);
	}
}
