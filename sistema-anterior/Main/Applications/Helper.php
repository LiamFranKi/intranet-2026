<?php
function Config_get($key, $default = null){
	return Config::get($key, $default);
}

function getToken(){
	return sha1(time() + rand(1, 9999999999));
}

function getMonth($mes){
    $meses = Array('Enero', 'Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre');
    return $meses[$mes-1];
}

function parseFechaHora($fecha_hora = null){
    return date('d-m-Y h:i A', strtotime($fecha_hora));
}

function parseFecha($fecha){
    $fecha = explode('-', date('Y-m-d', strtotime($fecha)));
    return $fecha[2].' de '.getMonth(intval($fecha[1])).' del '.$fecha[0];
}

function numeroLetras($monto){
    $decimal = (string) ($monto - intval($monto)) * 100;
    $letras = strtoupper(num2letras(intval($monto))).' '.(str_pad($decimal, 2, 0, STR_PAD_LEFT)).'/100 NUEVOS SOLES';
    return $letras;
}

function uploadFileBlackList($key, $blacklist = null, $directory = './Static/Archivos'){
	$file = $_FILES[$key];
	if($file['error'] == UPLOAD_ERR_OK){
		$info = pathinfo($file['name']);
		
		if(!is_null($blacklist) && is_array($blacklist) && in_array(strtolower($info['extension']), $blacklist))
			return null;

		$archivo = uploadFile($key, null, $directory);
		return $archivo;
	}

	return null;
}

function uploadFile($key, $extensions = null, $directory = './Static/Archivos'){
	$file = is_array($key) ? $key : $_FILES[$key];
	if($file['error'] == UPLOAD_ERR_OK){
		$info = pathinfo($file['name']);
		$blacklist = ['php', 'sh', 'exe'];
		if(!is_null($extensions) && is_array($blacklist) && in_array(strtolower($info['extension']), $blacklist))
			return null;

		if(!is_null($extensions) && is_array($extensions) && !in_array(strtolower($info['extension']), $extensions))
			return null;

		$newName = getToken().'.'.$info['extension'];
		if(move_uploaded_file($file['tmp_name'], $directory.'/'.$newName))
			return array('new_name' => $newName, 'real_name' => $file['name']);
	}
	return null;
}

function uploadFileMultiple($key, $extensions = null, $directory = './Static/archivos'){
    $files = $_FILES[$key];
    $upload = [];
    if(count($files['name']) > 0)
        foreach($files['name'] As $key => $name){
            $data = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key],
            ];
        

            $file = uploadFile($data, $extensions, $directory);
            if(!is_null($file)){
                $upload[$key] = $file;
            }
        }
    return $upload;
}

function getLongAgo($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'año',
        'm' => 'mes',
        'w' => 'semana',
        'd' => 'dia',
        'h' => 'hora',
        'i' => 'minuto',
        's' => 'segundo',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'Hace '.implode(', ', $string) : 'Hace un momento';
}

function minuteDifference($from, $to){
    $to_time = strtotime($to);
    $from_time = strtotime($from);
    return round(($to_time - $from_time) / 60,2);
  }







//****** API ******/

function sendNotificationToDeviceAPI($device, $params){
	$params = (object) $params;
	
	$data = array(
        'to' => $device->device_token,
        'priority' => 'high',
        'delay_while_idle' => true,
        'time_to_live' => 3,
        //'notification' => array(
            //'title' => empty($params->title) ? 'IES LA MERCED - PUNO' : $params->title,
            //'body' => $params->body,
            //'sound' => 'default',
            //"click_action" => $params->activity
        //),
        'data' => $params->data
    );

    $data_string = json_encode($data);                                                                                   
    //print_r($data);                                                                                              
    $ch = curl_init();      
                  
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');                                                    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");             
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt($ch, CURLOPT_HEADER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
		'Authorization: key=AAAAKryrHMA:APA91bGzjSwQfy_sC9ASh78DN2UvM42l11-Xc1ALmwYoxM3h6cXM-q8tHHNcUCrnaVA5S2DsqSDOwhRvkYdPGvEfgCAktmD5SBRyoNVuS5mR6NmIlyNN2LQwQIqjjZtHerOf8rNADSHY'

    ));                                                                                                                   
                                                                                                                         
    $result = curl_exec($ch);
    
    curl_close($ch);
}

function sendNotificationAPI($usuario, $params){
    if(count($usuario->devices) == 0) return false;
    $params = (object) $params;

    foreach($usuario->devices As $device){
        
    	sendNotificationToDeviceAPI($device, $params);
	    
    }
    
}

function sendNotificationForMensajeAPI($mensaje){
	if(!is_null($mensaje->destinatario))

	sendNotificationAPI($mensaje->destinatario, array(
		'data' => array(
			'tipo' => 'MENSAJE',
			'mensaje_id' => (int) $mensaje->id,
			'title' => 'Colegios Vanguard Schools',
			'body' => 'Nuevo mensaje de '.$mensaje->remitente->getFullName(),
		)
	));
}

function sanear_string($string)
{

    $string = trim($string);

    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );

    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );

    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );

    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );

    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );

    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );

    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(
        array("\\", "¨", "º", "-", "~",
             "#", "@", "|", "!", "\"",
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "`", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", ";", ",", ":",
             ".", '*'),
        '',
        $string
    );


    return $string;
}

function writeExcel($excel, $filename = null){
    
    
  // WRITE
  $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
  $writer->setIncludeCharts(TRUE);
  $filename = $filename ?? getToken().'.xlsx';

  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment;filename="'.$filename.'"');
  header('Cache-Control: max-age=0');  
  flush();
  ob_clean();
  $writer->save('php://output');
}

function getNameFromNumber($num) {
    $numeric = ($num - 1) % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval(($num - 1) / 26);
    if ($num2 > 0) {
        return getNameFromNumber($num2) . $letter;
    } else {
        return $letter;
    }
}

function numToChar($num){
  return getNameFromNumber($num);
}

