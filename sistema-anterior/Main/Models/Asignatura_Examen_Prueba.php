<?php
class Asignatura_Examen_Prueba extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'asignaturas_examenes_pruebas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Examen',
			'class_name' => 'Asignatura_Examen',
			'foreign_key' => 'examen_id',
		),
		array(
			'Matricula',
			'class_name' => 'Matricula',
			'foreign_key' => 'matricula_id',
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


	function createPreguntas(){
		$examen = $this->examen;
		$preguntas_examen = $examen->getPreguntas();
		$preguntas_prueba = !empty($this->preguntas) ? unserialize(base64_decode($this->preguntas)) : array();
        if($examen->orden_preguntas == "ALEATORIO"){
            shuffle($preguntas_examen);shuffle($preguntas_examen);shuffle($preguntas_examen);
        }
		

		$preguntas_prueba = array();
		//for($i=0; $i < $examen->preguntas_max; ++$i){
		foreach($preguntas_examen As $pregunta){
			if(count($preguntas_prueba) < $examen->preguntas_max){
				$preguntas_prueba[] = $pregunta->id;
			}
		}
			
		//}
        //print_r($preguntas_prueba);

		$this->preguntas = base64_encode(serialize($preguntas_prueba));
		$this->save();
		//return $preguntas_prueba;
	}

	function getPreguntasId(){
		$preguntas = !empty($this->preguntas) ? unserialize(base64_decode($this->preguntas)) : array();
		return $preguntas;
	}

	function getPreguntas(){
		if(empty($this->preguntas)){
            return $this->examen->getPreguntas();
            //echo "NOOOOO";
        }
			

		$ids = $this->getPreguntasId();
		if(count($ids) <= 0)
			return [];

        
		
		$preguntas = Asignatura_Examen_Pregunta::all([
			'conditions' => 'id IN ('.implode(',', $ids).')',
            'order' => 'FIELD(id, '.implode(',', $ids).')'
		]);

        //print_r($preguntas);
		
		return $preguntas;
	}

	function activa(){
		return $this->estado == 'ACTIVO';
	}

	function calificar(){
		$respuestas = $this->getRespuestas();
		$puntaje = 0;
		$examen = $this->examen;
		$correctas = 0;
		$incorrectas = 0;
		foreach($this->getPreguntas() As $pregunta){
			$correcta = false;
			if($pregunta->tipo == 'ALTERNATIVAS'){
				foreach($pregunta->getAlternativas() As $alternativa){
					if($respuestas[$pregunta->id] == $alternativa->id && $alternativa->correcta()){
						$puntaje += $examen->puntajeGeneral() ? $examen->puntos_correcta : $pregunta->puntos;
						$correcta = true;
						++$correctas;
						break;
					}
				}
			}
			
			if($pregunta->tipo == 'PUZZLE'){
				if($respuestas[$pregunta->id] == 'OK'){
					$puntaje += $examen->puntajeGeneral() ? $examen->puntos_correcta : $pregunta->puntos;
					$correcta = true;
					++$correctas;
				}
			}

            if($pregunta->tipo == "COMPLETAR"){
                

                if($pregunta->checkRespuesta($respuestas[$pregunta->id])){
                    $puntaje += $examen->puntajeGeneral() ? $examen->puntos_correcta : $pregunta->puntos;
					$correcta = true;
					++$correctas;
                }

                /* $puntaje += $examen->puntajeGeneral() ? $examen->puntos_correcta : $pregunta->puntos;
                $correcta = true;
                ++$correctas; */
            }


			if(!$correcta){
				if($examen->penalizarIncorrecta()) $puntaje -= $examen->penalizacion_incorrecta;
				++$incorrectas;
			}
		}
		if($puntaje < 0) $puntaje = 0;
		if($puntaje > 20) $puntaje = 20; 
		$this->puntaje = $puntaje;
		$this->correctas = $correctas;
		$this->incorrectas = $incorrectas;
		return $this->save();
	}

	function setToFinished(){
		$this->calificar();
		$this->estado = 'FINALIZADA';
		return $this->save();
		return false;
	}

	function getRemainingTime(){
        if(time() > strtotime($this->expiracion)) return 0;
        
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
		if($this->examen->hasTiempoLimite() && time() > strtotime($this->expiracion)){
			$this->setToFinished();
			return true;
		}
		return false;
	}

	function setRespuestas($respuestas){
		$this->respuestas = base64_encode(serialize($respuestas));
		return $this->save();
	}

	function getRespuestas(){
		$respuestas = empty($this->respuestas) ? array() : unserialize(base64_decode($this->respuestas));
		return $respuestas;
	}
}
