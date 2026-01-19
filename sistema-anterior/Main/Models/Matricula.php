<?php
class Matricula extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'matriculas';
	static $connection = 'main';
	static $puntajes = array();
	static $belongs_to = array(
		array(
			'alumno',
			'class_name' => 'Alumno',
		),
		array(
			'colegio',
			'class_name' => 'Colegio',
		),
		array(
			'grupo',
			'class_name' => 'Grupo',
		),
		array(
			'costo',
			'class_name' => 'Costo',
		),
		array(
			'personal',
			'class_name' => 'Personal',
		),
	);
	static $has_many = array();
	static $has_one = array();
	
	static $validates_presence_of = array(
		array(
			'grupo_id',
		),
		array(
			'estado',
		),
		array(
			'costo_id',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
	
	function getEstado(){
		return $this->ESTADOS_MATRICULA[$this->estado];
	}
	
	function getPagos(){
		$pagos = Pago::all(array(
			'conditions' => 'matricula_id="'.$this->id.'"',
			'order' => 'tipo ASC, nro_pago ASC'
		));
		return $pagos;
	}

	function getPagosAlumno(){
		$pagos = Pago::all(array(
			'conditions' => 'estado="ACTIVO" AND matricula_id="'.$this->id.'" AND estado_pago="CANCELADO"',
			'order' => 'tipo ASC, nro_pago ASC'
		));
		return $pagos;
	}

	function getPagosAlumnoHistorial(){
		$pagos = Pago::all(array(
			'conditions' => 'estado="ACTIVO" AND matricula_id="'.$this->id.'"',
			'order' => 'tipo ASC, nro_pago ASC'
		));
		return $pagos;
	}
	
	function getRecomendaciones(){
		$recomendaciones = !empty($this->recomendaciones) ? unserialize(base64_decode($this->recomendaciones)) : array();
		return $recomendaciones;
	}
	
	function getRecomendacion($ciclo){
		$recomendaciones = $this->getRecomendaciones();
		return (object) $recomendaciones[$ciclo];
	}
	
	function setRecomendacion($ciclo, $recomendacion){
		$recomendaciones = $this->getRecomendaciones();
		$recomendaciones[$ciclo] = $recomendacion;
		$this->recomendaciones = base64_encode(serialize($recomendaciones));
	}
	
	function getTotalPagar(){
        
        $pagos = $this->colegio->total_pensiones;
        $total = $this->costo->pension * $pagos;
        $total += $this->costo->matricula;
        return $total;
    }
    
    function getTotalPagado(){
        $total = Pago::find(array(
            'select' => 'SUM(monto) As total',
            'conditions' => 'matricula_id="'.$this->id.'" AND (tipo=0 OR tipo=1) AND estado="ACTIVO" AND estado_pago="CANCELADO"'
        ));
        return $total->total;
    }
    
	function getSaldo(){
		return $this->getTotalPagar() - $this->getTotalPagado();
	}
    
	function getOtrosPagos(){
		$total = Pago::find(array(
			'select' => 'SUM(monto) As total',
			'conditions' => 'estado="ACTIVO" AND matricula_id="'.$this->id.'" AND (tipo=2)'
		));
		return $total->total;
	}
    
    function getAsistencia($fecha){
		$fecha = $this->setFecha($fecha);
		$asistencia = Matricula_Asistencia::find(array(
			'conditions' => 'matricula_id="'.$this->id.'"  AND fecha="'.$fecha.'"'
		));
        return $asistencia;
     }
    
	function getAsistenciaAsignatura($asignatura_id, $fecha){
		$fecha = $this->setFecha($fecha);
		$asistencia = Asignatura_Asistencia::find(array(
			'conditions' => 'matricula_id="'.$this->id.'" AND asignatura_id="'.$asignatura_id.'" AND fecha="'.$fecha.'"'
		));
		return $asistencia;
	}
     
	function getNota($asignatura_id, $criterio_id, $ciclo){
		$nota = Nota::find(array(
			'conditions' => 'matricula_id="'.$this->id.'" AND criterio_id="'.$criterio_id.'" AND asignatura_id="'.$asignatura_id.'" AND ciclo="'.$ciclo.'"'
		));

		if(is_numeric($nota->nota) && $nota->nota == 0) return '';
		return $nota->nota;
	}
	
	

	function getNotaDetalle($asignatura_id, $ciclo, $criterio_id, $indicador_id, $indice){
        $nota = Nota_Detalle::find(array(
            'conditions' => 'matricula_id="'.$this->id.'"  AND asignatura_id="'.$asignatura_id.'" AND ciclo="'.$ciclo.'"'
        ));
        
        if(!$nota) return null;
        $data = unserialize($nota->data);
        return $data[$criterio_id][$indicador_id][$indice];
    }
    
    function getPromedio($asignatura_id, $ciclo){
        $promedio = Promedio::find(array(
            'conditions' => 'matricula_id="'.$this->id.'" AND asignatura_id="'.$asignatura_id.'" AND ciclo="'.$ciclo.'"'
        ));
        if(is_numeric($promedio->promedio)) return round($promedio->promedio, 0);
        return $promedio->promedio;
    }
    
    function getNotaExamenMensual($asignatura_id, $nro, $ciclo){
    	$nota = Nota_Examen_Mensual::find(array(
			'conditions' => 'matricula_id="'.$this->id.'" AND nro="'.$nro.'" AND asignatura_id="'.$asignatura_id.'" AND ciclo="'.$ciclo.'"'
		));
		if(is_numeric($nota->nota) && $nota->nota == 0) return '';
		return $nota->nota;
    }

    function getPromedioExamenMensual($asignatura, $ciclo, $reduced = false){

    	if(!$asignatura instanceOf Asignatura) $asignatura = Asignatura::find($asignatura);
    	$notas = Nota_Examen_Mensual::find_all_by_matricula_id_and_asignatura_id_and_ciclo($this->id, $asignatura->id, $ciclo);
    	//echo $this->id.' - '.$asignatura->id.' - '.$ciclo.'<br />';
    	if(count($notas) == 0) return ''; // no hay notas
    	
    	$total = array();
    	foreach($notas As $nota){
    		$total[] = $nota->nota;
    	}

    	$promedio = (count($total) > 0) ? round(array_sum($total) / count($total)) : 0;
    	if(!$reduced) return $promedio;

    	$peso = $asignatura->curso->peso_examen_mensual;
    	$promedio = $promedio * $peso / 100;
    	//echo $promedio;
    	return $promedio;
    }

    function updatePromedioFromCriterios($asignatura, $ciclo){
		if(!$asignatura instanceOf Asignatura) $asignatura = Asignatura::find($asignatura);
		
		$notas_criterios = Nota::all(array(
			'conditions' => 'matricula_id="'.$this->id.'" AND asignatura_id="'.$asignatura->id.'" AND ciclo="'.$ciclo.'"'
		));
		
		$promedio = array();
		foreach($notas_criterios As $nota){
			$nota = $asignatura->grupo->nivel->calificacionPorcentual() ? ($nota->nota * $nota->criterio->peso / 100) : $nota->nota;
			$promedio[] = $nota;
		}
		
		//print_r($promedio);

		$promedio = array_filter($promedio);
		
		if($asignatura->grupo->nivel->calificacionPorcentual()){
			$promedio = array_sum($promedio);
		}else{
			$promedio = count($promedio) > 0 ? round(array_sum($promedio) / count($promedio)) : null; 
		}
		
		// Añade el examen mensual
		if($asignatura->curso->examenMensual()){
			$promedioExamenMensual = $this->getPromedioExamenMensual($asignatura, $ciclo, true);

			$promedio += $promedioExamenMensual;
		}

		Promedio::table()->delete(array(
			'matricula_id' => $this->id,
			'asignatura_id' => $asignatura->id,
			'ciclo' => $ciclo,
		));

		
		if(isset($promedio) && count($notas_criterios) > 0){
			Promedio::create(array(
				'matricula_id' => $this->id,
				'asignatura_id' => $asignatura->id,
				'ciclo' => $ciclo,
				'promedio' => round($promedio)
			));
		}
	}
	
	
	function getAsignaturasByArea($area_id){
        $asignaturas = Asignatura::all(array(
            'conditions' => 'asignaturas.colegio_id="'.$this->colegio_id.'" AND asignaturas.grupo_id="'.$this->grupo_id.'" AND areas_cursos.area_id="'.$area_id.'"',
            'joins' => 'INNER JOIN areas_cursos ON areas_cursos.curso_id = asignaturas.curso_id',
           
        ));
        return $asignaturas;
    }
    
    function getAsignaturas($options = null){
        $conditions = array(
            'conditions' => array('asignaturas.colegio_id="'.$this->colegio_id.'" AND asignaturas.grupo_id="'.$this->grupo_id.'"'),
            'joins' => array('curso'),
			'order' => 'cursos.orden ASC'
        );
        
        if(isset($options)){
            $conditions = array_merge($conditions, $options);
        }
        
        $asignaturas = Asignatura::all($conditions);
        return $asignaturas;
    }
    
	function getMesTardanzasInjustificadas($mes){
        $total = Matricula_Asistencia::find(array(
            'select' => 'COUNT(*) As total',
            'conditions' => 'matricula_id = "'.$this->id.'" AND MONTH(fecha) = "'.($mes).'" AND YEAR(fecha) = "'.$this->grupo->anio.'" AND tipo = "TARDANZA_INJUSTIFICADA"'
        ));
    
        return $total->total;
    }
    
    function getMesFaltas($mes, $tipo){
        $total = Matricula_Asistencia::find(array(
            'select' => 'COUNT(*) As total',
            'conditions' => 'matricula_id = "'.$this->id.'" AND MONTH(fecha) = "'.($mes).'" AND YEAR(fecha) = "'.$this->grupo->anio.'" AND tipo = "FALTA_'.$tipo.'"'
        ));
        return $total->total;
    }

    function getRangeAsistencia($from, $to, $tipo){
    	$from = date('Y-m-d', strtotime($from.'-'.$this->grupo->anio));
    	$to = date('Y-m-d', strtotime($to.'-'.$this->grupo->anio));

        $total = Matricula_Asistencia::find(array(
            'select' => 'COUNT(*) As total',
            'conditions' => 'matricula_id = "'.$this->id.'" AND tipo = "'.$tipo.'" AND fecha BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
        ));
        return $total->total;
        //return $from.'-'.$to.' - '.$total->total;
    }
    
    public $ALLOWED = array('AD','A', 'B', 'C');
    public $VALUES = array('AD' => 4, 'A' => 3, 'B' => 2, 'C' => 1);
    public $MAP = array(1 => 'C', 2 => 'B', 3 => 'A', 4 => 'AD');
    
    
    function getPromedioArea($area_id, $ciclo){
        $asignaturas = $this->getAsignaturasByArea($area_id);
        $total = array();
        foreach($asignaturas As $asignatura){
            $promedio = $this->getPromedio($asignatura->id, $ciclo);
            if($promedio){
                if($this->grupo->nivel->calificacionCuantitativa()){
                    $total[] = $promedio;
                }else{
                    $total[] = $this->VALUES[$promedio];
                }
            }
        }
        
        if(count($asignaturas) == 0 || count($total) == 0) return null;
        
        if($this->grupo->nivel->calificacionCuantitativa()){

            //return round(array_sum($total) / count($asignaturas)); // todas las asignaturas
            return round(array_sum($total) / count($total));
        }else{
            return $this->MAP[round(array_sum($total) / count($asignaturas))];
        }
    }
    
    function getPromedioFinalArea($area_id){
        $total = array();
        $ciclos = $this->colegio->total_notas;
        
        for($i=1;$i<=$ciclos;$i++){
            $promedio = $this->getPromedioArea($area_id, $i);
            if($promedio){
                if($this->grupo->nivel->calificacionCuantitativa()){
                    $total[] = $promedio;
                }else{
                    $total[] = $this->VALUES[$promedio];
                }
            }
        }

        if(count($total) == 0 || array_sum($total) == 0) return null;
 
        if($this->grupo->nivel->calificacionCuantitativa()){
            return round(array_sum($total) / count($total));
        }else{
            return $this->MAP[round(array_sum($total) / count($total))];
        }
    }
    
    function getPromedioFinalAsignatura($asignatura_id){
        $total = array();
        $ciclos = $this->colegio->total_notas;
        
        for($i=1;$i<=$ciclos;$i++){
            $promedio = $this->getPromedio($asignatura_id, $i);
            if($promedio){
                if($this->grupo->nivel->calificacionCuantitativa()){
                    $total[] = $promedio;
                }else{
                    $total[] = $this->VALUES[$promedio];
                }
			}
        }
        
        if(count($total) == 0 || array_sum($total) == 0) return null;
        if($this->grupo->nivel->calificacionCuantitativa()){
            return round(array_sum($total) / count($total));
        }else{
            return $this->MAP[round(array_sum($total) / count($total))];
        }
    }
    
    function getTrabajosEncargados(){
		$trabajos = Trabajo::all(array(
			'conditions' => 'grupo_id="'.$this->grupo_id.'"',
			'order' => 'fecha_hora DESC'
		));
		return $trabajos;
	}

	function getExamenesCompartidos(){
		$compartidos = Examen_Compartido::all(array(
			'conditions' => 'grupo_id="'.$this->grupo_id.'"',
			'order' => 'id DESC'
		));
		return $compartidos;
	}

	function getExamenesBloquesCompartidos($archivado = 'NO'){
		$compartidos = Examen_Bloque_Compartido::all(array( 
			'conditions' => 'grupo_id="'.$this->grupo_id.'" AND examenes_bloques.estado = "ACTIVO" AND examenes_bloques.archivado = "'.$archivado.'"',
			'joins' => 'INNER JOIN examenes_bloques ON examenes_bloques_compartidos.examen_id = examenes_bloques.id',
			//'joins' => 'INNER JOIN asignaturas ON asignaturas.id = examenes_bloques_compartidos.asignatura_id',
			'order' => 'id DESC'
		));
		return $compartidos;
	}

	function getPruebaActiva($compartido){
		$prueba = Examen_Prueba::find(array(
			'conditions' => 'compartido_id="'.$compartido->id.'" AND matricula_id="'.$this->id.'" AND estado="ACTIVO"'
		));

		if($prueba) $prueba->checkFinished();
		
		return $prueba;
	}

	function canDoTest($compartido){
		$pruebas = Examen_Prueba::count(array(
			'conditions' => 'compartido_id="'.$compartido->id.'" AND matricula_id="'.$this->id.'"'
		));

		if(time() > strtotime($compartido->expiracion)) return false;
		return $pruebas < $compartido->intentos;
	}

	function getBestTest($compartido){
		$prueba = Examen_Prueba::find(array(
			'conditions' => 'compartido_id="'.$compartido->id.'" AND matricula_id="'.$this->id.'"',
			'order' => 'puntaje DESC',
			'limit' => 1
		));
		//echo '1111111111';
		return $prueba;
	}

	function getPuntajeCiclo($ciclo){
		$asignaturas = $this->getAsignaturas();
        $total = array();
        foreach($asignaturas As $asignatura){
        	$promedio = $this->getPromedio($asignatura->id, $ciclo);
        	if($promedio){
                if($this->grupo->nivel->calificacionCuantitativa()){
                    $total[] = $promedio;
                }else{
                    $total[] = $this->VALUES[$promedio];
                }
			}
			//$total[] = $this->getPromedio($asignatura->id, $ciclo);
			//.'('.$asignatura->id.')';
		}
		
		return array_sum($total);
	}

	function getTotalCursosNotas($ciclo){
		$asignaturas = $this->getAsignaturas();
		$total = 0;
		foreach($asignaturas As $asignatura){
        	$promedio = $this->getPromedio($asignatura->id, $ciclo);
        	if($promedio){
        		++$total;
        	}
        }
        return $total;
	}

	function getPromedioCiclo($ciclo, $total_cursos = null){
		if(!isset($total_cursos)){
			$total_cursos = $this->getTotalCursosNotas($ciclo);
		}
		
		if($total_cursos == 0) return '-';
		$puntaje = $this->getPuntajeCiclo($ciclo);
		//return  / $total_cursos;

		if($this->grupo->nivel->calificacionCuantitativa()){
            return round($puntaje / $total_cursos, 4);
        }else{
            return $this->MAP[round($puntaje / $total_cursos)];
        }
	}

	function getOrdenMeritoCiclo($ciclo){
        if(!isset(self::$puntajes['ciclo'][$ciclo]) || empty(self::$puntajes['ciclo'][$ciclo])){
			$matriculas = $this->grupo->getMatriculas();
			$puntajes = array();
			foreach($matriculas As $matricula){

				$puntajes[$matricula->id] = $matricula->getPuntajeCiclo($ciclo);
			}
			
			self::$puntajes['ciclo'][$ciclo] = $puntajes;
		}
		
		$puntajes = self::$puntajes['ciclo'][$ciclo];
		
		asort($puntajes, SORT_NUMERIC);
		return $this->getRank($puntajes, $this->id);
    }
    
    function getRank($puntajes, $matricula_id){
		$rank = array();
        $i = 1;
        $ids = array_reverse(array_keys($puntajes));
        foreach($ids As $key => $id){
            $puntaje = $puntajes[$id];
            
            if($puntaje == $current_puntaje){
                $rank[$id] = $current_rank;
            }else{
                $rank[$id] = $i;
                $i++;
            }
            
            $current_puntaje = $puntaje;
            $current_rank = $rank[$id];
        }
        /*
        echo '<pre>';
        print_r($puntajes);
        echo '</pre>';
        */
        return $rank[$matricula_id];
	}

	function getSanciones(){
		$sanciones = Matricula_Sancion::find_all_by_matricula_id($this->id);
		return $sanciones;
	}

	function getPromedioConducta($ciclo){
		$meritos = Matricula_Sancion::find(array(
			'select' => 'SUM(sancion) As total',
			'conditions' => 'matricula_id="'.$this->id.'" AND ciclo="'.$ciclo.'" AND tipo="MERITO"'
		));

		$demeritos = Matricula_Sancion::find(array(
			'select' => 'SUM(sancion) As total',
			'conditions' => 'matricula_id="'.$this->id.'" AND ciclo="'.$ciclo.'" AND tipo="DEMERITO"'
		));
		
		$nota =  (20 - $demeritos->total) + $meritos->total;
		if($nota > 20) return 20;
		if($nota < 0) return 0;
		return $nota;
	}

	function hasPago($tipo, $nro){
		$pago = Pago::find(array(
			'conditions' => 'estado="ACTIVO" AND tipo="'.$tipo.'" AND nro_pago="'.$nro.'" AND matricula_id="'.$this->id.'"'
		));
		if($pago) return true;
		return false;
	}

	function hasPagoCancelado($tipo, $nro){
		$pago = Pago::find(array(
			'conditions' => 'estado_pago = "CANCELADO" AND estado="ACTIVO" AND tipo="'.$tipo.'" AND nro_pago="'.$nro.'" AND matricula_id="'.$this->id.'"'
		));
		if($pago) return true;
		return false;
	}

	function getDeudas(){
		$currentMonth = intval(date('m'));
		$nroPago = $currentMonth - 2;
		$deudas = array();
		if($this->costo->pension > 0){
			for($i=1; $i <= $nroPago; ++$i){
				$vencimiento = $this->colegio->getVencimientoPension($i);
				$xvencimiento = explode('-', $vencimiento);
				if(count($xvencimiento) == 1){
					$fechaVencimiento = strtotime($vencimiento.'-'.($i + 2).'-'.$this->grupo->anio);
				}elseif(count($xvencimiento) == 2){
					$fechaVencimiento = strtotime($vencimiento.'-'.$this->grupo->anio);
				}elseif(count($xvencimiento) == 3){
					$fechaVencimiento = strtotime($vencimiento);
				}
				

				$tolerancia = 0;
				if($this->colegio->dias_tolerancia > 0){
					$tolerancia = 60*60*24*$this->colegio->dias_tolerancia;
				}
				if(!$this->hasPagoCancelado(1, $i) && strtotime(date('Y-m-d')) > ($fechaVencimiento + $tolerancia)){
					$deudas[] = 'Pensión '.$this->colegio->getCicloPensionesSingle($i).' - Vencimiento: '.date('d-m-Y', $fechaVencimiento);
				}
			}

		}

		//return true;
		return $deudas;
	}



	// EXAMENES BLOQUES
	function getPruebaActivaBloque($compartido){
		$prueba = Examen_Bloque_Prueba::find(array(
			'conditions' => 'compartido_id="'.$compartido->id.'" AND matricula_id="'.$this->id.'" AND estado="ACTIVO"'
		));

		if($prueba) $prueba->checkFinished();
		
		return $prueba;
	}

	function canDoTestBloque($compartido){
		$pruebas = Examen_Bloque_Prueba::count(array(
			'conditions' => 'compartido_id="'.$compartido->id.'" AND matricula_id="'.$this->id.'"'
		));

		if(time() > strtotime($compartido->expiracion)) return false;
		return $pruebas < $compartido->intentos;
	}

	function getBestTestBloque($compartido){
		$prueba = Examen_Bloque_Prueba::find(array(
			'conditions' => 'compartido_id="'.$compartido->id.'" AND matricula_id="'.$this->id.'"',
			'order' => 'id DESC',
			'limit' => 1
		));
		return $prueba;
	}

	// CONCURSOS
	function getPruebaActivaConcurso($concurso){
		$prueba = Concurso_Prueba::find(array(
			'conditions' => 'concurso_id="'.$concurso->id.'" AND matricula_id="'.$this->id.'" AND estado="ACTIVO"'
		));

		if($prueba) $prueba->checkFinished();
		
		return $prueba;
	}

	function canDoTestConcurso($concurso){
		$pruebas = Concurso_Prueba::count(array(
			'conditions' => 'concurso_id="'.$concurso->id.'" AND matricula_id="'.$this->id.'"'
		));

		if(time() > strtotime($concurso->expiracion)) return false;
		return $pruebas == 0;
	}

	function getBestTestConcurso($concurso){
		$prueba = Concurso_Prueba::find(array(
			'conditions' => 'concurso_id="'.$concurso->id.'" AND matricula_id="'.$this->id.'"',
			'order' => 'id DESC',
			'limit' => 1
		));
		//echo '1111111111';
		return $prueba;
		/*
		$best = null;
		foreach($pruebas As $prueba){
			$respuestas = $prueba->getRespuestas();
			if(is_null($best)) $best = $prueba;
			if($respuestas[$prueba->])
		}

		return $best;
		*/
	}

	// TAREAS
	function getPruebaActivaTarea($tarea, $create = true){
		$prueba = Tarea_Prueba::find(array(
			'conditions' => 'tarea_id="'.$tarea->id.'" AND matricula_id="'.$this->id.'"'
		));

		if(!$prueba && $create){
			$prueba = Tarea_Prueba::create(array(
				'tarea_id' => $tarea->id,
				'matricula_id' => $this->id,
				
				'estado' => 'ACTIVO',
				'token' => getToken()
			));
		}
		return $prueba;
	}

	function getPagosPendientesVerificacion($fecha){

		$pagos = Pago::all(array(
			'conditions' => 'matricula_id="'.$this->id.'" AND (estado_pago = "PENDIENTE" OR (estado_pago = "CANCELADO" AND fecha_cancelado > DATE("'.$fecha.'")))',
			'order' => 'tipo ASC, nro_pago ASC'
		));
		return $pagos;
	}

	function isOculto(){
		return $this->ocultar == 'SI';
	}

	function isNuevo(){
		$lastMatricula = Matricula::find([
			'conditions' => 'matriculas.alumno_id = "'.$this->alumno_id.'" AND grupos.anio = "'.($this->grupo->anio - 1).'"',
			'joins' => 'inner join grupos on grupos.id = matriculas.grupo_id'
		]);

		return is_null($lastMatricula);
	}

	/** */
	/**
	 * Verifica si el alumno aún tiene una prueba sin finalizar
	 * @param type $examen 
	 * @return type
	 */
	function getPruebaActivaAula($examen){
        $prueba = Asignatura_Examen_Prueba::find(array(
            'conditions' => 'examen_id="'.$examen->id.'" AND matricula_id="'.$this->id.'" AND estado="ACTIVO"'
        ));

        if($prueba) $prueba->checkFinished();
        
        return $prueba;
    }

    /**
     * Verifica si el alumno puede dar el examen
     * @param type $examen 
     * @return type
     */
    function canDoTestAula($examen){

        $pruebas = Asignatura_Examen_Prueba::count(array(
            'conditions' => 'examen_id="'.$examen->id.'" AND matricula_id="'.$this->id.'"'
        ));

        if($examen->estado == 'INACTIVO') return false;
       /*  $now = time();
        if($now < strtotime($examen->fecha_desde.' '.$examen->hora_desde) || $now > strtotime($examen->fecha_hasta.' '.$examen->hora_hasta)) return false; */

        return $pruebas < $examen->intentos;
    }

    /**
     * Obtiene el mejor examen que haya dado el alumno
     * @param type $examen 
     * @return type
     */
    function getBestTestAula($examen){
        $prueba = Asignatura_Examen_Prueba::find(array(
            'conditions' => 'examen_id="'.$examen->id.'" AND matricula_id="'.$this->id.'"',
            'order' => 'puntaje DESC',
            'limit' => 1
        ));
        //echo '1111111111';
        return $prueba;
    }


    function getStarsAmount(){
        /*
        $stars = EnrollmentIncident::find([
            'select' => 'SUM(points) AS total',
            'joins' => 'inner join matriculas on matriculas.id = enrollment_incidents.enrollment_id',
            'conditions' => ['matriculas.alumno_id = ?', $this->alumno_id]
        ]);
        return $stars->total | 0;
        */
        return $this->alumno->getStarsAmount();
    }
}