function num2letras($num, $fem = true, $dec = true) { 
//if (strlen($num) > 14) die("El n?mero introducido es demasiado grande"); 
   $matuni[2]  = "dos"; 
   $matuni[3]  = "tres"; 
   $matuni[4]  = "cuatro"; 
   $matuni[5]  = "cinco"; 
   $matuni[6]  = "seis"; 
   $matuni[7]  = "siete"; 
   $matuni[8]  = "ocho"; 
   $matuni[9]  = "nueve"; 
   $matuni[10] = "diez"; 
   $matuni[11] = "once"; 
   $matuni[12] = "doce"; 
   $matuni[13] = "trece"; 
   $matuni[14] = "catorce"; 
   $matuni[15] = "quince"; 
   $matuni[16] = "dieciseis"; 
   $matuni[17] = "diecisiete"; 
   $matuni[18] = "dieciocho"; 
   $matuni[19] = "diecinueve"; 
   $matuni[20] = "veinte"; 
   $matunisub[2] = "dos"; 
   $matunisub[3] = "tres"; 
   $matunisub[4] = "cuatro"; 
   $matunisub[5] = "quin"; 
   $matunisub[6] = "seis"; 
   $matunisub[7] = "sete"; 
   $matunisub[8] = "ocho"; 
   $matunisub[9] = "nove"; 

   $matdec[2] = "veint"; 
   $matdec[3] = "treinta"; 
   $matdec[4] = "cuarenta"; 
   $matdec[5] = "cincuenta"; 
   $matdec[6] = "sesenta"; 
   $matdec[7] = "setenta"; 
   $matdec[8] = "ochenta"; 
   $matdec[9] = "noventa"; 
   $matsub[3]  = 'mill'; 
   $matsub[5]  = 'bill'; 
   $matsub[7]  = 'mill'; 
   $matsub[9]  = 'trill'; 
   $matsub[11] = 'mill'; 
   $matsub[13] = 'bill'; 
   $matsub[15] = 'mill'; 
   $matmil[4]  = 'millones'; 
   $matmil[6]  = 'billones'; 
   $matmil[7]  = 'de billones'; 
   $matmil[8]  = 'millones de billones'; 
   $matmil[10] = 'trillones'; 
   $matmil[11] = 'de trillones'; 
   $matmil[12] = 'millones de trillones'; 
   $matmil[13] = 'de trillones'; 
   $matmil[14] = 'billones de trillones'; 
   $matmil[15] = 'de billones de trillones'; 
   $matmil[16] = 'millones de billones de trillones'; 

   $num = trim((string)@$num); 
   if ($num[0] == '-') { 
      $neg = 'menos '; 
      $num = substr($num, 1); 
   }else 
      $neg = ''; 
   while ($num[0] == '0') $num = substr($num, 1); 
   if ($num[0] < '1' or $num[0] > 9) $num = '0' . $num; 
   $zeros = true; 
   $punt = false; 
   $ent = ''; 
   $fra = ''; 
   for ($c = 0; $c < strlen($num); $c++) { 
      $n = $num[$c]; 
      if (! (strpos(".,'''", $n) === false)) { 
         if ($punt) break; 
         else{ 
            $punt = true; 
            continue; 
         } 

      }elseif (! (strpos('0123456789', $n) === false)) { 
         if ($punt) { 
            if ($n != '0') $zeros = false; 
            $fra .= $n; 
         }else 

            $ent .= $n; 
      }else 

         break; 

   } 
   
   $ent = '     ' . $ent; 
   if ($dec and $fra and ! $zeros) { 
      $fin = ' coma'; 
      for ($n = 0; $n < strlen($fra); $n++) { 
         if (($s = $fra[$n]) == '0') 
            $fin .= ' cero'; 
         elseif ($s == '1') 
            $fin .= $fem ? ' una' : ' un'; 
         else 
            $fin .= ' ' . $matuni[$s]; 
      } 
   }else 
      $fin = ''; 
    
   if ((int)$ent === 0) return 'Cero ' . $fin; 
   $tex = ''; 
   $sub = 0; 
   $mils = 0; 
   $neutro = false; 
   while ( ($num = substr($ent, -3)) != '   ') { 
      $ent = substr($ent, 0, -3); 
      if (++$sub < 3 and $fem) { 
         $matuni[1] = 'una'; 
         $subcent = 'as'; 
      }else{ 
         $matuni[1] = $neutro ? 'un' : 'uno'; 
         $subcent = 'os'; 
      } 
      $t = ''; 
      $n2 = substr($num, 1); 
      if ($n2 == '00') { 
      }elseif ($n2 < 21) 
         $t = ' ' . $matuni[(int)$n2]; 
      elseif ($n2 < 30) { 
         $n3 = $num[2]; 
         if ($n3 != 0) $t = 'i' . $matuni[$n3]; 
         $n2 = $num[1]; 
         $t = ' ' . $matdec[$n2] . $t; 
      }else{ 
         $n3 = $num[2]; 
         if ($n3 != 0) $t = ' y ' . $matuni[$n3]; 
         $n2 = $num[1]; 
         $t = ' ' . $matdec[$n2] . $t; 
      } 
      $n = $num[0]; 
      if ($n == 1) { 
         $t = ' ciento' . $t; 
      }elseif ($n == 5){ 
         $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t; 
      }elseif ($n != 0){ 
         $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t; 
      } 
      if ($sub == 1) { 
      }elseif (! isset($matsub[$sub])) { 
         if ($num == 1) { 
            $t = ' mil'; 
         }elseif ($num > 1){ 
            $t .= ' mil'; 
         } 
      }elseif ($num == 1) { 
         $t .= ' ' . $matsub[$sub] . '?n'; 
      }elseif ($num > 1){ 
         $t .= ' ' . $matsub[$sub] . 'ones'; 
      }   
      if ($num == '000') $mils ++; 
      elseif ($mils != 0) { 
         if (isset($matmil[$sub])) $t .= ' ' . $matmil[$sub]; 
         $mils = 0; 
      } 
      $neutro = true; 
      $tex = $t . $tex; 
   } 
   $tex = $neg . substr($tex, 1) . $fin; 
   return ucfirst($tex); 
} 


// FACTURACION

/**
 * Genera la boleta INAFECTA a partir de la impresión de un pago
 * @param type $id 
 * @param type $COLEGIO 
 * @return type
 */
