<?php
class PagosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'APODERADO' => 'historial'
		]);
	}

	function index(){

		$this->crystal->load('Form:*');
		$form = new Form(null, $this->COLEGIO->searchGrupoForm((array) $this->get));

		$grupo = $this->COLEGIO->getGrupo($this->get);
		if($grupo) $matriculas = $grupo->getMatriculas();
		

		$this->render(array('matriculas' => $matriculas, 'form' => $form));
	}

	function detalles($r){
		$matricula = Matricula::find([
			'conditions' => 'sha1(id) = "'.$this->get->matricula_id.'"'
		]);
		$pagos = $matricula->getPagos();
		
		$this->render(array('pagos' => $pagos, 'matricula' => $matricula));
	}

	function historial(){
		$alumno = Alumno::find([
			'conditions' => ['sha1(id) = ?', $this->get->alumno_id]
		]);
		
        $link_consulta = Config::get('link_consulta_facturas');

		$matriculas = $alumno->getMatriculas();

        $sales_items = Boleta_Detalle::all([
            'conditions' => ['dni = ?', $alumno->nro_documento],
            'joins' => ['boleta'],
            'order' => 'boletas.fecha DESC',
        ]);

		$this->render(array('matriculas' => $matriculas, 'link_consulta' => $link_consulta, 'sales_items' => $sales_items, 'alumno' => $alumno));
	}
	
	function save(){
		$r = -5;
		$this->pago->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'matricula_id' => $this->post->matricula_id,
			'nro_pago' => $this->post->nro_pago,
			'monto' => $this->post->monto,
			'mora' => $this->post->mora,
			'fecha_hora' => date('Y-m-d H:i', strtotime($this->post->fecha_hora)),

			'tipo' => $this->post->tipo,
			'descripcion' => $this->post->descripcion,
			'observaciones' => $this->post->observaciones,
			'personal_id' => $this->USUARIO->personal_id,
			'estado_pago' => $this->post->estado_pago,
			'forma_pago' => $this->post->forma_pago,
			'tipo_tarjeta' => $this->post->tipo_tarjeta,
			'lugar_pago' => 'CAJA',
			'comision_tarjeta' => ($this->post->tipo_tarjeta == 'DEBITO' ? $this->COLEGIO->comision_tarjeta_debito : $this->COLEGIO->comision_tarjeta_credito)
		));

		if($this->post->estado_pago == 'CANCELADO'){
			$this->pago->fecha_cancelado = date('Y-m-d', strtotime($this->post->fecha_cancelado));
		}

		if($this->pago->is_new_record()){
			$this->post->nro_recibo = '-';
		}
		
		if($this->pago->is_valid()){
			$r = $this->pago->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->pago->id, 'errors' => $this->pago->errors->get_all()));
	}

    function imprimir_externo(){
        $pago = Pago::find([
            'conditions' => ['sha1(id) = ?', $this->params->id]
        ]);

        $impresion = $pago->getActiveImpresion();
        if(!is_null($impresion)){
            $impresiones[] = $impresion;
            if($impresion->impreso == 'NO'){
                $impresion->update_attributes(array(
                    'impreso' => 'SI',
                    'fecha_impresion' => date('Y-m-d'),
                    'hora_impresion' => date('H:i:s')
                ));
            }
        }
        
        $serie = array($impresion->serie, $impresion->numero);
		//$tipo = $this->pago->tipo == 0 ? 'MATRÍCULA' : ($this->pago->tipo == 1 ? 'MENSUALIDAD '.strtoupper($this->COLEGIO->getCicloPensionesSingle($this->pago->nro_pago)) : $this->pago->descripcion);
		$total = $pago->getTotal();
		$decimal = (string) round(($pago->getTotal() - intval($pago->getTotal())) * 100);
		
		$letras = strtoupper(num2letras(intval($pago->getTotal()))).' CON '.(str_pad($decimal, 2, 0, STR_PAD_LEFT)).'/100 SOLES';
        
        $this->render(array('serie' => $serie, 'tipo' => $tipo, 'total' => $total, 'letras' => $letras, 'pago' => $pago, 'impresion' => $impresion));

    }

	function imprimir(){
		if(isset($this->get->pago_id) && count($this->get->pago_id) > 0){
			$pagos = Pago::all(array(
				'conditions' => 'id IN ('.implode(',', $this->get->pago_id).')'
			));
		}else{
			$pagos = array($this->pago);
		}
		$impresiones = array();

		foreach($pagos As $pago){
			$impresion = $pago->getActiveImpresion();
			if(!is_null($impresion)){
				$impresiones[] = $impresion;
				if($impresion->impreso == 'NO'){
					$impresion->update_attributes(array(
						'impreso' => 'SI',
						'fecha_impresion' => date('Y-m-d'),
						'hora_impresion' => date('H:i:s')
					));
				}
			}
		}

		$this->render(array('impresiones' => $impresiones));
	}

	function imprimir_mora_nota_debito(){
		if(isset($this->get->pago_id) && count($this->get->pago_id) > 0){
			$pagos = Pago::all(array(
				'conditions' => 'id IN ('.implode(',', $this->get->pago_id).')'
			));
		}else{
			$pagos = array($this->pago);
		}

		$impresiones = array();
		foreach($pagos As $pago){
			
			$impresion = $pago->getActiveImpresionMora();
			if(!is_null($impresion)){
				$impresiones[] = $impresion;
				if($impresion->impreso == 'NO'){
					$impresion->update_attributes(array(
						'impreso' => 'SI',
						'fecha_impresion' => date('Y-m-d')
					));
				}
			}
		}

		$this->imprimir_mora_pdf($impresiones);
		//$this->render(array('impresiones' => $impresiones));
	}

	function imprimir_comision(){
		if(isset($this->get->pago_id) && count($this->get->pago_id) > 0){
			$pagos = Pago::all(array(
				'conditions' => 'id IN ('.implode(',', $this->get->pago_id).')'
			));
		}else{
			$pagos = array($this->pago);
		}
		$impresiones = array();

		

		foreach($pagos As $pago){
			
			$impresion = $pago->getActiveImpresionComision();
			if(!is_null($impresion)){

				$impresiones[] = $impresion;
				if($impresion->impreso == 'NO'){
					$impresion->update_attributes(array(
						'impreso' => 'SI',
						'fecha_impresion' => date('Y-m-d')
					));
				}
			}
		}

		$this->render(array('impresiones' => $impresiones));
	}

	function imprimir_mora_pdf($impresiones){
		$this->crystal->load('TCPDF');
		$nd = (object) $this->COLEGIO->getImpresionNotasDebito();
		//$pageLayout = array(  297.638,  419.528); //  or array($height, $width) 
		//$pageLayout = array(  297.638,  (count($impresiones) * $nd->alto) + count($impresiones) * $nd->espaciado);
		//$pdf = new TCPDF('p', 'mm', $pageLayout, true, 'UTF-8', false);
		pdfMora($impresiones, $nd);
	}
    
    function borrar($r){
		$r = $this->pago->delete() ? 1 : 0;
	
		echo json_encode(array($r));
	}

	function anular($r){
		//$r = $this->pago->delete() ? 1 : 0;
		if($this->pago->estado == 'ACTIVO'){
			$this->pago->set_attributes(array(
				'estado' => 'ANULADO',
				'fecha_anulado' => date('Y-m-d')
			));
		}else{
			$this->pago->set_attributes(array(
				'estado' => 'ACTIVO',
				//'fecha_anulado' => date('Y-m-d')
			));
		}

		$r = $this->pago->save() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'pagos', true);
		$this->pago = !empty($this->params->id) ? Pago::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Pago();
		$this->context('pago', $this->pago); // set to template
		if(in_array($this->params->Action, array('form'))){
			if(isset($this->get->matricula_id)){
				$matricula = Matricula::find([
					'conditions' => 'sha1(id) = "'.$this->get->matricula_id.'"'
				]);
				$this->pago->matricula_id = $matricula->id;
			}
			$this->form = $this->__getForm($this->pago);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'lugar_pago' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'forma_pago' => array(
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
			'colegio_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'matricula_id' => array(
			
				'type' => 'hidden'
			),
			'nro_pago' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => $this->COLEGIO->getOptionsNroPago()
			),
			'monto' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'mora' => array(
				'class' => 'form-control',
				
			),
			'fecha_hora' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d H:i:s')
			),
			'tipo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => $object->TIPOS_PAGO
			),
			'descripcion' => array(
				'class' => 'form-control',

			),
			'observaciones' => array(
				'class' => 'form-control',
			
			),
			'personal_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_anulado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado_pago' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_cancelado' => array(
				'class' => 'form-control calendar',
				'data-bv-notempty' => 'true',
                'type' => 'date',
			),
			'incluye_agenda' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nro_movimiento_banco' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nro_movimiento_importado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'banco' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	

	// BOLETEAR
	function do_boletear_json(){

		$grupo = Grupo::find($this->get->grupo_id);
		$matriculas = $grupo->getMatriculas();
		$pagos = array();
		$impresiones = array();

		foreach($matriculas As $matricula){
			if($matricula->isOculto()) continue;
			if($matricula->costo->pension <= 0) continue;

			if(!$matricula->hasPago(1, $this->get->nro_pago)){

				//$nro_recibo = $this->boletaConfig->getCurrentSerie().'-'.$this->boletaConfig->getCurrentNumero();

				$pago = new Pago(array(
					'colegio_id' => $this->COLEGIO->id,
					'matricula_id' => $matricula->id,
					'nro_pago' => $this->get->nro_pago,
					'monto' => $matricula->costo->pension,
					'mora' => 0,
					'fecha_hora' => date('Y-m-d H:i'),
					//'nro_recibo' => $nro_recibo,
					'tipo' => 1,
					'personal_id' => $this->USUARIO->personal_id,
					'estado' => 'ACTIVO',
					'estado_pago' => 'PENDIENTE',
					'observaciones' => 'PAGO TEMPORAL'
				));

				if($pago->save()){
					//$pagos[] = $pago;
					//$this->boletaConfig->numero += 1;
					//$this->boletaConfig->save();
				}
			}else{
				$pago = Pago::find(array(
					'conditions' => 'estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="'.$this->get->nro_pago.'" AND tipo="1"'
				));

				if($pago){
					//if($pago->nro_recibo == '-' || empty($pago->nro_recibo)){
					//	$pago->nro_recibo = $this->boletaConfig->getCurrentSerie().'-'.$this->boletaConfig->getCurrentNumero();
						$pago->observaciones = 'PAGO TEMPORAL';

						if($pago->save()){
							//$pagos[] = $pago;
							//$this->boletaConfig->numero += 1;
							//$this->boletaConfig->save();
						}
					//}
				}
			}

			$impresion = $pago->getActiveImpresion();
			if(!is_null($impresion)){
				
				if($impresion->impreso == 'NO'){
					$impresiones[] = $impresion;
					$impresion->update_attributes(array(
						'impreso' => 'SI',
						'fecha_impresion' => date('Y-m-d')
					));
				}
			}
		}

		//$this->render('imprimir', array('impresiones' => $impresiones));

		generarBoletasZip($impresiones, $this->COLEGIO);

	}

	function do_boletear_json2(){

		//$grupo = Grupo::find($this->get->grupo_id);
		//$grupos = $this->COLEGIO->getGrupos();
		$grupos = Grupo::all([
			'conditions' => 'anio = "'.$this->COLEGIO->anio_activo.'"',
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		]);
		$impresiones = array();
		
		foreach($grupos As $grupo){

			
			$matriculas = $grupo->getMatriculas();
			$pagos = array();
			

			foreach($matriculas As $matricula){
				if($matricula->isOculto()) continue;
				if($matricula->costo->pension <= 0) continue;

				if(!$matricula->hasPago(1, $this->get->nro_pago)){

					//$nro_recibo = $this->boletaConfig->getCurrentSerie().'-'.$this->boletaConfig->getCurrentNumero();

					$pago = new Pago(array(
						'colegio_id' => $this->COLEGIO->id,
						'matricula_id' => $matricula->id,
						'nro_pago' => $this->get->nro_pago,
						'monto' => $matricula->costo->pension,
						'mora' => 0,
						'fecha_hora' => date('Y-m-d H:i'),
						//'nro_recibo' => $nro_recibo,
						'tipo' => 1,
						'personal_id' => $this->USUARIO->personal_id,
						'estado' => 'ACTIVO',
						'estado_pago' => 'PENDIENTE',
						'observaciones' => 'PAGO TEMPORAL'
					));

					if($pago->save()){
						//$pagos[] = $pago;
						//$this->boletaConfig->numero += 1;
						//$this->boletaConfig->save();
					}
				}else{
					$pago = Pago::find(array(
						'conditions' => 'estado="ACTIVO" AND colegio_id="'.$this->COLEGIO->id.'" AND matricula_id="'.$matricula->id.'" AND nro_pago="'.$this->get->nro_pago.'" AND tipo="1"'
					));

					if($pago){
						//if($pago->nro_recibo == '-' || empty($pago->nro_recibo)){
						//	$pago->nro_recibo = $this->boletaConfig->getCurrentSerie().'-'.$this->boletaConfig->getCurrentNumero();
							$pago->observaciones = 'PAGO TEMPORAL';

							if($pago->save()){
								//$pagos[] = $pago;
								//$this->boletaConfig->numero += 1;
								//$this->boletaConfig->save();
							}
						//}
					}
				}

				$impresion = $pago->getActiveImpresion();
				if(!is_null($impresion)){
					
					if($impresion->impreso == 'NO'){
						$impresiones[] = $impresion;
						$impresion->update_attributes(array(
							'impreso' => 'SI',
							'fecha_impresion' => date('Y-m-d')
						));
					}
				}
			}
		}

		//$this->render('imprimir', array('impresiones' => $impresiones));

		generarBoletasZip($impresiones, $this->COLEGIO);

	}

	function do_boletear_moras(){

		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$pagosx = Pago::all(array(
			'conditions' => 'estado_pago= "CANCELADO"
			AND estado="ACTIVO"
			AND colegio_id="'.$this->COLEGIO->id.'"
			AND tipo="1"
			AND fecha_cancelado BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")
			AND mora > 0
			ORDER BY fecha_cancelado ASC
			'
		));

		$impresiones = array();
		$limit = 0;
		foreach($pagosx As $pago){
			if($pago->matricula->isOculto()) continue;

			if($limit >= $this->get->cantidad){
				break;
			}

			$impresion = $pago->getActiveImpresionMora();
			if(!is_null($impresion)){

				if($impresion->impreso == 'NO'){
					$impresiones[] = $impresion;
					$impresion->update_attributes(array(
						'impreso' => 'SI',
						'fecha_impresion' => date('Y-m-d')
					));
					++$limit;
				}
			}

		}
		$this->imprimir_mora_pdf($impresiones);
	}

	function do_boletear_moras_json(){

		$from = date('Y-m-d', strtotime($this->get->from));
		$to = date('Y-m-d', strtotime($this->get->to));

		$pagosx = Pago::all(array(
			'conditions' => 'estado_pago= "CANCELADO"
			AND estado="ACTIVO"
			AND colegio_id="'.$this->COLEGIO->id.'"
			AND tipo="1"
			AND fecha_cancelado BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")
			AND mora > 0
			ORDER BY fecha_cancelado ASC
			'
		));

		$impresiones = array();
		$limit = 0;
		foreach($pagosx As $key => $pago){
			if($pago->matricula->isOculto()) continue;

			if($limit >= $this->get->cantidad){
				break;
			}
			
			$impresionBoleta = $pago->getActiveImpresion(false);
			$impresionMora = $pago->getActiveImpresionMora(false);

			if(!$impresionBoleta) continue;
			$mes = date('n', strtotime($impresionBoleta->fecha_impresion));
			$anio = date('Y', strtotime($impresionBoleta->fecha_impresion));

			//echo $key.' - '.$impresionMora->getNumero().'<br />';
			if($anio <= 2018 && $mes <= 8 && is_null($impresionMora)){
				
				$impresion = $pago->getActiveImpresionMoraBoleta();	
			}else{

				$impresion = $pago->getActiveImpresionMora();
			}
			
			//$impresion = $pago->getActiveImpresionMora();
			
			//$impresiones[] = $impresion;

			
			if(!is_null($impresion)){
				
				if($impresion->impreso == 'NO'){
					$impresiones[] = $impresion;
					$impresion->update_attributes(array(
						'impreso' => 'SI',
						'fecha_impresion' => date('Y-m-d')
					));
					++$limit;
				}
			}
		}

		generarNotasDebitoZip($impresiones, $this->COLEGIO);
	}

	function importaciones(){
		$importaciones = Pago_Historial::all(array(
			'conditions' => 'colegio_id="'.$this->COLEGIO->id.'" AND YEAR(fecha) = "'.$this->COLEGIO->anio_activo.'"'
		));

		$this->render(array('importaciones' => $importaciones));
	}
	function detalles_historial(){
		$historial = Pago_Historial::find($this->get->historial_id);
		$pagos = $historial->getPagos();

		$this->render(array('historial' => $historial, 'pagos' => $pagos));
	}

	function imprimir_historial(){
		$historial = Pago_Historial::find($this->get->historial_id);
		$impresiones = array();
		if($historial){
			$pagos = $historial->getPagos();
			foreach($pagos As $pago){
				/*
				if($pago->nro_recibo == '-'){
					$pago->nro_recibo = $this->boletaConfig->getCurrentSerie().'-'.$this->boletaConfig->getCurrentNumero();
					$this->boletaConfig->numero += 1;
					$this->boletaConfig->save();
				}
				$pago->save();
				*/
				$impresion = $pago->getActiveImpresion();
				if(!is_null($impresion)){
					$impresiones[] = $impresion;
					// el pago ha sido adelantado
					if($impresion->impreso == 'NO'){
						$impresion->update_attributes(array(
							'impreso' => 'SI',
							//'fecha_impresion' => date('Y-m-d')
							'fecha_impresion' => $pago->fecha_cancelado
						));
					}
				}
			}

			$historial->impreso = 'SI';
			$historial->save();
			$this->render('imprimir', array('impresiones' => $impresiones));
		}

	}
}
