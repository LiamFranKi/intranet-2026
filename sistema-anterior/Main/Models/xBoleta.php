<?php
class xBoleta{
	public $type;
	public $object;


	function __construct($object, $type){
		$this->type = $type;
		$this->object = $object;

	}

	function isPenalidad(){
		return $this->type == 'MORA_BOLETA';
	}

	function getPagoAgenda(){
		if($this->object->incluye_agenda == 'SI'){
			$pagoAgenda = Pago::find(array(
				'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->object->colegio_id.'" AND matricula_id="'.$this->object->matricula_id.'" AND nro_pago="1" AND tipo="2" AND incluye_agenda="SI"'
			));
			return $pagoAgenda;
		}
		return null;
	}

	function getDNI(){
		switch($this->type){
			case 'BOLETA':
			case 'MATRICULA_TALLER':
				return $this->object->dni;
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'MORA_BOLETA':
			case 'MORA_NOTA':
			case 'AGENDA':
			case 'COMEDOR':
				return $this->object->pago->matricula->alumno->nro_documento;
			break;
		}
	}

	function getFechaCancelado(){
		switch($this->type){
			case 'BOLETA':
				return $this->object->fecha;
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
				return $this->object->pago->fecha_cancelado;
			break;

			case 'MORA_BOLETA':
				return $this->object->pago->fecha_cancelado;
			break;

			case 'MORA_NOTA':
				return $this->object->pago->fecha_cancelado;
			break;
			case 'MATRICULA_TALLER':
				return date('Y-m-d', strtotime($this->object->fecha_registro));
			break;
		}
	}

	function getNroMovimiento(){
		switch($this->type){
			case 'BOLETA':
				return "";
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
				return $this->object->pago->nro_movimiento_importado; //importado;
			break;

			case 'MORA_BOLETA':
				return $this->object->pago->nro_movimiento_importado; //importado;
			break;

			case 'MORA_NOTA':
				return $this->object->pago->nro_movimiento_importado; //importado;
			break;
			case 'MATRICULA_TALLER':
				return "";
			break;
		}
	}

	function getFecha(){
		switch($this->type){
			case 'BOLETA':
				return $this->object->fecha;
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
				return $this->object->fecha_impresion;
			break;

			case 'MORA_BOLETA':
				//$impresion = $this->object->getActiveImpresionMora(false);
				return $this->object->pago->fecha_cancelado;
				//return $this->object->fecha_impresion;
			break;

			case 'MORA_NOTA':
				//return $this->object->fecha_impresion;
				return $this->object->pago->fecha_cancelado;
			break;
			case 'MATRICULA_TALLER':
				return date('Y-m-d', strtotime($this->object->fecha_registro));
			break;
		}
	}

	function getFechaVencimiento(){
		switch($this->type){
			case 'BOLETA':
				return $this->object->fecha;
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
				return $this->object->getFechaCancelado();
			break;

			case 'MORA_BOLETA':
				//$impresion = $this->object->getActiveImpresionMora(false);
				return $this->object->pago->fecha_cancelado;
			break;

			case 'MORA_NOTA':
				return $this->object->pago->fecha_cancelado;
			break;
			case 'MATRICULA_TALLER':
				return date('Y-m-d', strtotime($this->object->fecha_registro));
			break;
		}
	}

	function getApellidos(){
		switch($this->type){
			case 'BOLETA':
				$apellidos = str_replace(',', '', $this->object->nombre);
				$apellidos = explode(' ', $apellidos);
				
				return ($apellidos[0].' '.$apellidos[1]);
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'MORA_BOLETA':
			case 'MORA_NOTA':
			case 'AGENDA':
			case 'COMEDOR':
				$alumno = $this->object->pago->matricula->alumno;
				return $alumno->apellido_paterno.' '.$alumno->apellido_materno;
			break;

			case 'MATRICULA_TALLER':
				return $this->object->apellido_paterno.' '.$this->object->apellido_materno;
			break;
		}
	}