function getBoletaFromImpresionId($id, $COLEGIO){
    $impresion = Impresion::find($id);
    $pago = $impresion->pago;
    $matricula = $pago->matricula;
    $alumno = $matricula->alumno;

    //$nombre = $COLEGIO->ruc.'-03-B'.$impresion->getSerie().'-'.$impresion->getNumero().'.json';

    $prefijo = $matricula->grupo->sede->prefijo_boleta;
    $serie = $prefijo == 'B' ? $prefijo.$impresion->getSerie() : $prefijo.$impresion->getSerie(2);
    $nombre = $COLEGIO->ruc.'-03-'.$serie.'-'.$impresion->getNumero().'.json';

    $json = [
        'boleta' => [
            'IDE' => [
                'numeracion' => $serie.'-'.$impresion->getNumero(),
                'fechaEmision' => $impresion->fecha_impresion,
                'codTipoDocumento' => '03',
                'tipoMoneda' => 'PEN',
                'fechaVencimiento' => $impresion->getFechaCancelado()
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $matricula->grupo->sede->direccion,
                'codigoAsigSUNAT' => $matricula->grupo->sede->codigo_sunat
            ],
            'REC' => [
                'tipoDocId' => (string) $alumno->getTipoDocumentoFacturacion(),
                'numeroDocId' => trim($alumno->nro_documento),
                'razonSocial' => $alumno->getApellidosNombres()
            ],
            'CAB' => [
                'inafectas' => [
                    'codigo' => '1002',
                    'totalVentas' => (string) number_format($pago->monto, 2),
                ],
                'totalImpuestos' => [
                    [
                        'idImpuesto' => '9998',
                        'montoImpuesto' => "0.00"
                    ]
                ],
                'importeTotal' => (string) number_format($pago->monto, 2),
                'tipoOperacion' => '0101',
                'leyenda' => [
                    [
                        'codigo' => '1000',
                        'descripcion' => $pago->getLetras()      
                    ]
                    // COVID
                    /*[
                        'codigo' => '3000',
                        'descripcion' => "(Estado de Emergencia Nacional COVID-19  D.S. Nº 044-2020-PCM)"
                    ]*/
                ],
                'montoTotalImpuestos' => '0.00'
            ],
            'DET' => [
                [
                    'numeroItem' => '001',
                    'descripcionProducto' => $pago->getDescription().' - '.$matricula->grupo->getNombreShort2(),
                    'cantidadItems' => '1.00',
                    'unidad' => 'NIU',
                    'valorUnitario' => (string) number_format($pago->monto, 2),
                    'precioVentaUnitario' => (string) number_format($pago->monto, 2),
                    'totalImpuestos' => [
                        [
                            'idImpuesto' => '9998',
                            'montoImpuesto' => "0.00",
                            'tipoAfectacion' => '30',
                            'montoBase' => (string) number_format($pago->monto, 2),
                            'porcentaje' => '0.00'
                        ]
                    ],
                    'valorVenta' => (string) number_format($pago->monto, 2),
                    'montoTotalImpuestos' => '0.00'
                ]
            ]


        ]
    ];

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}



/**
 * Genera el archivo ZIP de las notas de debito moras
 * @return type
 */
function generarNotasDebitoZip($impresiones, $COLEGIO){
    $zip = new ZipArchive();
    $archivo = getToken().'.zip';

    $res = $zip->open('./Static/Temp/'.$archivo, ZipArchive::CREATE);
    if ($res === TRUE) {
        foreach($impresiones As $impresion){
            if($impresion->tipo_documento == 'NOTA')
                $json = getNotaDebitoFromImpresionId($impresion->id, $COLEGIO);
            if($impresion->tipo_documento == 'BOLETA'){
                $json = getBoletaMoraFromImpresionId($impresion->id, $COLEGIO);
            }
            
            
                $zip->addFromString($json->nombre, json_encode($json->contenido, JSON_PRETTY_PRINT));
        }
        
        $zip->close();
    }

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$archivo);

    echo file_get_contents('./Static/Temp/'.$archivo);
    @unlink('./Static/Temp/'.$archivo);
}


/**
 * Genera la nota de débito a partir del ID de la impresión
 * @return type
 */
function getNotaDebitoFromImpresionId($id, $COLEGIO){
    $_impresion = Impresion::find($id);

    $pago = $_impresion->pago;
    $matricula = $pago->matricula;
    $alumno = $matricula->alumno;
    
    $impresion = $pago->getActiveImpresion(false);
    $impresionMora = $_impresion;

    $prefijo = $matricula->grupo->sede->prefijo_boleta;
    $serie = $prefijo == 'B' ? $prefijo.$impresion->getSerie() : $prefijo.$impresion->getSerie(2);

    $nombre = $COLEGIO->ruc.'-08-BND'.intval($impresionMora->getSerie()).'-'.$impresionMora->getNumero().'.json';

    $json = [
        'notaDebito' => [
            'IDE' => [
                'numeracion' => 'BND'.intval($impresionMora->getSerie()).'-'.$impresionMora->getNumero(),
                'fechaEmision' => $pago->fecha_cancelado,
                'tipoMoneda' => 'PEN'
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $matricula->grupo->sede->direccion,
                'codigoAsigSUNAT' => $matricula->grupo->sede->codigo_sunat
            ],
            'REC' => [
                'tipoDocId' => (string) $alumno->getTipoDocumentoFacturacion(),
                'numeroDocId' => trim($alumno->nro_documento),
                'razonSocial' => $alumno->getApellidosNombres()
            ],
            'DRF' => [
                [
                    "tipoDocRelacionado" =>  "03",
                    "numeroDocRelacionado" => $serie.'-'.$impresion->getNumero(),
                    "codigoMotivo" => "03",
                    "descripcionMotivo" => "PENALIDAD POR ATRASO DE PAGO"
                ]

            ],
    
            'CAB' => [
                'inafectas' => [
                    'codigo' => '1002',
                    'totalVentas' => (string) number_format($pago->mora, 2),
                ],
                'totalImpuestos' => [
                    [
                        'idImpuesto' => '9998',
                        'montoImpuesto' => "0.00"
                    ]
                ],
                'importeTotal' => (string) number_format($pago->mora, 2),
                'tipoOperacion' => '0101',
                'leyenda' => [
                    [
                        'codigo' => '1000',
                        'descripcion' => $pago->getLetrasMora()      
                    ]
                ],
                "montoTotalImpuestos" => '0.00'
            ],
            'DET' => [
                [
                    'numeroItem' => '001',
                    'descripcionProducto' => 'PENALIDAD - '.$pago->getDescription(),
                    'cantidadItems' => '1.00',
                    'unidad' => 'NIU',
                    'valorUnitario' => (string) number_format($pago->mora, 2),
                    'precioVentaUnitario' => (string) number_format($pago->mora, 2),
                    'totalImpuestos' => [
                        [
                            'idImpuesto' => '9998',
                            'montoImpuesto' => "0.00",
                            'tipoAfectacion' => '30',
                            'montoBase' => (string) number_format($pago->mora, 2),
                            'porcentaje' => '0.00'
                        ]
                    ],
                    'valorVenta' => (string) number_format($pago->mora, 2),
                    "montoTotalImpuestos" => '0.00'
                ]
            ]
        ]
    ];

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}

