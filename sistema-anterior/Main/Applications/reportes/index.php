<?php
class ReportesApplication extends Core\Application{

	function index($r){
		$this->crystal->load('Form:*');
		$form = new Form(null, $this->COLEGIO->searchGrupoForm());
		$costos = $this->COLEGIO->getCostos();
		$personal = $this->COLEGIO->getPersonal();
		$canchas = Cancha::all();
		$this->render(array('FORM' => $form, 'costos' => $costos, 'personal' => $personal, 'canchas' => $canchas));
	}

	function setLogo($pdf){
		//$pdf->image('./Static/Image/Insignias/'.$this->COLEGIO->login_insignia, 10, 4, 23, 17);
	}

	function estadisticas_notas_bloques(){
		set_time_limit(0);
		$bloque = Bloque::find($this->get->bloque_id);
		$cursos = $bloque->cursos;

		$grupos = $this->COLEGIO->getGrupos($this->get->anio);

		$this->render(array('bloque' => $bloque, 'cursos' => $cursos, 'grupos' => $grupos));
	}
		
	function estadisticas_notas_bloques_pdf_imagex($data, $timestamp, $rotation = 0){
		set_time_limit(0);
		

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
		$myPicture->drawLegend(590, 20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

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

    function estadisticas_notas_bloques_pdf_image($data, $timestamp, $rotation = 0){
    	$this->crystal->load('jpgraph:jpgraph');
		$this->crystal->load('jpgraph:jpgraph_bar');

		$data1y= $data[1];
		$data2y= $data[2];
		//$data3y=array(115,50,70,93);

		$labels = $data['cursos'];

		// Create the graph. These two calls are always required
		$graph = new Graph(700, 400,'auto');
		$graph->SetScale("textlin");
		

		$theme_class = new UniversalTheme;
		$graph->SetTheme($theme_class);

		//$graph->yaxis->SetTickPositions(array(0,30,60,90,120,150), array(15,45,75,105,135));
		$graph->SetBox(false);

		$graph->ygrid->SetFill(false);
		$graph->xaxis->SetTickLabels($labels);
		$graph->yaxis->HideLine(false);
		$graph->yaxis->HideTicks(false,false);

		// Create the bar plots
		$b1plot = new BarPlot($data1y);
		$b2plot = new BarPlot($data2y);
		//$b3plot = new BarPlot($data3y);

		// Create the grouped bar plot
		$gbplot = new GroupBarPlot(array($b1plot,$b2plot));
		// ...and add it to the graPH
		$graph->Add($gbplot);


		$b1plot->SetColor("white");
		$b1plot->SetFillColor("#cc1111");

		$b2plot->SetColor("white");
		$b2plot->SetFillColor("#11cccc");

		//$b3plot->SetColor("white");
		//$b3plot->SetFillColor("#1111cc");

		$graph->title->Set("Comparativo");

		// Display the graph
		$graph->Stroke("./Static/Temp/".$timestamp.".png");
    }

    function estadisticas_notas_bloques_pdf(){
    	set_time_limit(0);
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

					//$sortedMatricula['promedio'] = round($promedio);
					$sortedMatricula['promedio'] = round($promedio, 2);
					$sortedMatriculas[] = $sortedMatricula;

					// GUARDA EL PROMEDIO PARA EL FINAL DE AULA
					$promedioBloque = $bloque->puntaje(-1, $grupo->id, $nro, $promedio);
				}

				// END SORT DATA
				$pdf->addPage('P');
				$pdf->SetFont('helvetica', 'b', 13);
				$pdf->setFillColor(240, 240, 240);
				$this->setLogo($pdf);
				$pdf->cell(0, 5,'CUADRO DE DATOS - BLOQUE '.$nro.' - BIMESTRE '.$this->get->ciclo, 0, 1,'R');
				$pdf->SetFont('helvetica', '', 9);
				$pdf->cell(0, 5, 'BLOQUE - '.$bloque->nombre, 0, 1,'R');
				$pdf->ln(15);
				$pdf->SetFont('helvetica', '', 10);


				$pdf->cell(40, 7, 'TUTOR', 1, 0, 'C', 1);
				$pdf->cell(0, 7, !is_null($grupo->tutor) ? $grupo->tutor->getFullName() : '-', 1, 0, 'C', 0, 0, 1);
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
						$pdf->cell(($totalWidth - $nameWidth)/ ($bloqueCursosTotal + 1), 7, !empty($bc->curso->abreviatura) ? $bc->curso->abreviatura : substr($bc->curso->nombre, 0, 4), 1, 0, 'C', 1, 0, 1);
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
				$pdf->cell(($totalWidth - $nameWidth) / ($bloqueCursosTotal + 1), 7, round($promedioBloque, 2), 1, 0, 'C', 1, 0, 1);
			}

			//file_put_contents('x.txt', print_r($graphCursosData, true));

			// GRAFICO
			
			$pdf->addPage('L');
			$pdf->SetFont('helvetica', 'b', 13);
			$this->setLogo($pdf);
			$pdf->cell(0, 5,'COMPARATIVO ENTRE BLOQUES 1 Y 2'.' - BIMESTRE '.$this->get->ciclo, 0, 1,'R');
			$pdf->SetFont('helvetica', '', 8);
			$pdf->cell(0, 5, $grupo->getNombreShort(), 0, 1,'R');
			$pdf->ln(15);
			$pdf->SetFont('helvetica', '', 8);

			//echo '<pre>';
			//print_r($graphCursosData);
			//echo '</pre>';
			$timestamp = getToken();

			$this->estadisticas_notas_bloques_pdf_image($graphCursosData, $timestamp, 45);
			$pdf->image('./Static/Temp/'.$timestamp.'.png', 10, 25, 275, 150);
			@unlink('./Static/Temp/'.$timestamp.'.png');
			

			$graphGruposData['cursos'][] = $grupo->getNombreShort3();

			$promedioBloque1 = $bloque->puntaje(-1, $grupo->id, 1);
			$promedioBloque1 = round(count($matriculas) > 0 ? ($promedioBloque1->total / count($matriculas)) : 0);
			$promedioBloque2 = $bloque->puntaje(-1, $grupo->id, 2);
			$promedioBloque2 = round(count($matriculas) > 0 ? ($promedioBloque2->total / count($matriculas)) : 0);

			$graphGruposData[1][] = $promedioBloque1;
			$graphGruposData[2][] = $promedioBloque2;
		}
		/*
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
		*/
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
		$totalGeneralHombres = 0;
		$totalGeneralMujeres = 0;
		foreach($niveles As $nivel){
			$pdf->SetFont('helvetica', 'b', 12);
			$pdf->cell(100, 10, strtoupper($nivel->nombre), 0, 1, 'L', 0);
			$pdf->SetFont('helvetica', '', 10);
			$pdf->cell(100, 5, 'GRUPO', 1, 0, 'C', 1);
			$pdf->cell(30, 5, 'HOMBRES', 1, 0, 'C', 1);
			$pdf->cell(30, 5, 'MUJERES', 1, 0, 'C', 1);
			$pdf->cell(30, 5, 'TOTAL', 1, 0, 'C', 1);

			$pdf->ln(5);
			$total = 0;
			$total_hombres = 0;
			$total_mujeres = 0;

			foreach($grupos As $grupo){
				if($grupo->nivel_id != $nivel->id) continue;
				$matriculas = count($grupo->getMatriculas());
				$pdf->cell(100, 5, $grupo->getNombre(), 1, 0, 'L', 0, 0, 1);
				$pdf->cell(30, 5, $grupo->getTotalGenero(0), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(30, 5, $grupo->getTotalGenero(1), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(30, 5, $matriculas, 1, 0, 'C', 0, 0, 1);
				$pdf->ln(5);
				$total_hombres += $grupo->getTotalGenero(0);
				$total_mujeres += $grupo->getTotalGenero(1);
				$total += $matriculas;
			}
			$pdf->cell(70);
			$pdf->cell(30, 5, 'TOTAL', 1, 0, 'C', 1);
			$pdf->cell(30, 5, $total_hombres, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, $total_mujeres, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, $total, 1, 0, 'C', 0, 0, 1);
			$pdf->ln(5);
			$totalGeneralHombres += $total_hombres;
			$totalGeneralMujeres += $total_mujeres;

			$totalGeneral += $total;
		}

		$pdf->ln(5);
		$pdf->cell(100, 5, 'TOTAL GENERAL', 1, 0, 'C', 1);
		$pdf->cell(30, 5, $totalGeneralHombres, 1, 0, 'C', 0, 0, 1);
		$pdf->cell(30, 5, $totalGeneralMujeres, 1, 0, 'C', 0, 0, 1);
		$pdf->cell(30, 5, $totalGeneral, 1, 0, 'C', 0, 0, 1);

		$pdf->output();
	}

	function alumnos_costo(){
		$this->crystal->load('TCPDF');
		$costo = Costo::find($this->get->costo_id);
		$matriculas = Matricula::all(Array(
			'conditions' => 'matriculas.colegio_id="'.$this->COLEGIO->id.'" AND costo_id="'.$this->get->costo_id.'" AND grupos.nivel_id="'.$this->get->nivel_id.'" AND grupos.anio="'.$this->get->anio.'"',
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
		$asignatura = Asignatura::find([
			'conditions' => ['sha1(id) = ?', $this->get->id]
		]);
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

		$grupo = Grupo::find($this->params->id);
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

	function objetivos_matriculados(){
		$anio = $this->COLEGIO->anio_activo;

		$matriculas = Matricula::all([
			'conditions' => 'grupos.anio = "'.$anio.'" AND estado = 0',
			'joins' => ['grupo', 'alumno'],
			'order' => 'grupos.nivel_id ASC, grupos.grado ASC, grupos.seccion ASC, alumnos.apellido_paterno ASC'
		]);

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
		$pdf->AddPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(220, 220, 220);
		$pdf->cell(0,10,'LISTA DE ALUMNOS - '.$anio,0,0,'R');
		$pdf->ln(17);
		$pdf->setFont('Helvetica','b',10);
		$pdf->cell(10,5,'Nº',1,0,'C', 1);
		$pdf->cell(80,5,'Apellidos y Nombres',1,0,'C', 1);
		$pdf->cell(60,5,'Grado / Sección',1,0,'C', 1);
		$pdf->cell(50,5,'Estado',1,0,'C', 1);
		


		$pdf->ln(5);
		$pdf->setFont('Helvetica','',9);
		$i = 1;
		$hombres = 0;
		$mujeres = 0;
		foreach($matriculas As $matricula){
			$alumno = $matricula->alumno;
			

			$pdf->cell(10,5,$i,1,0,'C');
			$pdf->cell(80,5, $alumno->getFullName(),1,0,'L', 0, '', 1);
			$pdf->cell(60,5, $matricula->grupo->getNombreShort2(),1,0, 'C', 0, '', 1);
			$pdf->cell(50,5, $matricula->isNuevo() ? 'NUEVO' : 'RATIFICACIÓN',1,0,'C', 0, '', 1);
			$pdf->ln(5);
			$i++;
		}

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
		$pdf->cell(57,5,'Apellidos y Nombres',1,0,'C', 1);
		$pdf->cell(17,5,'Nº Doc.',1,0,'C', 1);
        $pdf->cell(57,5,'Apoderado',1,0,'C', 1);
        $pdf->cell(17,5,'Nº Doc.',1,0,'C', 1);
		$pdf->cell(17,5,'Sexo',1,0,'C', 1);
		$pdf->cell(16,5,'Fec.Nac.',1,0,'C', 1);
		
		$pdf->cell(10,5,'Edad',1,0,'C', 1);
		$pdf->cell(35,5,'Teléfono',1,0,'C', 1);
		$pdf->cell(40,5,'Email',1,0,'C', 1);
		//$pdf->cell(40,5,'Dirección',1,0,'C', 1);


		$pdf->ln(5);
		$pdf->setFont('Helvetica','',9);
		$i = 1;
		$hombres = 0;
		$mujeres = 0;
		foreach($matriculas As $matricula){
			$alumno = $matricula->alumno;
			$domicilio = $alumno->getDomicilio();
			$apoderado = $alumno->getFirstApoderado();

			$pdf->cell(7,5,$i,1,0,'C');
			$pdf->cell(57,5, $alumno->getFullName(),1,0,'L', 0, '', 1);
			$pdf->cell(17,5, $alumno->nro_documento,1,0,'C',  0, '', 1);
            $pdf->cell(57,5, !is_null($apoderado) ? $apoderado->getFullName() : '',1,0,'L', 0, '', 1);
            $pdf->cell(17,5, !is_null($apoderado) ? $apoderado->nro_documento : '',1,0,'C',  0, '', 1);
			$pdf->cell(17,5, $alumno->getSexo(),1,0,'C',  0, '', 1);
			$pdf->cell(16,5, $alumno->fecha_nacimiento,1,0,'C',  0, '', 1);
			
			$pdf->cell(10,5, $alumno->getEdad(),1,0,'C',  0, '', 1);

			$apoderado = $matricula->alumno->getFirstApoderado();
			$telefonos = array();
			if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
			if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

			$pdf->cell(35, 5, implode(' - ', $telefonos), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(40, 5, $apoderado->email, 1, 0, 'C', 0, 0, 1);
			//$pdf->cell(40, 5, $apoderado->direccion, 1, 0, 'C', 0, 0, 1);

			//$pdf->cell(20,5, $apoderado->telefono_fijo,1,0,'C',  0, '', 1);
			//$direccion = reset($domicilio);

			//$pdf->cell(50,5, !$direccion ? '' : $direccion['direccion'],1,0,'C', 0, '', 1);

			//$pdf->cell(42,5,$alumno->getFirstApoderado()->direccion,1,0,'C', 0, '', 1);
			$pdf->ln(5);
			if($alumno->sexo == 0) $hombres++;
			if($alumno->sexo == 1) $mujeres++;
			$i++;
		}

		$pdf->ln(5);
		$pdf->cell(30,5,'Hombres',1,0,'C', 1);
		$pdf->cell(30, 5, $hombres, 1, 0, 'C', 0, 0, 1);

		$pdf->cell(30,5,'Mujeres',1,0,'C', 1);
		$pdf->cell(30, 5, $mujeres, 1, 0, 'C', 0, 0, 1);

		$pdf->cell(30,5,'Total',1,0,'C', 1);
		$pdf->cell(30, 5, count($matriculas), 1, 0, 'C', 0, 0, 1);

		$pdf->output('asistencia.pdf','I');
	}

	function ranking_notas(){
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
		$pdf->AddPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(220, 220, 220);
		$pdf->cell(0,5,'RANKING DE NOTAS - BIMESTRE '.$this->get->ciclo,0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, $grupo->getNombre(),0, 0, 'R');
		$pdf->ln(17);
		$pdf->setFont('Helvetica','b',10);

		$pdf->cell(80,5,'Apellidos y Nombres',1,0,'C', 1, 0, 1);
		$pdf->cell(40,5,'Puntaje',1,0,'C', 1, 0, 1);
		$pdf->cell(40,5,'Promedio',1,0,'C', 1, 0, 1);
		$pdf->cell(30,5,'Puesto',1,0,'C', 1, 0, 1);



		$pdf->ln(5);
		$pdf->setFont('Helvetica','',9);
		$i = 1;
		$ciclo = $this->get->ciclo;
		usort($matriculas, function($a, $b) use ($ciclo){
			$a = $a->getOrdenMeritoCiclo($ciclo);
			$b = $b->getOrdenMeritoCiclo($ciclo);

			if ($a == $b) {
		        return 0;
		    }
		    return ($a < $b) ? -1 : 1;
		});

		foreach($matriculas As $matricula){
			$alumno = $matricula->alumno;

			$orden = $matricula->getOrdenMeritoCiclo($this->get->ciclo);
			$puntaje = $matricula->getPuntajeCiclo($this->get->ciclo);
			$promedio = $matricula->getPromedioCiclo($this->get->ciclo);


			$pdf->cell(80,5, $alumno->getFullName(),1,0,'L', 0, '', 1);
			$pdf->cell(40,5, $puntaje,1, 0,'C', 0, '', 1);
			$pdf->cell(40,5, $promedio,1, 0,'C', 0, '', 1);
			$pdf->cell(30,5, $orden,1, 0,'C', 0, '', 1);


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
			'conditions' => 'pagos.colegio_id="'.$this->COLEGIO->id.'" AND pagos.estado="ACTIVO" AND DATE(fecha_cancelado) BETWEEN DATE("'.$fecha1.'") AND DATE("'.$fecha2.'")',
			'order' => 'fecha_cancelado ASC',
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
		$pdf->cell(35, 5, 'FECHA PAGO', 1, 0, 'C', 1);
		$total_ingresos = 0;
		foreach($ingresos As $ingreso){
			if($ingreso->matricula->isOculto()) continue;

			$alumno = $ingreso->matricula->alumno;
			$impresion = $ingreso->getActiveImpresion(false); // IMPRESION
			$pdf->ln(5);

			$pdf->cell(30, 5, $impresion ? $impresion->getSerieNumero() : '-', 1, 0, 'C', 0);
			$pdf->cell(20, 5, number_format($ingreso->monto + $ingreso->mora, 2), 1, 0, 'C', 0);
			$pdf->cell(42, 5, $ingreso->getTipoDescription(), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(73, 5, $alumno->getFullName(), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(35, 5, date('Y-m-d', strtotime($ingreso->fecha_cancelado)), 1, 0, 'C', 0, 0, 1);
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
		$dataPorcentaje = array();

		foreach($optionsPago As $key => $option){
			$total_neto = 0;
			$total_pagado = 0;

			foreach($matriculas As $matricula){
				if($matricula->isOculto()) continue;
			
				$total_neto += $matricula->costo->pension;
				$total_pagos = Pago::find(array(
					'select' => 'SUM(monto) As monto_total',
					'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND pagos.estado_pago="CANCELADO" AND pagos.estado="ACTIVO" AND pagos.matricula_id="'.$matricula->id.'" AND pagos.tipo="1" AND pagos.nro_pago="'.$key.'" AND YEAR(pagos.fecha_cancelado)="'.$this->get->anio.'" AND DATE(pagos.fecha_cancelado) <= DATE("'.date('Y-m-d', strtotime($this->get->fecha)).'")',
					'joins' => array('matricula')
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
				'c' => ($total_pagado > $total_neto) ? 0 : $total_neto - $total_pagado,

			);
			
			$dataPorcentaje[] = array(
				'mes' => $option,
				'a' => $total_neto,
				'b' => round($total_pagado * 100 / $total_neto, 0),
				'c' => ($total_pagado > $total_neto) ? 0 : round(($total_neto - $total_pagado) * 100 / $total_neto, 0)
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
		foreach($data As $key => $d){
			$pdf->ln(5);
			$pdf->cell(5);
			$pdf->cell(50, 5, $d['mes'], 1, 0, 'C', 0);
			$pdf->cell(75, 5, number_format($d['a'], 2), 1, 0, 'C', 0);
			$pdf->cell(75, 5, number_format($d['b'], 2).' ('.$dataPorcentaje[$key]['b'].'%)', 1, 0, 'C', 0);
			$pdf->cell(75, 5, number_format($d['c'], 2).' ('.$dataPorcentaje[$key]['c'].'%)', 1, 0, 'C', 0);
		}
		$pdf->output();
		//$this->render(array('data' => $data));
	}

	function alumnos_deudores_excel(){
		$this->crystal->load('PHPExcel');

		$excel = PHPExcel_IOFactory::load('./Static/Templates/alumnos_deudores.xlsx');
		$s1 = $excel->getSheet(0);

		$options = $this->COLEGIO->getOptionsNroPago();
		if($this->get->nro_pago >= 0){
			$s1->setCellValue('A2', ($this->get->nro_pago == 0 ? 'Matrícula' : 'Mensualidad '.$options[$this->get->nro_pago]).' - '.$this->get->anio);
		}else{
			$s1->setCellValue('A2', 'Todo el año');
		}

		$grupos = $this->COLEGIO->getGrupos($this->get->anio);
		$total = 0;
		$numero = 0;
		$currentRow = 5;
		foreach($grupos As $grupo){
			$matriculas = $grupo->getMatriculas();
			foreach($matriculas As $key_matricula => $matricula){
				if($matricula->isOculto()) continue;
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

					//$pdf->ln(5);
					//$pdf->cell(10, 5, $numero, 1, 0, 'C', 0, 0, 1);
					$s1->setCellValue('A'.$currentRow, $matricula->alumno->nro_documento);
					$s1->setCellValue('B'.$currentRow, $matricula->alumno->getFullName());
					$s1->setCellValue('C'.$currentRow, $matricula->grupo->nivel->nombre.' - '.$matricula->grupo->getGrado().' '.$matricula->grupo->seccion);


					if($tipo_pago == 0){
						$s1->setCellValue('D'.$currentRow, 'Matrícula');
						$s1->setCellValue('E'.$currentRow, number_format($matricula->costo->matricula, 2));


						$total += ($matricula->costo->matricula - $total_pagos->monto_total);
					}else{
						$s1->setCellValue('D'.$currentRow, $this->COLEGIO->getCicloPensionesSingle($x));
						$s1->setCellValue('E'.$currentRow, number_format($matricula->costo->pension, 2));

						$total += ($matricula->costo->pension - $total_pagos->monto_total);
					}


					++$numero;

					$s1->setCellValue('F'.$currentRow, number_format($total_pagos->monto_total, 2));



					$apoderado = $matricula->alumno->getFirstApoderado();
					$telefonos = array();
					if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
					if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

					$s1->setCellValue('G'.$currentRow, implode(' - ', $telefonos));
					$s1->setCellValue('H'.$currentRow, $apoderado->email);
					$s1->setCellValue('I'.$currentRow, $apoderado->direccion);
					++$currentRow;
				}
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

	    if($currentRow > 5){
	    	$s1->getStyle('A5:I'.$currentRow)->applyFromArray($normalStyle);
	    	//$s1->setCellValue('A'.$currentRow, 'REGISTROS');
	    	//$s1->setCellValue('B'.$currentRow, $numero);
	    	$s1->setCellValue('D'.$currentRow, 'TOTAL');
	    	$s1->setCellValue('E'.$currentRow, number_format($total, 2));
	    }

		writeExcel($excel);
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
		$pdf->ln(15);
		$pdf->setFont('helvetica', 'b', 9);
		//$pdf->cell(10, 5, 'Nº', 1, 0, 'C', 1);
		$pdf->cell(60, 5, 'ALUMNO', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'GRUPO', 1, 0, 'C', 1);
		$pdf->cell(15, 5, 'DNI', 1, 0, 'C', 1);
		$pdf->cell(35, 5, 'CONCEPTO', 1, 0, 'C', 1);
		$pdf->cell(15, 5, 'MONTO', 1, 0, 'C', 1);

		$pdf->cell(35, 5, 'TELF. APODERADO', 1, 0, 'C', 1);
		$pdf->cell(45, 5, 'EMAIL APODERADO', 1, 0, 'C', 1);
		$pdf->cell(60, 5, 'DIRECCIÓN', 1, 0, 'C', 1);
		$pdf->setFont('helvetica', '', 8);

		$total = 0;
		$numero = 0;
		//$alumnos = Alumno::find_all_by_colegio_id($this->COLEGIO->id);
		//foreach($alumnos As $alumno){
	    	// boletas
			/*
	    	$boletas = Boleta::all(array(
				'conditions' => '
					estado = "ACTIVO" 
					AND tipo = "ALUMNO"
					AND dni = "'.$alumno->nro_documento.'"
					AND (estado_pago = "PENDIENTE" AND YEAR(fecha) = "'.$this->get->anio.'")'
			)); 

			foreach($boletas As $boleta){
				++$numero;
				$pdf->ln(5);
				$pdf->cell(60, 5, $alumno->getFullName(), 1, 0, 'C', 0);
				$pdf->cell(15, 5, $alumno->nro_documento, 1, 0, 'C', 0);
				$pdf->cell(45, 5, 'VENTA', 1, 0, 'C', 0);
				$pdf->cell(25, 5, $boleta->getMontoTotal(), 1, 0, 'C', 0);
		

				$apoderado = $alumno->getFirstApoderado();
				$telefonos = array();
				if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
				if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

				$pdf->cell(35, 5, implode(' - ', $telefonos), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(45, 5, $apoderado->email, 1, 0, 'C', 0, 0, 1);
				$pdf->cell(60, 5, $apoderado->direccion, 1, 0, 'C', 0, 0, 1);
				
		    	$total += $boleta->getMontoTotal();
		    	++$currentRow;
			}
			*/
	    	// pensiones
	    	$conditions = '';
	    	if($this->get->nro_pago != ''){
	    		if($this->get->nro_pago > 0)
	    			$conditions .= ' AND (tipo = 1 AND nro_pago = "'.$this->get->nro_pago.'")';
	    		if($this->get->nro_pago == 0)
	    			$conditions .= ' AND (tipo = 0 AND nro_pago = 1)';
	    	}

	    	$pagos = Pago::all(array(
				'conditions' => '
					pagos.estado = "ACTIVO" 
					AND matriculas.estado != 2 AND (pagos.estado_pago = "PENDIENTE" AND grupos.anio = "'.$this->get->anio.'")'.$conditions,
	    		'joins' => '
					INNER JOIN matriculas ON matriculas.id = pagos.matricula_id
					INNER JOIN grupos ON grupos.id = matriculas.grupo_id
					INNER JOIN alumnos ON alumnos.id = matriculas.alumno_id
		    	',
		    	'order' => 'grupos.nivel_id ASC, grupos.grado ASC, grupos.seccion ASC'
			));

			foreach($pagos As $pago){
				if($pago->matricula->isOculto()) continue;

				$alumno = $pago->matricula->alumno;
				//$impresion = $pago->getActiveImpresion(false);
				//if(!$impresion) continue;
				++$numero;
				$pdf->ln(5);
		    	$pdf->cell(60, 5, $alumno->getFullName(), 1, 0, 'C', 0, 0, 1);
		    	$pdf->cell(20, 5, $pago->matricula->grupo->getNombreShort3(), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(15, 5, $alumno->nro_documento, 1, 0, 'C', 0);
				$pdf->cell(35, 5, $pago->getDescription(), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(15, 5, $pago->monto, 1, 0, 'C', 0);
		

				$apoderado = $alumno->getFirstApoderado();
				$telefonos = array();
				if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
				if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

				$pdf->cell(35, 5, implode(' - ', $telefonos), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(45, 5, $apoderado->email, 1, 0, 'C', 0, 0, 1);
				$pdf->cell(60, 5, $apoderado->direccion, 1, 0, 'C', 0, 0, 1);


		    	$total += $pago->monto;
		    	++$currentRow;
			}
	    //}

	    $pdf->ln(5);
		
		$pdf->cell(60, 5, "REGISTROS", 1, 0, 'C', 1);
		$pdf->cell(20, 5, $numero, 1, 0, 'C', 0);
		$pdf->cell(50, 5, "TOTAL", 1, 0, 'C', 1);
		$pdf->cell(15, 5, number_format($total, 2), 1, 0, 'C', 0, 0, 1);


	    $pdf->output();
	}

	function alumnos_deudoresxx(){
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
		$totalp = 0;
		foreach($grupos As $grupo){
			$matriculas = $grupo->getMatriculas();
			foreach($matriculas As $key_matricula => $matricula){
				for($x = 0; $x<=$this->COLEGIO->total_pensiones; $x++){
					if($this->get->nro_pago != -1 && $x != $this->get->nro_pago) continue;
					if($matricula->estado != 0) continue;
					//if($x == 0) continue; // no cuenta matriculas
					if($x == 0){
						$tipo_pago = 0;
						$nro_pago = 1;
					}else{
						$tipo_pago = 1;
						$nro_pago = $x;
					}


					$total_pagos = Pago::find(array(
						'select' => 'SUM(monto) As monto_total',
						'conditions' => 'pagos.estado_pago="CANCELADO" AND pagos.estado="ACTIVO" AND pagos.colegio_id="'.$this->COLEGIO->id.'" AND pagos.matricula_id="'.$matricula->id.'" AND pagos.tipo="'.$tipo_pago.'" AND pagos.nro_pago="'.$nro_pago.'"',
						'joins' => array('matricula')
					));


					//if($tipo_pago == 0 && $total_pagos->monto_total >= $matricula->costo->matricula) continue;
					//if($tipo_pago == 1 && $total_pagos->monto_total >= $matricula->costo->pension) continue;
					// PAGO ALMENOS 1 SOL
					

					//$pago = Pago::find(array(
					//	'conditions' => 'estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="'.$nro_pago.'" AND tipo="'.$tipo_pago.'"'
					//));

					if($pago) continue;
					$monto = $tipo_pago == 0 ? $matricula->costo->matricula : $matricula->costo->pension;
					if($monto <= 0) continue; // becado
					if($total_pagos->monto_total >= $monto) continue;
					$pdf->ln(5);
					//$pdf->cell(10, 5, $numero, 1, 0, 'C', 0, 0, 1);
					$pdf->cell(60, 5, $matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
					$pdf->cell(30, 5, $matricula->grupo->nivel->nombre.' - '.$matricula->grupo->getGrado().' '.$matricula->grupo->seccion, 1, 0, 'C', 0, 0, 1);

					if($tipo_pago == 0){
						$pdf->cell(20, 5, 'Matrícula', 1, 0, 'C', 0, 0, 1);
						$pdf->cell(25, 5, number_format($matricula->costo->matricula, 2), 1, 0, 'C', 0);

						$total += $matricula->costo->matricula; 
					}else{
						$pdf->cell(20, 5, $this->COLEGIO->getCicloPensionesSingle($x), 1, 0, 'C', 0, 0, 1);
						$pdf->cell(25, 5, number_format($matricula->costo->pension, 2), 1, 0, 'C', 0);

						$total += $matricula->costo->pension;
					}


					++$numero;
					$pdf->cell(25, 5, number_format($total_pagos->monto_total, 2), 1, 0, 'C', 0);
					$totalp += $total_pagos->monto_total;
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
		$pdf->cell(30);
		$pdf->cell(30, 5, "REGISTROS", 1, 0, 'C', 1);
		$pdf->cell(30, 5, $numero, 1, 0, 'C', 0);
		$pdf->cell(20, 5, "TOTAL", 1, 0, 'C', 1);
		$pdf->cell(25, 5, number_format($total, 2), 1, 0, 'C', 0);
		$pdf->cell(25, 5, number_format($totalp, 2), 1, 0, 'C', 0);

		$pdf->output();
	}


	function alumnos_pagadores(){
		$this->crystal->load('PHPExcel');

		$excel = PHPExcel_IOFactory::load('./Static/Templates/alumnos_deudores.xlsx');
		$s1 = $excel->getSheet(0);

		$options = $this->COLEGIO->getOptionsNroPago();
		$s1->setCellValue('A1', 'Alumnos Pagadores');
		if($this->get->nro_pago >= 0){
			$s1->setCellValue('A2', ($this->get->nro_pago == 0 ? 'Matrícula' : 'Mensualidad '.$options[$this->get->nro_pago]).' - '.$this->get->anio);
		}else{
			$s1->setCellValue('A2', 'Todo el año');
		}

		$matriculas = Matricula::all(array(
			'conditions' => 'matriculas.colegio_id="'.$this->COLEGIO->id.'" AND estado="0" AND grupos.anio="'.$this->get->anio.'"',
			'joins' => array('grupo', 'alumno'),
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		));

		$total = 0;
		$totalWithMora = 0;
		$numero = 0;
		$currentRow = 5;
		foreach($matriculas As $key_matricula => $matricula){
			//isset($matricula->alumno)
            if($matricula->isOculto()) continue;

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

				//if($tipo_pago == 0 && $total_pagos->monto_total < $matricula->costo->matricula) continue;
				//if($tipo_pago == 1 && $total_pagos->monto_total < $matricula->costo->pension) continue;
				if($total_pagos->monto_total <= 0) continue;



				$s1->setCellValue('A'.$currentRow, $matricula->alumno->nro_documento);
				$s1->setCellValue('B'.$currentRow, $matricula->alumno->getFullName());
				$s1->setCellValue('C'.$currentRow, $matricula->grupo->nivel->nombre.' - '.$matricula->grupo->getGrado().' '.$matricula->grupo->seccion);

				if($tipo_pago == 0){
					$s1->setCellValue('D'.$currentRow, 'Matrícula');
					$s1->setCellValue('E'.$currentRow, number_format($matricula->costo->matricula, 2));
					$total += $matricula->costo->matricula;
				}else{
					$s1->setCellValue('D'.$currentRow, $this->COLEGIO->getCicloPensionesSingle($x));
					$s1->setCellValue('E'.$currentRow, number_format($matricula->costo->pension, 2));
					$total += $matricula->costo->pension;
				}

				$totalWithMora += $total_pagos->monto_total;

				++$numero;
				$s1->setCellValue('F'.$currentRow, number_format($total_pagos->monto_total, 2));
				$apoderado = $matricula->alumno->getFirstApoderado();
				$telefonos = array();
				if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
				if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

				$s1->setCellValue('G'.$currentRow, implode(' - ', $telefonos));
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

	    if($currentRow > 5){
	    	$s1->getStyle('A5:I'.$currentRow)->applyFromArray($normalStyle);
	    	//$s1->setCellValue('A'.$currentRow, 'REGISTROS');
	    	//$s1->setCellValue('B'.$currentRow, $numero);
	    	//$s1->setCellValue('D'.$currentRow, 'TOTAL');
	    	//$s1->setCellValue('E'.$currentRow, number_format($total, 2));
	    }

		writeExcel($excel);
	}

	function pagos_comedor2(){
		$this->crystal->load('TCPDF');

		$pagos = Pago_Comedor::all(array(
			'conditions' => 'pagos_comedor_fechas.fecha = "'.$this->get->fecha.'"',
			'joins' => 'INNER JOIN pagos_comedor_fechas ON pagos_comedor_fechas.pago_id = pagos_comedor.id'
		));


		$pdf = new TCPDF();
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);

		$pdf->addPage();
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		$this->setLogo($pdf);
		$pdf->cell(0, 5,'Lista de Alumnos Comedor', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->cell(0,5, $this->get->fecha,0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(100, 5, 'APELLIDOS Y NOMBRES', 1, 0, 'C', 1);
		$pdf->cell(70, 5, 'GRADO - SECCIÓN', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'Nº DOCUMENTO', 1, 0, 'C', 1);
		$pdf->SetFont('helvetica', '', 9);
		foreach($pagos As $pago){
			$matricula = $pago->matricula;
			$alumno = $matricula->alumno;

			$pdf->ln(5);
			$pdf->cell(100, 5, $alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
			$pdf->cell(70, 5, $matricula->grupo->getNombre(), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, $alumno->nro_documento, 1, 0, 'C', 0, 0, 1);
		}
		$pdf->output();
	}


	function pagos_comedor(){
		$this->crystal->load('PHPExcel');

		$excel = PHPExcel_IOFactory::load('./Static/Templates/alumnos_deudores.xlsx');
		$s1 = $excel->getSheet(0);

		$options = $this->COLEGIO->getOptionsNroPago();
		$s1->setCellValue('A1', 'PAGOS COMEDOR');
		$s1->setCellValue('A2', $options[$this->get->nro_pago].' - '.$this->get->anio);


		$matriculas = Matricula::all(array(
			'conditions' => 'matriculas.colegio_id="'.$this->COLEGIO->id.'" AND grupos.anio="'.$this->get->anio.'"',
			'joins' => array('grupo'),
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		));

		$total = 0;
		$numero = 0;
		$currentRow = 5;
		foreach($matriculas As $key_matricula => $matricula){
			//for($x = 0; $x<=$this->COLEGIO->total_pensiones; $x++){
			//	if($this->get->nro_pago != -1 && $x != $this->get->nro_pago) continue;

				$pago = Pago::find(array(
					'conditions' => 'estado_pago="CANCELADO" AND estado = "ACTIVO" AND matricula_id="'.$matricula->id.'" AND tipo="3" AND nro_pago="'.$this->get->nro_pago.'"'
				));
				//echo $matricula->id.' - '.$this->get->nro_pago.'<br />';
				if(!$pago) continue;

				$s1->setCellValue('A'.$currentRow, $matricula->alumno->nro_documento);
				$s1->setCellValue('B'.$currentRow, $matricula->alumno->getFullName());
				$s1->setCellValue('C'.$currentRow, $matricula->grupo->nivel->nombre.' - '.$matricula->grupo->getGrado().' '.$matricula->grupo->seccion);


				$s1->setCellValue('D'.$currentRow, $pago->getDescription());
				$s1->setCellValue('E'.$currentRow, $this->COLEGIO->pago_comedor);
				$total += $this->COLEGIO->pago_comedor;


				$totalWithMora += $pago->getTotal();

				++$numero;
				$s1->setCellValue('F'.$currentRow, number_format($pago->getTotal(), 2));
				$apoderado = $matricula->alumno->getFirstApoderado();
				$telefonos = array();
				if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
				if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

				$s1->setCellValue('G'.$currentRow, implode(' - ', $telefonos));
				$s1->setCellValue('H'.$currentRow, $apoderado->email);
				$s1->setCellValue('I'.$currentRow, $apoderado->direccion);
				++$currentRow;
			//}
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

	    if($currentRow > 5){
	    	$s1->getStyle('A5:I'.$currentRow)->applyFromArray($normalStyle);
	    	//$s1->setCellValue('A'.$currentRow, 'REGISTROS');
	    	//$s1->setCellValue('B'.$currentRow, $numero);
	    	//$s1->setCellValue('D'.$currentRow, 'TOTAL');
	    	//$s1->setCellValue('E'.$currentRow, number_format($total, 2));
	    }

		writeExcel($excel);
	}

	function alumnos_sin_deudas(){
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
		$pdf->cell(0,5,'Alumnos Sin Moras',0,1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->ln(15);
		$pdf->setFont('helvetica', 'b', 9);
		//$pdf->cell(10, 5, 'Nº', 1, 0, 'C', 1);
		$pdf->cell(70, 5, 'ALUMNO', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'GRUPO', 1, 0, 'C', 1);
		$pdf->cell(25, 5, 'DNI', 1, 0, 'C', 1);


		$pdf->cell(35, 5, 'TELF. APODERADO', 1, 0, 'C', 1);
		$pdf->cell(45, 5, 'EMAIL APODERADO', 1, 0, 'C', 1);
		$pdf->cell(70, 5, 'DIRECCIÓN', 1, 0, 'C', 1);
		$pdf->setFont('helvetica', '', 8);

		$total = 0;
		$numero = 0;
		//$alumnos = Alumno::find_all_by_colegio_id($this->COLEGIO->id);
		//foreach($alumnos As $alumno){
	    	// boletas
			
	    	// pensiones
	    	$conditions = '';
	    	/*
	    	$matriculas = Matricula::all(array(
	    		'select' => 'matriculas.*, SUM(pagos.estado_pago) As total_pendientes',
				'conditions' => '
					pagos.estado = "ACTIVO" 
					AND matriculas.estado != 2 AND (pagos.estado_pago = "PENDIENTE" AND grupos.anio = "'.$this->get->anio.'")'.$conditions,
	    		'joins' => '
					INNER JOIN pagos ON pagos.matricula_id = matriculas.id
					INNER JOIN grupos ON grupos.id = matriculas.grupo_id
					INNER JOIN alumnos ON alumnos.id = matriculas.alumno_id
		    	',
		    	'order' => 'grupos.nivel_id ASC, grupos.grado ASC, grupos.seccion ASC',
		    	'group' => 'matriculas.id'
			));*/

			$matriculas = Matricula::all(array(
	    		'select' => 'matriculas.*, SUM(pagos.mora) As total_mora',
				'conditions' => '
					pagos.estado = "ACTIVO" 
					AND matriculas.estado != 2 AND (pagos.nro_movimiento_banco != "" AND pagos.estado_pago = "CANCELADO" AND grupos.anio = "'.$this->get->anio.'")',
	    		'joins' => '
					INNER JOIN pagos ON pagos.matricula_id = matriculas.id
					INNER JOIN grupos ON grupos.id = matriculas.grupo_id
					INNER JOIN alumnos ON alumnos.id = matriculas.alumno_id
		    	',
		    	'order' => 'grupos.nivel_id ASC, grupos.grado ASC, grupos.seccion ASC',
		    	'group' => 'matriculas.id'
			));


			foreach($matriculas As $matricula){
				if($matricula->isOculto()) continue;
				//if($matricula->total_pendientes > 1) continue;
				if($matricula->total_mora > 0) continue;

				$alumno = $matricula->alumno;
				
				++$numero;
				$pdf->ln(5);
		    	$pdf->cell(70, 5, $alumno->getFullName(), 1, 0, 'C', 0, 0, 1);
		    	$pdf->cell(30, 5, $matricula->grupo->getNombreShort3(), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(25, 5, $alumno->nro_documento, 1, 0, 'C', 0);
			
		

				$apoderado = $alumno->getFirstApoderado();
				$telefonos = array();
				if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
				if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

				$pdf->cell(35, 5, implode(' - ', $telefonos), 1, 0, 'C', 0, 0, 1);
				$pdf->cell(45, 5, $apoderado->email, 1, 0, 'C', 0, 0, 1);
				$pdf->cell(70, 5, $apoderado->direccion, 1, 0, 'C', 0, 0, 1);


	
		    	++$currentRow;
			}
	    //}

	    $pdf->ln(5);
		
		$pdf->cell(70, 5, "REGISTROS", 1, 0, 'C', 1);
		$pdf->cell(30, 5, $numero, 1, 0, 'C', 0);



	    $pdf->output();
	}


	function alumnos_pagadores_pdf(){
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
			if($matricula->isOculto()) continue;

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

				//if($tipo_pago == 0 && $total_pagos->monto_total < $matricula->costo->matricula) continue;
				//if($tipo_pago == 1 && $total_pagos->monto_total < $matricula->costo->pension) continue;

				if($total_pagos->monto_total <= 0) continue;

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
		$matricula = Matricula::find([
			'conditions' => ['sha1(id) = ?', $r->id]
		]);
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
		$excel = PHPExcel_IOFactory::load('./Static/Templates/objetivos.xlsx');
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

		$currentRow = 6;
		$totales = array();
		$letras = array('B', 'C', 'H', 'I', 'J', 'K', 'L');
		foreach($niveles As $key => $nivel){
			$grupos = $this->COLEGIO->getGruposByNivel($nivel->id, $this->get->anio);
			$s1->setCellValue('A'.$currentRow, mb_strtoupper($nivel->nombre, 'utf-8'));
			$s1->setCellValue('B'.$currentRow, 'OBJETIVOS VACANTES');
			$s1->setCellValue('C'.$currentRow, 'PASAN DEL '.($this->get->anio - 1));
			$s1->setCellValue('H'.$currentRow, 'SE RETIRAN');
			$s1->setCellValue('I'.$currentRow, 'TOTAL ANTIGUOS');
			$s1->setCellValue('J'.$currentRow, 'RATIFICACIÓN '.$this->get->anio);
			$s1->setCellValue('K'.$currentRow, 'FALTAN ANTIGUOS');
			$s1->setCellValue('L'.$currentRow, 'TOTAL NUEVOS');
			$s1->setCellValue('M'.$currentRow, 'MATRICULADOS NUEVOS');
			$s1->setCellValue('N'.$currentRow, 'FALTAN NUEVOS');
			$s1->setCellValue('O'.$currentRow, 'TOTAL MATRICULADOS');
			$s1->setCellValue('P'.$currentRow, 'TOTAL FALTAN PAGAR');
			$s1->setCellValue('Q'.$currentRow, 'TOTAL VACANTES');

			$s1->getStyle('A'.$currentRow.':Q'.$currentRow)->applyFromArray($normalStyle);
			$s1->getStyle('A'.$currentRow.':Q'.$currentRow)->applyFromArray($fillStyle);
			$s1->getStyle('A'.$currentRow.':Q'.$currentRow)->applyFromArray($headerStyle);
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
				$s1->getStyle('A'.$currentRow.':Q'.$currentRow)->applyFromArray($normalStyle);
				$s1->getStyle('B'.$currentRow.':Q'.$currentRow)->applyFromArray($textStyle);
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


    function objetivos_matriculas_old($r){
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
					'conditions' => 'dia="'.$keyDia.'" AND anio="'.$this->COLEGIO->anio_activo.'"',
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
		$cancha = Cancha::find_by_id($this->get->cancha_id);
		$alquileres = Alquiler_Cancha::all(array(
			'conditions' => 'DATE(inicio) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND colegio_id="'.$this->COLEGIO->id.'"'.(!is_null($cancha) ? ' AND cancha_id = "'.$this->get->cancha_id.'"' : ''),
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
		$pdf->cell(0, 5,'Alquiler de Cancha'.(!is_null($cancha) ? ' - '.$cancha->nombre : ''), 0, 1,'R');
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


			$pdf->cell(60, 5, is_null($alquiler->cliente) ? $alquiler->nombre : $alquiler->cliente->nombres.' - '.$alquiler->nombre, 1, 0, 'L', 0, 0, 1);
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

		$conditions = 'colegio_id = "'.$this->COLEGIO->id.'" AND estado_pago = "CANCELADO" AND estado = "ACTIVO" AND mora > 0';
		$conditions .= ' AND fecha_cancelado BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")';
		$order = 'fecha_cancelado ASC';

		/*
		if($this->get->estado_impresion == 'IMPRESO'){
			$conditions .= ' AND fecha_cancelado BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")';
			if(!empty($this->get->tipo_mora))
				$conditions .=  ' AND tipo_mora="'.$this->get->tipo_mora.'"';

			$order = 'fecha_cancelado ASC';
		}else{
			$conditions .= ' AND fecha_cancelado BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")';
			$conditions .=  ' AND tipo_mora="NINGUNO"';
			$order = 'fecha_cancelado ASC';
		}*/

		$pagos = Pago::all(array(
			'conditions' => $conditions,
			'order' => $order
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
			if($pago->matricula->isOculto()) continue;

			$impresionMoraConditions = 'pago_id = "'.$pago->id.'" AND estado = "ACTIVO" AND tipo = "MORA"';
			if(!empty($this->get->tipo_mora)){
				$impresionMoraConditions .= ' AND tipo_documento="'.$this->get->tipo_mora.'"';
			}

			$impresionMora = Impresion::find(array(
				'conditions' => $impresionMoraConditions
			));

			$alumno = $pago->matricula->alumno;
			if(!isset($alumno)) continue;

			if($this->get->estado_impresion == 'IMPRESO' && !$impresionMora) continue;
			if($this->get->estado_impresion == 'NO_IMPRESO' && $impresionMora) continue;

			$impresionPago = $pago->getActiveImpresion(false); // NO SAVE
			if(!$impresionPago) continue;

			$s1->setCellValue('A'.$currentRow, $alumno->getFullName());
			$s1->setCellValue('B'.$currentRow, $pago->getDescription());
			$s1->setCellValue('C'.$currentRow, $impresionPago->getSerieNumero());
			$s1->setCellValue('D'.$currentRow, $this->COLEGIO->setFecha($pago->fecha_cancelado));

			$s1->setCellValue('E'.$currentRow, !$impresionMora ? 'NINGUNO' : $impresionMora->tipo_documento);
			if($impresionMora->impreso == 'SI'){
				$s1->setCellValue('F'.$currentRow, $impresionMora->getSerieNumero());
				$s1->setCellValue('G'.$currentRow, $this->COLEGIO->setFecha($impresionMora->fecha_impresion));
			}


			$s1->setCellValue('H'.$currentRow, number_format($pago->getMonto(), 2));
			$s1->setCellValue('I'.$currentRow, number_format($pago->mora, 2));
			$s1->setCellValue('J'.$currentRow, $alumno->nro_documento);



			$total += $pago->monto;
			$totalMora += $pago->mora;
			++$currentRow;
		}

		if($currentRow > 6){
			$s1->setCellValue('G'.$currentRow, 'TOTAL');
			$s1->setCellValue('H'.$currentRow, number_format($total, 2));
			$s1->setCellValue('I'.$currentRow, number_format($totalMora, 2));
			$s1->getStyle('A6:J'.$currentRow)->applyFromArray($normalStyle);
		}


		writeExcel($excel);
	}


	function historial_pagos(){
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/historial_pagos.xlsx');
		$s1 = $excel->getSheet(0);

		$historiales = Pago_Historial::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND fecha BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")',
		));

		/*
		$pagos = Pago::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND fecha_cancelado BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND estado_pago = "CANCELADO" AND estado = "ACTIVO" AND mora > 0'
		));
		*/

		$s1->setCellValue('A2', 'Del '.$this->COLEGIO->parseFecha($from).' al '.$this->COLEGIO->parseFecha($to));

		$total = 0;
		$totalMora = 0;
		$currentRow = 5;

		foreach($historiales As $historial){
			$pagos = $historial->getPagos();
			foreach($pagos As $pago){
				if(!$pago->matricula) continue;

				$s1->setCellValue('A'.$currentRow, $pago->matricula->alumno->nro_documento);
				$s1->setCellValue('B'.$currentRow, $pago->matricula->alumno->getFullName());
				$s1->setCellValue('C'.$currentRow, $pago->matricula->grupo->getNombreShort2());
				$s1->setCellValue('D'.$currentRow, $pago->getDescription());
				$s1->setCellValue('E'.$currentRow, $this->COLEGIO->getFecha($historial->fecha));
				$s1->setCellValue('F'.$currentRow, $this->COLEGIO->getFecha($pago->fecha_cancelado));

				$impresion = $pago->getActiveImpresion(false);
				$s1->setCellValue('G'.$currentRow, $impresion ? $impresion->getSerieNumero() : '-');
				$s1->setCellValue('H'.$currentRow, $pago->nro_movimiento_banco);
				$s1->setCellValue('I'.$currentRow, number_format($pago->getMonto(), 2));
				$s1->setCellValue('J'.$currentRow, number_format($pago->mora, 2));

				$total += $pago->getMonto();
				$totalMora += $pago->mora;

				++$currentRow;
			}

		}

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

	    if($currentRow > 5){
	    	$s1->getStyle('A5:J'.$currentRow)->applyFromArray($normalStyle);
	    	$s1->setCellValue('H'.$currentRow, 'TOTAL');
	    	$s1->setCellValue('I'.$currentRow, number_format($total, 2));
	    	$s1->setCellValue('J'.$currentRow, number_format($totalMora, 2));
	    }


		writeExcel($excel);
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

	function concar_banco(){
		set_time_limit(0);
		
		$this->crystal->load('PHPExcel');
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$excel = PHPExcel_IOFactory::load('./Static/Templates/concar.xlsx');
		$s1 = $excel->getSheet(0);

		// PENSIONES
		$registros = Pago::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND estado_pago = "CANCELADO" AND pagos.estado = "ACTIVO"',
			'joins' => 'inner join matriculas on matriculas.id = pagos.matricula_id',
			'order' => 'fecha_cancelado ASC'
		));

		/*
		$registros = array();

		foreach($pensiones As $pension)
			$registros[] = $pension;

		
		usort($registros, function($a, $b){
			$nro1 = $a->getSerieNumero();
			$nro2 = $b->getSerieNumero();
			return strcmp($nro1, $nro2);
		});
		*/

		$currentRow = 2;
		$autoNumeric = 1;
		Pago::fillHistorial();
	
		foreach($registros As $registro){
			if($registro->descripcion == 'PAGO DE AGENDA') continue;
			if($registro->matricula->isOculto()) continue;

			//$xBoleta = new xBoleta();
		

			if(!$registro->inHistorial()) continue; // no es un pago del banco

			$impresion = $registro->getActiveImpresion(false);
			$impresionMora = $registro->getActiveImpresionMora(false);
			if(!$impresion) continue;

			// PRIMERA LINEA
			$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, '21', PHPExcel_Cell_DataType::TYPE_STRING);
			$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
			$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado)));
			$s1->setCellValue('D'.$currentRow, 'MN');
			$s1->setCellValue('E'.$currentRow, $registro->getDescriptionConcarBanco().' '.mb_strtoupper($registro->getApellidosBanco(), 'utf-8').' '.$impresion->getTipoSerieIntNumero());
			$s1->setCellValue('F'.$currentRow, 0);
			$s1->setCellValue('G'.$currentRow, 'V');
			$s1->setCellValue('H'.$currentRow, 'S');
			$s1->setCellValue('J'.$currentRow, $registro->getCuentaContable()); // XXX
			$s1->setCellValueExplicitByColumnAndRow(10, $currentRow, $registro->getCodigoAnexo(), PHPExcel_Cell_DataType::TYPE_STRING);

			$s1->setCellValue('L'.$currentRow, '');
			$s1->setCellValue('M'.$currentRow, 'D');
			$s1->setCellValue('N'.$currentRow, number_format($registro->getTotal(), 2));
			$s1->setCellValue('Q'.$currentRow, 'EN');
			$s1->setCellValue('R'.$currentRow, $registro->getNroMovimiento());
			$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado)));
			$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado)));
			$s1->setCellValueExplicitByColumnAndRow(20, $currentRow, '010', PHPExcel_Cell_DataType::TYPE_STRING);

			$s1->setCellValue('V'.$currentRow, $registro->getDescription());
			$s1->setCellValueExplicitByColumnAndRow(23, $currentRow, '001', PHPExcel_Cell_DataType::TYPE_STRING);
			++$currentRow;
			//if($pago->)
			// 2DA LINEA
			$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, '21', PHPExcel_Cell_DataType::TYPE_STRING);
			$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
			$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado)));
			$s1->setCellValue('D'.$currentRow, 'MN');
			$s1->setCellValue('E'.$currentRow, $registro->getDescriptionConcarBanco().' '.mb_strtoupper($registro->getApellidosBanco(), 'utf-8').' '.$impresion->getTipoSerieIntNumero());
			$s1->setCellValue('F'.$currentRow, 0);
			$s1->setCellValue('G'.$currentRow, 'V');
			$s1->setCellValue('H'.$currentRow, 'S');
			$s1->setCellValue('J'.$currentRow, $registro->tipo == 0 ? '703211' : '703212'); // XXX -> '121201'
			$s1->setCellValue('K'.$currentRow, $registro->matricula->alumno->nro_documento);
			$s1->setCellValue('L'.$currentRow, '');
			$s1->setCellValue('M'.$currentRow, 'H');
			$s1->setCellValue('N'.$currentRow, number_format($registro->getMonto(), 2));
			$s1->setCellValue('Q'.$currentRow, 'BV');
			$s1->setCellValue('R'.$currentRow, $impresion->getTipo().$impresion->getSerieIntNumero());
			$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado)));
			$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado))); // llama un método del objeto "Impresion"
			$s1->setCellValue('V'.$currentRow, $registro->getDescription());

			++$currentRow;

			// PENALIDAD
			if($registro->mora > 0){
				if(!$impresionMora) continue;
				$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, '21', PHPExcel_Cell_DataType::TYPE_STRING);
				$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
				$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado)));
				$s1->setCellValue('D'.$currentRow, 'MN');
				$s1->setCellValue('E'.$currentRow, $registro->getDescriptionConcarBanco().' '.mb_strtoupper($registro->getApellidosBanco(), 'utf-8').' '.$impresion->getTipoSerieIntNumero());
				$s1->setCellValue('F'.$currentRow, 0);
				$s1->setCellValue('G'.$currentRow, 'V');
				$s1->setCellValue('H'.$currentRow, 'S');
				$s1->setCellValue('J'.$currentRow, '703218'); // XXX
				$s1->setCellValue('K'.$currentRow, $registro->matricula->alumno->nro_documento);
				$s1->setCellValue('L'.$currentRow, '');
				$s1->setCellValue('M'.$currentRow, 'H');
				$s1->setCellValue('N'.$currentRow, number_format($registro->mora, 2));
				$s1->setCellValue('Q'.$currentRow, 'ND');
				$s1->setCellValue('R'.$currentRow, $impresionMora->getTipo().$impresionMora->getSerieIntNumero());
				$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado)));
				$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado))); // llama un método del objeto "Impresion"
				$s1->setCellValue('V'.$currentRow, 'PENALIDAD '.$registro->getDescription());
				++$currentRow;
			}
			
			// AGENDA
			if($registro->incluye_agenda == 'SI'){
				$pagoAgenda = Pago::find(array(
					'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$registro->matricula_id.'" AND nro_pago="1" AND tipo="2" AND incluye_agenda="SI"'
				));
				if(!$pagoAgenda) continue;

				$impresionAgenda =  $pagoAgenda->getActiveImpresion(false);
				if(!$impresionAgenda) continue; 

				$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, '21', PHPExcel_Cell_DataType::TYPE_STRING);
				$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
				$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado)));
				$s1->setCellValue('D'.$currentRow, 'MN');
				$s1->setCellValue('E'.$currentRow, $registro->getDescriptionConcarBanco().'/AGENDA '.mb_strtoupper($registro->getApellidosBanco(), 'utf-8').' '.$impresion->getTipoSerieIntNumero());
				$s1->setCellValue('F'.$currentRow, 0);
				$s1->setCellValue('G'.$currentRow, 'V');
				$s1->setCellValue('H'.$currentRow, 'S');
				$s1->setCellValue('J'.$currentRow, '701202'); // XXX
				$s1->setCellValue('K'.$currentRow, $registro->matricula->alumno->nro_documento);
				$s1->setCellValue('L'.$currentRow, '');
				$s1->setCellValue('M'.$currentRow, 'H');
				$s1->setCellValue('N'.$currentRow, number_format($pagoAgenda->monto, 2));
				$s1->setCellValue('Q'.$currentRow, 'BV');
				$s1->setCellValue('R'.$currentRow, $impresionAgenda->getTipo().$impresionAgenda->getSerieIntNumero());
				$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado)));
				$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->fecha_cancelado))); // llama un método del objeto "Impresion"
				$s1->setCellValue('V'.$currentRow, 'PAGO DE AGENDA');
				

				++$currentRow;
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
		$excel->removeSheetByIndex(1);
		writeExcel($excel);
	}

	function concar(){
		set_time_limit(0);

		$this->crystal->load('PHPExcel');
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$excel = PHPExcel_IOFactory::load('./Static/Templates/concar.xlsx');
		$s1 = $excel->getSheet(0);

		// BOLETAS - FACTURACIÓN

		$boletas = Boleta::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND fecha BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
		));

		$matriculasTalleres = Grupo_Taller_Matricula::all([
			'conditions' => 'DATE(fecha_registro) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
		]);

		// PENSIONES
		/*
		$matriculas = Pago::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(fecha_hora) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 0 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN impresiones ON impresiones.pago_id = pagos.id
			'
		));
		*/
		$matriculas = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 0 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));
		/*
		$pensiones = Pago::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(fecha_hora) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 1 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN impresiones ON impresiones.pago_id = pagos.id
			'
		));
		*/
		$pensiones = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(impresiones.fecha_impresion) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 1 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		$comedores = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(impresiones.fecha_impresion) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 3 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		// MORAS
		/*
		$moras = Pago::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(impresiones.fecha_impresion) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.mora > 0 AND pagos.estado_pago = "CANCELADO" AND impresiones.tipo = "MORA" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN impresiones ON impresiones.pago_id = pagos.id
			'
		));
		*/
		$moras = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.mora > 0 AND pagos.estado_pago = "CANCELADO" AND impresiones.tipo = "MORA" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		$agendas = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 2 AND (pagos.incluye_agenda = "SI" OR observaciones LIKE "%AGENDA%" OR descripcion LIKE "%AGENDA%") AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		$registros = array();

		foreach($matriculasTalleres As $matriculaTaller){
			$registros[] = new xBoleta($matriculaTaller, 'MATRICULA_TALLER');
		}

		foreach($boletas As $boleta)
			$registros[] = new xBoleta($boleta, 'BOLETA');

		foreach($matriculas As $matricula)
			$registros[] = new xBoleta($matricula, 'MATRICULA');

		foreach($pensiones As $pension)
			$registros[] = new xBoleta($pension, 'PENSION');


		foreach($moras As $mora)
			$registros[] = new xBoleta($mora, 'MORA_BOLETA');

		foreach($agendas As $agenda)
			$registros[] = new xBoleta($agenda, 'AGENDA');

		foreach($comedores As $comedor)
			$registros[] = new xBoleta($comedor, 'COMEDOR');


		usort($registros, function($a, $b){
			$nro1 = $a->getSerieNumero();
			$nro2 = $b->getSerieNumero();
			return strcmp($nro1, $nro2);
		});


		$currentRow = 1;
		$autoNumeric = 1;
		foreach($registros As $registro){
			//print_r($registro);
			if($registro->type != 'BOLETA' && $registro->type != 'MATRICULA_TALLER'){
				if(is_null($registro->object->pago->matricula))
					continue;

				if($registro->object->pago->matricula->isOculto()) continue;
			}


			if($registro->type == "BOLETA"){
				$sede = $registro->object->sede;
			}else{
				$sede = $registro->object->pago->matricula->grupo->sede;
			}

			//$sede = $registro->object->pago->matricula->sede;

			if(!$registro->anulado()){
				++$currentRow;
				
				// PRIMERA FILA GENERAL
				/*
				if(in_array($registro->type, array('MATRICULA', 'PENSION', 'COMEDOR'))){
					$this->concarLineBanco($registro, $s1, $currentRow);
					continue;
				}
				*/

				$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario(), PHPExcel_Cell_DataType::TYPE_STRING);
				$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
				$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('D'.$currentRow, 'MN');
				$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', BV '.$registro->getSerieIntNumero());
				$s1->setCellValue('F'.$currentRow, 0);
				$s1->setCellValue('G'.$currentRow, 'V');
				$s1->setCellValue('H'.$currentRow, 'S');
				$s1->setCellValue('J'.$currentRow, '121201');
				$s1->setCellValue('K'.$currentRow, $registro->getDNI());
				$s1->setCellValue('L'.$currentRow, '');
				$s1->setCellValue('M'.$currentRow, 'D');
				$s1->setCellValue('N'.$currentRow, number_format($registro->getMontoTotal(), 2));
				$s1->setCellValue('Q'.$currentRow, 'BV');
				$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
				$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());
				
				// CAMPOS ADICIONALES
				if($registro->isPenalidad()){
					$s1->setCellValue('Y'.$currentRow, 'BV');
					$s1->setCellValue('Z'.$currentRow, $registro->getSerieIntNumeroForMora());
					$s1->setCellValue('AA'.$currentRow, date('d/m/Y', strtotime($registro->getFechaForMora())));
					$s1->setCellValue('AB'.$currentRow, $registro->getMontoForMora());
					$s1->setCellValue('AC'.$currentRow, '0');
				}

				$cCostos = [1 => 300, 2 => 1300];
				if($registro->desglozarIGV()){
					++$currentRow;

					$importe = $registro->getMontoTotal() / 1.18;
					$igv = $importe * 18 / 100;

					

					$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario(), PHPExcel_Cell_DataType::TYPE_STRING);

					//$s1->setCellValue('A'.$currentRow, "05");
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', BV '.$registro->getSerieIntNumero());
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'S');

					$s1->setCellValue('J'.$currentRow, '401111');
					$s1->setCellValue('K'.$currentRow, $registro->getDNI());
					$s1->setCellValue('L'.$currentRow, '');
					$s1->setCellValue('M'.$currentRow, 'H');
					$s1->setCellValue('N'.$currentRow, number_format($igv, 2));

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

					$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());

					// TERCERA FILA
					++$currentRow;
					$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario(), PHPExcel_Cell_DataType::TYPE_STRING);
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', BV '.$registro->getSerieIntNumero());
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'S');

					$s1->setCellValue('J'.$currentRow, $registro->getCuenta());
					$s1->setCellValue('K'.$currentRow, $registro->getDNI());
					
					
 
					$s1->setCellValue('L'.$currentRow, $cCostos[$sede->id]);

					$s1->setCellValue('M'.$currentRow, 'H');
					$s1->setCellValue('N'.$currentRow, number_format($importe, 2));

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

					$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());
				}else{
					++$currentRow;
					$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario(), PHPExcel_Cell_DataType::TYPE_STRING);
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', BV '.$registro->getSerieIntNumero());
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'S');

					$s1->setCellValue('J'.$currentRow, $registro->getCuenta());
					$s1->setCellValue('K'.$currentRow, $registro->getDNI());
					$s1->setCellValue('L'.$currentRow, $cCostos[$sede->id]);
					$s1->setCellValue('M'.$currentRow, 'H');
					$s1->setCellValue('N'.$currentRow, number_format($registro->getMontoTotal(), 2));

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

					$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());
					if($registro->isPenalidad()){
						$s1->setCellValue('Y'.$currentRow, 'BV');
						$s1->setCellValue('Z'.$currentRow, $registro->getSerieIntNumeroForMora());
						$s1->setCellValue('AA'.$currentRow, date('d/m/Y', strtotime($registro->getFechaForMora())));
						$s1->setCellValue('AB'.$currentRow, $registro->getMontoForMora());
						$s1->setCellValue('AC'.$currentRow, '0');
					}
				}
			}else{
				++$currentRow;
				$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario(), PHPExcel_Cell_DataType::TYPE_STRING);
				$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
				$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('D'.$currentRow, 'MN');

				$s1->setCellValue('E'.$currentRow, 'ANULADO BV '.$registro->getSerieIntNumero());
				$s1->setCellValue('F'.$currentRow, 0);
				$s1->setCellValue('G'.$currentRow, 'V');
				$s1->setCellValue('H'.$currentRow, 'N');

				$s1->setCellValue('J'.$currentRow, '121201');
				$s1->setCellValue('K'.$currentRow, '0001 ');
				$s1->setCellValue('L'.$currentRow, '');
				$s1->setCellValue('M'.$currentRow, 'D');
				$s1->setCellValue('N'.$currentRow, '0');

				$s1->setCellValue('Q'.$currentRow, 'BV');
				$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
				$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

				$s1->setCellValue('V'.$currentRow, 'ANULADO BV '.$registro->getSerieIntNumero());
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


	    // HOJA 2
	    // MORAS
		$registros = array();
		$s1 = $excel->getSheet(1);
		/*
		$moras = Pago::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(impresiones.fecha_impresion) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.mora > 0 AND pagos.estado_pago = "CANCELADO" AND impresiones.tipo = "MORA" AND impresiones.tipo_documento = "NOTA"',
			'joins' => '
				INNER JOIN impresiones ON impresiones.pago_id = pagos.id
			'
		));
		*/

		$moras = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.mora > 0 AND pagos.estado_pago = "CANCELADO" AND impresiones.tipo = "MORA" AND impresiones.tipo_documento = "NOTA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		/*
		$moras = Pago::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND DATE(fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND tipo_mora = "NOTA" AND estado_pago = "CANCELADO"'
		));
		*/


		foreach($moras As $mora)
			$registros[] = new xBoleta($mora, 'MORA_NOTA');


		usort($registros, function($a, $b){
			$nro1 = $a->getSerieNumero();
			$nro2 = $b->getSerieNumero();
			return strcmp($nro1, $nro2);
		});

		$currentRow = 1;
		$autoNumeric = 1;
		foreach($registros As $registro){
			if(!$registro->anulado()){
				++$currentRow;
				$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario(), PHPExcel_Cell_DataType::TYPE_STRING);
				$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
				$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('D'.$currentRow, 'MN');

				$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', ND '.$registro->getSerieIntNumero());
				$s1->setCellValue('F'.$currentRow, 0);
				$s1->setCellValue('G'.$currentRow, 'V');
				$s1->setCellValue('H'.$currentRow, 'S');

				$s1->setCellValue('J'.$currentRow, '121201');
				$s1->setCellValue('K'.$currentRow, $registro->getDNI());
				$s1->setCellValue('L'.$currentRow, '');
				$s1->setCellValue('M'.$currentRow, 'D');
				$s1->setCellValue('N'.$currentRow, number_format($registro->getMontoTotal(), 2));

				$s1->setCellValue('Q'.$currentRow, 'ND');
				$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
				$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

				$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());
				$s1->setCellValue('Y'.$currentRow, 'BV');
				$s1->setCellValue('Z'.$currentRow, $registro->getSerieIntNumeroForMora());
				$s1->setCellValue('AA'.$currentRow, date('d/m/Y', strtotime($registro->getFechaForMora())));
				$s1->setCellValue('AB'.$currentRow, $registro->getMontoForMora());
				$s1->setCellValue('AC'.$currentRow, '0');

				// FILA 2
				++$currentRow;
				$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario(), PHPExcel_Cell_DataType::TYPE_STRING);
				$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
				$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('D'.$currentRow, 'MN');
				$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', ND '.$registro->getSerieIntNumero());
				$s1->setCellValue('F'.$currentRow, 0);
				$s1->setCellValue('G'.$currentRow, 'V');
				$s1->setCellValue('H'.$currentRow, 'S');

				$s1->setCellValue('J'.$currentRow, $registro->getCuenta());
				$s1->setCellValue('K'.$currentRow, $registro->getDNI());
				$s1->setCellValue('L'.$currentRow, '300');
				$s1->setCellValue('M'.$currentRow, 'H');
				$s1->setCellValue('N'.$currentRow, number_format($registro->getMontoTotal(), 2));

				$s1->setCellValue('Q'.$currentRow, 'ND');
				$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
				$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

				$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());
				$s1->setCellValue('Y'.$currentRow, 'BV');
				$s1->setCellValue('Z'.$currentRow, $registro->getSerieIntNumeroForMora());
				$s1->setCellValue('AA'.$currentRow, date('d/m/Y', strtotime($registro->getFechaForMora())));
				$s1->setCellValue('AB'.$currentRow, $registro->getMontoForMora());
				$s1->setCellValue('AC'.$currentRow, '0');
			}else{
				++$currentRow;
				$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario(), PHPExcel_Cell_DataType::TYPE_STRING);
				$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
				$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('D'.$currentRow, 'MN');

				$s1->setCellValue('E'.$currentRow, 'ANULADO ND '.$registro->getSerieIntNumero());
				$s1->setCellValue('F'.$currentRow, 0);
				$s1->setCellValue('G'.$currentRow, 'V');
				$s1->setCellValue('H'.$currentRow, 'N');

				$s1->setCellValue('J'.$currentRow, '121201');
				$s1->setCellValue('K'.$currentRow, '0001 ');
				$s1->setCellValue('L'.$currentRow, '');
				$s1->setCellValue('M'.$currentRow, 'D');
				$s1->setCellValue('N'.$currentRow, '0');

				$s1->setCellValue('Q'.$currentRow, 'ND');
				$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
				$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

				$s1->setCellValue('V'.$currentRow, 'ANULADO ND '.$registro->getSerieIntNumero());
			}

			++$autoNumeric;
		}

		if($currentRow > 2) $s1->getStyle('A2:AJ'.($currentRow))->applyFromArray($normalStyle);

		writeExcel($excel);
	}


	function montos_alumnos(){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/montos.xlsx');

		$s = $excel->getSheet(0);
		$s->setCellValue('A1', 'MONTOS A PAGAR DE LOS ALUMNOS MATRICULADOS EN EL AÑO '.$this->get->anio);
		$grupos = $this->COLEGIO->getGrupos($this->get->anio, $this->get->sede_id);
		$currentRow = 2;

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

	    $leftStyle = array(
		    'alignment' => array(
				'wrap' => true,
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
	    );

	    $headerStyle = array(
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
			'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'FBE4D5')
	        )
	    );
	    $totales = array();
		foreach($grupos As $grupo){
			if($this->get->mostrar == 'TODOS'){
				$matriculas = $grupo->getMatriculas();
			}elseif($this->get->mostrar == 'PAGADOS'){
				$xdeudores = Matricula::all(array(
					//'select' => 'matriculas.id, matriculas.alumno_id, matriculas.grupo_id',
					'conditions' => 'matriculas.grupo_id = "'.$grupo->id.'" AND pagos.estado_pago = "PENDIENTE"',
					'joins' => 'INNER JOIN pagos ON pagos.matricula_id = matriculas.id',
					'group' => 'pagos.matricula_id'
				));

				$ids = [];

				foreach($xdeudores As $deudor){
					$ids[] = $deudor->id;
				}

				$matriculas = $grupo->getMatriculas();

			}else{
				$matriculas = Matricula::all(array(
					//'select' => 'matriculas.id, matriculas.alumno_id, matriculas.grupo_id',
					'conditions' => 'matriculas.grupo_id = "'.$grupo->id.'" AND pagos.estado_pago = "PENDIENTE"',
					'joins' => 'INNER JOIN pagos ON pagos.matricula_id = matriculas.id',
					'group' => 'pagos.matricula_id'
				));
			}
			if(count($matriculas) == 0) continue;

			// HEADER
			$s->setCellValue('A'.$currentRow, $grupo->getNombreShort2());
			$s->mergeCells('A'.$currentRow.':P'.$currentRow);
			++$currentRow;
			// GRADOS
			$s->setCellValue('A'.$currentRow, 'N°');
			$s->setCellValue('B'.$currentRow, 'ALUMNO');
			$s->setCellValue('C'.$currentRow, 'DNI');
			

			$s->setCellValue('D'.$currentRow, 'MATRÍCULA');
			$s->setCellValue('E'.$currentRow, 'AGENDA');
			$currentCol = 6;
			for($i = 1; $i<= 10; $i++){
				$s->setCellValue(getNameFromNumber($currentCol).$currentRow, mb_strtoupper($this->COLEGIO->MESES[$i + 1]));
				++$currentCol;
			}
			$s->setCellValue('P'.$currentRow, 'ANUAL');
			
			$s->getStyle('A'.($currentRow - 1).':P'.($currentRow))->applyFromArray($headerStyle);

			++$currentRow;

			

			$initialRow = $currentRow;
			$xkey = 1;
			foreach($matriculas As $keyMatricula => $matricula){
				if($matricula->isOculto()) continue;

				if($this->get->mostrar == 'PAGADOS' && in_array($matricula->id, $ids)) continue;

				$s->setCellValue('A'.$currentRow, $xkey);

				$s->setCellValue('B'.$currentRow, $matricula->alumno->getFullName());
				$s->setCellValue('C'.$currentRow, $matricula->alumno->nro_documento);
				
				$s->setCellValue('D'.$currentRow, $matricula->costo->matricula);
				$s->setCellValue('E'.$currentRow, $matricula->costo->agenda);

				$currentCol = 6;
				for($i = 1; $i<= 10; $i++){
					$pago = Pago::find([
						'conditions' => 'tipo = "1" AND nro_pago = "'.$i.'" AND matricula_id = "'.$matricula->id.'"'
					]);

					$s->setCellValue(getNameFromNumber($currentCol).$currentRow, !is_null($pago) ? $pago->monto : $matricula->costo->pension);
					++$currentCol;
				}
				$s->setCellValue('P'.$currentRow, '=SUM(D'.$currentRow.':O'.$currentRow.')');
				$s->getStyle('A'.($currentRow).':P'.($currentRow))->applyFromArray($normalStyle);
				$s->getStyle('B'.($currentRow))->applyFromArray($leftStyle);
				++$xkey;
				++$currentRow;
			}

			// TOTALES
			$s->setCellValue('A'.$currentRow, 'TOTALES');
			$s->mergeCells('A'.$currentRow.':C'.$currentRow);

			$s->setCellValue('D'.$currentRow, '=SUM(D'.$initialRow.':D'.($currentRow - 1).')');
			$s->setCellValue('E'.$currentRow, '=SUM(E'.$initialRow.':E'.($currentRow - 1).')');
			$currentCol = 6;
			for($i = 1; $i<= 10; $i++){
				$s->setCellValue(getNameFromNumber($currentCol).$currentRow, '=SUM('.getNameFromNumber($currentCol).$initialRow.':'.getNameFromNumber($currentCol).($currentRow - 1).')');
				++$currentCol;
			}
			$s->setCellValue('P'.$currentRow, '=SUM(P'.$initialRow.':P'.($currentRow - 1).')');
			$totales[] = $currentRow;
			$s->getStyle('A'.($currentRow).':P'.($currentRow))->applyFromArray($headerStyle);
			++$currentRow;
			++$currentRow;
		}
		//++$currentRow;
		$s->setCellValue('A'.$currentRow, 'TOTAL ANUALES');
		$s->mergeCells('A'.$currentRow.':C'.$currentRow);

		$s->setCellValue('D'.$currentRow, $this->getTotales($totales, 'D'));
		$s->setCellValue('E'.$currentRow, $this->getTotales($totales, 'E'));
		$currentCol = 6;
		for($i = 1; $i<= 10; $i++){
			$s->setCellValue(getNameFromNumber($currentCol).$currentRow, $this->getTotales($totales, getNameFromNumber($currentCol)));
			++$currentCol;
		}
		$s->setCellValue('P'.$currentRow, $this->getTotales($totales, 'P'));
		$s->getStyle('A'.($currentRow).':P'.($currentRow))->applyFromArray($headerStyle);
		writeExcel($excel);
	}

	function montos_alumnos_real(){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/montos.xlsx');

		$s = $excel->getSheet(0);
		$s->setCellValue('A1', 'MONTOS A PAGAR DE LOS ALUMNOS MATRICULADOS EN EL AÑO '.$this->get->anio);
		$grupos = $this->COLEGIO->getGrupos($this->get->anio, $this->get->sede_id);
		$currentRow = 2;

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

	    $leftStyle = array(
		    'alignment' => array(
				'wrap' => true,
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
	    );

	    $headerStyle = array(
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
			'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'FBE4D5')
	        )
	    );
	    $totales = array();
		foreach($grupos As $grupo){
			if($this->get->mostrar == 'TODOS'){
				$matriculas = $grupo->getMatriculas();
			}elseif($this->get->mostrar == 'PAGADOS'){
				$xdeudores = Matricula::all(array(
					//'select' => 'matriculas.id, matriculas.alumno_id, matriculas.grupo_id',
					'conditions' => 'matriculas.grupo_id = "'.$grupo->id.'" AND pagos.estado_pago = "PENDIENTE"',
					'joins' => 'INNER JOIN pagos ON pagos.matricula_id = matriculas.id',
					'group' => 'pagos.matricula_id'
				));

				$ids = [];

				foreach($xdeudores As $deudor){
					$ids[] = $deudor->id;
				}

				$matriculas = $grupo->getMatriculas();

			}else{
				$matriculas = Matricula::all(array(
					//'select' => 'matriculas.id, matriculas.alumno_id, matriculas.grupo_id',
					'conditions' => 'matriculas.grupo_id = "'.$grupo->id.'" AND pagos.estado_pago = "PENDIENTE"',
					'joins' => 'INNER JOIN pagos ON pagos.matricula_id = matriculas.id',
					'group' => 'pagos.matricula_id'
				));
			}
			if(count($matriculas) == 0) continue;

			// HEADER
			$s->setCellValue('A'.$currentRow, $grupo->getNombreShort2());
			$s->mergeCells('A'.$currentRow.':P'.$currentRow);
			++$currentRow;
			// GRADOS
			$s->setCellValue('A'.$currentRow, 'N°');
			$s->setCellValue('B'.$currentRow, 'ALUMNO');
			$s->setCellValue('C'.$currentRow, 'DNI');
			

			$s->setCellValue('D'.$currentRow, 'MATRÍCULA');
			$s->setCellValue('E'.$currentRow, 'AGENDA');
			$currentCol = 6;
			for($i = 1; $i<= 10; $i++){
				$s->setCellValue(getNameFromNumber($currentCol).$currentRow, mb_strtoupper($this->COLEGIO->MESES[$i + 1]));
				++$currentCol;
			}
			$s->setCellValue('P'.$currentRow, 'ANUAL');
			
			$s->getStyle('A'.($currentRow - 1).':P'.($currentRow))->applyFromArray($headerStyle);

			++$currentRow;

			

			$initialRow = $currentRow;
			$xkey = 1;
			foreach($matriculas As $keyMatricula => $matricula){
				if($matricula->isOculto()) continue;

				if($this->get->mostrar == 'PAGADOS' && in_array($matricula->id, $ids)) continue;

				$s->setCellValue('A'.$currentRow, $xkey);

				$s->setCellValue('B'.$currentRow, $matricula->alumno->getFullName());
				$s->setCellValue('C'.$currentRow, $matricula->alumno->nro_documento);
				
				$pagoMatricula = Pago::find([
						'conditions' => 'tipo = "0" AND nro_pago = "1" AND matricula_id = "'.$matricula->id.'"'
					]);

				$s->setCellValue('D'.$currentRow, !is_null($pagoMatricula) ? $matricula->costo->matricula : 0);
				$pagoAgenda = null;
				if($pagoMatricula){
					$pagoAgenda = Pago::find([
						'conditions' => 'tipo = "2" AND nro_pago = "1" AND matricula_id = "'.$matricula->id.'" AND descripcion LIKE "%agenda%"'
					]);
				}

				$s->setCellValue('E'.$currentRow, !is_null($pagoAgenda) ? $matricula->costo->agenda : 0);

				$currentCol = 6;
				for($i = 1; $i<= 10; $i++){
					$pago = Pago::find([
						'conditions' => 'tipo = "1" AND nro_pago = "'.$i.'" AND matricula_id = "'.$matricula->id.'"'
					]);

					if(!is_null($pago)){
						$monto = $pago->monto;
					}else{
						$monto = $matricula->costo->pension;
						/*
						if($i == 1 || $pago->matricula->descontar == 'NO'){
							$monto = $matricula->costo->pension;
						}elseif($i >= 2 && $i <= 4){
							$monto = $matricula->costo->pension * 0.5; // 50%
						}else{
							$monto = $matricula->costo->pension * 0.6; // 60%
						}
						*/
					}

					$s->setCellValue(getNameFromNumber($currentCol).$currentRow, $monto);
					++$currentCol;
				}

				$s->setCellValue('P'.$currentRow, '=SUM(D'.$currentRow.':O'.$currentRow.')');
				$s->getStyle('A'.($currentRow).':P'.($currentRow))->applyFromArray($normalStyle);
				$s->getStyle('B'.($currentRow))->applyFromArray($leftStyle);
				++$xkey;
				++$currentRow;
			}

			// TOTALES
			$s->setCellValue('A'.$currentRow, 'TOTALES');
			$s->mergeCells('A'.$currentRow.':C'.$currentRow);

			$s->setCellValue('D'.$currentRow, '=SUM(D'.$initialRow.':D'.($currentRow - 1).')');
			$s->setCellValue('E'.$currentRow, '=SUM(E'.$initialRow.':E'.($currentRow - 1).')');
			$currentCol = 6;
			for($i = 1; $i<= 10; $i++){
				$s->setCellValue(getNameFromNumber($currentCol).$currentRow, '=SUM('.getNameFromNumber($currentCol).$initialRow.':'.getNameFromNumber($currentCol).($currentRow - 1).')');
				++$currentCol;
			}
			$s->setCellValue('P'.$currentRow, '=SUM(P'.$initialRow.':P'.($currentRow - 1).')');
			$totales[] = $currentRow;
			$s->getStyle('A'.($currentRow).':P'.($currentRow))->applyFromArray($headerStyle);
			++$currentRow;
			++$currentRow;
		}
		//++$currentRow;
		$s->setCellValue('A'.$currentRow, 'TOTAL ANUALES');
		$s->mergeCells('A'.$currentRow.':C'.$currentRow);

		$s->setCellValue('D'.$currentRow, $this->getTotales($totales, 'D'));
		$s->setCellValue('E'.$currentRow, $this->getTotales($totales, 'E'));
		$currentCol = 6;
		for($i = 1; $i<= 10; $i++){
			$s->setCellValue(getNameFromNumber($currentCol).$currentRow, $this->getTotales($totales, getNameFromNumber($currentCol)));
			++$currentCol;
		}
		$s->setCellValue('P'.$currentRow, $this->getTotales($totales, 'P'));
		$s->getStyle('A'.($currentRow).':P'.($currentRow))->applyFromArray($headerStyle);
		writeExcel($excel);
	}

	function getTotales($totales, $col){
		foreach($totales As $key => $total){
			$totales[$key] = $col.$total;
		}
		return '='.implode('+', $totales);
	}



	function historial_boletas_pendientesx(){
		$fecha = date('Y-m-d', strtotime($this->get->fecha));
		
		$boletas = Boleta::all(array(
			'conditions' => '
				estado = "ACTIVO" 
				AND ((estado_pago = "PENDIENTE" AND DATE("'.$fecha.'") >= fecha) 
				OR (estado_pago = "CANCELADO" AND fecha != fecha_pago AND DATE("'.$fecha.'") <= fecha_pago AND DATE("'.$fecha.'") >= fecha))'
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
		$pdf->cell(0, 5,'Boletas Pendientes', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(0,5, 'Hasta '.$this->COLEGIO->parseFecha($fecha),0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(60, 5, 'NOMBRE', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'N° DE RECIBO', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'FECHA', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'FECHA PAGO', 1, 0, 'C', 1);

		$pdf->cell(20, 5, 'MONTO S/.', 1, 0, 'C', 1, 0, 1);
		
		$total = 0;
		foreach($boletas As $boleta){
			
			$pdf->ln(5);


			$pdf->cell(60, 5, $boleta->nombre, 1, 0, 'L', 0, 0, 1);
			$pdf->cell(30, 5, $boleta->getNroBoleta(), 1, 0, 'C', 0, null, 1);
			$pdf->cell(30, 5, $this->COLEGIO->getFecha($boleta->fecha), 1, 0, 'C', 0, null, 1);
			$pdf->cell(30, 5, $boleta->estado_pago == 'CANCELADO' ? $this->COLEGIO->getFecha($boleta->fecha_pago) : '-', 1, 0, 'C', 0, null, 1);
			$pdf->cell(20, 5, number_format($boleta->getMontoTotal(), 2), 1, 0, 'C', 0, null, 1);

			$total += $boleta->getMontoTotal();
		}


		$pdf->ln(5);

		$pdf->cell(120);
		$pdf->cell(30, 5, 'TOTAL', 1, 0, 'C', 1, null, 1);
		$pdf->cell(20, 5, number_format($total, 2), 1, 0, 'C', 0, null, 1);
		


		$pdf->output();
	}

	function historial_boletas_pendientes_all(){
	
		$boletas = Boleta::all(array(
			'conditions' => '
				estado = "ACTIVO" 
				AND ((estado_pago = "PENDIENTE") 
				OR (estado_pago = "CANCELADO" AND fecha != fecha_pago))'
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
		$pdf->cell(0, 5,'Boletas Pendientes', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(0,5, 'Todas las boletas pendientes',0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(60, 5, 'NOMBRE', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'N° DE RECIBO', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'FECHA', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'FECHA PAGO', 1, 0, 'C', 1);

		$pdf->cell(20, 5, 'MONTO S/.', 1, 0, 'C', 1, 0, 1);
		
		$total = 0;
		foreach($boletas As $boleta){
			
			$pdf->ln(5);


			$pdf->cell(60, 5, $boleta->nombre, 1, 0, 'L', 0, 0, 1);
			$pdf->cell(30, 5, $boleta->getNroBoleta(), 1, 0, 'C', 0, null, 1);
			$pdf->cell(30, 5, $this->COLEGIO->getFecha($boleta->fecha), 1, 0, 'C', 0, null, 1);
			$pdf->cell(30, 5, $boleta->estado_pago == 'CANCELADO' ? $this->COLEGIO->getFecha($boleta->fecha_pago) : '-', 1, 0, 'C', 0, null, 1);
			$pdf->cell(20, 5, number_format($boleta->getMontoTotal(), 2), 1, 0, 'C', 0, null, 1);

			$total += $boleta->getMontoTotal();
		}


		$pdf->ln(5);

		$pdf->cell(120);
		$pdf->cell(30, 5, 'TOTAL', 1, 0, 'C', 1, null, 1);
		$pdf->cell(20, 5, number_format($total, 2), 1, 0, 'C', 0, null, 1);
		


		$pdf->output();
	}


	function pagos_manuales(){
	
		$pagos = Pago::all(array(
			'conditions' => 'estado = "ACTIVO" AND estado_pago = "CANCELADO" AND YEAR(fecha_cancelado) = "'.$this->get->anio.'"',
			'order' => 'fecha_cancelado ASC'
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
		$pdf->cell(0, 5,'Pagos Realizados', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(0,5, 'Pagos realizados manualmente - Año '.$this->get->anio,0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->cell(70, 5, 'NOMBRE', 1, 0, 'C', 1);
		$pdf->cell(50, 5, 'CONCEPTO', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'N° DE RECIBO', 1, 0, 'C', 1);
	
		$pdf->cell(30, 5, 'FECHA PAGO', 1, 0, 'C', 1);

		$pdf->cell(20, 5, 'MONTO S/.', 1, 0, 'C', 1, 0, 1);
		
		$total = 0;
		Pago::fillHistorial();
		
		foreach($pagos As $pago){
			if($pago->matricula->isOculto()) continue;
			//if(!preg_match('/importado/', strtolower($pago->descripcion)) && !preg_match('/importado/', strtolower($pago->observaciones))) continue;
			if(in_array($pago->id, Pago::$historial)) continue; // es importado
			if($pago->incluye_agenda == 'SI') continue; // importado
			$pdf->ln(5);

			$impresion = $pago->getActiveImpresion(false);

			$pdf->cell(70, 5, $pago->matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
			$pdf->cell(50, 5, $pago->getDescription(), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, isset($impresion) ? $impresion->getSerieNumero() : '-', 1, 0, 'C', 0, null, 1);
			$pdf->cell(30, 5, $this->COLEGIO->getFecha($pago->fecha_cancelado), 1, 0, 'C', 0, null, 1);
			
			$pdf->cell(20, 5, number_format($pago->getTotal(), 2), 1, 0, 'C', 0, null, 1);

			$total += $pago->getTotal();
		}


		$pdf->ln(5);

		$pdf->cell(120);
		$pdf->cell(30, 5, 'TOTAL', 1, 0, 'C', 1, null, 1);
		$pdf->cell(20, 5, number_format($total, 2), 1, 0, 'C', 0, null, 1);
		
		

		//print_r(Pago::$historial);

		$pdf->output();
	}

	function registros_web(){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/registros_web.xlsx');

		$s1 = $excel->getSheet(0);
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));
		
		

		$normalStyle = array(
		    'font'  => array(
		        //'bold'  => true,
		        'color' => array('rgb' => '000000'),
		        'size'  => 10,
		        'name'  => 'Calibri'
		    ),
		    'alignment' => array(
				'wrap' => true,
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
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

	    $headerStyle = array(
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
			'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'FBE4D5')
	        )
	    );
	    $currentRow = 5;
	    
	    $registros = Contacto::all(array(
	    	'conditions' => 'DATE(fecha_registro) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
	    ));

	    foreach($registros As $registro){
	    	$s1->setCellValue('A'.$currentRow, $registro->nombres_apoderado);
	    	$s1->setCellValue('B'.$currentRow, $registro->telefono_fijo);
	    	$s1->setCellValue('C'.$currentRow, $registro->telefono_celular);
	    	$s1->setCellValue('D'.$currentRow, $registro->email);
	    	$s1->setCellValue('E'.$currentRow, $registro->nombres_alumno);
	    	$s1->setCellValue('F'.$currentRow, $registro->dni_alumno);

	    	$s1->setCellValue('G'.$currentRow, $registro->colegio);
	    	$s1->setCellValue('H'.$currentRow, $registro->grado_actual);
	    	$s1->setCellValue('I'.$currentRow, $registro->fecha_registro);
	    	++$currentRow;
	    }

		if(count($registros) > 0)
			$s1->getStyle('A5:I'.($currentRow - 1))->applyFromArray($normalStyle);
		writeExcel($excel);
	}








	function historial_boletas_pendientes(){
		$fecha = date('Y-m-d', strtotime($this->get->fecha));
		
		

		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/boletas_pendientes.xlsx');

		$s1 = $excel->getSheet(0);
		$alumnos = Alumno::find_all_by_colegio_id($this->COLEGIO->id);

		$normalStyle = array(
		    'font'  => array(
		        //'bold'  => true,
		        'color' => array('rgb' => '000000'),
		        'size'  => 10,
		        'name'  => 'Calibri'
		    ),
		    'alignment' => array(
				'wrap' => true,
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
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
	    );


	    $currentRow = 5;
	    
	 	$total = 0;

	    foreach($alumnos As $alumno){
	    	
	    	$boletas = Boleta::all(array(
				'conditions' => '
					estado = "ACTIVO" 
					AND tipo = "ALUMNO"
					AND dni = "'.$alumno->nro_documento.'"
					AND ((estado_pago = "PENDIENTE" AND DATE("'.$fecha.'") >= fecha) 
					OR (estado_pago = "CANCELADO" AND fecha != fecha_pago AND DATE("'.$fecha.'") <= fecha_pago AND DATE("'.$fecha.'") >= fecha))'
			));
			foreach($boletas As $boleta){
				$s1->setCellValue('A'.$currentRow, $alumno->nro_documento);
				$s1->setCellValue('B'.$currentRow, $alumno->getFullName());
		    	$s1->setCellValue('C'.$currentRow, 'BV');
		    	$s1->setCellValue('D'.$currentRow, $boleta->getCurrentSerie());
		    	$s1->setCellValue('E'.$currentRow, $boleta->getCurrentNumero());
		    	$s1->setCellValue('F'.$currentRow, date('d/m/Y', strtotime($boleta->fecha)));
		    	$s1->setCellValue('G'.$currentRow, date('d/m/Y', strtotime($boleta->fecha)));
		    	$s1->setCellValue('H'.$currentRow, 'VENTA');
		    	$s1->setCellValue('I'.$currentRow, $boleta->getMontoTotal());
		    	$total += $boleta->getMontoTotal();
		    	++$currentRow;
			}
			
	    	// pensiones
	    	
	    	$pagos = Pago::all(array(
				'conditions' => '
					pagos.estado = "ACTIVO" 
					AND alumnos.nro_documento = "'.$alumno->nro_documento.'"
					AND ((pagos.estado_pago = "PENDIENTE" AND DATE("'.$fecha.'") >= DATE(pagos.fecha_hora)) 
					OR (pagos.estado_pago = "CANCELADO" AND (DATE(pagos.fecha_hora) != pagos.fecha_cancelado) AND DATE("'.$fecha.'") <= pagos.fecha_cancelado AND DATE("'.$fecha.'") >= DATE(pagos.fecha_hora)))',
	    		'joins' => '
					INNER JOIN matriculas ON matriculas.id = pagos.matricula_id
					INNER JOIN alumnos ON alumnos.id = matriculas.alumno_id
		    	'
			));
			

			foreach($pagos As $pago){
				$impresion = $pago->getActiveImpresion(false);
				if(!$impresion) continue;

				$s1->setCellValue('A'.$currentRow, $alumno->nro_documento);
				$s1->setCellValue('B'.$currentRow, $alumno->getFullName());
		    	$s1->setCellValue('C'.$currentRow, 'BV');
		    	$s1->setCellValue('D'.$currentRow, $impresion->getSerie());
		    	$s1->setCellValue('E'.$currentRow, $impresion->getNumero());
		    	$s1->setCellValue('F'.$currentRow, date('d/m/Y', strtotime($impresion->fecha_impresion)));
		    	$s1->setCellValue('G'.$currentRow, date('d/m/Y', strtotime($impresion->fecha_impresion)));
		    	$s1->setCellValue('H'.$currentRow, $pago->getDescription());
		    	$s1->setCellValue('I'.$currentRow, $pago->monto);
		    	$total += $pago->monto;
		    	++$currentRow;
			}
	    }
	   
	    // TRABAJADORES
	    $personal = Personal::find_all_by_colegio_id($this->COLEGIO->id);
	    foreach($personal As $persona){
	    	// boletas
	    	$boletas = Boleta::all(array(
				'conditions' => '
					estado = "ACTIVO" 
					AND tipo = "DOCENTE"
					AND dni = "'.$persona->nro_documento.'"
					AND ((estado_pago = "PENDIENTE" AND DATE("'.$fecha.'") >= fecha) 
					OR (estado_pago = "CANCELADO" AND fecha != fecha_pago AND DATE("'.$fecha.'") <= fecha_pago AND DATE("'.$fecha.'") >= fecha))'
			));
			foreach($boletas As $boleta){
				$s1->setCellValue('A'.$currentRow, $persona->nro_documento);
				$s1->setCellValue('B'.$currentRow, $persona->getFullName());
		    	$s1->setCellValue('C'.$currentRow, 'BV');
		    	$s1->setCellValue('D'.$currentRow, $boleta->getCurrentSerie());
		    	$s1->setCellValue('E'.$currentRow, $boleta->getCurrentNumero());
		    	$s1->setCellValue('F'.$currentRow, date('d/m/Y', strtotime($boleta->fecha)));
		    	$s1->setCellValue('G'.$currentRow, date('d/m/Y', strtotime($boleta->fecha)));
		    	$s1->setCellValue('H'.$currentRow, 'VENTA');
		    	$s1->setCellValue('I'.$currentRow, $boleta->getMontoTotal());
		    	$total += $boleta->getMontoTotal();
		    	++$currentRow;
			}
		}

		// EXTERNOS
		$boletas = Boleta::all(array(
			'conditions' => '
				estado = "ACTIVO" 
				AND tipo = "EXTERNO"
				AND ((estado_pago = "PENDIENTE" AND DATE("'.$fecha.'") >= fecha) 
				OR (estado_pago = "CANCELADO" AND fecha != fecha_pago AND DATE("'.$fecha.'") <= fecha_pago AND DATE("'.$fecha.'") >= fecha))',
			'order' => 'dni ASC'
		));
		foreach($boletas As $boleta){
			$s1->setCellValue('A'.$currentRow, $boleta->dni);
			$s1->setCellValue('B'.$currentRow, $boleta->nombre);
	    	$s1->setCellValue('C'.$currentRow, 'BV');
	    	$s1->setCellValue('D'.$currentRow, $boleta->getCurrentSerie());
	    	$s1->setCellValue('E'.$currentRow, $boleta->getCurrentNumero());
	    	$s1->setCellValue('F'.$currentRow, date('d/m/Y', strtotime($boleta->fecha)));
	    	$s1->setCellValue('G'.$currentRow, date('d/m/Y', strtotime($boleta->fecha)));
	    	$s1->setCellValue('H'.$currentRow, 'VENTA');
	    	$s1->setCellValue('I'.$currentRow, $boleta->getMontoTotal());
	    	$total += $boleta->getMontoTotal();
	    	++$currentRow;
		}
		
		$s1->setCellValue('H'.$currentRow, 'TOTAL');
	    $s1->setCellValue('I'.$currentRow, $total);
	    $s1->setCellValue('A2', 'HISTORIAL DE BOLETAS PENDIENTES - HASTA EL '.mb_strtoupper($this->COLEGIO->parseFecha($fecha), 'UTF-8'));
		if($currentRow > 5){
			$s1->getStyle('A5:I'.($currentRow))->applyFromArray($normalStyle);
			$s1->getStyle('C5:I'.($currentRow))->applyFromArray($centerStyle);
			$s1->getStyle('A5:A'.($currentRow))->applyFromArray($centerStyle);
		}
			
		writeExcel($excel);


	}

	function alumnos_no_matriculados(){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/no_matriculados.xlsx');
		$s = $excel->getSheet(0);
		$s->setCellValue('A2', 'ALUMNOS NO MATRICULADOS - '.$this->get->anio);
		$grupos = $this->COLEGIO->getGrupos($this->get->anio - 1); // año anterior

		$row = 5;
		$key = 1;
		foreach($grupos As $grupo){
			$matriculas = $grupo->getMatriculas();
			foreach($matriculas As $matricula){

				$currentMatricula = Matricula::find(array(
					'conditions' => 'grupos.anio = "'.$this->get->anio.'" AND matriculas.alumno_id = "'.$matricula->alumno_id.'"',
					'joins' => ['grupo']
				));
				if($currentMatricula) continue;

				$apoderado = $matricula->alumno->getFirstApoderado();
				$telefonos = array();
				if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
				if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

				$s->setCellValue('A'.$row, $key);
				$s->setCellValue('B'.$row, $matricula->alumno->getFullName());
				$s->setCellValue('C'.$row, $grupo->getNombreShort2());
				$s->setCellValue('D'.$row, $matricula->alumno->nro_documento);
				$s->setCellValue('E'.$row, implode(',', $telefonos));
				$s->setCellValue('F'.$row, $apoderado->email);

				++$key;
				++$row;
			}

		}

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

		$s->getStyle('A5:F'.($row - 1))->applyFromArray($normalStyle);
		$s->getStyle('B5:B'.($row - 1))->applyFromArray(array(
			'alignment' => array(
				'wrap' => true,
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
		));

		writeExcel($excel);
	}

	function alumnos_no_matriculadosx(){
		

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
		$pdf->cell(0, 5,'ALUMNOS NO MATRICULADOS', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 9);
		$pdf->cell(0,5, 'Año '.$this->get->anio,0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 9);

		$pdf->cell(10, 5, 'N°', 1, 0, 'C', 1);
		$pdf->cell(80, 5, 'APELLIDOS Y NOMBRES', 1, 0, 'C', 1);
		$pdf->cell(60, 5, 'ULTIMA MATRÍCULA', 1, 0, 'C', 1);
		$pdf->cell(50, 5, 'CERTIFICADO', 1, 0, 'C', 1);
		/*
		$pdf->cell(60, 5, 'REGISTRADO POR', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'FECHA', 1, 0, 'C', 1);
		$pdf->cell(40, 5, 'HORA(S)', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'PRECIO S/.', 1, 0, 'C', 1, 0, 1);
		*/

		$alumnos = Alumno::all(array(
			'conditions' => 'grupos.anio < "'.$this->get->anio.'"',
			'order' => $this->COLEGIO->ALUMNOS_ORDER,
			'joins' => '
				INNER JOIN matriculas ON matriculas.alumno_id = alumnos.id
				INNER JOIN grupos ON grupos.id = matriculas.grupo_id
			'
		));

		$total = 0;
		$i = 1;
		foreach($alumnos As $alumno){
			$last = $alumno->getLastMatricula();
			if($last->grupo->anio == $this->get->anio) continue; // si tiene matricula actual
			$pdf->ln(5);

			$pdf->cell(10, 5, $i, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(80, 5, $alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
			$pdf->cell(60, 5, !is_null($last) ? $last->grupo->getNombre() : '-', 1, 0, 'C', 0, 0, 1);

			$detalle = Boleta_Detalle::find(array(
				'conditions' => 'boletas_detalles.concepto_id = 98 AND boletas.dni = "'.$alumno->nro_documento.'"',
				'joins' => 'INNER JOIN boletas ON boletas.id = boletas_detalles.boleta_id'
			));

			$pdf->cell(50, 5, !is_null($detalle) ? $detalle->boleta->getNroBoleta() : '-', 1, 0, 'C', 0, 0, 1);

			++$i;

		}

		$pdf->ln(5);
		$pdf->output();
	}

	function chart(){
		$this->crystal->load('PHPExcel');
		$excel = new PHPExcel();
		$excel->createSheet();
        $excel->setActiveSheetIndex(1);
        $excel->getActiveSheet()->setTitle('Grafico');


        $objWorksheet = $excel->getActiveSheet();
        $objWorksheet->fromArray(
                array(
                    array('', 'Rainfall (mm)', 'Temperature (°F)', 'Humidity (%)'),
                    array('Jan', 78, 52, 61),
                    array('Feb', 64, 54, 62),
                    array('Mar', 62, 57, 63),
                    array('Apr', 21, 62, 59),
                    array('May', 11, 75, 60),
                    array('Jun', 1, 75, 57),
                    array('Jul', 1, 79, 56),
                    array('Aug', 1, 79, 59),
                    array('Sep', 10, 75, 60),
                    array('Oct', 40, 68, 63),
                    array('Nov', 69, 62, 64),
                    array('Dec', 89, 57, 66),
                )
        );


        //  Set the Labels for each data series we want to plot
        //      Datatype
        //      Cell reference for data
        //      Format Code
        //      Number of datapoints in series
        //      Data values
        //      Data Marker
        $dataseriesLabels1 = array(
            new \PHPExcel_Chart_DataSeriesValues('String', 'Grafico!$B$1', NULL, 1), //  Temperature
        );
        $dataseriesLabels2 = array(
            new \PHPExcel_Chart_DataSeriesValues('String', 'Grafico!$C$1', NULL, 1), //  Rainfall
        );
        $dataseriesLabels3 = array(
            new \PHPExcel_Chart_DataSeriesValues('String', 'Grafico!$D$1', NULL, 1), //  Humidity
        );

        //  Set the X-Axis Labels
        //      Datatype
        //      Cell reference for data
        //      Format Code
        //      Number of datapoints in series
        //      Data values
        //      Data Marker
        $xAxisTickValues = array(
            new \PHPExcel_Chart_DataSeriesValues('String', 'Grafico!$A$2:$A$13', NULL, 12), //  Jan to Dec
        );


        //  Set the Data values for each data series we want to plot
        //      Datatype
        //      Cell reference for data
        //      Format Code
        //      Number of datapoints in series
        //      Data values
        //      Data Marker
        $dataSeriesValues1 = array(
            new \PHPExcel_Chart_DataSeriesValues('Number', 'Grafico!$B$2:$B$13', NULL, 12),
        );

        //  Build the dataseries
        $series1 = new \PHPExcel_Chart_DataSeries(
                \PHPExcel_Chart_DataSeries::TYPE_BARCHART, // plotType
                \PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED, // plotGrouping
                range(0, count($dataSeriesValues1) - 1), // plotOrder
                $dataseriesLabels1, // plotLabel
                $xAxisTickValues, // plotCategory
                $dataSeriesValues1                              // plotValues
        );
        //  Set additional dataseries parameters
        //      Make it a vertical column rather than a horizontal bar graph
        $series1->setPlotDirection(\PHPExcel_Chart_DataSeries::DIRECTION_COL);


        //  Set the Data values for each data series we want to plot
        //      Datatype
        //      Cell reference for data
        //      Format Code
        //      Number of datapoints in series
        //      Data values
        //      Data Marker
        $dataSeriesValues2 = array(
            new \PHPExcel_Chart_DataSeriesValues('Number', 'Grafico!$C$2:$C$13', NULL, 12),
        );

        //  Build the dataseries
        $series2 = new \PHPExcel_Chart_DataSeries(
                \PHPExcel_Chart_DataSeries::TYPE_LINECHART, // plotType
                \PHPExcel_Chart_DataSeries::GROUPING_STANDARD, // plotGrouping
                range(0, count($dataSeriesValues2) - 1), // plotOrder
                $dataseriesLabels2, // plotLabel
                NULL, // plotCategory
                $dataSeriesValues2                              // plotValues
        );


        //  Set the Data values for each data series we want to plot
        //      Datatype
        //      Cell reference for data
        //      Format Code
        //      Number of datapoints in series
        //      Data values
        //      Data Marker
        $dataSeriesValues3 = array(
            new \PHPExcel_Chart_DataSeriesValues('Number', 'Grafico!$D$2:$D$13', NULL, 12),
        );

        //  Build the dataseries
        $series3 = new \PHPExcel_Chart_DataSeries(
                \PHPExcel_Chart_DataSeries::TYPE_AREACHART, // plotType
                \PHPExcel_Chart_DataSeries::GROUPING_STANDARD, // plotGrouping
                range(0, count($dataSeriesValues2) - 1), // plotOrder
                $dataseriesLabels3, // plotLabel
                NULL, // plotCategory
                $dataSeriesValues3                              // plotValues
        );


        //  Set the series in the plot area
        $plotarea = new \PHPExcel_Chart_PlotArea(NULL, array($series1, $series2, $series3));
        //  Set the chart legend
        $legend = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);

        $title = new \PHPExcel_Chart_Title('Chart awesome');

        //  Create the chart
        $chart = new \PHPExcel_Chart(
                'chart1', // name
                $title, // title
                $legend, // legend
                $plotarea, // plotArea
                true, // plotVisibleOnly
                0, // displayBlanksAs
                NULL, // xAxisLabel
                NULL            // yAxisLabel
        );

        //  Set the position where the chart should appear in the worksheet
        $chart->setTopLeftPosition('F2');
        $chart->setBottomRightPosition('O16');

        //  Add the chart to the worksheet
        $objWorksheet->addChart($chart);
        

        /*
        // if you're using plain php use instead :
        $writer = new PHPExcel_Writer_Excel2007($excel);
        //$writer = $this->get('phpexcel')->createWriter($excel, 'Excel2007');
        $writer->setIncludeCharts(TRUE);

        // Save the file somewhere in your project
        $writer->save();
        */
        writeExcel($excel);
	}


	



	function alumnos_deudores_retirados(){
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
		$pdf->cell(0,5,'Alumnos Deudores - Retirados',0,1,'R');
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
		$totalp = 0;
		foreach($grupos As $grupo){
			$matriculas = $grupo->getMatriculasRetirados();
			
			foreach($matriculas As $key_matricula => $matricula){
				for($x = 0; $x<=$this->COLEGIO->total_pensiones; $x++){
					if($this->get->nro_pago != -1 && $x != $this->get->nro_pago) continue;
					//if($matricula->estado != 0) continue;
					//if($x == 0) continue; // no cuenta matriculas
					if($x == 0){
						$tipo_pago = 0;
						$nro_pago = 1;
					}else{
						$tipo_pago = 1;
						$nro_pago = $x;
					}


					$total_pagos = Pago::find(array(
						'select' => 'SUM(monto) As monto_total',
						'conditions' => 'pagos.estado_pago="CANCELADO" AND pagos.estado="ACTIVO" AND pagos.colegio_id="'.$this->COLEGIO->id.'" AND pagos.matricula_id="'.$matricula->id.'" AND pagos.tipo="'.$tipo_pago.'" AND pagos.nro_pago="'.$nro_pago.'"',
						'joins' => array('matricula')
					));


					//if($tipo_pago == 0 && $total_pagos->monto_total >= $matricula->costo->matricula) continue;
					//if($tipo_pago == 1 && $total_pagos->monto_total >= $matricula->costo->pension) continue;
					// PAGO ALMENOS 1 SOL
					

					/*$pago = Pago::find(array(
						'conditions' => 'estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="'.$nro_pago.'" AND tipo="'.$tipo_pago.'"'
					));*/

					if($pago) continue;
					$monto = $tipo_pago == 0 ? $matricula->costo->matricula : $matricula->costo->pension;
					if($monto <= 0) continue; // becado
					if($total_pagos->monto_total >= $monto) continue;
					$pdf->ln(5);
					//$pdf->cell(10, 5, $numero, 1, 0, 'C', 0, 0, 1);
					$pdf->cell(60, 5, $matricula->alumno->getFullName(), 1, 0, 'L', 0, 0, 1);
					$pdf->cell(30, 5, $matricula->grupo->nivel->nombre.' - '.$matricula->grupo->getGrado().' '.$matricula->grupo->seccion, 1, 0, 'C', 0, 0, 1);

					if($tipo_pago == 0){
						$pdf->cell(20, 5, 'Matrícula', 1, 0, 'C', 0, 0, 1);
						$pdf->cell(25, 5, number_format($matricula->costo->matricula, 2), 1, 0, 'C', 0);

						$total += $matricula->costo->matricula; 
					}else{
						$pdf->cell(20, 5, $this->COLEGIO->getCicloPensionesSingle($x), 1, 0, 'C', 0, 0, 1);
						$pdf->cell(25, 5, number_format($matricula->costo->pension, 2), 1, 0, 'C', 0);

						$total += $matricula->costo->pension;
					}


					++$numero;
					$pdf->cell(25, 5, number_format($total_pagos->monto_total, 2), 1, 0, 'C', 0);
					$totalp += $total_pagos->monto_total;
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
		$pdf->cell(30);
		$pdf->cell(30, 5, "REGISTROS", 1, 0, 'C', 1);
		$pdf->cell(30, 5, $numero, 1, 0, 'C', 0);
		$pdf->cell(20, 5, "TOTAL", 1, 0, 'C', 1);
		$pdf->cell(25, 5, number_format($total, 2), 1, 0, 'C', 0);
		$pdf->cell(25, 5, number_format($totalp, 2), 1, 0, 'C', 0);

		$pdf->output();
	}

	function impresiones(){
		$impresiones = Impresion::all(array(
			'conditions' => 'serie = '.intval($this->get->serie).' AND (numero >= '.intval($this->get->numero_desde).' AND numero <= '.intval($this->get->numero_hasta).') AND tipo_documento = "'.$this->get->tipo_documento.'"',
			'order' => 'serie ASC, numero ASC'
		));
		

		if($this->get->tipo_documento == 'BOLETA') $this->render('pagos:imprimir', array('impresiones' => $impresiones));

		if($this->get->tipo_documento == 'NOTA'){
			$this->crystal->load('TCPDF');
			$nd = (object) $this->COLEGIO->getImpresionNotasDebito();
			pdfMora($impresiones, $nd);
		}
	}

	function verificacion_saldos(){
		if(!isset($this->get->grupo_id)){
			$grupo = $this->COLEGIO->getGrupo($this->get);
		}else{
			$grupo = Grupo::find($this->get->grupo_id);
		}

		if(!$grupo){
			$grupo = new Grupo((array) $this->get);
		}

		if($this->get->mostrar == 'TODOS'){
			$matriculas = $grupo->getMatriculas();
		}elseif($this->get->mostrar == 'PAGADOS'){
			$xdeudores = Matricula::all(array(
				//'select' => 'matriculas.id, matriculas.alumno_id, matriculas.grupo_id',
				'conditions' => 'matriculas.grupo_id = "'.$grupo->id.'" AND pagos.estado_pago = "PENDIENTE"',
				'joins' => 'INNER JOIN pagos ON pagos.matricula_id = matriculas.id',
				'group' => 'pagos.matricula_id'
			));
			$ids = [];

			foreach($xdeudores As $deudor){
				$ids[] = $deudor->id;
			}

			$matriculas = $grupo->getMatriculas();

		}else{
			$matriculas = Matricula::all(array(
				//'select' => 'matriculas.id, matriculas.alumno_id, matriculas.grupo_id',
				'conditions' => 'matriculas.grupo_id = "'.$grupo->id.'" AND pagos.estado_pago = "PENDIENTE"',
				'joins' => 'INNER JOIN pagos ON pagos.matricula_id = matriculas.id',
				'group' => 'pagos.matricula_id'
			));
		}
		
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF();
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->setFillColor(225, 225, 225);
		foreach($matriculas As $matricula){
			if($matricula->isOculto()) continue;
			if($this->get->mostrar == 'PAGADOS' && in_array($matricula->id, $ids)) continue;
			$pdf->addPage();
			$pdf->setFont('Helvetica', 'b', '13');
			$pdf->cell(0, 10, 'SALDO DE PENSIONES DEL ALUMNO', 0, 0, 'C');
			$pdf->ln(13);
			$pdf->setFont('Helvetica', 'b', 9);
			$pdf->cell(30, 5, 'NOMBRE', 1, 0, 'C', 1);
			$pdf->setFont('Helvetica', '', 9);
			$pdf->cell(80, 5, $matricula->alumno->getFullName(), 1, 0, 'C', 0, 0, 1);

			$pdf->setFont('Helvetica', 'b', 9);
			$pdf->cell(30, 5, 'FECHA', 1, 0, 'C', 1);
			$pdf->setFont('Helvetica', '', 9);
			$pdf->cell(50, 5, date('d-m-Y'), 1, 0, 'C', 0, 0, 1);

			$pdf->ln(5);
			$pdf->setFont('Helvetica', 'b', 9);
			$pdf->cell(30, 5, 'DNI', 1, 0, 'C', 1);
			$pdf->setFont('Helvetica', '', 9);
			$pdf->cell(80, 5, $matricula->alumno->nro_documento, 1, 0, 'C', 0, 0, 1);
			$pdf->ln(5);

			$tutor = $matricula->grupo->tutor;
			$pdf->setFont('Helvetica', 'b', 9);
			$pdf->cell(30, 5, 'TUTOR', 1, 0, 'C', 1);
			$pdf->setFont('Helvetica', '', 9);
			$pdf->cell(80, 5, !is_null($tutor) ? $tutor->getFullName() : '-', 1, 0, 'C', 0, 0, 1);

			$pdf->setFont('Helvetica', 'b', 9);
			$pdf->cell(30, 5, 'GRADO', 1, 0, 'C', 1);
			$pdf->setFont('Helvetica', '', 9);
			$pdf->cell(50, 5, $matricula->grupo->nivel->nombre.' '.$matricula->grupo->getGrado().' '.$matricula->grupo->seccion, 1, 0, 'C', 0, 0, 1);

			$pdf->ln(10);
			$pagos = $matricula->getPagos();

			$pdf->setFont('Helvetica', 'b', 9);
			$pdf->cell(20, 5, 'N°', 1, 0, 'C', 1);
			$pdf->cell(40, 5, 'RECIBO', 1, 0, 'C', 1);
			$pdf->cell(60, 5, 'TIPO DE PAGO', 1, 0, 'C', 1);
			$pdf->cell(30, 5, 'MONTO', 1, 0, 'C', 1);
			$pdf->cell(40, 5, 'ESTADO', 1, 0, 'C', 1);

			foreach($pagos As $key => $pago){
				$impresion = $pago->getActiveImpresion(false);
				$pdf->ln(5);
				$pdf->setFont('Helvetica', '', 9);
				$pdf->cell(20, 5, $key + 1, 1, 0, 'C', 0);
				$pdf->cell(40, 5, !is_null($impresion) ? $impresion->getSerieNumero() : '-', 1, 0, 'C', 0);
				$pdf->cell(60, 5, $pago->getTipoDescription(), 1, 0, 'C', 0);
				$pdf->cell(30, 5, number_format($pago->monto, 2), 1, 0, 'C', 0);
				if($pago->estado_pago == 'PENDIENTE'){
					$pdf->setTextColor(255, 0, 0);
				}
				$pdf->cell(40, 5, $pago->estado_pago, 1, 0, 'C', 0);
				$pdf->setTextColor(0, 0, 0);
			}
		}

		$pdf->output();

	}









	function concar_caja_chica(){
		set_time_limit(0);

		$this->crystal->load('PHPExcel');
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$excel = PHPExcel_IOFactory::load('./Static/Templates/concar.xlsx');
		$s1 = $excel->getSheet(0);

		// BOLETAS - FACTURACIÓN

		$conditions = 'colegio_id = "'.$this->COLEGIO->id.'" AND fecha BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")';
		if(!empty($this->get->tipo_pago)){
			$conditions .= ' AND tipo_pago = "'.$this->get->tipo_pago.'"';
		}

		$boletas = Boleta::all(array(
			'conditions' => $conditions
		));

		$matriculasTalleres = Grupo_Taller_Matricula::all([
			'conditions' => 'DATE(fecha_registro) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
		]);


		$registros = array();
		foreach($boletas As $boleta)
			$registros[] = new xBoleta($boleta, 'BOLETA');

		foreach($matriculasTalleres As $matriculaTaller){
			$registros[] = new xBoleta($matriculaTaller, 'MATRICULA_TALLER');
		}

		usort($registros, function($a, $b){
			$nro1 = $a->getSerieNumero();
			$nro2 = $b->getSerieNumero();
			return strcmp($nro1, $nro2);
		});


		$currentRow = 1;
		$autoNumeric = 1;
		foreach($registros As $registro){
			if(!$registro->anulado()){
				++$currentRow;
				
				// PRIMERA FILA GENERAL
				/*
				if(in_array($registro->type, array('MATRICULA', 'PENSION', 'COMEDOR'))){
					$this->concarLineBanco($registro, $s1, $currentRow);
					continue;
				}
				*/

				$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario('CAJA_CHICA'), PHPExcel_Cell_DataType::TYPE_STRING);
				$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
				$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('D'.$currentRow, 'MN');
				$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', BV '.$registro->getSerieIntNumero());
				$s1->setCellValue('F'.$currentRow, 0);
				$s1->setCellValue('G'.$currentRow, 'V');
				$s1->setCellValue('H'.$currentRow, 'S');

				//$s1->setCellValue('J'.$currentRow, $registro->getCuenta()); // OLD -> '121201'
				$s1->setCellValue('J'.$currentRow, '101101');
				//$s1->setCellValueExplicitByColumnAndRow(10, $currentRow, '04112', PHPExcel_Cell_DataType::TYPE_STRING);

				$s1->setCellValue('K'.$currentRow, '');

				$s1->setCellValue('L'.$currentRow, '');
				$s1->setCellValue('M'.$currentRow, 'D');
				$s1->setCellValue('N'.$currentRow, number_format($registro->getMontoTotal(), 2));
				$s1->setCellValue('Q'.$currentRow, 'BV');
				$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
				$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

				$s1->setCellValueExplicitByColumnAndRow(20, $currentRow, '010', PHPExcel_Cell_DataType::TYPE_STRING);

				$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());
				
				$s1->setCellValueExplicitByColumnAndRow(23, $currentRow, '008', PHPExcel_Cell_DataType::TYPE_STRING);

				// CAMPOS ADICIONALES
				if($registro->isPenalidad()){
					$s1->setCellValue('Y'.$currentRow, 'BV');
					$s1->setCellValue('Z'.$currentRow, $registro->getSerieIntNumeroForMora());
					$s1->setCellValue('AA'.$currentRow, date('d/m/Y', strtotime($registro->getFechaForMora())));
					$s1->setCellValue('AB'.$currentRow, $registro->getMontoForMora());
					$s1->setCellValue('AC'.$currentRow, '0');
				}

				if(false){
				//if($registro->desglozarIGV()){
					++$currentRow;

					$importe = $registro->getMontoTotal() / 1.18;
					$igv = $importe * 18 / 100;

					

					$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario('CAJA_CHICA'), PHPExcel_Cell_DataType::TYPE_STRING);

					//$s1->setCellValue('A'.$currentRow, "05");
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', BV '.$registro->getSerieIntNumero());
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'S');

					$s1->setCellValue('J'.$currentRow, '401111');
					$s1->setCellValue('K'.$currentRow, $registro->getDNI());
					$s1->setCellValue('L'.$currentRow, '');
					$s1->setCellValue('M'.$currentRow, 'H');
					$s1->setCellValue('N'.$currentRow, number_format($igv, 2));

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

					$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());

					// TERCERA FILA
					++$currentRow;
					$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario('CAJA_CHICA'), PHPExcel_Cell_DataType::TYPE_STRING);
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', BV '.$registro->getSerieIntNumero());
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'S');

					$s1->setCellValue('J'.$currentRow, $registro->getCuenta());
					$s1->setCellValue('K'.$currentRow, $registro->getDNI());
					$s1->setCellValue('L'.$currentRow, '');
					$s1->setCellValue('M'.$currentRow, 'H');
					$s1->setCellValue('N'.$currentRow, number_format($importe, 2));

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

					$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());
				}else{
					++$currentRow;
					$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario('CAJA_CHICA'), PHPExcel_Cell_DataType::TYPE_STRING);
					$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
					$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('D'.$currentRow, 'MN');
					$s1->setCellValue('E'.$currentRow, mb_strtoupper($registro->getApellidos(), 'utf-8').', BV '.$registro->getSerieIntNumero());
					$s1->setCellValue('F'.$currentRow, 0);
					$s1->setCellValue('G'.$currentRow, 'V');
					$s1->setCellValue('H'.$currentRow, 'S');

					$s1->setCellValue('J'.$currentRow, $registro->getCuenta()); // OLD: getCuenta() - '121201'
					$s1->setCellValue('K'.$currentRow, $registro->getDNI());
					$s1->setCellValue('L'.$currentRow, '');
					$s1->setCellValue('M'.$currentRow, 'H');
					$s1->setCellValue('N'.$currentRow, number_format($registro->getMontoTotal(), 2));

					$s1->setCellValue('Q'.$currentRow, 'BV');
					$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
					$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
					$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

					$s1->setCellValue('V'.$currentRow, $registro->getDescripcion());
					if($registro->isPenalidad()){
						$s1->setCellValue('Y'.$currentRow, 'BV');
						$s1->setCellValue('Z'.$currentRow, $registro->getSerieIntNumeroForMora());
						$s1->setCellValue('AA'.$currentRow, date('d/m/Y', strtotime($registro->getFechaForMora())));
						$s1->setCellValue('AB'.$currentRow, $registro->getMontoForMora());
						$s1->setCellValue('AC'.$currentRow, '0');
					}
				}
			}else{
				++$currentRow;
				$s1->setCellValueExplicitByColumnAndRow(0, $currentRow, $registro->getSubDiario('CAJA_CHICA'), PHPExcel_Cell_DataType::TYPE_STRING);
				$s1->setCellValue('B'.$currentRow, date('m', strtotime($from)).str_pad($autoNumeric, 4, 0, STR_PAD_LEFT).' ');
				$s1->setCellValue('C'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('D'.$currentRow, 'MN');

				$s1->setCellValue('E'.$currentRow, 'ANULADO BV '.$registro->getSerieIntNumero());
				$s1->setCellValue('F'.$currentRow, 0);
				$s1->setCellValue('G'.$currentRow, 'V');
				$s1->setCellValue('H'.$currentRow, 'N');

				$s1->setCellValue('J'.$currentRow, '121201');
				$s1->setCellValue('K'.$currentRow, '');
				$s1->setCellValue('L'.$currentRow, '');
				$s1->setCellValue('M'.$currentRow, 'D');
				$s1->setCellValue('N'.$currentRow, '0');

				$s1->setCellValue('Q'.$currentRow, 'BV');
				$s1->setCellValue('R'.$currentRow, $registro->getSerieIntNumero());
				$s1->setCellValue('S'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));
				$s1->setCellValue('T'.$currentRow, date('d/m/Y', strtotime($registro->getFecha())));

				$s1->setCellValue('V'.$currentRow, 'ANULADO BV '.$registro->getSerieIntNumero());
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


	    // HOJA 2
	    // MORAS
		
		$excel->removeSheetByIndex(1);

		writeExcel($excel);
	}


	function visitantes(){

		$visitantes = !empty($this->get->fecha) ? Visitante::find_all_by_fecha(date('Y-m-d', strtotime($this->get->fecha))) : Visitante::all();

		
		$this->crystal->load('TCPDF');
		$pdf = new TCPDF('L');
		$pdf->setMargins(5,5,5);
		$pdf->setAutoPageBreak(true, 5);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);

		$pdf->addPage();
		$this->setLogo($pdf);
		$pdf->SetFont('helvetica', 'b', 13);
		$pdf->setFillColor(240, 240, 240);
		//$pdf->image('./Static/Image/logo.jpg', 10, 3, 45, 17);
		$pdf->cell(0, 5,'Registros Visita Guiada', 0, 1,'R');
		$pdf->SetFont('helvetica', 'b', 10);
		//$pdf->cell(0,5, 'Profesores del: '.$grupo->getGrado().' '.$this->get->seccion.' - '.$nivel->nombre,0, 0, 'R');
		$pdf->ln(15);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->cell(10, 5, 'N°', 1, 0, 'C', 1);
		$pdf->cell(20, 5, 'DNI', 1, 0, 'C', 1);
		$pdf->cell(55, 5, 'APODERADO', 1, 0, 'C', 1);
		$pdf->cell(25, 5, 'TELÉFONO', 1, 0, 'C', 1);
		$pdf->cell(45, 5, 'EMAIL', 1, 0, 'C', 1);
		$pdf->cell(30, 5, 'GRADO', 1, 0, 'C', 1);
		$pdf->cell(55, 5, 'ALUMNO', 1, 0, 'C', 1);
		$pdf->cell(45, 5, 'DIRECCIÓN', 1, 0, 'C', 1);
		//$pdf->cell(30, 5, 'DNI', 1, 0, 'C', 1);
		foreach($visitantes As $key => $visitante){
			
			$pdf->ln(5);
			$pdf->cell(10, 5, $key + 1, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(20, 5, $visitante->dni, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(55, 5, $visitante->apoderado, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(25, 5, $visitante->telefono, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(45, 5, $visitante->email, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, $visitante->grado.'° '.$visitante->nivel, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(55, 5, $visitante->alumno, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(45, 5, $visitante->direccion, 1, 0, 'C', 0, 0, 1);

		}
		$pdf->output();
	}



	function pagos_sin_moras(){
		
		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$pagos = Pago::all([
			'conditions' => 'pagos.tipo = 1 AND pagos.estado_pago = "CANCELADO" AND pagos.mora <= 0 AND fecha_cancelado BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND grupos.anio = "'.$this->get->anio.'"',
			'joins' => '
			INNER JOIN matriculas ON matriculas.id = pagos.matricula_id
			INNER JOIN grupos ON grupos.id = matriculas.grupo_id',
			'order' => 'fecha_cancelado ASC'
		]);

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
		$pdf->cell(0,10,'PAGOS SIN MORAS',0,0,'R');
		$pdf->ln(17);
		$pdf->setFont('Helvetica','b',10);
		$pdf->cell(7,5,'Nº',1,0,'C', 1);
		$pdf->cell(60,5,'Apellidos y Nombres',1,0,'C', 1);
		
		
		$pdf->cell(25,5,'Nº Doc.',1,0,'C', 1);
		
		$pdf->cell(45,5,'Teléfono',1,0,'C', 1);
		$pdf->cell(50,5,'Email',1,0,'C', 1);
		$pdf->cell(30,5,'Tipo de Pago',1,0,'C', 1);
		$pdf->cell(20,5,'Fecha de Pago',1,0,'C', 1, 0, 1);
		$pdf->cell(20,5,'Mes',1,0,'C', 1);
		$pdf->cell(30,5,'Sección',1,0,'C', 1);

		$pdf->ln(5);
		$pdf->setFont('Helvetica','',9);
		$i = 1;

		foreach($pagos As $pago){
			if($pago->matricula->isOculto()) continue;
			if(!$pago->inHistorial()) continue;
			
			$matricula = $pago->matricula;
			$alumno = $matricula->alumno;
			$domicilio = $alumno->getDomicilio();
			$apoderado = $alumno->getFirstApoderado();

			$pdf->cell(7,5,$i,1,0,'C');
			$pdf->cell(60,5, $alumno->getFullName(),1,0,'L', 0, '', 1);
			

			$pdf->cell(25,5, $alumno->nro_documento,1,0,'C',  0, '', 1);
			

			$apoderado = $matricula->alumno->getFirstApoderado();
			$telefonos = array();
			if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
			if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

			$pdf->cell(45, 5, implode(' - ', $telefonos), 1, 0, 'C', 0, 0, 1);
			$pdf->cell(50, 5, $apoderado->email, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, $pago->getTipoDescription(), 1, 0, 'C', 0, 0, 1);
			
			$pdf->cell(20, 5, $pago->fecha_cancelado, 1, 0, 'C', 0, 0, 1);
			$pdf->cell(20, 5, $this->COLEGIO->MESES[intval(date('m', strtotime($pago->fecha_cancelado)))-1], 1, 0, 'C', 0, 0, 1);
			$pdf->cell(30, 5, $matricula->grupo->nivel->nombre.' - '.$matricula->grupo->grado.''.$matricula->grupo->seccion, 1, 0, 'C', 0, 0, 1);
			$pdf->ln(5);

		
			$i++;
		}

		$pdf->output('asistencia.pdf','I');
	}


	function consolidado_promedios(){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/consolidado_final.xlsx');
		$sheet = $excel->getSheet(0);

		$alumnoFillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => '99CCFF',
				),
			),
		);

		

		$cursoFillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'CCFFCC',
				),
			),
		);

		$areaFillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'FFFF99',
				),
			),
		);

		$promedioFillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'FDE9D9',
				),
			),
		);

		$promedioFinalStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'D4E6F1',
				),
			),
		);

		$jaladoStyle = array(
	        'font'  => array(
		        //'bold'  => true,
		        'color' => array('rgb' => 'FF0000'),
		        //'size'  => 10,
		        //'name'  => 'Calibri'
		    ),
	    );

		$headerRow = 7;
		$contentRow = 8;
		$grupos = Grupo::all([
			'conditions' => 'anio = "'.$this->get->anio.'"',
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		]);
		$ciclo = $this->get->ciclo;
		$bonificacion = $this->get->bonificacion;

		foreach($grupos As $keyGrupo => $grupo){
			$s = clone $sheet;
			$s->setTitle($grupo->nivel->nombre.'-'.$grupo->grado.''.$grupo->seccion);
			$excel->addSheet($s);

			$matriculas = $grupo->getMatriculas();
		

			$s->getStyle('A'.$headerRow)->applyFromArray($alumnoFillStyle);
			$s->getStyle('B'.$headerRow)->applyFromArray($alumnoFillStyle);
			$s->getStyle('C'.$headerRow)->applyFromArray($alumnoFillStyle);

			$s->setCellValue('A'.$headerRow, 'N°');
			//$s->mergeCells('A'.$headerRow.':A'.($headerRow + 1));
			$s->setCellValue('B'.$headerRow, 'DNI');
			//$s->mergeCells('B'.$headerRow.':B'.($headerRow + 1));
			$s->setCellValue('C'.$headerRow, 'ALUMNOS');
			//$s->mergeCells('C'.$headerRow.':C'.($headerRow + 1));

			$row = $headerRow;
			$col = 4;
			
			

			$xasignaturas = $grupo->getAsignaturas();

			foreach($xasignaturas As $asignatura){
				$cell = numToChar($col).$row;
				$s->getStyle($cell)->applyFromArray($areaFillStyle);
				$s->getStyle($cell)->getAlignment()
                            ->setTextRotation(90)
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$s->setCellValue($cell, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'));
				++$col;
			} 

			$row = $contentRow;
			$i = 1;
			foreach($matriculas As $key => $matricula){
				$alumno = $matricula->alumno;

				$s->setCellValue('A'.$row, $i);
				$s->setCellValue('B'.$row, $alumno->nro_documento);
				$s->setCellValue('C'.$row, $alumno->getFullName());
				$col = 4;
				
				foreach($xasignaturas As $asignatura){
					if(in_array($asignatura->id, $used)) continue;

					$promedioAsignatura = $matricula->getPromedio($asignatura->id, $ciclo);
					$s->setCellValue(numToChar($col).$row, $promedioAsignatura);
					if($promedioAsignatura < 11){
						$s->getStyle(numToChar($col).$row)->applyFromArray($jaladoStyle);
					}
					//$s->getStyle(numToChar($col).$row)->applyFromArray($promedioFillStyle);
					++$col;
				}

				++$i;
				++$row;
			}



			$s->getColumnDimension('A')->setWidth(5);
			$s->getColumnDimension('B')->setWidth(15);
			$s->getColumnDimension('C')->setWidth(50);

			$s->getStyle('A'.$headerRow.':'.numToChar($col - 1).($row - 1))->applyFromArray(array(
	            'font'  => array(
			        //'bold'  => true,
			        //'color' => array('rgb' => '000000'),
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
	        ));

			$s->getStyle('A'.$headerRow.':'.numToChar($col - 1).($headerRow ))->applyFromArray(array(
				'font'  => array(
			        'bold'  => true,
			        //'color' => array('rgb' => '000000'),
			        'size'  => 10,
			        'name'  => 'Calibri'
				),
			));

	        $s->getStyle('C'.$contentRow.':C'.($row - 1))->applyFromArray(array(
	        	'alignment' => array(
					'wrap' => true,
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				),
	        ));

			$s->freezePane( "D".$headerRow );
		}


		$excel->removeSheetByIndex(0);

	
		writeExcel($excel);

		//writeExcel($excel);
	}


	function consolidado_promedios_detalles(){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/consolidado_final.xlsx');
		$sheet = $excel->getSheet(0);

		$alumnoFillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => '99CCFF',
				),
			),
		);

		

		$cursoFillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'CCFFCC',
				),
			),
		);

		$areaFillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'FFFF99',
				),
			),
		);

		$promedioFillStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'FDE9D9',
				),
			),
		);

		$promedioFinalStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'D4E6F1',
				),
			),
		);

		$jaladoStyle = array(
	        'font'  => array(
		        //'bold'  => true,
		        'color' => array('rgb' => 'FF0000'),
		        //'size'  => 10,
		        //'name'  => 'Calibri'
		    ),
	    );

		$headerRow = 7;
		$contentRow = 9;
		$grupos = Grupo::all([
			'conditions' => 'anio = "'.$this->get->anio.'" AND nivel_id = "'.$this->get->nivel_id.'"',
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		]);

		$ciclo = $this->get->ciclo;
		$bonificacion = $this->get->bonificacion;

		foreach($grupos As $keyGrupo => $grupo){
			$s = clone $sheet;
			$s->setTitle($grupo->nivel->nombre.'-'.$grupo->grado.''.$grupo->seccion);
			$excel->addSheet($s);

			$matriculas = $grupo->getMatriculas();
		

			$s->getStyle('A'.$headerRow)->applyFromArray($alumnoFillStyle);
			$s->getStyle('B'.$headerRow)->applyFromArray($alumnoFillStyle);
			$s->getStyle('C'.$headerRow)->applyFromArray($alumnoFillStyle);

			$s->setCellValue('A'.$headerRow, 'N°');
			$s->mergeCells('A'.$headerRow.':A'.($headerRow + 1));
			$s->setCellValue('B'.$headerRow, 'DNI');
			$s->mergeCells('B'.$headerRow.':B'.($headerRow + 1));
			$s->setCellValue('C'.$headerRow, 'ALUMNOS');
			$s->mergeCells('C'.$headerRow.':C'.($headerRow + 1));

			$row = $headerRow;
			$col = 4;
			
			

			$xasignaturas = $grupo->getAsignaturas();

			foreach($xasignaturas As $asignatura){
				$criterios = $asignatura->getCriterios();

				$cell = numToChar($col).$row;
				$s->mergeCells($cell.':'.numToChar($col + count($criterios)).$row);
				$s->getStyle($cell)->applyFromArray($areaFillStyle);
				//$s->getStyle($cell)->getAlignment()
                //            ->setTextRotation(90)
                //            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				$s->setCellValue($cell, mb_strtoupper($asignatura->curso->nombre, 'UTF-8'));
				
				//++$col;
				$col += count($criterios) + 1;
			}

			$col = 4;
			foreach($xasignaturas As $asignatura){
				$criterios = $asignatura->getCriterios();
				foreach($criterios As $keyCriterio => $criterio){
					$cell = numToChar($col).($row + 1);
					$s->getStyle($cell)->applyFromArray($cursoFillStyle);
					$s->setCellValue($cell, mb_strtoupper('C'.($keyCriterio + 1), 'UTF-8'));
					$descripcion = $criterio->descripcion;
					if($asignatura->grupo->nivel->calificacionPorcentual()){
						$descripcion .= ' ('.$criterio->peso.'%)';
					}
					$s->getComment($cell)
                            ->getText()->createTextRun($descripcion);
					++$col;
				}

				$cell = numToChar($col).($row + 1);
				$s->getStyle($cell)->applyFromArray($cursoFillStyle);
				$s->setCellValue($cell, mb_strtoupper('PROM', 'UTF-8'));
				++$col;
			}


			$row = $contentRow;
			$i = 1;
			foreach($matriculas As $key => $matricula){
				$alumno = $matricula->alumno;

				$s->setCellValue('A'.$row, $i);
				$s->setCellValue('B'.$row, $alumno->nro_documento);
				$s->setCellValue('C'.$row, $alumno->getFullName());
				$col = 4;
				
				foreach($xasignaturas As $asignatura){
					if(in_array($asignatura->id, $used)) continue;

					$criterios = $asignatura->getCriterios();
					foreach($criterios As $keyCriterio => $criterio){
						$cell = numToChar($col).($row);
						$nota = $matricula->getNota($asignatura->id, $criterio->id, $ciclo);
						$s->setCellValue($cell, $nota);
						++$col;
					}

					$promedioAsignatura = $matricula->getPromedio($asignatura->id, $ciclo);
					$s->setCellValue(numToChar($col).$row, $promedioAsignatura);
					if($promedioAsignatura < 11){
						$s->getStyle(numToChar($col).$row)->applyFromArray($jaladoStyle);
					}
					$s->getStyle(numToChar($col).$row)->applyFromArray($promedioFillStyle);
					++$col;
				}

				++$i;
				++$row;
			}



			$s->getColumnDimension('A')->setWidth(5);
			$s->getColumnDimension('B')->setWidth(15);
			$s->getColumnDimension('C')->setWidth(50);

			$s->getStyle('A'.$headerRow.':'.numToChar($col - 1).($row - 1))->applyFromArray(array(
	            'font'  => array(
			        //'bold'  => true,
			        //'color' => array('rgb' => '000000'),
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
	        ));

			$s->getStyle('A'.$headerRow.':'.numToChar($col - 1).($headerRow ))->applyFromArray(array(
				'font'  => array(
			        'bold'  => true,
			        //'color' => array('rgb' => '000000'),
			        'size'  => 10,
			        'name'  => 'Calibri'
				),
			));

	        $s->getStyle('C'.$contentRow.':C'.($row - 1))->applyFromArray(array(
	        	'alignment' => array(
					'wrap' => true,
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				),
	        ));

			$s->freezePane( "D".$headerRow );

			//break; // remote this
		}


		$excel->removeSheetByIndex(0);

	
		writeExcel($excel);

		//writeExcel($excel);
	}


	function reporte_alumnos(){
		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/no_matriculados.xlsx');
		$s = $excel->getSheet(0);
		$s->setCellValue('A2', 'REPORTE DE ALUMNOS '.$this->get->anio);
		$s->setCellValue('E4', 'TIPO');
		$s->setCellValue('F4', 'ANTIGUEDAD');
		$grupos = $this->COLEGIO->getGrupos($this->get->anio); // año anterior

		$row = 5;
		$key = 1;
		foreach($grupos As $grupo){
			$matriculas = $grupo->getMatriculas();
			foreach($matriculas As $matricula){
				$totalMatriculas = Matricula::count_by_alumno_id($matricula->alumno_id);

				if(!empty($this->get->modalidad) && $this->get->modalidad != $matricula->modalidad)
					continue;
				if($this->get->antiguedad == "NUEVO" && $totalMatriculas > 1)
					continue;

				if($this->get->antiguedad == "ANTIGUO" && $totalMatriculas <= 1)
					continue;

				$apoderado = $matricula->alumno->getFirstApoderado();
				$telefonos = array();
				if(!empty($apoderado->telefono_fijo)) $telefonos[] = $apoderado->telefono_fijo;
				if(!empty($apoderado->telefono_celular)) $telefonos[] = $apoderado->telefono_celular;

				$s->setCellValue('A'.$row, $key);
				$s->setCellValue('B'.$row, $matricula->alumno->getFullName());
				$s->setCellValue('C'.$row, $grupo->getNombreShort2());
				$s->setCellValue('D'.$row, $matricula->alumno->nro_documento);
				$s->setCellValue('E'.$row, $matricula->modalidad);

				

				$s->setCellValue('F'.$row, $totalMatriculas > 1 ? 'ANTIGUO' : 'NUEVO');

				++$key;
				++$row;
			}

		}

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

		$s->getStyle('A5:F'.($row - 1))->applyFromArray($normalStyle);
		$s->getStyle('B5:B'.($row - 1))->applyFromArray(array(
			'alignment' => array(
				'wrap' => true,
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
		));

		writeExcel($excel);
	}


	function asistencia_personal(){
		$this->crystal->load('PHPExcel:PHPExcel');

		$normalStyle = array(
		    'font'  => array(
		        //'bold'  => true,
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

	    $leftStyle = array(
	    	'alignment' => array(
				'wrap' => true,
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
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

		$excel = PHPExcel_IOFactory::load('./Static/Templates/entradas_salidas.xlsx');
		$s1 = $excel->getSheet(0);
        $s2 = $excel->getSheet(1);

        $from = date('Y-m-d', strtotime($this->get->from));
        $to = date('Y-m-d', strtotime($this->get->to));

        $s1->setCellValue('A1', 'RESULTADOS FINALES - DESCUENTOS del '.$from.' al '.$to);

        $trabajadores = $this->COLEGIO->getPersonal();
        $total = array();
		while(true){
			$s = clone $s2;
			$s->setTitle($from);
			$excel->addSheet($s);

			// detalles for fecha
			$currentRow = 5;
			$s->setCellValue('A3', $from);
			$s->setCellValue('J4', $this->COLEGIO->moneda.' '.number_format($this->COLEGIO->descuento_minuto, 2));
			foreach($trabajadores As $key => $trabajador){
				$entrada = Trabajador_Asistencia::find_by_fecha_and_tipo_and_trabajador_id($from, 'ENTRADA', $trabajador->id);
				$salida = Trabajador_Asistencia::find_by_fecha_and_tipo_and_trabajador_id($from, 'SALIDA', $trabajador->id);


				$s->setCellValue('A'.$currentRow, $key + 1);
				$s->setCellValue('B'.$currentRow, $trabajador->getFullName());

				$s->setCellValue('C'.$currentRow, $entrada->hora_real);
				$s->setCellValue('D'.$currentRow, $salida->hora_real);

				$s->setCellValue('E'.$currentRow, $trabajador->hora_entrada);
				$s->setCellValue('F'.$currentRow, $trabajador->hora_salida);

				$s->setCellValue('G'.$currentRow, $entrada->minutos_tardanza);
				$s->setCellValue('H'.$currentRow, $salida->minutos_tardanza);
				$s->setCellValue('I'.$currentRow, $entrada->minutos_tardanza + $salida->minutos_tardanza);
				$total[$trabajador->id]['minutos'] += $entrada->minutos_tardanza + $salida->minutos_tardanza;

				//$s->setCellValue('J'.$currentRow, number_format($this->COLEGIO->descuento_minuto, 2));
				$s->setCellValue('J'.$currentRow, '=J4'); 

				$s->setCellValue('K'.$currentRow, $this->COLEGIO->moneda.' '.number_format($entrada->descuento, 2));
				$s->setCellValue('L'.$currentRow, $this->COLEGIO->moneda.' '.number_format($salida->descuento, 2));
				$s->setCellValue('M'.$currentRow, $this->COLEGIO->moneda.' '.number_format($entrada->descuento + $salida->descuento, 2));
				$falta = $trabajador->getAsistencia('FALTA', $from);
				$s->setCellValue('N'.$currentRow, isset($falta) ? 'FALTA' : 'PRESENTE');
				$total[$trabajador->id]['descuento'] += $entrada->descuento + $salida->descuento;
				++$currentRow;
			}

			$s->getStyle('A5:N'.(count($trabajadores) - 1 + 5))->applyFromArray($normalStyle);
			$s->getStyle('B5:B'.(count($trabajadores) - 1 + 5))->applyFromArray($leftStyle);

			if(strtotime($from) == strtotime($to) || strtotime($from) > strtotime($to)){
				break;
			}
			
			$from = date('Y-m-d', strtotime($from. ' + 1 days'));
			
		}

		// totales
		$currentRow = 5;
		foreach($trabajadores As $key => $trabajador){
			$s1->setCellValue('A'.$currentRow, $key + 1);
			$s1->setCellValue('B'.$currentRow, $trabajador->getFullName());
			$s1->setCellValue('C'.$currentRow, $total[$trabajador->id]['minutos']);
			$s1->setCellValue('D'.$currentRow, $this->COLEGIO->moneda.' '.number_format($total[$trabajador->id]['descuento'], 2));
			$s1->setCellValue('E'.$currentRow, $trabajador->getTotalAsistencia('FALTA', $this->get->from, $this->get->to));

			++$currentRow;
		}

		$s1->getStyle('A5:E'.(count($trabajadores) - 1 + 5))->applyFromArray($normalStyle);
		$s1->getStyle('B5:B'.(count($trabajadores) - 1 + 5))->applyFromArray($leftStyle);

		$excel->removeSheetByIndex(1);
		//print_r($s1);

		writeExcel($excel);
	}

	function reporte_starsoft(){
		set_time_limit(0);

		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/reporte_starsoft.xlsx');
		$s = $excel->getSheet(0);

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

		$registros = $this->getRegistrosConcar();
		$done = [];
		$row = 2;
		$correlativo = 1;
		foreach($registros As $registro){
			$numero = $registro->getTipo().$registro->getSerie(4-strlen($registro->getTipo())).str_pad($registro->getNumero(), 8, 0, STR_PAD_LEFT);
			if(in_array($numero, $done))
				continue;
			// LINEA 1
			$s->setCellValue("A{$row}", "1212101"); // defecto
			$s->setCellValue("B{$row}", $this->params->anio.str_pad($this->params->mes, 2, 0, STR_PAD_LEFT));
			$s->setCellValueExplicit("C{$row}", "03", PHPExcel_Cell_DataType::TYPE_STRING);
			$s->setCellValueExplicit("D{$row}", str_pad($correlativo, 4, 0, STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
			$s->setCellValue("E{$row}", date('d/m/Y', strtotime($registro->getFecha())));
			$s->setCellValueExplicit("F{$row}", "02", PHPExcel_Cell_DataType::TYPE_STRING);
			$s->setCellValueExplicit("G{$row}", $registro->getDNI(), PHPExcel_Cell_DataType::TYPE_STRING);
			$s->setCellValue("H{$row}", $registro->getTipoDocumento());
			$s->setCellValue("I{$row}", $numero);
			$s->setCellValue("K{$row}", date('d/m/Y', strtotime($registro->getFecha())));
			$s->setCellValue("L{$row}", $registro->getTipoDocumentoForMora());
			$s->setCellValue("M{$row}", $registro->getSerieIntNumeroForMora2());
			$s->setCellValueExplicit("R{$row}", number_format($registro->getMontoTotal(), 2), PHPExcel_Cell_DataType::TYPE_STRING);
			$s->setCellValue("S{$row}", "VTA");
			$s->setCellValue("U{$row}", $registro->getDescripcionGlosa());
			$s->setCellValue("V{$row}", $registro->getDescripcion());
			$s->setCellValue("W{$row}", 0);
			$s->setCellValue("X{$row}", 'D');
			$s->setCellValue("AA{$row}", '');
			$s->setCellValue("AB{$row}", date('d/m/Y', strtotime($registro->getFechaVencimiento())));
			$s->setCellValue("AC{$row}", $registro->getFechaForMora2());
			$s->setCellValue("AD{$row}", 0);
			++$row;
			$s->setCellValue("A{$row}", $registro->getCuentaStarsoft()); 
			$s->setCellValue("B{$row}", $this->params->anio.str_pad($this->params->mes, 2, 0, STR_PAD_LEFT));
			$s->setCellValueExplicit("C{$row}", "03", PHPExcel_Cell_DataType::TYPE_STRING);
			$s->setCellValueExplicit("D{$row}", str_pad($correlativo, 4, 0, STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
			$s->setCellValue("E{$row}", date('d/m/Y', strtotime($registro->getFecha())));
			$s->setCellValue("H{$row}", $registro->getTipoDocumento());
			$s->setCellValue("I{$row}", $registro->getTipo().$registro->getSerie(4-strlen($registro->getTipo())).str_pad($registro->getNumero(), 8, 0, STR_PAD_LEFT));
			$s->setCellValue("K{$row}", date('d/m/Y', strtotime($registro->getFecha())));
			$s->setCellValueExplicit("R{$row}", number_format($registro->getMontoTotal(), 2), PHPExcel_Cell_DataType::TYPE_STRING);
			$s->setCellValue("S{$row}", "VTA");
			$s->setCellValue("U{$row}", $registro->getDescripcionGlosa());
			$s->setCellValue("V{$row}", $registro->getDescripcion());
			$s->setCellValue("W{$row}", 0);
			$s->setCellValue("X{$row}", 'H');
			$s->setCellValueExplicit("AA{$row}", '060201', PHPExcel_Cell_DataType::TYPE_STRING);
			$s->setCellValue("AB{$row}", date('d/m/Y', strtotime($registro->getFechaVencimiento())));
			$s->setCellValue("AD{$row}", 0);

			$row++;
			$correlativo++;
			$done[] = $numero;
		}

		if(count($registros) > 0){
			$s->getStyle('A2:AH'.($row - 1))->applyFromArray($normalStyle);
		}

		writeExcel($excel, "V_RV ".str_pad($this->params->mes, 2, 0, STR_PAD_LEFT).$this->params->anio.".xlsx");
	}

	function reporte_starsoft_ingresos(){
		set_time_limit(0);

		$this->crystal->load('PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/reporte_starsoft_ingresos.xlsx');
		$s = $excel->getSheet(0);

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

		$registros = $this->getRegistrosIngresosBanco();
		$done = [];
		$row = 2;
		$correlativo = 1;
		$numerosBanco = [];
		foreach($registros As $registro){
			$numeroBanco = substr($registro->getNroMovimiento(), -6);
			
			$numerosBanco[$numeroBanco]['total']++;
			$numerosBanco[$numeroBanco]['actual'] = 0;
		}
		$letras = explode(',', 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z');
		
		foreach($registros As $registro){
			$numero = $registro->getTipo().$registro->getSerie(4-strlen($registro->getTipo())).str_pad($registro->getNumero(), 8, 0, STR_PAD_LEFT);
			if(in_array($numero, $done))
				continue;
			if($registro->type == "MORA_NOTA")
				continue;

			$numeroBanco = substr($registro->getNroMovimiento(), -6);
			if($numerosBanco[$numeroBanco]['total'] > 1){
				$numeroBancoLetras = $numeroBanco.' - '.$letras[$numerosBanco[$numeroBanco]['actual']];
				$numerosBanco[$numeroBanco]['actual'] += 1;
				$numeroBanco = $numeroBancoLetras;
			}
			
			// linea 1
			$s->setCellValue('A'.$row, '1041101');$s->setCellValue('B'.$row, '|');
			$s->setCellValue("C{$row}", $this->params->anio.str_pad($this->params->mes, 2, 0, STR_PAD_LEFT));$s->setCellValue('D'.$row, '|');
			$s->setCellValueExplicit("E{$row}", "07", PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('F'.$row, '|');
			$s->setCellValueExplicit("G{$row}", str_pad($correlativo, 4, 0, STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('H'.$row, '|');
			$s->setCellValue("I{$row}", date('d/m/Y', strtotime($registro->getFechaCancelado())));$s->setCellValue('J'.$row, '|');
			$s->setCellValue('K'.$row, '');$s->setCellValue('L'.$row, '|');
			$s->setCellValueExplicit("M{$row}", "", PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('N'.$row, '|');
			$s->setCellValueExplicit("O{$row}", 'EN');$s->setCellValue('P'.$row, '|');
			$s->setCellValueExplicit("Q{$row}", $numeroBanco);$s->setCellValue('R'.$row, '|');
			$s->setCellValue("S{$row}", date('d/m/Y', strtotime($registro->getFechaVencimiento())));$s->setCellValue('T'.$row, '|');
			$s->setCellValueExplicit("U{$row}", 'MN');$s->setCellValue('V'.$row, '|');
			$s->setCellValueExplicit("W{$row}", number_format($registro->getMontoConMora(), 2), PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('X'.$row, '|');
			$s->setCellValueExplicit("Y{$row}", 'VTA');$s->setCellValue('Z'.$row, '|');
			$s->setCellValue("AA{$row}", date('d/m/Y', strtotime($registro->getFechaCancelado())));$s->setCellValue('AB'.$row, '|');
			$s->setCellValue("AC{$row}", '');$s->setCellValue('AD'.$row, '|');
			$s->setCellValue("AE{$row}", $registro->getDescripcionIngreso());$s->setCellValue('AF'.$row, '|');
			$s->setCellValue("AG{$row}", '');$s->setCellValue('AH'.$row, '|');
			$s->setCellValue("AI{$row}", "PAGO ".$registro->getDescripcion());$s->setCellValue('AJ'.$row, '|');
			$s->setCellValue("AK{$row}", '0');$s->setCellValue('AL'.$row, '|');
			$s->setCellValue("AM{$row}", 'D');$s->setCellValue('AN'.$row, '|');
			$s->setCellValueExplicit("AO{$row}", "001", PHPExcel_Cell_DataType::TYPE_STRING);
			// linea 2
			++$row;
			$s->setCellValue('A'.$row, '1212101');$s->setCellValue('B'.$row, '|');
			$s->setCellValue("C{$row}", $this->params->anio.str_pad($this->params->mes, 2, 0, STR_PAD_LEFT));$s->setCellValue('D'.$row, '|');
			$s->setCellValueExplicit("E{$row}", "07", PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('F'.$row, '|');
			$s->setCellValueExplicit("G{$row}", str_pad($correlativo, 4, 0, STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('H'.$row, '|');
			$s->setCellValue("I{$row}", date('d/m/Y', strtotime($registro->getFechaCancelado())));$s->setCellValue('J'.$row, '|');
			$s->setCellValueExplicit('K'.$row, '02');$s->setCellValue('L'.$row, '|');
			$s->setCellValueExplicit("M{$row}", $registro->getDNI(), PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('N'.$row, '|');
			$s->setCellValueExplicit("O{$row}", 'BV');$s->setCellValue('P'.$row, '|');
			$s->setCellValueExplicit("Q{$row}", $numero);$s->setCellValue('R'.$row, '|');
			$s->setCellValue("S{$row}", date('d/m/Y', strtotime($registro->getFechaVencimiento())));$s->setCellValue('T'.$row, '|');
			$s->setCellValueExplicit("U{$row}", 'MN');$s->setCellValue('V'.$row, '|');
			$s->setCellValueExplicit("W{$row}", number_format($registro->getMontoTotal(), 2), PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('X'.$row, '|');
			$s->setCellValueExplicit("Y{$row}", 'VTA');$s->setCellValue('Z'.$row, '|');
			$s->setCellValue("AA{$row}", date('d/m/Y', strtotime($registro->getFechaCancelado())));$s->setCellValue('AB'.$row, '|');
			$s->setCellValue("AC{$row}", '');$s->setCellValue('AD'.$row, '|');
			$s->setCellValue("AE{$row}", $registro->getDescripcionIngreso());$s->setCellValue('AF'.$row, '|');
			$s->setCellValue("AG{$row}", '');$s->setCellValue('AH'.$row, '|');
			$s->setCellValue("AI{$row}", "PAGO ".$registro->getDescripcion());$s->setCellValue('AJ'.$row, '|');
			$s->setCellValue("AK{$row}", '0');$s->setCellValue('AL'.$row, '|');
			$s->setCellValue("AM{$row}", 'H');$s->setCellValue('AN'.$row, '|');
			// linea 3
			$mora = $registro->getImpresionMora();
			
			if(!is_null($mora)){
				++$row;
				$registroMora = new xBoleta($mora, 'MORA_NOTA');
				//$registro = $registroMora;
				$numeroMora = $registroMora->getTipo().$registroMora->getSerie(4-strlen($registroMora->getTipo())).str_pad($registroMora->getNumero(), 8, 0, STR_PAD_LEFT);
				$s->setCellValue('A'.$row, '1212101');$s->setCellValue('B'.$row, '|');
				$s->setCellValue("C{$row}", $this->params->anio.str_pad($this->params->mes, 2, 0, STR_PAD_LEFT));$s->setCellValue('D'.$row, '|');
				$s->setCellValueExplicit("E{$row}", "07", PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('F'.$row, '|');
				$s->setCellValueExplicit("G{$row}", str_pad($correlativo, 4, 0, STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('H'.$row, '|');
				$s->setCellValue("I{$row}", date('d/m/Y', strtotime($registro->getFechaCancelado())));$s->setCellValue('J'.$row, '|');
				$s->setCellValueExplicit('K'.$row, '02');$s->setCellValue('L'.$row, '|');
				$s->setCellValueExplicit("M{$row}", $registro->getDNI(), PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('N'.$row, '|');
				$s->setCellValueExplicit("O{$row}", 'CD');$s->setCellValue('P'.$row, '|');
				$s->setCellValueExplicit("Q{$row}", $numeroMora);$s->setCellValue('R'.$row, '|');
				$s->setCellValue("S{$row}", date('d/m/Y', strtotime($registro->getFechaVencimiento())));$s->setCellValue('T'.$row, '|');
				$s->setCellValueExplicit("U{$row}", 'MN');$s->setCellValue('V'.$row, '|');
				$s->setCellValueExplicit("W{$row}", number_format($registroMora->getMontoTotal(), 2), PHPExcel_Cell_DataType::TYPE_STRING);$s->setCellValue('X'.$row, '|');
				$s->setCellValueExplicit("Y{$row}", 'VTA');$s->setCellValue('Z'.$row, '|');
				$s->setCellValue("AA{$row}", date('d/m/Y', strtotime($registro->getFechaCancelado())));$s->setCellValue('AB'.$row, '|');
				$s->setCellValue("AC{$row}", '');$s->setCellValue('AD'.$row, '|');
				$s->setCellValue("AE{$row}", $registro->getDescripcionIngreso());$s->setCellValue('AF'.$row, '|');
				$s->setCellValue("AG{$row}", '');$s->setCellValue('AH'.$row, '|');
				$s->setCellValue("AI{$row}", "PAGO ".$registro->getDescripcion());$s->setCellValue('AJ'.$row, '|');
				$s->setCellValue("AK{$row}", '0');$s->setCellValue('AL'.$row, '|');
				$s->setCellValue("AM{$row}", 'H');$s->setCellValue('AN'.$row, '|');
			}
			
			$row++;
			$correlativo++;
			$done[] = $numero;
		}

		if(count($registros) > 0){
			$s->getStyle('A2:AO'.($row - 1))->applyFromArray($normalStyle);
		}

		//print_r($numerosBanco);
		writeExcel($excel, "S_BI ".str_pad($this->params->mes, 2, 0, STR_PAD_LEFT).$this->params->anio.".xlsx");
	}

	function getRegistrosConcar($field = "impresiones.fecha_impresion"){
		$excel = PHPExcel_IOFactory::load('./Static/Templates/concar.xlsx');
		$s1 = $excel->getSheet(0);

		$from = date('Y-m-d', strtotime("{$this->params->anio}-{$this->params->mes}-01"));
		$to = date("Y-m-t", strtotime($from));
		// BOLETAS - FACTURACIÓN

		$boletas = Boleta::all(array(
			'conditions' => 'colegio_id = "'.$this->COLEGIO->id.'" AND fecha BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
		));

		$matriculasTalleres = Grupo_Taller_Matricula::all([
			'conditions' => 'DATE(fecha_registro) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
		]);

		$matriculas = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE('.$field.') BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 0 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		$pensiones = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE('.$field.') BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 1 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		$comedores = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE('.$field.') BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 3 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		// MORAS
		$moras = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.mora > 0 AND pagos.estado_pago = "CANCELADO" AND impresiones.tipo = "MORA" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		// MORAS
		$morasNotas = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.mora > 0 AND pagos.estado_pago = "CANCELADO" AND impresiones.tipo = "MORA" AND impresiones.tipo_documento = "NOTA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		$agendas = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 2 AND (pagos.incluye_agenda = "SI" OR observaciones LIKE "%AGENDA%" OR descripcion LIKE "%AGENDA%") AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		$registros = array();

		foreach($matriculasTalleres As $matriculaTaller){
			$registros[] = new xBoleta($matriculaTaller, 'MATRICULA_TALLER');
		}

		foreach($boletas As $boleta)
			$registros[] = new xBoleta($boleta, 'BOLETA');

		foreach($matriculas As $matricula)
			$registros[] = new xBoleta($matricula, 'MATRICULA');

		foreach($pensiones As $pension)
			$registros[] = new xBoleta($pension, 'PENSION');


		foreach($moras As $mora)
			$registros[] = new xBoleta($mora, 'MORA_BOLETA');

		foreach($morasNotas As $mora)
			$registros[] = new xBoleta($mora, 'MORA_NOTA');

		foreach($agendas As $agenda)
			$registros[] = new xBoleta($agenda, 'AGENDA');

		foreach($comedores As $comedor)
			$registros[] = new xBoleta($comedor, 'COMEDOR');


		usort($registros, function($a, $b){
			$nro1 = $a->getTipo().$a->getSerieNumero();
			$nro2 = $b->getTipo().$b->getSerieNumero();
			return strcmp($nro1, $nro2);
		});

		return $registros;
	}

	function getRegistrosIngresosBanco(){
		$excel = PHPExcel_IOFactory::load('./Static/Templates/concar.xlsx');
		$s1 = $excel->getSheet(0);

		$from = date('Y-m-d', strtotime("{$this->params->anio}-{$this->params->mes}-01"));
		$to = date("Y-m-t", strtotime($from));
		// BOLETAS - FACTURACIÓN

		$matriculas = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 0 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA" AND pagos.nro_movimiento_banco != ""',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		$pensiones = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 1 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"  AND pagos.nro_movimiento_banco != ""',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));

		$comedores = Impresion::all(array(
			'conditions' => 'pagos.colegio_id = "'.$this->COLEGIO->id.'" AND DATE(pagos.fecha_cancelado) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND pagos.tipo = 3 AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"  AND pagos.nro_movimiento_importado != ""',
			'joins' => '
				INNER JOIN pagos ON impresiones.pago_id = pagos.id
			'
		));




		$registros = array();

		foreach($matriculas As $matricula)
			$registros[] = new xBoleta($matricula, 'MATRICULA');

		foreach($pensiones As $pension)
			$registros[] = new xBoleta($pension, 'PENSION');

		foreach($comedores As $comedor)
			$registros[] = new xBoleta($comedor, 'COMEDOR');

		
		usort($registros, function($a, $b){
			$nro1 = strtotime($a->getFechaCancelado());
			$nro2 = strtotime($b->getFechaCancelado());
			return strcmp($nro1, $nro2);
		});
		
		return $registros;
	}



	function imprimir_lista_alumnos_registro_auxiliar(){
		$conditions = 'id != 0';

        if(!empty($this->params->grupo_id)){
            $conditions .= ' AND sha1(id) = "'.$this->params->grupo_id.'"';
        }else{
            if(!empty($this->params->sede_id))
                $conditions .= ' AND sede_id = "'.$this->params->sede_id.'"';
            if(!empty($this->params->nivel_id))
                $conditions .= ' AND nivel_id = "'.$this->params->nivel_id.'"';
            if(!empty($this->params->grado))
                $conditions .= ' AND grado = "'.$this->params->grado.'"';
            if(!empty($this->params->anio))
                $conditions .= ' AND anio = "'.$this->params->anio.'"';
        }

		

		$grupos = Grupo::all([
			'conditions' => $conditions,
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		]);

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
		
		foreach($grupos As $grupo){

		
			$matriculas = $grupo->getMatriculas();
			if(count($matriculas) <= 0)
				continue;

			$pdf->AddPage('L');

			$this->setLogo($pdf);
			$pdf->SetFont('helvetica', 'b', 11);
			$pdf->setFillColor(220, 220, 220);
			$pdf->cell(100,10,'REGISTRO ACADÉMICO', 0,0,'L');
			$pdf->SetFont('helvetica', 'b', 9);
			$pdf->cell(70,10,'CURSO: __________________________', 0,0,'C');
			$pdf->cell(40,10,'GRADO: '.$grupo->grado.'º '.$grupo->seccion, 0,0,'C');
			$pdf->cell(70,10,'MES: __________________________', 0,0,'C');
			$pdf->ln(15);
			$pdf->setFont('Helvetica','b',10);
			$pdf->cell(7,5,'Nº',1,0,'C', 1);
			$pdf->cell(60,5,'Apellidos y Nombres',1,0,'C', 1);
			$totalWidth = 220;
			$cells = 25;
			$width = $totalWidth / $cells;
			
			for($i = 1; $i <= $cells; $i++)
				$pdf->cell($width, 5, '', 1, 0, 'C', 1);


			$pdf->ln(5);
			$pdf->setFont('Helvetica','', 8);
			$n = 1;

			foreach($matriculas As $matricula){
				$alumno = $matricula->alumno;

				$pdf->cell(7,6,$n,1,0,'C');
				$pdf->cell(60,6, $alumno->getFullName(),1,0,'L', 0, '', 1);
				for($i = 1; $i <= $cells; $i++)
					$pdf->cell($width, 6, '',1,0,'C');
				$pdf->ln(6);

				$n++;
			}
		}

		$pdf->output();
	}

    function imprimir_lista_alumnos_registro_auxiliar_excel(){
        $conditions = 'id != 0';

        if(!empty($this->params->grupo_id)){
            $conditions .= ' AND sha1(id) = "'.$this->params->grupo_id.'"';
        }else{
            if(!empty($this->params->sede_id))
                $conditions .= ' AND sede_id = "'.$this->params->sede_id.'"';
            if(!empty($this->params->nivel_id))
                $conditions .= ' AND nivel_id = "'.$this->params->nivel_id.'"';
            if(!empty($this->params->grado))
                $conditions .= ' AND grado = "'.$this->params->grado.'"';
            if(!empty($this->params->anio))
                $conditions .= ' AND anio = "'.$this->params->anio.'"';
        }

        $grupos = Grupo::all([
            'conditions' => $conditions,
            'order' => 'nivel_id ASC, grado ASC, seccion ASC'
        ]);

        $this->crystal->load('PHPExcel');
        $excel = new PHPExcel();
        
        $sheetIndex = 0;
        foreach($grupos As $grupo){
            $matriculas = $grupo->getMatriculas();
            if(count($matriculas) <= 0)
                continue;

            if($sheetIndex > 0){
                $excel->createSheet();
            }
            $excel->setActiveSheetIndex($sheetIndex);
            $sheet = $excel->getActiveSheet();
            $sheet->setTitle($grupo->nivel->nombre.'-'.$grupo->grado.''.$grupo->seccion);

            // Set headers
            $sheet->setCellValue('A1', 'REGISTRO ACADÉMICO');
            $sheet->setCellValue('D1', 'CURSO: _________________');
            $sheet->setCellValue('F1', 'GRADO: '.$grupo->grado.'º '.$grupo->seccion);
            $sheet->setCellValue('H1', 'MES: _________________');

            // Headers style
            $headerStyle = array(
                'font' => array(
                    'bold' => true,
                    'size' => 11
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            );
            $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

            // Column headers
            $sheet->setCellValue('A3', 'Nº');
            $sheet->setCellValue('B3', 'Apellidos y Nombres');
            
            // Add 25 columns for dates
            $colStart = 3;
            for($i = 0; $i < 25; $i++) {
                $col = numToChar($colStart++);
                $sheet->setCellValue($col.'3', '');
            }

            // Column headers style
            $columnHeaderStyle = array(
                'font' => array('bold' => true),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'DCDCDC')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            );
            $sheet->getStyle('A3:AB3')->applyFromArray($columnHeaderStyle);

            // Add data
            $row = 4;
            $n = 1;
            foreach($matriculas As $matricula){
                $alumno = $matricula->alumno;
                $sheet->setCellValue('A'.$row, $n);
                $sheet->setCellValue('B'.$row, $alumno->getFullName());
                
                $colStart = 3;
                // Add empty cells for dates
                for($i = 0; $i < 25; $i++) {
                    $col = numToChar($colStart++);
                    $sheet->setCellValue($col.$row, '');
                }
                
                $row++;
                $n++;
            }

            // Style for data rows
            $dataStyle = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            );
            $sheet->getStyle('A4:AB'.($row-1))->applyFromArray($dataStyle);

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(40);
            $colStart = 3;
            for($i = 0; $i < 25; $i++) {
                $col = numtoChar($colStart++);
                $sheet->getColumnDimension($col)->setWidth(5);
            }

            // Set row height
            $sheet->getRowDimension('1')->setRowHeight(30);
            $sheet->getRowDimension('3')->setRowHeight(20);
            for($i = 4; $i < $row; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(25);
            }

            $sheetIndex++;
        }

        writeExcel($excel, 'registro_auxiliar.xlsx');
    }

    function reporte_deuda_x_anio(){
        
        $this->DB->query("SET NAMES utf8"); 
        $query = $this->DB->query("
            WITH PagosFiltrados AS (
                SELECT
                    g.grado,
                    g.seccion,
                    n.nombre as nivel_nombre,
                    c.pension as costo_pension,
                    a.nombres,
                    a.apellido_paterno,
                    a.apellido_materno,
                    p.matricula_id,
                    p.monto,
                    p.estado_pago,
                    p.fecha_hora,
                    p.nro_pago AS payment_num -- Asumiendo que nro_pago ya es el número secuencial del pago
                    -- Si nro_pago no es secuencial o no existe, usa:
                    -- ROW_NUMBER() OVER (PARTITION BY p.matricula_id ORDER BY p.fecha_hora) AS payment_num
                FROM
                    pagos p
                INNER JOIN matriculas m ON m.id = p.matricula_id
                INNER JOIN alumnos a ON a.id = m.alumno_id
                INNER JOIN grupos g ON g.id = m.grupo_id
                INNER JOIN niveles n ON n.id = g.nivel_id
                inner join costos c on c.id = m.costo_id
                WHERE
                    p.tipo = 1
                    AND g.anio = ".$this->params->anio."
            )
            SELECT
                grado,
                seccion,
                nivel_nombre,
                costo_pension,
                nombres,
                apellido_paterno,
                apellido_materno,
                matricula_id,
                MAX(CASE WHEN payment_num = 1 THEN monto ELSE NULL END) AS 'Pago 1',
                MAX(CASE WHEN payment_num = 1 THEN estado_pago ELSE NULL END) AS 'Estado Pago 1',
                MAX(CASE WHEN payment_num = 2 THEN monto ELSE NULL END) AS 'Pago 2',
                MAX(CASE WHEN payment_num = 2 THEN estado_pago ELSE NULL END) AS 'Estado Pago 2',
                MAX(CASE WHEN payment_num = 3 THEN monto ELSE NULL END) AS 'Pago 3',
                MAX(CASE WHEN payment_num = 3 THEN estado_pago ELSE NULL END) AS 'Estado Pago 3',
                MAX(CASE WHEN payment_num = 4 THEN monto ELSE NULL END) AS 'Pago 4',
                MAX(CASE WHEN payment_num = 4 THEN estado_pago ELSE NULL END) AS 'Estado Pago 4',
                MAX(CASE WHEN payment_num = 5 THEN monto ELSE NULL END) AS 'Pago 5',
                MAX(CASE WHEN payment_num = 5 THEN estado_pago ELSE NULL END) AS 'Estado Pago 5',
                MAX(CASE WHEN payment_num = 6 THEN monto ELSE NULL END) AS 'Pago 6',
                MAX(CASE WHEN payment_num = 6 THEN estado_pago ELSE NULL END) AS 'Estado Pago 6',
                MAX(CASE WHEN payment_num = 7 THEN monto ELSE NULL END) AS 'Pago 7',
                MAX(CASE WHEN payment_num = 7 THEN estado_pago ELSE NULL END) AS 'Estado Pago 7',
                MAX(CASE WHEN payment_num = 8 THEN monto ELSE NULL END) AS 'Pago 8',
                MAX(CASE WHEN payment_num = 8 THEN estado_pago ELSE NULL END) AS 'Estado Pago 8',
                MAX(CASE WHEN payment_num = 9 THEN monto ELSE NULL END) AS 'Pago 9',
                MAX(CASE WHEN payment_num = 9 THEN estado_pago ELSE NULL END) AS 'Estado Pago 9',
                MAX(CASE WHEN payment_num = 10 THEN monto ELSE NULL END) AS 'Pago 10',
                MAX(CASE WHEN payment_num = 10 THEN estado_pago ELSE NULL END) AS 'Estado Pago 10'
            FROM
                PagosFiltrados
            GROUP BY
                nombres, apellido_paterno, apellido_materno, matricula_id
            ORDER BY
                nivel_nombre, grado, seccion, apellido_paterno, apellido_materno, nombres;
        ");
        echo $this->DB->error;

        $this->crystal->load('PHPExcel');
        $excel = PHPExcel_IOFactory::load('./Static/Templates/reporte_deuda_x_anio.xlsx');
		$sheet = $excel->getSheet(0);
        $sheet->setTitle('Reporte de Deuda x Año');
        
        $headers = [
            'Nivel', 'Grado', 'Sección', 'Nombres', 'Apellido Paterno', 'Apellido Materno'
        ];
        
        for ($i = 1; $i <= 10; $i++) {
            $headers[] = $this->COLEGIO->MESES[$i + 1];
            //$headers[] = 'Estado Pago ' . $i;
        }

        $headers[] = 'Total';

        $normalStyle = array(
		    'font'  => array(
		        //'bold'  => true,
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

        // Escribir los encabezados en la primera fila
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        $sheet->getStyle('A1:' . chr(ord('A') + count($headers) - 1) . '1')->applyFromArray($normalStyle);

        // --- Llenar los datos ---
        $row = 2;
        while($data = $query->fetch_assoc()){
            $column = 'A';
            $sheet->setCellValue($column++ . $row, $data['nivel_nombre']);
            $sheet->setCellValue($column++ . $row, $data['grado']);
            $sheet->setCellValue($column++ . $row, $data['seccion']);
            $sheet->setCellValue($column++ . $row, mb_strtoupper($data['nombres'], 'UTF-8'));
            $sheet->setCellValue($column++ . $row, mb_strtoupper($data['apellido_paterno'], 'UTF-8'));
            $sheet->setCellValue($column++ . $row, mb_strtoupper($data['apellido_materno'], 'UTF-8'));
            //$sheet->setCellValue($column++ . $row, $data['matricula_id']);
            $total = 0;
            for ($i = 1; $i <= 10; $i++) {
                $monto = $data['Estado Pago ' . $i] == 'CANCELADO' ? 0 : ($data['Pago ' . $i] == null ? $data['costo_pension'] : $data['Pago ' . $i]);
                $sheet->setCellValue($column++ . $row, $monto > 0 ? $monto : '-');
                $total += $monto;
                //$sheet->setCellValue($column++ . $row, $data['Estado Pago ' . $i]);
            }

            $sheet->setCellValue($column++ . $row, $total);


            $row++;
        }

        if(count($query) > 0){
			$sheet->getStyle('A2:Q'.($row - 1))->applyFromArray($normalStyle);
		}

        // Ajustar el ancho de las columnas automáticamente
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            //$sheet->getColumnDimension($col)->setAutoSize(true);
        }

        writeExcel($excel, "Reporte-Deuda-".$this->params->anio.".xlsx");
    }
}
