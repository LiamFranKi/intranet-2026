<?php
class Concurso_Prueba extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'concursos_pruebas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Concurso',
			'class_name' => 'Concurso',
		),
		array(
			'Matricula',
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

	function calificar(){
		$respuestas = $this->getRespuestas();
		$puntaje = 0;
		$concurso = $this->concurso;
		$asignaturas = $concurso->getAsignaturas();
	

		$resultados = array();
		foreach($asignaturas As $asignatura){

			$correctas = 0;
			$incorrectas = 0;
			$puntaje = 0;

			foreach($this->getPreguntas($asignatura->id) As $pregunta){
				if($this->respuestaCorrecta($pregunta)){
					$puntaje += $concurso->puntos_correcta;
					++$correctas;
				}else{
					$puntaje -= $concurso->puntos_incorrecta;
					++$incorrectas;
				}
			}

			//if($puntaje < 6) $puntaje = 6;
			//if($puntaje > 20) $puntaje = 20;

			
			$resultados[$asignatura->id] = array(
				'puntaje' => $puntaje,
				'correctas' => $correctas,
				'incorrectas' => $incorrectas
			);
		}
		
		$this->resultados = serialize($resultados);
		return $this->save();
	}

	function getTotalPuntaje(){
		$total = 0;
		foreach($this->getResultados() As $data){
			$total += $data['puntaje'];
		}
		return $total;
	}

	function getPromedio(){
		$total = 0;
		$resultados = $this->getResultados();
		foreach($resultados As $asignatura_id => $data){
			$total += $data['puntaje'];
		}
		if($total == 0){
			return 0;
		}
		return round($total / count($resultados), 2);
	}

	function respuestaCorrecta($pregunta){
		$correcta = false;
		$concurso = $this->concurso;
		$respuestas = $this->getRespuestas();
		foreach($pregunta->getAlternativas() As $alternativa){
			if($respuestas[$pregunta->id] == $alternativa->id && $alternativa->correcta()){
				$correcta = true;
				break;
			}
		}
		return $correcta;
	}

	function getRank(){
		
	}

	function getRespuestas(){
		$respuestas = empty($this->respuestas) ? array() : unserialize($this->respuestas);
		return $respuestas;
	}

	function getPreguntas($asignatura_id){

		$preguntas = !empty($this->preguntas) ? unserialize(base64_decode($this->preguntas)) : array();

		if(!isset($preguntas[$asignatura_id]) || count($preguntas[$asignatura_id]) <= 0){

			$preguntas = $this->buildPreguntas($asignatura_id);
		}

		return $preguntas[$asignatura_id];
	}

	function buildPreguntas($asignatura_id){
		$concurso = $this->concurso;
		$preguntas_concurso = $concurso->getPreguntas($asignatura_id, true);
		$preguntas_prueba = !empty($this->preguntas) ? unserialize(base64_decode($this->preguntas)) : array();
		shuffle($preguntas_concurso);

		//$preguntas_prueba[$curso_id] = array_slice($preguntas_examen, $examen->preguntas_max);
		$preguntas_prueba[$asignatura_id] = array();
		for($i=0; $i < $concurso->preguntas_max; ++$i){
			if(count($preguntas_concurso) > $i){
				$preguntas_prueba[$asignatura_id][] = $preguntas_concurso[$i];
			}
		}



		$this->preguntas = base64_encode(serialize($preguntas_prueba));
		$this->save();
		return $preguntas_prueba;
	}

	function checkFinished(){
		if($this->estado == 'FINALIZADA') return true;
		if($this->concurso->hasTiempoLimite() && time() > strtotime($this->expiracion)){
			$this->setToFinished();
			return true;
		}
		return false;
	}

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

	function setRespuestas($respuestas){
		$this->respuestas = serialize($respuestas);
		$this->save();
	}

	function getResultados(){
		$resultados = empty($this->resultados) ? array() : unserialize($this->resultados);
		return $resultados;
	}
}