function getBoletaMoraFromImpresionId($id, $COLEGIO){
    $_impresion = Impresion::find($id);

    $pago = $_impresion->pago;
    $matricula = $pago->matricula;
    $alumno = $matricula->alumno;
    
    $impresion = $pago->getActiveImpresion(false);
    $impresionMora = $_impresion;


    //$nombre = $COLEGIO->ruc.'-08-BND'.$impresionMora->getSerie().'-'.$impresionMora->getNumero().'.json';
    
    $prefijo = $matricula->grupo->sede->prefijo_boleta;
    $serie = $prefijo == 'B' ? $prefijo.$impresion->getSerie() : $prefijo.$impresion->getSerie(2);
    $serieMora = $prefijo == 'B' ? $prefijo.$impresionMora->getSerie() : $prefijo.$impresionMora->getSerie(2);
    

    $nombre = $COLEGIO->ruc.'-03-'.$serieMora.'-'.$impresionMora->getNumero().'.json';

    
    

    //$nombre = $COLEGIO->ruc.'-03-'.$serie.'-'.$impresion->getNumero().'.json';

    $json = [
        'boleta' => [
            'IDE' => [
                'numeracion' => $serieMora.'-'.$impresionMora->getNumero(),
                'fechaEmision' => $pago->fecha_cancelado,
                'codTipoDocumento' => '03',
                'tipoMoneda' => 'PEN',
                'fechaVencimiento' => $impresionMora->fecha_impresion
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $matricula->grupo->sede->direccion
            ],
            'REC' => [
                'tipoDocId' => (string) $alumno->getTipoDocumentoFacturacion(),
                'numeroDocId' => trim($alumno->nro_documento),
                'razonSocial' => $alumno->getApellidosNombres()
            ],
            'CAB' => [
                'inafectas' => [
                    'codigo' => '1002',
                    'totalVentas' => (string) number_format($pago->mora, 2),
                ],
                'totalImpuestos' => [
                    [
                        'idImpuesto' => '1000',
                        'montoImpuesto' => "0.00"
                    ]
                ],
                'importeTotal' => (string) number_format($pago->mora, 2),
                'leyenda' => [
                    [
                        'codigo' => '1000',
                        'descripcion' => $pago->getLetrasMora()      
                    ]
                ]
            ],
            'DET' => [
                [
                    'numeroItem' => '001',
                    'descripcionProducto' => 'PENALIDAD POR ATRASO DE PAGO - '.$serie.'-'.$impresion->getNumero(),
                    'cantidadItems' => '1.00',
                    'unidad' => 'NIU',
                    'valorUnitario' => (string) number_format($pago->mora, 2),
                    'precioVentaUnitario' => (string) number_format($pago->mora, 2),
                    'totalImpuestos' => [
                        [
                            'idImpuesto' => '1000',
                            'montoImpuesto' => "0.00",
                            'tipoAfectacion' => '31'
                        ]
                    ],
                    'valorVenta' => (string) number_format($pago->mora, 2),
                ]
            ]


        ]
    ];

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}

/**
 * Genera el contenido JSON de una boleta GRAVADA a partir de una VENTA
 * @param type $id 
 * @param type $COLEGIO 
 * @return type
 */
function getBoletaGravadaFromVenta($id, $COLEGIO){
    $boleta = Boleta::find($id);
    $prefijo = $boleta->sede->prefijo_boleta;
    $serie = $prefijo == 'B' ? $prefijo.$boleta->getCurrentSerie() : $prefijo.$boleta->getCurrentSerie(2);

    $nombre = $COLEGIO->ruc.'-03-'.$serie.'-'.$boleta->getCurrentNumero().'.json';

    $json = [
        'boleta' => [
            'IDE' => [
                'numeracion' => $serie.'-'.$boleta->getCurrentNumero(),
                'fechaEmision' => $boleta->fecha,
                'codTipoDocumento' => '03',
                'tipoMoneda' => 'PEN',
                'fechaVencimiento' => $boleta->fecha
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $boleta->sede->direccion,
                'codigoAsigSUNAT' => $boleta->sede->codigo_sunat
            ],
            'REC' => [
                'tipoDocId' => $boleta->getTipoDocumentoFacturacion(),
                'numeroDocId' => $boleta->dni,
                'razonSocial' => $boleta->nombre
            ],
            'CAB' => [
                'gravadas' => [
                    'codigo' => '1001',
                    'totalVentas' => (string) number_format($boleta->getMontoGravado(), 2)
                ],
                'totalImpuestos' => [
                    [
                        'idImpuesto' => '1000',
                        'montoImpuesto' => (string) number_format($boleta->getIGV(), 2)
                    ]
                ],
                'importeTotal' => (string) number_format(round($boleta->getMontoTotal(), 2), 2),
                'tipoOperacion' => '0101',
                'leyenda' => [
                    [
                        'codigo' => '1000',
                        'descripcion' => $boleta->getLetras()      
                    ]
                ],
                'montoTotalImpuestos' => (string) number_format($boleta->getIGV(), 2)
            ],
            'DET' => [
                
                
                
            ]


        ]
    ];

    $detalles = [];

    foreach($boleta->detalles As $key => $detalle){
        $detalle = [
            'numeroItem' => str_pad($key + 1, 3, 0, STR_PAD_LEFT),
            'descripcionProducto' => $detalle->concepto->descripcion,
            'cantidadItems' => number_format($detalle->cantidad, 2),
            'unidad' => 'NIU',
            'valorUnitario' => (string) number_format($detalle->getPrecioGravado(), 2),
            'precioVentaUnitario' => (string) number_format(round($detalle->getPrecio(), 2), 2),
            'totalImpuestos' => [
                [
                    'idImpuesto' => '1000',
                    'montoImpuesto' => (string) number_format($detalle->getIGV(), 2),
                    'tipoAfectacion' => '10',
                    'montoBase' => (string) number_format($detalle->getImporteGravado(), 2), //$detalle->getPrecioGravado()
                    'porcentaje' => '18.00'
                ]
            ],
            'valorVenta' => (string) number_format($detalle->getImporteGravado(), 2),
            'montoTotalImpuestos' => (string) number_format($detalle->getIGV(), 2)
        ];
        $detalles[] = $detalle;
    }

    $json['boleta']['DET'] = $detalles;

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}