	function getApellidoNombre(){
		switch($this->type){
			case 'BOLETA':
				$nombres = str_replace(',', '', $this->object->nombre);
				$apellidos = explode(' ', $nombres);
				
				return ($apellidos[0].' '.$apellidos[2]);
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'MORA_BOLETA':
			case 'MORA_NOTA':
			case 'AGENDA':
			case 'COMEDOR':
				$alumno = $this->object->pago->matricula->alumno;
				return $alumno->apellido_paterno.' '.$alumno->nombres;
			break;

			case 'MATRICULA_TALLER':
				return $this->object->apellido_paterno.' '.$this->object->nombres;
			break;
		}
	}

	function getImpresionMora(){
		if($this->type == "PENSION"){
			return $this->object->pago->getActiveImpresionMora(false);
		}
		return null;
	}

	function getNombresApellidos(){
		switch($this->type){
			case 'BOLETA':
				$nombres = str_replace(',', '', $this->object->nombre);
				return $nombres;
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'MORA_BOLETA':
			case 'MORA_NOTA':
			case 'AGENDA':
			case 'COMEDOR':
				$alumno = $this->object->pago->matricula->alumno;
				return $alumno->nombres.' '.$alumno->apellido_paterno.' '.$alumno->apellido_materno;
			break;

			case 'MATRICULA_TALLER':
				return $this->object->nombres.' '.$this->object->apellidos;
			break;
		}
	}

	function getTipo(){
		if($this->object->serie == 3) return '';


		if($this->type == "BOLETA"){
			$sede = $this->object->sede;
		}else{
			$sede = $this->object->pago->matricula->grupo->sede;
		}
		

		switch($this->type){
			case 'BOLETA':
				return $sede->prefijo_boleta;
				//return 'B';
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
				return $sede->prefijo_boleta;
				//return 'B';
			break;

			case 'MORA_BOLETA':
				return $sede->prefijo_boleta;
				//return 'B';
			break;

			case 'MORA_NOTA':
				return 'BND';
			break;

			case 'MATRICULA_TALLER':
				return $sede->prefijo_boleta;
				//return 'B';
			break;
		}
	}

	function getTipoDocumento(){
		switch($this->type){
			case 'BOLETA':
			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
			case 'MORA_BOLETA':
			case 'MATRICULA_TALLER':
				return "BV";
				//return 'B';
			break;

			case 'MORA_NOTA':
				return 'CD';
			break;
		}
	}

	function getSerieDNI(){

	}

	
	function getNumero(){
		switch($this->type){
			case 'BOLETA':
				return intval($this->object->getCurrentNumero());
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
			case 'MATRICULA_TALLER':
				return $this->object->numero;
			break;

			case 'MORA_BOLETA':
				return $this->object->numero;
			break;
			case 'MORA_NOTA':
				if($this->object->serie == 3)
					return $this->object->numero;

				return $this->object->numero;
			break;
		}

		return null;
	}

	function getSerieNumero(){
		switch($this->type){
			case 'BOLETA':
				return $this->object->getCurrentSerie().'-'.$this->object->getCurrentNumero();
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
			case 'MATRICULA_TALLER':
				return $this->object->getSerie().'-'.$this->object->getNumero();
			break;

			case 'MORA_BOLETA':
			case 'MORA_NOTA':
				return $this->object->getSerie().'-'.$this->object->getNumero();
			break;
		}

		return null;
	}

	function getSerie($ceros = 3){
		switch($this->type){
			case 'BOLETA':
				return $this->object->getCurrentSerie($ceros);
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
			case 'MATRICULA_TALLER':
				return $this->object->getSerie($ceros);
			break;

			case 'MORA_BOLETA':
			case 'MORA_NOTA':
				return $this->object->getSerie($ceros);
			break;
		}

		return null;
	}

