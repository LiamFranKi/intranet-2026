<?php
class Pago extends TraitConstants{
//	use Constants;

	static $pk = 'id';
	static $table_name = 'pagos';
	static $connection = '';

	static $belongs_to = array(
		array(
			'colegio',
			'class_name' => 'Colegio',
		),
		array(
			'matricula',
			'class_name' => 'Matricula',
		),
		array(
			'personal',
			'class_name' => 'Personal',
		),
	);
	static $has_many = array(
		array(
			'impresiones',
			'class_name' => 'Impresion',
		),
	);
	static $has_one = array();

	static $validates_presence_of = array(
		array(
			'nro_pago',
		),
		array(
			'monto',
		),
		array(
			'fecha_hora',
		),
		/*
		array(
			'nro_recibo',
		),
		*/
		array(
			'personal_id',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();

	function getLastImpresion(){
		$impresion = Impresion::find(array(
			'conditions' => 'pago_id = "'.$this->id.'" AND tipo = "PAGO" AND tipo_documento = "BOLETA"'
		));

		return $impresion;
		
	}

	function getActiveImpresion($save = true){
		$impresion = Impresion::find(array(
			'conditions' => 'pago_id = "'.$this->id.'" AND estado = "ACTIVO" AND tipo = "PAGO" AND tipo_documento = "BOLETA"'
		));

		if($impresion) return $impresion;
		if($save && $this->monto > 0){

			$boletaConfig = Boleta_Configuracion::find_by_sede_id($this->matricula->grupo->sede_id);

			$impresion = new Impresion(array(
				'colegio_id' => $this->colegio_id,
				'tipo' => 'PAGO',
				'tipo_documento' => 'BOLETA',
				'numero' => intval($boletaConfig->getCurrentNumero()),
				'serie' => intval($boletaConfig->getCurrentSerie()),
				'estado' => 'ACTIVO',
				'impreso' => 'NO',
				//'fecha_impresion' => date('Y-m-d'),
				'pago_id' => $this->id
			));

			if($impresion->save()){
				$boletaConfig->numero += 1;
				$boletaConfig->save();
			}
		}
		return $impresion;
	}

	function getActiveImpresionMoraBoleta($save = true){
		return $this->_getActiveImpresionMora($save, 'BOLETA');
	}

	function getActiveImpresionMora($save = true){
		return $this->_getActiveImpresionMora($save, 'NOTA');
	}

	function _getActiveImpresionMora($save = true, $tipo){
		$impresion = Impresion::find(array(
			'conditions' => 'pago_id = "'.$this->id.'" AND estado = "ACTIVO" AND tipo = "MORA" AND tipo_documento="'.$tipo.'"'
		));
		if($impresion) return $impresion;
		if($save && $this->mora > 0){
			
			//$boletaConfig = Boleta_Configuracion::first();
			$boletaConfig = Boleta_Configuracion::find_by_sede_id($this->matricula->grupo->sede_id);

			if($tipo == 'NOTA'){
				$numero = $boletaConfig->getCurrentNumeroMora();
				$serie = $boletaConfig->getCurrentSerieMora();
			}
			if($tipo == 'BOLETA'){
				$numero = $boletaConfig->getCurrentNumero();
				$serie = $boletaConfig->getCurrentSerie();
			}
			
			$impresion = new Impresion(array(
				'tipo' => 'MORA',
				'tipo_documento' => $tipo,
				'numero' => intval($numero),
				'serie' => intval($serie),
				'estado' => 'ACTIVO',
				'impreso' => 'NO',
				//'fecha_impresion' => date('Y-m-d'),
				'pago_id' => $this->id
			));

			if($impresion->save()){
				if($tipo == 'NOTA')
					$boletaConfig->numero_mora += 1;
				if($tipo == 'BOLETA')
					$boletaConfig->numero += 1;

				$boletaConfig->save();
			}
		}


		return $impresion;
	}

	function getActiveImpresionComision($save = true){
		$impresion = Impresion::find(array(
			'conditions' => 'pago_id = "'.$this->id.'" AND estado = "ACTIVO" AND tipo = "COMISION" AND tipo_documento = "BOLETA"'
		));

		if($impresion) return $impresion;
		if($save && $this->forma_pago == "TARJETA" && $this->comision_tarjeta > 0){
			//$boletaConfig = Boleta_Configuracion::first();
			$boletaConfig = Boleta_Configuracion::find_by_sede_id($this->matricula->grupo->sede_id);

			$impresion = new Impresion(array(
				'colegio_id' => $this->colegio_id,
				'tipo' => 'COMISION',
				'tipo_documento' => 'BOLETA',
				'numero' => intval($boletaConfig->getCurrentNumero()),
				'serie' => intval($boletaConfig->getCurrentSerie()),
				'estado' => 'ACTIVO',
				'impreso' => 'NO',
				//'fecha_impresion' => date('Y-m-d'),
				'pago_id' => $this->id
			));

			if($impresion->save()){
				$boletaConfig->numero += 1;
				$boletaConfig->save();
			}
		}
		return $impresion;
	}

	function hasActiveImpresion(){

	}

	function getTipo(){
		return $this->TIPOS_PAGO[$this->tipo];
	}

	function getDescription(){
		///if($this->tipo == 0 && $this->incluye_agenda == 'SI') return 'MATRICULA Y AGENDA';
		if($this->tipo == 3){
			return 'COMEDOR '.mb_strtoupper($this->MESES[$this->nro_pago + $this->colegio->inicio_pensiones - 1], 'utf-8');
		}
		return ($this->tipo == 0 ? 'MATRÍCULA '.$this->matricula->grupo->anio : ($this->tipo == 1 ? 'MENSUALIDAD '.strtoupper($this->colegio->getCicloPensionesSingle($this->nro_pago)).' '.$this->matricula->grupo->anio : $this->descripcion));
	}

	function getDescriptionConcarBanco(){
		if($this->tipo == 0 && $this->incluye_agenda == 'SI') return 'COBRO MATRICULA';
		if($this->tipo == 3){
			return 'COBRO ALIMENTOS';
		}
		return 'COBRO '.($this->tipo == 0 ? 'MATRICULA' : ($this->tipo == 1 ? 'MENSUALIDAD' : $this->descripcion));
	}

	function getTotal(){


		if($this->incluye_agenda == 'SI' && $this->tipo == 0){ // incluye agenda solo para la matrícula
			$pagoAgenda = Pago::find(array(
				'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->colegio_id.'" AND matricula_id="'.$this->matricula_id.'" AND nro_pago="1" AND tipo="2" AND incluye_agenda="SI"'
			));
			if($pagoAgenda){
				return $this->monto + $pagoAgenda->monto;
			}
		}

		return $this->monto + $this->mora;
	}

	function getDecimal(){
		$decimal = (string) ($this->getMonto() - intval($this->getMonto())) * 100;
		return $decimal;
	}

	function getDecimalMora(){
		$decimal = (string) ($this->mora - intval($this->mora)) * 100;
		return $decimal;
	}
	/*
	function getLetras(){
		$letras = strtoupper(num2letras(intval($this->getTotal()))).' '.(str_pad($this->getDecimal(), 2, 0, STR_PAD_LEFT)).'/100 NUEVOS SOLES';
		return $letras;
	}
	*/

	function getMonto(){
		///if($this->incluye_agenda == 'SI'){
//			$pagoAgenda = Pago::find(array(
//				'conditions' => 'estado_pago="CANCELADO" AND estado="ACTIVO" AND colegio_id="'.$this->colegio_id.'" AND matricula_id="'.$this->matricula_id.'" AND nro_pago="1" AND tipo="2" AND incluye_agenda="SI"'
//			));
//			if($pagoAgenda){
//				return $this->monto + $pagoAgenda->monto;
//			}
//		///}

		return $this->monto;
	}

	function getComisionPagoTarjeta(){
		if($this->forma_pago == "EFECTIVO") return 0;
		return $this->monto * $this->comision_tarjeta / 100;
	}

	function getLetras(){
		$letras = strtoupper(num2letras(intval($this->getMonto()))).' CON '.(str_pad($this->getDecimal(), 2, 0, STR_PAD_LEFT)).'/100 SOLES';
		return $letras;
	}

	function getLetrasMora(){
		$letras = strtoupper(num2letras(intval($this->mora))).' CON '.(str_pad($this->getDecimalMora(), 2, 0, STR_PAD_LEFT)).'/100 SOLES';
		return $letras;
	}

	function getTipoDescription(){
		if($this->getTipo() == 'Pensión'){
			$ciclo_pensiones = $this->colegio->getCicloPensiones();
			return ($ciclo_pensiones == 'Mes' ? 'Pensión - '.$this->MESES[$this->nro_pago + $this->colegio->inicio_pensiones - 1] : ($ciclo_pensiones.' '.$this->nro_pago));
		}
		if($this->getTipo() == 'Comedor'){
			return 'Comedor - '.$this->MESES[$this->nro_pago + $this->colegio->inicio_pensiones - 1];
		}
		return $this->getTipo();
	}

	function getCurrentSerie(){
		return str_pad($this->getSerie(), 3, '0', STR_PAD_LEFT);
	}

	function getCurrentNumero(){
		return str_pad($this->getNumero(), 7, '0', STR_PAD_LEFT);
	}

	function getCurrentSerieMora(){
		return str_pad($this->getSerieMora(), 3, '0', STR_PAD_LEFT);
	}

	function getCurrentNumeroMora(){
		return str_pad($this->getNumeroMora(), 7, '0', STR_PAD_LEFT);
	}

	function getSerie(){
		$serie = explode('-', $this->nro_recibo);
		return $serie[0];
	}

	function getNumero(){
		$serie = explode('-', $this->nro_recibo);
		return $serie[1];
	}

	function getSerieMora(){
		$serie = explode('-', $this->nro_recibo_mora);
		return $serie[0];
	}

	function getNumeroMora(){
		$serie = explode('-', $this->nro_recibo_mora);
		return $serie[1];
	}

	function getNroBoleta(){
		return $this->getSerie().'-'.$this->getNumero();
	}


	public static $historial = array();
	public static function fillHistorial(){
		$hs = Pago_Historial::all();
		foreach($hs As $h){
			Pago::$historial = array_merge(Pago::$historial, $h->getData());
		}
	}

	function inHistorial(){
		if(count(Pago::$historial) <= 0){
			Pago::fillHistorial();
		}

		return in_array($this->id, Pago::$historial);
	}

	function getApellidos(){
		$alumno = $this->matricula->alumno;
		return $alumno->apellido_paterno.' '.$alumno->apellido_materno;
	}

	function getApellidosBanco(){
		$alumno = $this->matricula->alumno;

		//return $alumno->apellido_paterno[0].' '.$alumno->apellido_materno[0];
		$abreviatura = '';
		foreach(explode(' ', $alumno->nombres) As $nombre){
			$abreviatura .= $nombre[0];
		}

		$abreviatura .= $alumno->apellido_paterno[0].$alumno->apellido_materno[0];
		return $abreviatura;
	}

	function getCuentaContable(){
		if($this->banco == 'BBVA'){
			return '104103';
		}elseif($this->banco == 'BCP'){
			return '104101';
		}
	}

	function getCodigoAnexo(){
		if($this->banco == 'BBVA'){
			return '04112';
		}elseif($this->banco == 'BCP'){
			return '2338930';
		}
	}

	function getNroMovimiento(){
		if($this->banco == 'BBVA'){
			return $this->nro_movimiento_banco;
		}elseif($this->banco == 'BCP'){
			//return '2338930';
			$letras = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
			$pagos = Pago::all(array(
				'conditions' => 'nro_movimiento_banco = "'.$this->nro_movimiento_banco.'" AND tipo != 2',
				'order' => 'id ASC'
			));
			if(count($pagos) > 1){
				$i = 0;
				foreach($pagos As $pago){
					if($pago->id == $this->id){
						return $this->nro_movimiento_importado.'-'.$letras[$i].'-'.$this->nro_movimiento_banco;
					}
					++$i;
				}
			}else{
				return $this->nro_movimiento_importado.'-'.$this->nro_movimiento_banco;
			}

		}
	}

	function getInteresPendiente(){
		$mes = $this->nro_pago + 2;
		$vencimiento = "31-".$mes.'-'.$this->matricula->grupo->anio;
		$interes = dayDifference(date('Y-m-d'), date('Y-m-d', strtotime($vencimiento)));
		return abs($interes);
	}


	// FACTURACION E

	function getIGV(){
		return round($this->monto - $this->getMontoGravado(), 2);
	}

	function getMontoGravado(){
		return round($this->monto / 1.18, 2); // 18%
	}


	function getIGVMora(){
		return round($this->mora - $this->getMontoGravadoMora(), 2);
	}

	function getMontoGravadoMora(){
		return round($this->mora / 1.18, 2); // 18%
	}
}