/**
 * Genera el contenido JSON de una boleta GRATUITA a partir de una VENTA
 * @param type $id 
 * @param type $COLEGIO 
 * @return type
 */
function getBoletaGratuitaFromVenta($id, $COLEGIO){
    $boleta = Boleta::find($id);
    $prefijo = $boleta->sede->prefijo_boleta;
    $serie = $prefijo == 'B' ? $prefijo.$boleta->getCurrentSerie() : $prefijo.$boleta->getCurrentSerie(2);

    $nombre = $COLEGIO->ruc.'-03-'.$serie.'-'.$boleta->getCurrentNumero().'.json';

    $json = [
        'boleta' => [
            'IDE' => [
                'numeracion' => $serie.'-'.$boleta->getCurrentNumero(),
                'fechaEmision' => $boleta->fecha,
                'codTipoDocumento' => '03',
                'tipoMoneda' => 'PEN',
                'fechaVencimiento' => $boleta->fecha
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $boleta->sede->direccion,
                'codigoAsigSUNAT' => $boleta->sede->codigo_sunat
            ],
            'REC' => [
                'tipoDocId' => $boleta->getTipoDocumentoFacturacion(),
                'numeroDocId' => trim($boleta->dni),
                'razonSocial' => $boleta->nombre
            ],
            'CAB' => [
                'gratuitas' => [
                    'codigo' => '1004',
                    'totalVentas' => (string) number_format(round($boleta->getMontoTotalDetalles(), 2), 2)
                ],
                'totalImpuestos' => [
                    [
                        'idImpuesto' => '9996',
                        'montoImpuesto' => "0.00"
                    ]
                ],
                'importeTotal' => "0.00",
                'tipoOperacion' => '0101',
                'leyenda' => [
                    [
                        'codigo' => '1000',
                        'descripcion' => "CERO CON 00/100"
                    ],
                    [
                        'codigo' => '1002',
                        'descripcion' => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE"
                    ]
                ],
                'montoTotalImpuestos' => '0.00'
            ],
            'DET' => [
                
                
                
            ]


        ]
    ];

    $detalles = [];

    foreach($boleta->detalles As $key => $detalle){
        $detalle = [
            'numeroItem' => str_pad($key + 1, 3, 0, STR_PAD_LEFT),
            'descripcionProducto' => $detalle->concepto->descripcion,
            'cantidadItems' => number_format($detalle->cantidad, 2),
            'unidad' => 'NIU',
            'valorUnitario' => "0.00",
            'precioVentaUnitario' => "0.00",
            'totalImpuestos' => [
                [
                    'idImpuesto' => '9996',
                    'montoImpuesto' => "0.00",
                    'tipoAfectacion' => '31',
                    "montoBase" => (string) number_format(round($detalle->precio, 2), 2),
                    'porcentaje' => '0.00'
                ]
            ],
            'valorVenta' => "0.00",
            "valorRefOpOnerosas" => (string) number_format(round($detalle->precio, 2), 2),
            'montoTotalImpuestos' => '0.00'
        ];
        $detalles[] = $detalle;
    }

    $json['boleta']['DET'] = $detalles;

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}

/**
 * Genera el contenido JSON de una boleta INAFECTA a partir de una VENTA
 * @param type $id 
 * @param type $COLEGIO 
 * @return type
 */
function getBoletaInafectaFromVenta($id, $COLEGIO){
    $boleta = Boleta::find($id);

    $prefijo = $boleta->sede->prefijo_boleta;
    $serie = $prefijo == 'B' ? $prefijo.$boleta->getCurrentSerie() : $prefijo.$boleta->getCurrentSerie(2);

    $nombre = $COLEGIO->ruc.'-03-'.$serie.'-'.$boleta->getCurrentNumero().'.json';

    $json = [
        'boleta' => [
            'IDE' => [
                'numeracion' => $serie.'-'.$boleta->getCurrentNumero(),
                'fechaEmision' => $boleta->fecha,
                'codTipoDocumento' => '03',
                'tipoMoneda' => 'PEN',
                'fechaVencimiento' => $boleta->fecha
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $boleta->sede->direccion,
                'codigoAsigSUNAT' => $boleta->sede->codigo_sunat
            ],
            'REC' => [
                'tipoDocId' => $boleta->getTipoDocumentoFacturacion(),
                'numeroDocId' => trim($boleta->dni),
                'razonSocial' => $boleta->nombre
            ],
            'CAB' => [
                'inafectas' => [
                    'codigo' => '1002',
                    'totalVentas' => (string) number_format($boleta->getMontoTotalDetalles(), 2)
                ],
                'totalImpuestos' => [
                    [
                        'idImpuesto' => '9998',
                        'montoImpuesto' => '0.00'
                    ]
                ],
                'importeTotal' => (string) number_format(round($boleta->getMontoTotalDetalles(), 2), 2),
                'tipoOperacion' => '0101',
                'leyenda' => [
                    [
                        'codigo' => '1000',
                        'descripcion' => $boleta->getLetras()      
                    ]
                ],
                'montoTotalImpuestos' => '0.00'
            ],
            'DET' => [
                
                
                
            ]


        ]
    ];

    $detalles = [];

    foreach($boleta->detalles As $key => $detalle){
        $detalle = [
            'numeroItem' => str_pad($key + 1, 3, 0, STR_PAD_LEFT),
            'descripcionProducto' => $detalle->concepto->descripcion,
            'cantidadItems' => number_format($detalle->cantidad, 2),
            'unidad' => 'NIU',
            'valorUnitario' => (string) number_format($detalle->precio, 2),
            'precioVentaUnitario' => (string) number_format(round($detalle->precio, 2), 2),
            'totalImpuestos' => [
                [
                    'idImpuesto' => '9998',
                    'montoImpuesto' => "0.00",
                    'tipoAfectacion' => '30',
                    'montoBase' => (string) number_format($detalle->precio, 2),
                    'porcentaje' => '0.00'
                ]
            ],
            'valorVenta' => (string) number_format($detalle->getImporte(), 2),
            'montoTotalImpuestos' => '0.00'
        ];
        $detalles[] = $detalle;
    }

    $json['boleta']['DET'] = $detalles;

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}

