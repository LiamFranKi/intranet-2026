<?php
class BoletasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__setConfig', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}

	function __setConfig(){
		$config = Boleta_Configuracion::find_by_sede_id($this->params->sede_id);
		if(!$config) $config = new Boleta_Configuracion();
		$this->set('boletaConfig', $config, true);
	}
	
	function index($r){
		$fecha1 = !empty($this->get->fecha1) ? $this->get->fecha1 : date('Y-m-d');
		$fecha2 = !empty($this->get->fecha2) ? $this->get->fecha2 : date('Y-m-d');
		$tipo = !empty($this->get->tipo) ? $this->get->tipo : '';
		$estado = !empty($this->get->estado) ? $this->get->estado : "ACTIVO";


		if($tipo == '' || $tipo == 'VENTAS'){
			$boletas = Boleta::all([
				'conditions' => ['fecha between DATE(?) and DATE(?) AND estado = ?', $fecha1, $fecha2, $estado]
			]);
		}


		if($tipo == '' || $tipo == 'PAGOS')
			$impresiones = Impresion::all([
				'conditions' => 'estado = "'.$estado.'" AND fecha_impresion BETWEEN DATE("'.$fecha1.'") AND DATE("'.$fecha2.'")'
			]);

		/*
		if($tipo == '' || $tipo == 'TALLERES'){
			$matriculas = Grupo_Taller_Matricula::all([
				'conditions' => 'DATE(fecha_registro) BETWEEN DATE("'.$fecha1.'") AND DATE("'.$fecha2.'")'
			]);
		}
		*/

		$this->render(array('boletas' => $boletas, 'impresiones' => $impresiones, 'matriculasTalleres' => $matriculas, 'fecha1' => $fecha1, 'fecha2' => $fecha2));
	}


	
	function save(){
		$r = -5;
		$this->boleta->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nombre' => $this->post->nombre,
			'fecha' => date('Y-m-d', strtotime($this->post->fecha)),
			'serie' => $this->post->serie,
			'numero' => $this->post->numero,
			'dni' => $this->post->dni,
			'tipo' => $this->post->tipo,
			'estado_pago' => $this->post->estado_pago,
			'transferencia_gratuita' => $this->post->transferencia_gratuita,
			//'tipo_pago' => $this->post->tipo_pago,
			//'tipo_tarjeta' => $this->post->tipo_tarjeta,
			'comision_tarjeta' => ($this->post->tipo_tarjeta == 'DEBITO' ? $this->COLEGIO->comision_tarjeta_debito : $this->COLEGIO->comision_tarjeta_credito),
			'sede_id' => $this->post->sede_id,
			'tipo_documento' => $this->post->tipo_documento,
            'entry' => $this->post->entry,
		));

		if($this->boleta->estado_pago == 'CANCELADO' && ($this->boleta->fecha_pago == '0000-00-00' || empty($this->boleta->fecha_pago))){
			$this->boleta->fecha_pago = date('Y-m-d');
		}
		
		if($this->boleta->is_valid()){
			if($this->boleta->is_new_record()){
				if($this->post->serie == 1){
					$this->boletaConfig->numero += 1;
				}elseif($this->post->serie == 2){
					$this->boletaConfig->numero_2 += 1;
				}elseif($this->post->serie == 3){
					$this->boletaConfig->numero_3 += 1;
				}

				$this->boletaConfig->save(); 

			}
			$r = $this->boleta->save() ? 1 : 0;
			if($r == 1){
				
				$this->boleta->resetStocks();
				Boleta_Detalle::table()->delete(array('boleta_id' => $this->boleta->id));
				foreach($this->post->categoria_id As $key => $categoria_id){
					Boleta_Detalle::create(array(
						'colegio_id' => $this->COLEGIO->id,
						'boleta_id' => $this->boleta->id,
						'concepto_id' => $this->post->concepto_id[$key],
						'categoria_id' => $this->post->categoria_id[$key],
						'cantidad' => $this->post->cantidad[$key],
						'precio' => $this->post->precio[$key],
					));
				}
			}
		}
		echo json_encode(array($r, 'id' => $this->boleta->id, 'errors' => $this->boleta->errors->get_all()));
	}

	/*
	function borrar($r){
		$r = $this->boleta->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	*/
	function borrar($r){
		if($this->boleta->estado == 'ACTIVO'){
			$this->boleta->set_attributes(array(
				'estado' => 'ANULADO'
			));
			$this->boleta->resetStocks();
		}else{
			$this->boleta->set_attributes(array(
				'estado' => 'ACTIVO'
			));
			$this->boleta->reduceStocks();
		}

		$r = $this->boleta->save() ? 1 : 0;
		echo json_encode(array($r));
	}

    function imprimir_externo(){
        if($this->boleta){
			if($this->boleta->numero == 0){
				$this->boleta->serie = $this->boletaConfig->getCurrentSerie();
				$this->boleta->numero = $this->boletaConfig->getCurrentNumero();
				$this->boletaConfig->numero += 1;
				$this->boletaConfig->save(); 
			}
			
			//$this->boleta->impreso = 'SI';
			$this->boleta->save();
		}

        $serie = array($this->boleta->serie, $this->boleta->numero);
		//$tipo = $this->pago->tipo == 0 ? 'MATRÍCULA' : ($this->pago->tipo == 1 ? 'MENSUALIDAD '.strtoupper($this->COLEGIO->getCicloPensionesSingle($this->pago->nro_pago)) : $this->pago->descripcion);
		$total = $this->pago->monto + $this->pago->mora;
		$decimal = (string) round(($this->boleta->getMontoTotal() - intval($this->boleta->getMontoTotal())) * 100);
		
		$letras = strtoupper(num2letras(intval($this->boleta->getMontoTotal()))).' CON '.(str_pad($decimal, 2, 0, STR_PAD_LEFT)).'/100 SOLES';
		$this->render(array('serie' => $serie, 'tipo' => $tipo, 'total' => $total, 'letras' => $letras));
    }

	function imprimir(){
		if($this->boleta){
			if($this->boleta->numero == 0){
				$this->boleta->serie = $this->boletaConfig->getCurrentSerie();
				$this->boleta->numero = $this->boletaConfig->getCurrentNumero();
				$this->boletaConfig->numero += 1;
				$this->boletaConfig->save(); 
			}
			
			$this->boleta->impreso = 'SI';
			$this->boleta->save();
		}

		$serie = array($this->boleta->serie, $this->boleta->numero);
		//$tipo = $this->pago->tipo == 0 ? 'MATRÍCULA' : ($this->pago->tipo == 1 ? 'MENSUALIDAD '.strtoupper($this->COLEGIO->getCicloPensionesSingle($this->pago->nro_pago)) : $this->pago->descripcion);
		$total = $this->pago->monto + $this->pago->mora;
		$decimal = (string) round(($this->boleta->getMontoTotal() - intval($this->boleta->getMontoTotal())) * 100);
		
		$letras = strtoupper(num2letras(intval($this->boleta->getMontoTotal()))).' '.(str_pad($decimal, 2, 0, STR_PAD_LEFT)).'/100 SOLES';
		$this->render(array('serie' => $serie, 'tipo' => $tipo, 'total' => $total, 'letras' => $letras));
	}

	function imprimir_comision(){
		if($this->boleta){
			if($this->boleta->comision_numero == 0){
				$this->boleta->comision_serie = $this->boletaConfig->getCurrentSerie();
				$this->boleta->comision_numero = $this->boletaConfig->getCurrentNumero();
				$this->boletaConfig->numero += 1;
				$this->boletaConfig->save(); 
			}
			
			$this->boleta->impreso = 'SI';
			$this->boleta->save();
		}

		$this->render();
	}

	function json($r){
		if($this->boleta->transferencia_gratuita == 'SI'){
			$json = getBoletaGratuitaFromVenta($this->boleta->id, $this->COLEGIO);
		}else{
			if($this->boleta->isServicio()){
				$json = getBoletaInafectaFromVenta($this->boleta->id, $this->COLEGIO);
			}else{
				$json = getBoletaGravadaFromVenta($this->boleta->id, $this->COLEGIO);
			}
		}
		

		if(!empty($this->get->check)){
			$this->boleta->update_attributes(['json_generado' => 'SI']);
		}
		
		header('Content-type: application/json;charset=utf-8');
		header('Content-Disposition: attachment; filename='.$json->nombre);

		echo json_encode($json->contenido, JSON_PRETTY_PRINT);
	}

	function json_rc($r){
		$json = $this->boleta->isServicio() ? getRCInafectaFromVenta($r->id, $this->COLEGIO) : getRCGravadaFromVenta($r->id, $this->COLEGIO);
		
		header('Content-type: application/json;charset=utf-8');
		header('Content-Disposition: attachment; filename='.$json->nombre);

		echo json_encode($json->contenido, JSON_PRETTY_PRINT);
	}


	function generar_all(){
		//print_r($this->post);
		$zip = new ZipArchive();
		$archivo = getToken().'.zip';

		$res = $zip->open('./Static/Temp/'.$archivo, ZipArchive::CREATE);
		if ($res === TRUE) {

			if(count($this->post->impresiones['BOLETA']) > 0){
				foreach($this->post->impresiones['BOLETA'] As $impresion_id){
					$json = getBoletaFromImpresionId($impresion_id, $this->COLEGIO);
					$zip->addFromString($json->nombre, json_encode($json->contenido, JSON_PRETTY_PRINT));
					Impresion::table()->update(['json_generado' => 'SI'], ['id' => $impresion_id]);
				}
			}

			if(count($this->post->impresiones['NOTA']) > 0){
				foreach($this->post->impresiones['NOTA'] As $impresion_id){
					$json = getNotaDebitoFromImpresionId($impresion_id, $this->COLEGIO);
					$zip->addFromString($json->nombre, json_encode($json->contenido, JSON_PRETTY_PRINT));
					Impresion::table()->update(['json_generado' => 'SI'], ['id' => $impresion_id]);
				}
			}

			if(count($this->post->impresiones['VENTAS']) > 0){
				foreach($this->post->impresiones['VENTAS'] As $impresion_id){
					
					$boleta = Boleta::find($impresion_id);

					if($boleta->isServicio()){
						$json = getBoletaInafectaFromVenta($impresion_id, $this->COLEGIO);
					}else{
						if($boleta->transferencia_gratuita == 'SI'){
							$json = getBoletaGratuitaFromVenta($impresion_id, $this->COLEGIO);
						}else{
							$json = getBoletaGravadaFromVenta($impresion_id, $this->COLEGIO);
						}
					}

					$zip->addFromString($json->nombre, json_encode($json->contenido, JSON_PRETTY_PRINT));
					Boleta::table()->update(['json_generado' => 'SI'], ['id' => $impresion_id]);
				}
			}

			if(count($this->post->impresiones['TALLERES']) > 0){
				foreach($this->post->impresiones['TALLERES'] As $matricula_id){
					$matricula = Grupo_Taller_Matricula::find($matricula_id);
					$json = $matricula->getJSON();
					$zip->addFromString($json->nombre, json_encode($json->contenido, JSON_PRETTY_PRINT));
					Grupo_Taller_Matricula::table()->update(['json' => 'SI'], ['id' => $matricula_id]);
				}
			}
		    
		    $zip->close();
		}

		//header('Content-Type: application/octet-stream');
		//header('Content-Disposition: attachment; filename='.$archivo);

		//echo file_get_contents('./Static/Temp/'.$archivo);
		//@unlink('./Static/Temp/'.$archivo);
		echo json_encode(['file' => $archivo]);
	}

	function get_current_numero(){

		if($this->post->serie == 1){
			$numero = $this->boletaConfig->numero;
		}elseif($this->post->serie == 2){
			$numero = $this->boletaConfig->numero_2;
		}elseif($this->post->serie == 3){
			$numero = $this->boletaConfig->numero_3;
		}

		echo json_encode([1, 'numero' => $numero]);
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'boletas', true);
		$this->boleta = !empty($this->params->id) ? Boleta::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Boleta();
		$this->context('boleta', $this->boleta); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->boleta);
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
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__first' => ['', '-- Seleccione --'],
				'data-bv-notempty' => 'true',
				'__options' => [Sede::all(), 'id', '$object->nombre'],
				'__dataset' => true
			),
			'tipo_documento' => array(
				'type' => 'select',
				'__options' => $object->TIPOS_DOCUMENTO,
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
            'entry' => array(
				'class' => 'form-control',
                //'type' => 'text'
			),
			'dni' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nombre' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha' => array(
				'class' => 'form-control calendar',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d')
			),
			'serie' => array(
				'class' => 'form-control tip',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				//'data-bv-notempty' => 'true',
				'__options' => [
					1 => '001',
					2 => '002',
					3 => '003'
				],
				'title' => '001 = Ventas<br />002 = Talleres<br />003 = Transferencias Gratuitas'
			),
			'numero' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'text'
			),
			'estado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_anulado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'numero_anulado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado_pago' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_pago' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'impreso' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'transferencia_gratuita' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo_pago' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo_tarjeta' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'comision_tarjeta' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'comision_serie' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'comision_numero' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'json_generado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	


	function reporte_registros_excel(){
		$this->crystal->load('PHPExcel:PHPExcel');
		$excel = PHPExcel_IOFactory::load('./Static/Templates/reporte_facturacion.xlsx');
		$s1 = $excel->getSheet(0);

		$from = $this->COLEGIO->setFecha($this->get->from);
		$to = $this->COLEGIO->setFecha($this->get->to);
		$conditions = 'boletas_detalles.colegio_id="'.$this->COLEGIO->id.'" AND boletas.fecha BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND boletas.transferencia_gratuita = "NO" AND boletas.numero != "-"';
		
		if(!empty($this->get->estado)){
			$conditions .= ' AND boletas.estado = "'.$this->get->estado.'"';
		}

		if(!empty($this->get->categoria_id)){
			$conditions .= ' AND boletas_detalles.categoria_id = "'.$this->get->categoria_id.'"';
			$categoria = Boleta_Categoria::find($this->get->categoria_id);
		}

		if(!empty($this->get->concepto_id)){
			$conditions .= ' AND boletas_detalles.concepto_id = "'.$this->get->concepto_id.'"';
		}

		$detalles = Boleta_Detalle::all(array(
			'select' => 'boletas_detalles.*',
			'conditions' => $conditions,
			'joins' => array('boleta'),
			'order' => 'boleta_id ASC'
		));

		// PAGOS
		$pagos = array();
		$impresiones = array();
		if((empty($this->get->categoria_id) || preg_match('/servicio/i', strtolower($categoria->nombre))) && empty($this->get->concepto_id)){
			$conditions = 'pagos.colegio_id="'.$this->COLEGIO->id.'"';
			if(!empty($this->get->estado)){
				$conditions .= ' AND impresiones.estado="'.$this->get->estado.'"';
				$conditions .= ' AND pagos.estado = "'.$this->get->estado.'"';
			}
			/*
			$pagos = Pago::all(array(
				'conditions' => $conditions.' AND DATE(fecha_hora) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA" AND impresiones.estado = "ACTIVO"',
				'joins' => '
					INNER JOIN impresiones ON pagos.id = impresiones.pago_id
				'
			));
			*/
			$conditions .= ' AND DATE(pagos.fecha_hora) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND impresiones.tipo = "PAGO" AND impresiones.tipo_documento = "BOLETA"';
			$impresiones = Impresion::all(array(
				'conditions' => $conditions,
				'joins' => '
					INNER JOIN pagos ON pagos.id = impresiones.pago_id
				'
			));
		}

		$matriculas = Grupo_Taller_Matricula::all([
			'conditions' => ' DATE(fecha_registro) BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
		]);

		// GROUP BOTH
		$registros = array();
		foreach($detalles As $detalle){
			//$registros[$detalle->boleta->getNroBoleta()] = $detalle;
			$registros[] = $detalle;
		}

		foreach($impresiones As $impresion){
			//$registros[$pago->getNroBoleta()] = $pago;
			$registros[] = $impresion;
		}

		foreach($matriculas As $matricula){
			$registros[] = $matricula;
		}

		usort($registros, function($a, $b){
			$nro1 = $a instanceOf Boleta_Detalle ? ($a->boleta->getCurrentSerie().'-'.$a->boleta->getCurrentNumero()) : ($a->getSerie().'-'.$a->getNumero());
			$nro2 = $b instanceOf Boleta_Detalle ? ($b->boleta->getCurrentSerie().'-'.$b->boleta->getCurrentNumero()) : ($b->getSerie().'-'.$b->getNumero());
			return strcmp($nro1, $nro2);
		});
		
		$s1->setCellValue('A1', 'Ventas / Servicios - '.date('d-m-Y'));
		$s1->setCellValue('A2', 'Desde: '.$this->get->from.', Hasta: '.$this->get->to);
		
		$currentRow = 5;

		$totalCantidad = 0;
		$total = 0;
		foreach($registros As $registro){
			
			if($registro instanceOf Boleta_Detalle){
				$detalle = $registro;
				$s1->setCellValue('A'.$currentRow, $detalle->boleta->nombre);
				$s1->setCellValue('B'.$currentRow, $detalle->boleta->dni);

				$s1->setCellValue('C'.$currentRow, $detalle->boleta->getCurrentSerie().'-'.$detalle->boleta->getCurrentNumero());
				$s1->setCellValue('D'.$currentRow, $detalle->boleta->tipo);
				$s1->setCellValue('E'.$currentRow, $detalle->categoria->nombre);
				$s1->setCellValue('F'.$currentRow, $detalle->concepto->descripcion);
				$s1->setCellValue('G'.$currentRow, date('d-m-Y', strtotime($detalle->boleta->fecha)));
				$s1->setCellValue('H'.$currentRow, $detalle->cantidad);
				$s1->setCellValue('I'.$currentRow, number_format($detalle->precio, 2));


				$importe = number_format($detalle->cantidad * $detalle->precio, 2);
				
				$s1->setCellValue('J'.$currentRow, $importe);
				$s1->setCellValue('K'.$currentRow, $detalle->boleta->estado);

				$total += $importe;
				$totalCantidad += $detalle->cantidad;

				++$currentRow;
			}

			if($registro instanceOf Impresion){
				$impresion = $registro;
				$pago = $impresion->pago;
				$alumno = $pago->matricula->alumno;
				if(!isset($alumno)) continue;
				
				$s1->setCellValue('A'.$currentRow, isset($alumno) ? $alumno->getFullName() : '-');
				$s1->setCellValue('B'.$currentRow, $alumno->nro_documento);

				$s1->setCellValue('C'.$currentRow, $impresion->getSerie().'-'.$impresion->getNumero());
				$s1->setCellValue('D'.$currentRow, 'ALUMNO');
				$s1->setCellValue('E'.$currentRow, 'Matrícula/Pensión');
				$s1->setCellValue('F'.$currentRow, $pago->getDescription());
				$s1->setCellValue('G'.$currentRow, date('d-m-Y', strtotime($pago->fecha_hora)));
				$s1->setCellValue('H'.$currentRow, 1);
				$s1->setCellValue('I'.$currentRow, number_format($pago->getMonto(), 2));
				$s1->setCellValue('J'.$currentRow, number_format($pago->getMonto(), 2));

				$s1->setCellValue('K'.$currentRow, $impresion->estado);
				if($registro->estado == 'ACTIVO')
					$total += $pago->getMonto();
				$totalCantidad += 1;
				++$currentRow;
			}


			if($registro instanceOf Grupo_Taller_Matricula){
				$matricula = $registro;
			
				
				$s1->setCellValue('A'.$currentRow, $matricula->getFullName());
				$s1->setCellValue('B'.$currentRow, $matricula->dni);

				$s1->setCellValue('C'.$currentRow, $matricula->getSerie().'-'.$matricula->getNumero());
				$s1->setCellValue('D'.$currentRow, 'ALUMNO');
				$s1->setCellValue('E'.$currentRow, 'Taller Educativo');
				$s1->setCellValue('F'.$currentRow, $matricula->getDescription());
				$s1->setCellValue('G'.$currentRow, date('d-m-Y', strtotime($matricula->fecha_registro)));
				$s1->setCellValue('H'.$currentRow, 1);
				$s1->setCellValue('I'.$currentRow, number_format($matricula->getMontoTotal(), 2));
				$s1->setCellValue('J'.$currentRow, number_format($matricula->getMontoTotal(), 2));

				$s1->setCellValue('K'.$currentRow, $matricula->estado);
				if($registro->estado == 'ACTIVO')
					$total += $matricula->getMontoTotal();
				$totalCantidad += 1;
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
				//'wrap' => true,
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
	    );
		$s1->setCellValue('G'.$currentRow, 'TOTAL');
		$s1->setCellValue('H'.$currentRow, $totalCantidad);
		$s1->setCellValue('I'.$currentRow, '-'); 
		$s1->setCellValue('J'.$currentRow, $total);
	    $s1->getStyle('A5:K'.(count($registros) + 5))->applyFromArray($normalStyle);

		//$pdf->ln(5);
		
		//$pdf->cell(70+30+20+45+60+20);
		//$pdf->cell(20, 5, 'TOTAL', 1, 0, 'C', 1, 0, 1);
		//$pdf->cell(20, 5, number_format($total, 2), 1, 0, 'C', 0, 0, 1);
		//$pdf->output();
		writeExcel($excel);
	}
}