	function getSerieIntNumero(){
		switch($this->type){
			case 'BOLETA':
				return $this->getTipo().$this->object->getCurrentSerie().'-'.intval($this->object->getCurrentNumero());
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
			case 'MATRICULA_TALLER':
				return $this->getTipo().$this->object->getSerie().'-'.$this->object->numero;
			break;

			case 'MORA_BOLETA':
				return $this->getTipo().$this->object->getSerie().'-'.$this->object->numero;
			break;
			case 'MORA_NOTA':
				if($this->object->serie == 3)
					return $this->getTipo().$this->object->getSerie().'-'.$this->object->numero;

				return $this->getTipo().$this->object->serie.'-'.$this->object->numero;
			break;
		}

		return null;
	}


	// RETORNA EL DOCUMENTO DEL PAGO AL QUE SE ANEXA LA MORA
	function getSerieIntNumeroForMora(){
		if($this->type != "MORA_NOTA") return "";

		$impresion = $this->object->pago->getActiveImpresion(false);
		if(!$impresion) return '';

		return $impresion->getTipo().$impresion->getSerie().'-'.$impresion->numero;
	}

	function getSerieIntNumeroForMora2(){
		if($this->type != "MORA_NOTA") return "";
		$impresion = $this->object->pago->getActiveImpresion(false);
			if(!$impresion) return '';

		$registro = new xBoleta($impresion, 'PENSION');

		return $registro->getTipo().$registro->getSerie(4-strlen($registro->getTipo())).$registro->getNumero();
	}

	function getTipoDocumentoForMora(){
		if($this->type != "MORA_NOTA") return "";
		return "BV";
	}

	function getFechaForMora2(){
		if($this->type != "MORA_NOTA") return "";
		$impresion = $this->object->pago->getActiveImpresion(false);
			if(!$impresion) return '';

		$registro = new xBoleta($impresion, 'PENSION');
		return date('d/m/Y', strtotime($registro->getFecha()));
	}

	function getFechaForMora(){
		return $this->object->pago->fecha_hora;
	}

	function getMontoForMora(){
		return $this->object->pago->getMonto();
	}

	function desglozarIGV(){
		if($this->type == 'BOLETA'){
			$subcategoria = $this->object->getSubcategoria();
			return $subcategoria->concar_igv == 'SI';
		}
		return false;
	}

	function getEstado(){
		return $this->object->estado;
	}

	function anulado(){
		return $this->object->estado == 'ANULADO';
	}

	function getCuentaStarsoft(){
		switch($this->type){
			case 'BOLETA':
				$subcategoria = $this->object->getSubcategoria();
				//return print_r($subcategoria->attributes(), true);
				return $subcategoria->starsoft_cuenta;
			break;
			case 'MATRICULA':
					return '7032101';
				if(date('Y', strtotime($this->object->pago->fecha_cancelado)) != $this->object->pago->matricula->grupo->anio){
					return '1221103';
				}else{
					return '7032101';// 704102
				}
				
			break;

			case 'MATRICULA_TALLER':
				if($this->object->taller->cuenta_contable != "")
					return $this->object->taller->cuenta_contable;

				return '7032109'; // 704104
			break;

			case 'PENSION':
				return '7032102';
			break;
			case 'AGENDA':
				if(date('Y', strtotime($this->object->pago->fecha_cancelado)) != $this->object->pago->matricula->grupo->anio){
					return '1221103';
				}else{
					return '7011103';
				}
			break;
			case 'COMEDOR':
				return '7041108';
			break;
			case 'MORA_BOLETA':
			case 'MORA_NOTA':
				return '7032108';
			break;
		}
	}