function getRCInafectaFromVenta($id, $COLEGIO){

    $boleta = Boleta::find($id);
    $boleta->updateRC();

    $prefijo = $boleta->sede->prefijo_boleta;
    $serie = $prefijo == 'B' ? $prefijo.$boleta->getCurrentSerie() : $prefijo.$boleta->getCurrentSerie(2);

    //$nombre = $COLEGIO->ruc.'-03-B'.$boleta->getCurrentSerie().'-'.$boleta->getCurrentNumero().'.json';

    $nombre = $COLEGIO->ruc.'-RC-'.str_replace('-', '', $boleta->fecha_anulado).'-'.$boleta->numero_anulado.'.json';

    $json = [
        'resumenComprobantes' => [
            'IDE' => [
                'numeracion' => 'RC-'.str_replace('-', '', $boleta->fecha_anulado).'-'.$boleta->numero_anulado,
                'fechaEmision' => $boleta->fecha_anulado,
                'fechaReferencia' => $boleta->fecha
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $boleta->sede->direccion
            ],
            
            'DET' => [
                [
                    'numeroItem' => '1',
                    'monedaItem' => 'PEN',
                    'numeracionItem' => $serie.'-'.$boleta->getCurrentNumero(),
                    'tipoComprobanteItem' => '03',
                    'numeroDocIdAdq' => $boleta->dni,
                    'tipoDocIdAdq' => '1',
                    'estadoItem' => '3',
                    'importeTotal' => (string) number_format(round($boleta->getMontoTotalDetalles(), 2), 2),
                    'inafectas' => [
                        'codigo' => '03',
                        'totalVentas' => (string) number_format(round($boleta->getMontoTotalDetalles(), 2), 2)
                    ],
                    'totalImpuestos' => [
                        [
                            'idImpuesto' => '1000',
                            'montoImpuesto' => '0.00',
                        ]
                    ],
                ]
            ]
        ]
    ];

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}

function getRCGravadaFromVenta($id, $COLEGIO){

    $boleta = Boleta::find($id);
    $boleta->updateRC();

    //$nombre = $COLEGIO->ruc.'-03-B'.$boleta->getCurrentSerie().'-'.$boleta->getCurrentNumero().'.json';
    $prefijo = $boleta->sede->prefijo_boleta;
    $serie = $prefijo == 'B' ? $prefijo.$boleta->getCurrentSerie() : $prefijo.$boleta->getCurrentSerie(2);

    $nombre = $COLEGIO->ruc.'-RC-'.str_replace('-', '', $boleta->fecha_anulado).'-'.$boleta->numero_anulado.'.json';

    $json = [
        'resumenComprobantes' => [
            'IDE' => [
                'numeracion' => 'RC-'.str_replace('-', '', $boleta->fecha_anulado).'-'.$boleta->numero_anulado,
                'fechaEmision' => $boleta->fecha_anulado,
                'fechaReferencia' => $boleta->fecha
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $boleta->sede->direccion
            ],
            
            'DET' => [
                [
                    'numeroItem' => '1',
                    'monedaItem' => 'PEN',
                    'numeracionItem' => $serie.'-'.$boleta->getCurrentNumero(),
                    'tipoComprobanteItem' => '03',
                    'numeroDocIdAdq' => $boleta->dni,
                    'tipoDocIdAdq' => '1',
                    'estadoItem' => '3',
                    'importeTotal' => (string) number_format(round($boleta->getMontoTotalDetalles(), 2), 2),
                    'gravadas' => [
                        'codigo' => '01',
                        'totalVentas' => (string) number_format($boleta->getMontoGravado(), 2)
                    ],
                    'totalImpuestos' => [
                        [
                            'idImpuesto' => '1000',
                            'montoImpuesto' => (string) number_format($boleta->getIGV(), 2),
                        ]
                    ],
                ]
            ]
        ]
    ];

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}

/**
 * Genera el RC de un pago inafecto para una boleta.
 * @param type $id 
 * @param type $COLEGIO 
 * @return type
 */
function getRCInafectaFromImpresionId($id, $COLEGIO){
    $impresion = Impresion::find($id);
    $pago = $impresion->pago;
    $matricula = $pago->matricula;
    $alumno = $matricula->alumno;

    $impresion->updateRC();

    $prefijo = $matricula->grupo->sede->prefijo_boleta;
    $serie = $prefijo == 'B' ? $prefijo.$impresion->getSerie() : $prefijo.$impresion->getSerie(2);

    $nombre = $COLEGIO->ruc.'-RC-'.str_replace('-', '', $impresion->fecha_anulado).'-'.$impresion->numero_anulado.'.json';

    //$rc = $impresion->getRC();
    

    $json = [
        'resumenComprobantes' => [
            'IDE' => [
                'numeracion' => 'RC-'.str_replace('-', '', $impresion->fecha_anulado).'-'.$impresion->numero_anulado,
                'fechaEmision' => $impresion->fecha_anulado,
                'fechaReferencia' => $impresion->fecha_impresion
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $matricula->grupo->sede->direccion
            ],
            
            'DET' => [
                [
                    'numeroItem' => '1',
                    'monedaItem' => 'PEN',
                    'numeracionItem' => $serie.'-'.$impresion->getNumero(),
                    'tipoComprobanteItem' => '03',
                    'numeroDocIdAdq' => $alumno->nro_documento,
                    'tipoDocIdAdq' => '1',
                    'estadoItem' => '3',
                    'importeTotal' => (string) number_format($pago->monto, 2),
                    'inafectas' => [
                        'codigo' => '03',
                        'totalVentas' => (string) number_format($pago->monto, 2)
                    ],
                    'totalImpuestos' => [
                        [
                            'idImpuesto' => '1000',
                            'montoImpuesto' => "0.00",
                        ]
                    ],
                ]
            ]
        ]
    ];

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}

