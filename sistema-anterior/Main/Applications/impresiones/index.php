<?php
class ImpresionesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$impresiones = $this->pago->impresiones;
		$this->render(array('impresiones' => $impresiones));
	}
	
	function save(){
		$r = -5;
		$this->impresion->set_attributes(array(
			/*'colegio_id' => $this->post->colegio_id,
			'tipo' => $this->post->tipo,
			'tipo_documento' => $this->post->tipo_documento,
			'numero' => $this->post->numero,
			'serie' => $this->post->serie,
			'estado' => $this->post->estado,
			'impreso' => $this->post->impreso,*/
			'fecha_impresion' => $this->post->fecha_impresion,
			'hora_impresion' => $this->post->hora_impresion,
			/*'pago_id' => $this->post->pago_id,
			'boleta_id' => $this->post->boleta_id,
			'enviado' => $this->post->enviado,
			'verificado' => $this->post->verificado,
			'fecha_anulado' => $this->post->fecha_anulado,
			'numero_anulado' => $this->post->numero_anulado,
			'json_generado' => $this->post->json_generado,*/
		));
		
		if($this->impresion->is_valid()){
			$r = $this->impresion->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->impresion->id, 'errors' => $this->impresion->errors->get_all()));
	}

	function borrar($r){
		$r = $this->impresion->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	

	function change_estado(){
		
		$this->impresion->estado = $this->impresion->estado == 'ANULADO' ? 'ACTIVO' : 'ANULADO';

		$r = $this->impresion->save() ? 1 : 0;
		echo json_encode(array($r));
	}


	function generar_json_boleta($r){

		$json = getBoletaFromImpresionId($this->impresion->id, $this->COLEGIO);

		if(!empty($this->get->check)){
			Impresion::table()->update(['json_generado' => 'SI'], ['id' => $this->impresion->id]);
		}

		header('Content-type: application/json;charset=utf-8');
		header('Content-Disposition: attachment; filename='.$json->nombre);
		
		echo json_encode($json->contenido, JSON_PRETTY_PRINT);
	}

	function enviar_json_boleta($r){
		header('Content-type: application/json;charset=utf-8');

		$json = getBoletaFromImpresionId($r->id, $this->COLEGIO);
		
		$data = [
			'customer' => [
				'username' => "20535891622VanGua02",
				'password' => "Vangua2018*"
			],
			'fileName' => $json->nombre,
			'fileContent' => base64_encode(json_encode($json->contenido))
		];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,            "http://calidad.escondatagate.net/wsParser/rest/parserWS" );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POST,           1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($data, JSON_PRETTY_PRINT) ); 
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json')); 

		$result = curl_exec ($ch);

		//echo json_encode($data, JSON_PRETTY_PRINT);

		echo $result;
	}

	function generar_json_nota_debito_from_pago($r){
		$impresion = $this->pago->getActiveImpresionMora();
		if(!is_null($impresion)){
			//$impresiones[] = $impresion;
			if($impresion->impreso == 'NO'){
				$impresion->update_attributes(array(
					'impreso' => 'SI',
					'json_generado' => 'SI',
					'fecha_impresion' => date('Y-m-d')
				));
			}

			$json = getNotaDebitoFromImpresionId($impresion->id, $this->COLEGIO);
			if(!empty($this->get->check)){
				Impresion::table()->update(['json_generado' => 'SI'], ['id' => $r->id]);
			}
			header('Content-type: application/json;charset=utf-8');
			header('Content-Disposition: attachment; filename='.$json->nombre);
			echo json_encode($json->contenido, JSON_PRETTY_PRINT);
		}
	}

	function generar_json_nota_debito($r){

		$json = getNotaDebitoFromImpresionId($this->impresion->id, $this->COLEGIO);
		if(!empty($this->get->check)){
			Impresion::table()->update(['json_generado' => 'SI'], ['id' => $this->impresion->id]);
		}
		header('Content-type: application/json;charset=utf-8');
		header('Content-Disposition: attachment; filename='.$json->nombre);
		echo json_encode($json->contenido, JSON_PRETTY_PRINT);
	}

	function generar_json_boleta_mora($r){

		$json = getBoletaMoraFromImpresionId($this->impresion->id, $this->COLEGIO);

		if(!empty($this->get->check)){
			Impresion::table()->update(['json_generado' => 'SI'], ['id' => $r->id]);
		}

		header('Content-type: application/json;charset=utf-8');
		header('Content-Disposition: attachment; filename='.$json->nombre);
		
		echo json_encode($json->contenido, JSON_PRETTY_PRINT);
	}

	function generar_json_rc_boleta($r){
		
		$json = getRCInafectaFromImpresionId($this->impresion->id, $this->COLEGIO);
		header('Content-type: application/json;charset=utf-8');
		header('Content-Disposition: attachment; filename='.$json->nombre);

		echo json_encode($json->contenido, JSON_PRETTY_PRINT);
	}

	function generar_json_rc_nota($r){
		
		$json = getRCNotaFromImpresionId($this->impresion->id, $this->COLEGIO);
		header('Content-type: application/json;charset=utf-8');
		header('Content-Disposition: attachment; filename='.$json->nombre);

		echo json_encode($json->contenido, JSON_PRETTY_PRINT);
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'impresiones', true);
		$this->impresion = !empty($this->params->id) ? Impresion::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Impresion();
		$this->context('impresion', $this->impresion); // set to template
		if(!empty($this->params->pago_id)){
			$this->pago = Pago::find([
				'conditions' => ['sha1(id) = ?', $this->params->pago_id]
			]);
			$this->context('pago', $this->pago);
		}
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->impresion);
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
			'tipo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo_documento' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'numero' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'serie' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'impreso' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_impresion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'date'
			),
			'hora_impresion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'time'
			),
			'pago_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'boleta_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'enviado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'verificado' => array(
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
			'json_generado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
