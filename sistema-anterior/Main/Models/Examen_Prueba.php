<?php
class Examen_Prueba extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'examenes_pruebas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'compartido',
			'class_name' => 'Examen_Compartido',
			'foreign_key' => 'compartido_id',
		),
		array(
			'matricula',
			'class_name' => 'Matricula',
			'foreign_key' => 'matricula_id',
		),
		array(
			'personal',
			'class_name' => 'Personal',
			'foreign_key' => 'personal_id',
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
		$correctas = 0;
		$incorrectas = 0;
		$resultados = [];

		foreach($examen->getPreguntas() As $pregunta){
			$correcta = false;
			foreach($pregunta->getAlternativas() As $alternativa){
				if($respuestas[$pregunta->id] == $alternativa->id && $alternativa->correcta()){
					$puntaje += $examen->puntajeGeneral() ? $examen->puntos_correcta : $pregunta->puntos;
					$resultados[$pregunta->categoria]['puntaje'] += $examen->puntajeGeneral() ? $examen->puntos_correcta : $pregunta->puntos;
					$correcta = true;
					
					$resultados[$pregunta->categoria]['correctas']++;

					++$correctas;
					break;
				}
			}

			if(!$correcta){
				if($examen->penalizarIncorrecta()){
					$puntaje -= $examen->penalizacion_incorrecta;
					$resultados[$pregunta->categoria]['puntaje'] -= $examen->penalizacion_incorrecta;
				} 

				$resultados[$pregunta->categoria]['incorrectas']++;
				++$incorrectas;
			}

			
		}

		if($puntaje < 0) $puntaje = 0;
		$this->puntaje = $puntaje;
		$this->correctas = $correctas;
		$this->incorrectas = $incorrectas;
		$this->resultados = serialize($resultados);
		return $this->save();
	}

	function getResultados(){
		$resultados = !empty($this->resultados) ? unserialize($this->resultados) : [];
		return $resultados;
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

	function checkFinished(){
		if($this->estado == 'FINALIZADA') return true;
		if($this->compartido->hasTiempoLimite() && time() > strtotime($this->expiracion)){
			$this->setToFinished();
			return true;
		}
		return false;
	}

	function setRespuestas($respuestas){
		$this->respuestas = base64_encode(serialize($respuestas));
		$this->save();
	}

	function getRespuestas(){
		$respuestas = empty($this->respuestas) ? array() : unserialize(base64_decode($this->respuestas));
		return $respuestas;
	}
}