/**
 * Genera el RC de una nota inafecto para una boleta.
 * @param type $id 
 * @param type $COLEGIO 
 * @return type
 */
function getRCNotaFromImpresionId($id, $COLEGIO){
    $_impresion = Impresion::find($id);

    $pago = $_impresion->pago;
    $matricula = $pago->matricula;
    $alumno = $matricula->alumno;
    
    //$impresion = $pago->getLastImpresion();
    $impresionMora = $_impresion;
    //$pago->getActiveImpresionMora(false);

    //print_r($impresion->attributes());

    /*
    $impresion = Impresion::find($id);
    $pago = $impresion->pago;
    $matricula = $pago->matricula;
    $alumno = $matricula->alumno;
    */

    //$impresion->updateRC();
    $impresionMora->updateRC();


    $nombre = $COLEGIO->ruc.'-RC-'.str_replace('-', '', $impresionMora->fecha_anulado).'-'.$impresionMora->numero_anulado.'.json';

    //$rc = $impresion->getRC();
    

    $json = [
        'resumenComprobantes' => [
            'IDE' => [
                'numeracion' => 'RC-'.str_replace('-', '', $impresionMora->fecha_anulado).'-'.$impresionMora->numero_anulado,
                'fechaEmision' => $impresionMora->fecha_anulado,
                'fechaReferencia' => $impresionMora->fecha_impresion
            ],
            'EMI' => [
                'tipoDocId' => '6',
                'numeroDocId' => $COLEGIO->ruc,
                'razonSocial' => $COLEGIO->razon_social,
                'direccion' => $matricula->grupo->sede->direccion
            ],
            
            'DET' => [
                [
                    'numeroItem' => '1',
                    'monedaItem' => 'PEN',
                    'numeracionItem' => 'BND'.intval($impresionMora->getSerie()).'-'.$impresionMora->getNumero(),
                    'tipoComprobanteItem' => '08',
                    'numeroDocIdAdq' => $alumno->nro_documento,
                    'tipoDocIdAdq' => '1',
                    'estadoItem' => '3',
                    'importeTotal' => (string) number_format($pago->mora, 2),
                    'inafectas' => [
                        'codigo' => '03',
                        'totalVentas' => (string) number_format($pago->mora, 2)
                    ],
                    'totalImpuestos' => [
                        [
                            'idImpuesto' => '1000',
                            'montoImpuesto' => "0.00",
                        ]
                    ],
                ]
            ]
        ]
    ];

    return (object) [
        'nombre' => $nombre,
        'contenido' => $json
    ];
}

/**
 * Genera el archivo ZIP de las boletas
 * @return type
 */
function generarBoletasZip($impresiones, $COLEGIO){
    $zip = new ZipArchive();
    $archivo = getToken().'.zip';

    $res = $zip->open('./Static/Temp/'.$archivo, ZipArchive::CREATE);
    if ($res === TRUE) {
        foreach($impresiones As $impresion){
            $json = getBoletaFromImpresionId($impresion->id, $COLEGIO);
            //print_r($json);
            $zip->addFromString($json->nombre, json_encode($json->contenido, JSON_PRETTY_PRINT));
        }
        
        $zip->close();
    }

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$archivo);

    echo file_get_contents('./Static/Temp/'.$archivo);
    @unlink('./Static/Temp/'.$archivo);
}


/** */
function pdfMora($impresiones, $nd){
        $pdf = new TCPDF();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $pdf->setFont('Helvetica', '', $nd->tamano);
        $advance = 0;
        $startY = $pdf->getY();
        foreach($impresiones As $impresion){
            $pdf->addPage();
            $pdf->setXY($nd->nro_boleta_x, $advance + $nd->nro_boleta_y);
            $pdf->cell(0, 5, $impresion->getSerieNumero());

            $pdf->setXY($nd->fecha_x, $advance + $nd->fecha_y);
            $pdf->cell(0, 5, date('d-m-Y', strtotime($impresion->pago->fecha_cancelado)));
            $pdf->setXY($nd->fecha2_x, $advance + $nd->fecha2_y);
            $pdf->cell(0, 5, date('d-m-Y', strtotime($impresion->pago->fecha_cancelado)));
            $pdf->setXY($nd->fecha3_x, $advance + $nd->fecha3_y);
            $pdf->cell(0, 5, date('d-m-Y', strtotime($impresion->pago->fecha_cancelado)));
            $pdf->setXY($nd->nombre_x, $advance + $nd->nombre_y);
            $pdf->cell(0, 5, $impresion->pago->matricula->alumno->getFullName());

            $pdf->setXY($nd->dni_x, $advance + $nd->dni_y);
            $pdf->cell(0, 5, $impresion->pago->matricula->alumno->nro_documento);

            $boletaPago = $impresion->pago->getActiveImpresion(false);

            $pdf->setXY($nd->dni_x, $advance + $nd->dni_y);
            $pdf->cell(0, 5, $impresion->pago->matricula->alumno->nro_documento);

            $pdf->setXY($nd->serie_pago_x, $advance + $nd->serie_pago_y);
            $pdf->cell(0, 5, $boletaPago->getSerie());

            $pdf->setXY($nd->nro_pago_x, $advance + $nd->nro_pago_y);
            $pdf->cell(0, 5, $boletaPago->getNumero());

            $pdf->setXY($nd->fecha_pago_x, $advance + $nd->fecha_pago_y);
            $pdf->cell(0, 5, date('d-m-Y', strtotime($boletaPago->fecha_impresion)));

            $pdf->setXY($nd->descripcion_x, $advance + $nd->descripcion_y);
            $pdf->cell(0, 5, 'PENALIDAD POR ATRASO DE PAGO - BV '.$boletaPago->getSerie().'-'.$boletaPago->getNumero());

            $pdf->setXY($nd->precio_unitario_x, $advance + $nd->precio_unitario_y);
            $pdf->cell(0, 5, number_format($impresion->pago->mora, 2));

            $pdf->setXY($nd->importe_x, $advance + $nd->importe_y);
            $pdf->cell(0, 5, number_format($impresion->pago->mora, 2));

            $pdf->setXY($nd->subtotal_x, $advance + $nd->subtotal_y);
            $pdf->cell(0, 5, number_format($impresion->pago->mora, 2));

            $pdf->setXY($nd->igv_x, $advance + $nd->igv_y);
            $pdf->cell(0, 5, number_format(0, 2));

            $pdf->setXY($nd->total_x, $advance + $nd->total_y);
            $pdf->cell(0, 5, number_format($impresion->pago->mora, 2));

            $pdf->setXY($nd->documento_pago_x, $advance + $nd->documento_pago_y);
            $pdf->cell(0, 5, 'BOLETA DE VENTA');

            $pdf->setXY($nd->total_letras_x, $advance + $nd->total_letras_y);
            $pdf->cell(0, 5, $impresion->pago->getLetrasMora());

            $pdf->setXY($nd->nombre2_x, $advance + $nd->nombre2_y);
            $pdf->cell(0, 5, $impresion->pago->matricula->alumno->getFullName());

            $pdf->setXY($nd->dni2_x, $advance + $nd->dni2_y);
            $pdf->cell(0, 5, $impresion->pago->matricula->alumno->nro_documento);
        /*
            

                <div style="position: absolute; top: {{ advance + nd.dni2_y }}px; left: {{ nd.dni2_x }}px">{{ impresion.pago.matricula.alumno.nro_documento }}</div>
                */
            //$advance += $nd->alto + $nd->espaciado;
        } 

        $pdf->output();
}

