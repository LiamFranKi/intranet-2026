<?php
class Importar_exportarApplication extends Core\Application{
	public $beforeFilter = array('__checkSession');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}

	function index($r){
		$anio = empty($this->get->anio) ? $this->COLEGIO->anio_activo : $this->get->anio;
		
		$this->render(['anio' => $anio]);
	}

	function do_bcp(){
		set_time_limit(0);
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename=CREP.txt');
		
		$result = array(); 

		if(!empty($this->get->matricula_id)){
			$matriculas = Matricula::all(array(
				'conditions' => 'id IN ('.implode(',', $this->get->matricula_id).')'
			));
		}

		if(!empty($this->get->grupo_id)){
			if($this->get->grupo_id == -1){
				//$grupos = $this->COLEGIO->getGrupos($this->get->anio);
			
				$grupos = Grupo::all([
					'conditions' => 'anio = "'.$this->get->anio.'"',
					'order' => 'nivel_id ASC, grado ASC, seccion ASC'
				]);
			}else{
				$grupos = array(Grupo::find($this->get->grupo_id));
			}
			$matriculas = array();
			foreach($grupos As $grupo){
				$matriculas = array_merge($matriculas, $grupo->getMatriculas());
			}
		}

		if($this->get->nro_pago > -1){
			$currentLine = 1;
			foreach($matriculas AS $matricula){
				if($this->get->tipo_alumno != 'TODOS'){
					
					$totalMatriculas = $matricula->alumno->getMatriculasCount();

					;
					if($this->get->tipo_alumno == 'NUEVOS' && $totalMatriculas > 1){ // NO ES NUEVO
						continue;
					}

					if($this->get->tipo_alumno == 'ANTIGUOS' && $totalMatriculas <= 1){ // NO ES ANTIGUO

						//echo $totalMatriculas."<br />";
						//break;
						continue;
					}
					//break;
				}

				$line = null;
				if($this->get->nro_pago == 20){
					$line = $this->getLineForMatriculaAgenda_bcp($matricula);
				}

				if($this->get->nro_pago == 21){
					$line = $this->getLineForAdelantoMatricula_bcp($matricula);
				}

				if($this->get->nro_pago == 22){
					$line = $this->getLineForCancelacionMatricula_bcp($matricula);
				}

				if($this->get->nro_pago > 50){
					$line = $this->getLineForMatriculaOrPension_bcp($matricula, $this->get->nro_pago - 50, 3);
				}

				if($this->get->nro_pago >= 1 && $this->get->nro_pago <= 10){
					$line = $this->getLineForMatriculaOrPension_bcp($matricula, $this->get->nro_pago, 1);
				}

				if($this->get->nro_pago == 0){
					$line = $this->getLineForMatriculaOrPension_bcp($matricula, 1, 0);
				}
				
				if(isset($line)){
					$result[$currentLine] = $line;
					++$currentLine;
				}
			}
		}else{
			$currentLine = 1;
			foreach($matriculas AS $matricula){
				if($this->get->tipo_alumno != 'TODOS'){
					$totalMatriculas = $matricula->alumno->getMatriculasCount();
					if($this->get->tipo == 'NUEVOS' && $totalMatriculas > 1){ // NO ES NUEVO
						continue;
					}
					if($this->get->tipo == 'ANTIGUOS' && $totalMatriculas <= 1){ // NO ES ANTIGUO
						continue;
					}
				}

				for($i=0; $i<=$this->COLEGIO->total_pensiones; ++$i){
					$line = $this->getLineForMatriculaOrPension_bcp($matricula, $i == 0 ? 1 : $i, $i > 0 ? 1 : 0);
					if(isset($line)){
						$result[$currentLine] = $line;
						++$currentLine;
					}
				}
			}
		}
		
		$result[0] = $this->getHeader_bcp($result);
		
		for($i=0; $i<count($result); ++$i){
			echo $result[$i][0]."\n";
		}
	}

	// FUNCTIONS TO RETRIEVE DATA

	function getLineForCancelacionMatricula_bcp($matricula){
		$pagoMatricula = Pago::find(array(
			'select' => 'SUM(monto) As total',
			'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="1" AND tipo="0"'
		));

		if($pagoMatricula->total <= 0) return null; // NO RESERVÓ

		$monto = $matricula->costo->matricula - $pagoMatricula->total;
		$pagoAgenda = Pago::find(array(
			'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="1" AND tipo="2" AND incluye_agenda="SI"'
		));

		if(!$pagoAgenda){
			// AGREGA LA AGENDA
			$monto += $matricula->costo->agenda;
		}

		if($monto <= 0) return null;

		return $this->buildLine($matricula, array(
			'monto' => $monto,
			'nro_pago' => 22,
			'descripcion' => 'CANCELACION MATRICULA'.$matricula->grupo->anio,
			'fecha_vencimiento' => $this->getFechaVencimiento($matricula, -1)
		));
	}

	function getLineForAdelantoMatricula_bcp($matricula){
		$pagoMatricula = Pago::find(array(
			'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="1" AND tipo="0"'
		));

		if($pagoMatricula) return null; // YA ESTÁ MATRICULADO

		if($matricula->costo->matricula > $matricula->grupo->nivel->monto_adelanto_matricula){
			$monto = $matricula->grupo->nivel->monto_adelanto_matricula;
		}else{
			$monto = $matricula->costo->matricula;
		}

		return $this->buildLine($matricula, array(
			'monto' => $monto,
			'nro_pago' => 21,
			'descripcion' => 'RESERVA DE VACANTE'.$matricula->grupo->anio,
			'fecha_vencimiento' => $this->getFechaVencimiento($matricula, -1)
		));
	}

	
	function getLineForMatriculaOrPension_bcp($matricula, $nro_pago, $tipo){
		// estado_pago="CANCELADO" AND 
		$pago = Pago::find(array(
			'conditions' => 'estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="'.$nro_pago.'" AND tipo="'.$tipo.'"'
		));

		//if(!$pago) return null;
		if($pago->estado_pago == 'CANCELADO') return null; // YA PAGÓ
		if($tipo == 1 && $matricula->costo->pension <= 0) return null; // BECADO


		

		if($tipo == 1){
			$fecha_vencimiento = $this->getFechaVencimiento($matricula, $nro_pago);
			//$monto = $matricula->costo->pension + $this->COLEGIO->monto_adicional;
			$monto = !is_null($pago) ? $pago->monto : ($matricula->costo->pension + $this->COLEGIO->monto_adicional);
			$descripcion = 'MENSUALIDAD '.strtoupper($this->COLEGIO->MESES[$nro_pago + $this->COLEGIO->inicio_pensiones - 1]);
		}

		if($tipo == 0){
			$fecha_vencimiento = $this->getFechaVencimiento($matricula, -1);
			//$monto = $matricula->costo->matricula;
			$monto =  !is_null($pago) ? $pago->monto : $matricula->costo->matricula;
			$descripcion = 'MATRICULA '.$matricula->grupo->anio;
		}
		
		if($tipo == 3){
			$fecha_vencimiento = $this->getFechaVencimiento($matricula, $nro_pago);
			$monto = $this->COLEGIO->pago_comedor;
			$descripcion = 'COMEDOR '.mb_strtoupper($this->COLEGIO->MESES[$nro_pago + $this->COLEGIO->inicio_pensiones - 1]);
			$fecha_vencimiento = $this->COLEGIO->fecha_comedor;
		}

		return $this->buildLine($matricula, array(
			'monto' => $monto,
			'nro_pago' => $tipo > 0 ? $nro_pago : 0,
			'descripcion' => $descripcion,
			'fecha_vencimiento' => $fecha_vencimiento
		));

	}

	function getLineForMatriculaAgenda_bcp($matricula){
		$total = 0;
		$pagoMatricula = Pago::find(array(
			'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="1" AND tipo="0"'
		));
		if(!$pagoMatricula){
			$total += $matricula->costo->matricula;
		}
		$pagoAgenda = Pago::find(array(
			'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="1" AND tipo="2" AND incluye_agenda="SI"'
		));

		if(!$pagoAgenda){
			$total += $matricula->costo->agenda;
		}

		if($total <= 0) return null;

		return $this->buildLine($matricula, array(
			'monto' => $total,
			'nro_pago' => 20,
			'descripcion' => 'MATRICULAYAGENDA'.$matricula->grupo->anio,
			'fecha_vencimiento' => $this->getFechaVencimiento($matricula, -1)
		));
	}

	function getFechaVencimiento($matricula, $nro_pago){
		$diaVencimiento = $this->COLEGIO->getVencimientoPension($nro_pago);

		if(!isset($diaVencimiento)){
			return '31-'.($this->COLEGIO->inicio_pensiones + 1).'-'.$matricula->grupo->anio;
		}
		
		$diaVencimientoParams = explode('-', $diaVencimiento);

		if(count($diaVencimientoParams) == 3){
			return $diaVencimiento;
		}

		if($nro_pago == -1 || count($diaVencimientoParams) == 2){
			return $diaVencimiento.'-'.$matricula->grupo->anio;
		}
		
		$fechaVencimiento = $diaVencimiento.'-'.($nro_pago + 2).'-'.$matricula->grupo->anio;

		return $fechaVencimiento;

	}

	function buildLine($matricula, $data){
		$data = (object) $data;
		$line = 'DD'; // Tipo de registro
		
		$line .= Config::get('recaudo_nro_sucursal');//'192'; // N° de sucursal
		$line .= '0'; // Código de moneda
		$line .= Config::get('recaudo_nro_cuenta');//'2338930'; // N° de cuenta

		$line .= str_pad(substr($matricula->alumno->nro_documento, 0, 8), 14, 0, STR_PAD_LEFT);
		$line .= str_pad(substr(preg_replace('/,/', '', sanear_string(mb_strtoupper($matricula->alumno->getFullName(), 'UTF-8'))), 0, 40), 40, ' '); // nombre
		$line .= $this->getIdentifier($matricula, $data); // identificador
		$line .= date('Ymd', strtotime($this->get->fecha));
		$line .= date('Ymd', strtotime($data->fecha_vencimiento));

		$line .= str_pad(intval($data->monto), 13, 0, STR_PAD_LEFT); // monto entero
		$line .= str_pad(preg_replace('/\d+\./', '', number_format($data->monto, 2)), 2, 0, STR_PAD_LEFT); // monto decimal

		$line .= str_pad(intval(0), 13, 0, STR_PAD_LEFT); // mora entero
		$line .= str_pad(preg_replace('/\d+\./', '', number_format(1, 2)), 2, 0, STR_PAD_LEFT); // mora decimal

		$line .= str_pad(intval($data->monto), 7, 0, STR_PAD_LEFT); // monto minimo entero
		$line .= str_pad(preg_replace('/\d+\./', '', number_format($data->monto, 2)), 2, 0, STR_PAD_LEFT); // monto minimo decimal

		$line .= $this->get->tipo; // Tipo Actualización

		$line .= str_pad(substr(str_replace(' ', '', $data->descripcion), 0, 20), 20, ' ', STR_PAD_LEFT); // DESCRIPCION
		$line .= str_pad(substr($matricula->alumno->nro_documento, 0, 16), 16, 0, STR_PAD_LEFT); // DOCUMENTO
		$line .= str_repeat(" ", 61);

		return array($line, $data);
	}

	function getIdentifier($matricula, $data){

		$id = str_pad(substr($matricula->alumno->nro_documento, 0, 8), 8, ' ');
		$id .= str_pad($matricula->id, 6, 0, STR_PAD_LEFT);
		$id .= str_pad($data->nro_pago, 3, 0, STR_PAD_LEFT);


		return substr(str_pad($id, 30, ' '), 0, 30);
	}

	function getHeader_bcp($lines){
		//print_r($lines);
		$line = 'CC';
		$line .= Config::get('recaudo_nro_sucursal'); //'192'; // N° de sucursal
		$line .= '0'; // Código de moneda
		$line .= Config::get('recaudo_nro_cuenta'); //'2338930'; // N° de cuenta
		$line .= 'C';
		$line .= str_pad(substr(Config::get('recaudo_razon_social'), 0, 40), 40, ' ');
		$line .= date('Ymd', strtotime($this->get->fecha));
		$line .= str_pad(count($lines), 9, 0, STR_PAD_LEFT);
		
		$monto = 0;
		foreach($lines As $_line){
			$monto += $_line[1]->monto; 
		}
		//$monto += 1.25;
		$line .= str_pad(intval($monto), 13, 0, STR_PAD_LEFT); // monto minimo entero
		$monto = $monto - intval($monto);

		$line .= str_pad(preg_replace('/\d+\./', '', number_format($monto, 2)), 2, 0, STR_PAD_LEFT); // monto minimo decimal
		$line .= $this->get->tipo_documento;
		$line .= str_repeat(" ", 6 + 157);
		return array($line, null);
	}

	/*** IMPORTAR PAGOS AL BCP ***/
	function importar_bcp(){
		//print_r($this->post);
		$archivo = $_FILES['archivo'];
		$newName = getToken().'.txt';
		$r = -1;
		$data = array();
		$errores = [];
		if($archivo['error'] == UPLOAD_ERR_OK){
			if(move_uploaded_file($archivo['tmp_name'], './Static/Temp/'.$newName)){
				$lines = file('./Static/Temp/'.$newName);
				for($x=1; $x < count($lines); $x++){
					$line = $lines[$x];
					$details = $this->getImportDetails($line);
					$pago = null;

					$matricula = Matricula::find_by_id($details->matricula_id);

					if(is_null($matricula)){
						$errores[] = 'ID: '.$details->matricula_id.' - DNI: '.$details->dni;
						continue;
					}

					//----
					if($details->nro_pago == 21){
						$pago = $this->importPagoAdelantoMatricula($matricula, $details);
					}
					//----
					if($details->nro_pago == 22){
						$pago = $this->importCancelacionAdelantoMatricula($matricula, $details);				
					}

					if($details->nro_pago > 50){
						$pago = $this->importComedor($matricula, $details);
					}

					if($details->nro_pago == 20){
						$pago = $this->importMatriculaAgenda($matricula, $details);
					}

					if($details->nro_pago == 0){
						$pago = $this->importPago($matricula, $details, 0, 1);
					}
					
					if($details->nro_pago >= 1 && $details->nro_pago <= 10){
						$pago = $this->importPago($matricula, $details, 1, $details->nro_pago);
					}

					if(!is_null($pago)){
						if(is_array($pago) && count($pago) > 0){
							foreach($pago As $p){
								$data[] = $p->id;
							}
							//$data = array_merge($data, $pago);
						}else{
							$data[] = $pago->id;	
						}
					}
				}

				if(count($data) > 0 && count($errores) <= 0)
					$historial = Pago_Historial::create(array(
						'colegio_id' => $this->COLEGIO->id,
						'archivo' => $archivo['name'],
						'fecha' => date('Y-m-d'),
						'data' => base64_encode(serialize($data)),
						'impreso' => 'NO'
					));

				$r = 1;
				@unlink('./Static/Temp/'.$newName);
				//print_r($lines);
			}
		}
		if(count($errores) > 0){
			$r = -2;
		}
		echo json_encode(array($r, 'errores' => $errores));
	}

	

	function getImportDetails($line){
		$data = array(
			'dni' => substr($line, 27, 8),
			'matricula_id' => intval(substr($line, 35, 6)),
			'nro_pago' => intval(substr($line, 41, 3)),
			'fecha' => date('Y-m-d', strtotime(substr($line, 57, 8))),
			'nro_movimiento' => substr($line, 124, 6),
			'importe_origen' => (float) substr($line, 73, 13).'.'.substr($line, 86, 2),
			'importe_total' => (float) substr($line, 103, 13).'.'.substr($line, 116, 2),
		);

		$data['importe_mora'] = $data['importe_total'] - $data['importe_origen'];
		
		
		return (object) $data;
	}

	function importPago($matricula, $details, $tipo, $nro_pago){

		$monto = $details->importe_origen;
		$mora = $details->importe_mora;

		$pago = Pago::find(array(
			'conditions' => 'estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="'.$nro_pago.'" AND tipo="'.$tipo.'"'
		));

		if(!$pago){
			$pago = new Pago(array(
				'colegio_id' => $matricula->colegio_id,
				'matricula_id' => $matricula->id,
				'nro_pago' => $nro_pago,
				'monto' => $monto,
				'mora' => $mora,
				'fecha_hora' => $details->fecha,
				'fecha_cancelado' => $details->fecha,
				//'nro_recibo' => '-',
				'tipo' => $tipo,
				'descripcion' => 'PAGO CANCELADO',
				'observaciones' => 'IMPORTADO',
				'personal_id' => $this->USUARIO->personal_id,
				'nro_movimiento_banco' => $details->nro_movimiento,
				'banco' => 'BCP'
			));
		}else{
			if($pago->estado_pago == 'PENDIENTE'){
				$pago->set_attributes(array(
					'monto' => $monto,
					'mora' => $mora,
					'fecha_cancelado' => $details->fecha,
					'descripcion' => 'PAGO CANCELADO',
					'observaciones' => 'IMPORTADO',
					'personal_id' => $this->USUARIO->personal_id,
					'estado_pago' => 'CANCELADO',
					'nro_movimiento_banco' => $details->nro_movimiento,
					'banco' => 'BCP'
				));
			}
		}
			
		if($pago->save()){
			if($matricula->estado == 4 && $tipo == 0){
				$matricula->update_attributes(array(
					'estado' => 0
				));
			}
			return $pago;
		}
	}

	function importMatriculaAgenda($matricula, $details){
		$monto = $details->importe_origen;
		$mora = $details->importe_mora;

		$costo = $matricula->costo;
		if($monto >= ($costo->matricula + $costo->agenda)){
			$montoAgenda = $monto - $costo->matricula;
			$monto = $monto - $costo->agenda; // SOLO MATRICULA

		}
		$pagos = [];

		$pago = Pago::find(array(
			'conditions' => 'estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="1" AND tipo="0"'
		));

		$pagoAgenda = Pago::find(array(
			'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="1" AND tipo="2" AND incluye_agenda="SI"'
		));

		if($pago){
			$pagos[] = $pago;
		}
		if($pagoAgenda){
			$pagos[] = $pagoAgenda;
		}

		if(!$pago){
			$pago = new Pago(array(
				'colegio_id' => $matricula->colegio_id,
				'matricula_id' => $matricula->id,
				'nro_pago' => 1,
				'monto' => $monto,
				'mora' => $mora,
				'fecha_hora' => $details->fecha,
				'fecha_cancelado' => $details->fecha,
				//'nro_recibo' => '-',
				'tipo' => 0,
				'descripcion' => 'PAGO CANCELADO',
				'observaciones' => 'IMPORTADO',
				'personal_id' => $this->USUARIO->personal_id,
				'nro_movimiento_banco' => $details->nro_movimiento,
				'banco' => 'BCP',
				'incluye_agenda' => 'SI',
			));

			$pagoAgenda = Pago::find(array(
				'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="1" AND tipo="2" AND incluye_agenda="SI"'
			));

			if(!$pagoAgenda){
				$pagoAgenda = Pago::create(array(
					'colegio_id' => $matricula->colegio_id,
					'matricula_id' => $matricula->id,
					'nro_pago' => 1,
					'monto' => $montoAgenda,
					'mora' => 0,
					'fecha_hora' => $details->fecha,
					'fecha_cancelado' => $details->fecha,
					//'nro_recibo' => '-',
					'tipo' => 2,
					'descripcion' => 'PAGO DE AGENDA',
					'observaciones' => 'IMPORTADO',
					'personal_id' => $this->USUARIO->personal_id,
					'incluye_agenda' => 'SI',
					'observaciones' => 'Matrícula y Agenda',
					'nro_movimiento_banco' => $details->nro_movimiento,
					'banco' => 'BCP'
				));

				$pagos[] = $pagoAgenda;
			}

			if($pago->save()){
				if($matricula->estado == 4){
					$matricula->update_attributes(array(
						'estado' => 0
					));
				}
				$pagos[] = $pago;
			}
		}

		return $pagos;
	}

	function importPagoAdelantoMatricula($matricula, $details){
		$monto = $details->importe_origen;
		$mora = $details->importe_mora;

		$pago = new Pago(array(
			'colegio_id' => $matricula->colegio_id,
			'matricula_id' => $matricula->id,
			'nro_pago' => 1,
			'monto' => $monto,
			'mora' => $mora,
			'fecha_hora' => $details->fecha,
			'fecha_cancelado' => $details->fecha,
			//'nro_recibo' => '-',
			'tipo' => 0,
			'descripcion' => 'ADELANTO MATRICULA',
			'observaciones' => 'IMPORTADO',
			'personal_id' => $this->USUARIO->personal_id,
			'nro_movimiento_banco' => $details->nro_movimiento,
			'banco' => 'BCP'
		));

		if($pago->save()){
			if($matricula->estado == 4){
				$matricula->estado = 0;
				$matricula->save();
			}
			return $pago;
		}
	}

	function importCancelacionAdelantoMatricula($matricula, $details){
		$monto = $details->importe_origen;
		$mora = $details->importe_mora;

		$montoMatricula = $monto - $matricula->costo->agenda;
		$montoAgenda = $matricula->costo->agenda;
		// PAGO CANCELACION MATRICULA
		$pago = new Pago(array(
			'colegio_id' => $matricula->colegio_id,
			'matricula_id' => $matricula->id,
			'nro_pago' => 1,
			'monto' => $montoMatricula,
			'mora' => $mora,
			'fecha_hora' => $details->fecha,
			'fecha_cancelado' => $details->fecha,
			//'nro_recibo' => '-',
			'tipo' => 0,
			'descripcion' => 'CANCELACION ADELANTO MATRICULA',
			'observaciones' => 'IMPORTADO',
			'personal_id' => $this->USUARIO->personal_id,
			'incluye_agenda' => 'SI',
			'nro_movimiento_banco' => $details->nro_movimiento,
			'banco' => 'BCP'
		));

		$pagoAgenda = Pago::find(array(
			'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="1" AND tipo="2" AND incluye_agenda="SI"'
		));

		// PAGO CANCELACION AGENDA
		if(!$pagoAgenda){
			Pago::create(array(
				'colegio_id' => $matricula->colegio_id,
				'matricula_id' => $matricula->id,
				'nro_pago' => 1,
				'monto' => $montoAgenda,
				'mora' => 0,
				'fecha_hora' => $details->fecha,
				'fecha_cancelado' => $details->fecha,
				//'nro_recibo' => '-',
				'tipo' => 2,
				'descripcion' => 'PAGO DE AGENDA',
				'observaciones' => 'IMPORTADO',
				'personal_id' => $this->USUARIO->personal_id,
				'incluye_agenda' => 'SI',
				'observaciones' => 'Matrícula y Agenda',
				'nro_movimiento_banco' => $details->nro_movimiento,
				'banco' => 'BCP'
			));
		}


		if($pago->save()){
			if($matricula->estado == 4){
				$matricula->estado = 0;
				$matricula->save();
			}
			return $pago;
		}
	}

	function importComedor($matricula, $details){
		$monto = $details->importe_origen;
		$mora = $details->importe_mora;
		$nro_pago = $details->nro_pago - 50;

		$pago = Pago::find(array(
			'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="'.$nro_pago.'" AND tipo="3"'
		));

		if(!$pago){
			$pago = new Pago(array(
				'colegio_id' => $matricula->colegio_id,
				'matricula_id' => $matricula->id,
				'nro_pago' => $nro_pago,
				'monto' => $monto,
				'mora' => $mora,
				'fecha_hora' => $details->fecha,
				'fecha_cancelado' => $details->fecha,
				//'nro_recibo' => '-',
				'tipo' => 3,
				'descripcion' => 'COMEDOR',
				'observaciones' => 'IMPORTADO',
				'personal_id' => $this->USUARIO->personal_id,
				'nro_movimiento_banco' => $details->nro_movimiento,
				'banco' => 'BCP'
			));
		}


		if($pago->save()){
			return $pago;
		}
	}


	function importar_operacion(){
		$archivo = $_FILES['archivo'];
		$newName = getToken().'.xlsx';
		$r = -1;
		$data = array();
		if($archivo['error'] == UPLOAD_ERR_OK){
			if(move_uploaded_file($archivo['tmp_name'], './Static/Temp/'.$newName)){
				$this->crystal->load('PHPExcel');
				$excel = PHPExcel_IOFactory::load('./Static/Temp/'.$newName);
				$s1 = $excel->getSheet(0);
				$currentRow = $this->post->start_row;
				$r = 1;
				while(true){
					$fecha = $s1->getCell('A'.$currentRow)->getValue();
					if($fecha == null || $fecha == "") break;

					$parsedFecha = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($fecha) + 60*60*24);
					$dni = substr($s1->getCell('C'.$currentRow)->getValue(), 14, 8);
					$numero = $s1->getCell('G'.$currentRow)->getValue();
                    $fecha = date('Y-m-d', strtotime(str_replace("/", "-", $fecha)));

					$pagos = Pago::all(array(
						'conditions' => 'pagos.fecha_cancelado = "'.$fecha.'" AND alumnos.nro_documento = "'.$dni.'"',
						'joins' => '
						INNER JOIN matriculas ON matriculas.id = pagos.matricula_id
						INNER JOIN alumnos ON alumnos.id = matriculas.alumno_id
						'
					));

                    //echo $fecha." - ".$parsedFecha."\n";

					foreach($pagos As $pago){
						$pago->nro_movimiento_importado = $numero;
						$pago->save();
						//print_r($pago->attributes());
					}
					 // YA NO HAY REGISTROS
					++$currentRow;
				}

				//@unlink('./Static/Temp/'.$newName);
			}
		}

		echo json_encode(array($r));
	}
}
