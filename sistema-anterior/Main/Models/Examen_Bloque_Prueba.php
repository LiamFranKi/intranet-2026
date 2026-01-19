<?php
class Examen_Bloque_Prueba extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'examenes_bloques_pruebas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'compartido',
			'class_name' => 'Examen_Bloque_Compartido',
			'foreign_key' => 'compartido_id',
		),
		array(
			'matricula',
			'class_name' => 'Matricula',
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


	function activa(){
		return $this->estado == 'ACTIVO';
	}
	
	function getPreguntas($curso_id){

		$preguntas = !empty($this->preguntas) ? unserialize(base64_decode($this->preguntas)) : array();

		if(!isset($preguntas[$curso_id]) || count($preguntas[$curso_id]) <= 0){

			$preguntas = $this->buildPreguntas($curso_id);
		}

		return $preguntas[$curso_id];
	}

	function buildPreguntas($curso_id){
		$examen = $this->compartido->examen;
		$preguntas_examen = $examen->getPreguntas($curso_id, true);
		$preguntas_prueba = !empty($this->preguntas) ? unserialize(base64_decode($this->preguntas)) : array();
		shuffle($preguntas_examen);

		//$preguntas_prueba[$curso_id] = array_slice($preguntas_examen, $examen->preguntas_max);
		$preguntas_prueba[$curso_id] = array();
		for($i=0; $i < $examen->preguntas_max; ++$i){
			if(count($preguntas_examen) > $i){
				$preguntas_prueba[$curso_id][] = $preguntas_examen[$i];
			}
		}

		$this->preguntas = base64_encode(serialize($preguntas_prueba));
		$this->save();
		return $preguntas_prueba;
	}

	function respuestaCorrecta($pregunta){
		$correcta = false;
		$examen = $this->compartido->examen;
		$respuestas = $this->getRespuestas();
		foreach($pregunta->getAlternativas() As $alternativa){
			if($respuestas[$pregunta->id] == $alternativa->id && $alternativa->correcta()){
				$correcta = true;
				break;
			}
		}
		return $correcta;
	}

	function calificar(){
		$respuestas = $this->getRespuestas();
		$puntaje = 0;
		$examen = $this->compartido->examen;
		$cursos = $examen->getCursos();
		$compartido = $this->compartido;
		

		$resultados = array();
		foreach($cursos As $curso){

			$correctas = 0;
			$incorrectas = 0;
			$puntaje = 0;

			foreach($this->getPreguntas($curso->id) As $pregunta){
				if($this->respuestaCorrecta($pregunta)){
					$puntaje += $examen->puntos_correcta;
					++$correctas;
				}else{
					++$incorrectas;
				}
			}

			if($puntaje < 6) $puntaje = 6;
			if($puntaje > 20) $puntaje = 20;

			
			
			$resultados[$curso->id] = array(
				'puntaje' => $puntaje,
				'correctas' => $correctas,
				'incorrectas' => $incorrectas
			);
		}
		
		$this->resultados = serialize($resultados);
		$save = $this->save();
		$this->asignarNotasAsignatura();
		return $save;
	}

	function asignarNotasAsignatura(){
		$resultados = $this->getResultados();
		$compartido = $this->compartido;
		$examen = $this->compartido->examen;
		$cursos = $examen->getCursos();
		foreach($cursos As $curso){
			$asignatura = Asignatura::find_by_grupo_id_and_curso_id($this->matricula->grupo_id, $curso->id);
			if($asignatura){
				// COLOCA LA NOTA DEL EXAMEN
				Nota_Examen_Mensual::table()->delete(array(
					'matricula_id' => $this->matricula_id,
					'asignatura_id' => $asignatura->id,
					'ciclo' => $compartido->ciclo,
					'nro' => $compartido->nro,
				));
				Nota_Examen_Mensual::create(array(
					'matricula_id' => $this->matricula_id,
					'asignatura_id' => $asignatura->id,
					'ciclo' => $compartido->ciclo,
					'nro' => $compartido->nro,
					'nota' => $resultados[$curso->id]['puntaje']
				));
				

				$this->matricula->updatePromedioFromCriterios($asignatura->id, $compartido->ciclo);
			}
		}
	}
	
	/*
	function calificar(){
		$respuestas = $this->getRespuestas();
		$compartido = $this->compartido;
		$examen = $this->compartido->examen;
		$preguntas = $examen->getPreguntas();
		$resultados = array();

		

		foreach($preguntas As $curso_id => $x_preguntas){

			$puntaje = 0;
			$correctas = 0;
			$incorrectas = 0;

			for($i=1; $i<= $examen->total_preguntas; ++$i){
				if($preguntas[$curso_id][$i] == $respuestas[$curso_id][$i]){
					$puntaje += $examen->puntos_correcta;
					++$correctas;
				}else{
					++$incorrectas;
				}
			}


			if($puntaje < 0) $puntaje = 0;
			if($puntaje > 20) $puntaje = 20;
			
			$asignatura = Asignatura::find_by_grupo_id_and_curso_id($this->matricula->grupo_id, $curso_id);
			//echo $asignatura->curso->nombre."\n";
			if($asignatura){
				// COLOCA LA NOTA DEL EXAMEN
				Nota_Examen_Mensual::table()->delete(array(
					'matricula_id' => $this->matricula_id,
					'asignatura_id' => $asignatura->id,
					'ciclo' => $compartido->ciclo,
					'nro' => $compartido->nro,
				));
				Nota_Examen_Mensual::create(array(
					'matricula_id' => $this->matricula_id,
					'asignatura_id' => $asignatura->id,
					'ciclo' => $compartido->ciclo,
					'nro' => $compartido->nro,
					'nota' => $puntaje
				));

				$this->matricula->updatePromedioFromCriterios($asignatura->id, $compartido->ciclo);
			}
			
			$resultados[$curso_id] = array(
				'puntaje' => $puntaje,
				'correctas' => $correctas,
				'incorrectas' => $incorrectas
			);

			//$this->puntaje = $puntaje;
			//$this->correctas = $correctas;
			//$this->incorrectas = $incorrectas;
			

		}

		
		$this->resultados = serialize($resultados);
		return $this->save();
		
	}
	*/

	function setToFinished(){
		$this->calificar();
		$this->estado = 'FINALIZADA';
		return $this->save();
		return false;
	}

	function getRemainingTime(){
		$current = new DateTime(date('Y-m-d H:i:s'));
		$expiracion = new DateTime($this->expiracion);

		$diff = $current->diff($expiracion);
		$horas = $diff->format('%h');
		$minutos = $diff->format('%i');
		$segundos = $diff->format('%s');

		$segundos += $horas * 3600;
		$segundos += $minutos * 60;
		return $segundos;
	}

	function checkFinished(){
		if($this->estado == 'FINALIZADA') return true;
		if($this->compartido->hasTiempoLimite() && time() > strtotime($this->expiracion)){
			$this->setToFinished();
			return true;
		}
		return false;
	}

	function setRespuestas($respuestas){
		$this->respuestas = serialize($respuestas);
		return $this->save();
	}

	function getRespuestas(){
		$respuestas = empty($this->respuestas) ? array() : unserialize($this->respuestas);
		return $respuestas;
	}

	function getResultados(){
		$resultados = empty($this->resultados) ? array() : unserialize($this->resultados);
		return $resultados;
	}
}