function enviarEmailMatriculaApoderado($matricula_id){
    $fromName = Config::get("remitente_emails");

    /* $contenido = '<p>Estimado Padre de Familia muchas gracias por su matrícula, en 24 horas podrá acercarse a un Banco BCP, Agente BCP, o por la Banca Movil del BCP y podrá cancelar automáticamente su mátricula, solo debe ingresar el DNI del alumno como Codigo.</p>

    <p>Que tenga buen día.</p>
    <p>Atentamente</p>
    '.$fromName.'.'; */

    $contenido = Config::get("email_matricula_apoderado");

    $matricula = Matricula::find($matricula_id);
    $alumno = $matricula->alumno;
    $correos = [];
    foreach($alumno->getApoderados() As $apoderado){
        if($apoderado->email != ''){
            $correos[] = $apoderado->email;
        }   
    }

    sendEmail($correos, 'Nueva Matrícula Registrada', $contenido);
} 
 
function enviarEmailMatricula($matricula_id, $apoderado_id){
    $matricula = Matricula::find($matricula_id);
    $url = Config::get('current_url');
    //$apoderado = Apoderado::find($apoderado_id);

    $contenido = 'Se registró una nueva matrícula online.<br >
    <b>Nº de Documento:</b> '.$matricula->alumno->nro_documento.'<br>
    <b>Apellidos y Nombres:</b> '.$matricula->alumno->getFullName().'<br >
    <b>Nivel:</b> '.$matricula->grupo->nivel->nombre.'<br>
    <b>Grado:</b> '.$matricula->grupo->grado.'º<br>
    <p><b>Foto DNI: </b><br /><img src="'.$url.'/Static/Archivos/'.$matricula->alumno->foto_dni.'" style="max-width: 500px" /></p>
    <p><b>Firma: </b><br /><img src="'.$url.'/apoderados/firma_digital/'.sha1($apoderado_id).'" style="max-width: 500px" /></p>';
    
    $correos = Config::get('email_notificacion_matricula_online');
    $correos = str_replace(' ', '', $correos);
    $correos = explode(',', $correos);

    sendEmail($correos, 'Nueva Matrícula Registrada', $contenido);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function sendEmail($para, $asunto, $mensaje, $images = [], $archivos = [], $fromName = ''){
	require_once './Crystals/PHPMailer/src/Exception.php';
	require_once './Crystals/PHPMailer/src/PHPMailer.php';
	require_once './Crystals/PHPMailer/src/SMTP.php';

	$mail = new PHPMailer(true);
	
    $fromName = Config::get("remitente_emails");

	try {
	    //Server settings
        
	    $mail->SMTPDebug = 0;                                 // Enable verbose debug output
	    $mail->isSMTP();
	    $mail->Host = "smtp.zoho.com";
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'no-reply@zarkiel.com';                     //SMTP username
        $mail->Password   = 'Z530807575+lol';                               //SMTP password
        $mail->SMTPSecure = true;            //Enable implicit TLS encryption
        $mail->Port       = 465;

        $mail->setFrom('no-reply@zarkiel.com', $fromName); 

	    foreach($para As $p){
	    	$mail->addAddress($p);
	    }

	    $mail->CharSet = 'UTF-8';

	    //Content
	    $mail->isHTML(true);                                  // Set email format to HTML
	    $mail->Subject = $asunto;
	    foreach($images As $key => $image){
	    	$mail->AddEmbeddedImage($image, $key);
	    }

	    foreach($archivos As $archivo){
	    	if(is_array($archivo)){
	    		//echo $archivo[0];
	    		$mail->addStringAttachment($archivo[1], $archivo[0]);
	    	}else{
	    		$mail->AddAttachment($archivo);
	    	}
	    	
	    }


	    $mail->Body = $mensaje;

	    

	    $mail->AltBody = '';

	    return @$mail->send();

	} catch (Exception $e) {
	    return false;
	}
}

function globdir($filepath) {
    $dirs   = glob($filepath.'/*', GLOB_ONLYDIR);
    $files  = glob($filepath.'/*');
    $all    = array_unique(array_merge($dirs,$files));
    $filter = array($filepath.'/Thumbs.db');
  
    return array_diff($all,$filter);
  }

  
/** 
* Converts bytes into human readable file size. 
* 
* @param string $bytes 
* @return string human readable file size (2,87 Мб)
* @author Mogilev Arseny 
*/ 
function FileSizeConvert($bytes)
{
    $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "Bytes",
                "VALUE" => 1
            ),
        );

    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}

function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            return deleteDir($file);
        } else {
            unlink($file);
        }
    }
    return rmdir($dirPath);
}

function deleteAnyFile($path)
{
    if (is_dir($path) === true)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file)
        {
            if (in_array($file->getBasename(), array('.', '..')) !== true)
            {
                if ($file->isDir() === true)
                {
                    rmdir($file->getPathName());
                }

                else if (($file->isFile() === true) || ($file->isLink() === true))
                {
                    unlink($file->getPathname());
                }
            }
        }

        return rmdir($path);
    }

    else if ((is_file($path) === true) || (is_link($path) === true))
    {
        return unlink($path);
    }

    return false;
}