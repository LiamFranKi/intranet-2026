<?php
class Grupo_Taller_Matricula extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'grupos_talleres_matriculas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'alumno',
			'class_name' => 'Alumno',
		),
		array(
			'taller',
			'class_name' => 'Grupo_Taller',
			'foreign_key' => 'taller_id',
		),
	);
	static $has_many = array();
	static $has_one = array();
	
	static $validates_presence_of = Array();
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = Array();
	static $validates_uniqueness_of = Array();

	function getFullName(){
		return mb_strtoupper($this->apellido_paterno.' '.$this->apellido_materno, 'utf-8').', '.ucwords(mb_strtolower($this->nombres, 'utf-8'));
	}
	/*
	function getMontoTotal(){
		return $this->monto + $this->mora;
	}*/

	function getFrecuencia(){
		return !empty($this->frecuencia) ? unserialize($this->frecuencia) : [];
	}

	// FACTURACION
	function getSerie(){
		return str_pad($this->serie, 3, '0', STR_PAD_LEFT);
	}

	function getNumero(){
		return str_pad($this->numero, 8, '0', STR_PAD_LEFT);
	}

	function getSerieNumero(){
		return $this->getSerie().'-'.$this->getNumero();
	}

	function getMontoTotal(){
		return $this->precio;
	}

	function getDecimal(){
		$decimal = (string) round(($this->getMontoTotal() - intval($this->getMontoTotal())) * 100, 0);
		return $decimal;
	}

	function getLetras(){
		$letras = strtoupper(num2letras(intval($this->getMontoTotal()))).' CON '.(str_pad($this->getDecimal(), 2, 0, STR_PAD_LEFT)).'/100 SOLES';
		return $letras;
	}

	function getDescription(){
		return 'Taller Educativo '.$this->taller->descripcion;
	}

	function getJSON(){

		$colegio = Colegio::first();

		$nombre = $colegio->ruc.'-03-B'.$this->getSerie().'-'.$this->getNumero().'.json';

		$json = [
			'boleta' => [
				'IDE' => [
					'numeracion' => 'B'.$this->getSerie().'-'.$this->getNumero(),
					'fechaEmision' => date('Y-m-d', strtotime($this->fecha_registro)),
					'codTipoDocumento' => '03',
					'tipoMoneda' => 'PEN',
					'fechaVencimiento' => date('Y-m-d', strtotime($this->fecha_registro))
				],
				'EMI' => [
					'tipoDocId' => '6',
					'numeroDocId' => $colegio->ruc,
					'razonSocial' => $colegio->razon_social,
					'direccion' => $colegio->direccion,
					'codigoAsigSUNAT' => '0000'
				],
				'REC' => [
					'tipoDocId' => '1',
					'numeroDocId' => $this->dni,
					'razonSocial' => $this->getFullName()
				],
				'CAB' => [
					'inafectas' => [
						'codigo' => '1002',
						'totalVentas' => (string) number_format($this->getMontoTotal(), 2)
					],
					'totalImpuestos' => [
						[
							'idImpuesto' => '9998',
							'montoImpuesto' => '0.00'
						]
					],
					'importeTotal' => (string) number_format(round($this->getMontoTotal(), 2), 2),
					'tipoOperacion' => '0101',
					'leyenda' => [
						[
							'codigo' => '1000',
							'descripcion' => $this->getLetras()      
						]
					],
					'montoTotalImpuestos' => '0.00'
				],
				'DET' => [
					
					
					
				]


			]
		];

		$detalles = [];

		
		$detalle = [
			'numeroItem' => '001',
			'descripcionProducto' => 'Taller Educativo '.$this->taller->descripcion,
			'cantidadItems' => '1.00',
			'unidad' => 'NIU',
			'valorUnitario' => (string) number_format($this->getMontoTotal(), 2),
			'precioVentaUnitario' => (string) number_format(round($this->getMontoTotal(), 2), 2),
			'totalImpuestos' => [
				[
					'idImpuesto' => '9998',
					'montoImpuesto' => "0.00",
					'tipoAfectacion' => '30',
					'montoBase' => (string) number_format($this->getMontoTotal(), 2),
					'porcentaje' => '0.00'
				]
			],
			'valorVenta' => (string) number_format($this->getMontoTotal(), 2),
			'montoTotalImpuestos' => '0.00'
		];
		$detalles[] = $detalle;
		

		$json['boleta']['DET'] = $detalles;

		return (object) [
			'nombre' => $nombre,
			'contenido' => $json
		];
	}
}