	function getCuenta(){
		switch($this->type){
			case 'BOLETA':
				$subcategoria = $this->object->getSubcategoria();
				return $subcategoria->concar_cuenta;
			break;
			case 'MATRICULA':
				if(date('Y', strtotime($this->object->pago->fecha_cancelado)) != $this->object->pago->matricula->grupo->anio){
					return '122103';
				}else{
					return '703211'; // 704102
				}
				
			break;

			case 'MATRICULA_TALLER':
				if($this->object->taller->cuenta_contable != "")
					return $this->object->taller->cuenta_contable;

				return '703219'; // 704104
			break;

			case 'PENSION':
				return '703212';
			break;
			case 'AGENDA':
				if(date('Y', strtotime($this->object->pago->fecha_cancelado)) != $this->object->pago->matricula->grupo->anio){
					return '122103';
				}else{
					return '701113';
				}
			break;
			case 'COMEDOR':
				return '704108';
			break;
			case 'MORA_BOLETA':
			case 'MORA_NOTA':
				return '703218';
			break;
		}
	}

	function getSubDiario($tipo = 'NORMAL'){
		if($tipo == 'NORMAL'){
			return '05';
		}
		if($tipo == 'CAJA_CHICA'){
			return '01';
		}
		/*
		switch($this->type){
			case 'BOLETA':
			case 'AGENDA':
				return '05';
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'COMEDOR':
			case 'MORA_BOLETA':
			case 'MORA_NOTA':
				return '21';
			break;

		}
		*/
		//return '05';
	}

	function getMontoConMora(){
		switch($this->type){
			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
				return $this->object->pago->monto + $this->object->pago->mora;
			break;

		}
	}

	function getMontoTotal(){
		switch($this->type){
			case 'BOLETA':
				return $this->object->getMontoTotal();
			break;

			case 'MATRICULA_TALLER':
				return $this->object->getMontoTotal();
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
				return $this->object->pago->getMonto();
			break;

			case 'MORA_BOLETA':
			case 'MORA_NOTA':
				return $this->object->pago->mora;
			break;
		}
	}

	function getDescripcion(){
		switch($this->type){
			case 'BOLETA':
				$subcategoria = $this->object->getSubcategoria();
				return mb_strtoupper($subcategoria->nombre, 'utf-8');
			break;

			case 'MATRICULA_TALLER':
				return 'Taller Educativo '.$this->object->taller->descripcion;
			break;

			case 'MATRICULA':
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
				//if($this->object->pago->incluye_agenda = 'SI') return 'Matricula y Agenda';
				return mb_strtoupper($this->object->pago->getDescription(), 'utf-8');
			break;

			case 'MORA_BOLETA':
			case 'MORA_NOTA':
				return mb_strtoupper('PENALIDAD '.$this->object->pago->getDescription(), 'utf-8');
			break;
		}
	}

	function getDescripcionTipoGlosa(){
		switch($this->type){
			case 'BOLETA':
				$subcategoria = $this->object->getSubcategoria();
				return mb_strtoupper($subcategoria->nombre, 'utf-8');
			break;

			case 'MATRICULA_TALLER':
				return 'TALLER EDUCATIVO';
			break;

			case 'MATRICULA':
				return 'MATRÍCULA';
			break;
			case 'PENSION':
			case 'AGENDA':
			case 'COMEDOR':
				//if($this->object->pago->incluye_agenda = 'SI') return 'Matricula y Agenda';
				return 'PENSIÓN';
			break;

			case 'MORA_BOLETA':
			case 'MORA_NOTA':
				return 'PENALIDAD';
			break;
		}
	}

	function getDescripcionGlosa(){
		return $this->getDescripcionTipoGlosa().' '.mb_strtoupper($this->getApellidoNombre(), 'utf-8').' '.$this->getSerieIntNumero();
	}

	function getDescripcionIngreso(){
		if($this->getTipoDocumento() != "BV") return "";
		$nombres = $this->getNombresApellidos();
		$nombres = explode(' ', $nombres);
		$nombres = $nombres[0][0].$nombres[1][0].$nombres[2][0].$nombres[3][0];

		return mb_strtoupper("INGRESO ".$this->getDescripcionTipoGlosa().' '.$nombres.' BV '.$this->getSerieIntNumero(), 'utf-8');
	}
}