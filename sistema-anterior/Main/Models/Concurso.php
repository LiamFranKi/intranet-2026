<?php
class Concurso extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'concursos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'grupo',
			'class_name' => 'Grupo',
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

	function getAsignaturasID(){
		$data = !empty($this->cursos) ? unserialize(base64_decode($this->cursos)) : array();
		return $data;
	}

	// Retorna el id de los cursos seleccionados
	
	function getAsignaturas(){
		$ids = $this->getAsignaturasID();
		$cursos = array();
		foreach($ids As $id){
			$curso = Asignatura::find($id);
			$cursos[] = $curso;
		}
		return $cursos;
	}

	function getPreguntas($curso_id = null, $shuffle = false){
		$conditions = 'concurso_id = "'.$this->id.'"';
		if(!is_null($curso_id)){
			$conditions .= ' AND asignatura_id = "'.$curso_id.'"';
		}
		$preguntas = Concurso_Pregunta::all(array(
			'conditions' => $conditions,
			'order' => 'orden ASC'
		));
		if($shuffle){
			shuffle($preguntas);
		}
		return $preguntas;
	}

	/*
	function getAsignaturas(){
		$asignaturas = Asignatura::all(array(
			'conditions' => 'id IN('.implode(', ', $this->getCursos()).')'
		));

		return $asignaturas;
	}
	*/

	function getRespuestas(){
		$data = !empty($this->respuestas) ? unserialize(base64_decode($this->respuestas)) : array();
		return $data;
	}

	function getRespuesta($matricula_id, $curso_id){
		$respuestas = $this->getRespuestas();
		return $respuestas[$matricula_id][$curso_id];
	}

	function getPuntajes(){
		$data = !empty($this->puntajes) ? unserialize(base64_decode($this->puntajes)) : array();
		return $data;
	}

	function getPuntaje($matricula_id, $curso_id){
		$puntajes = $this->getPuntajes();
		return $puntajes[$matricula_id][$curso_id];
	}

	function getPromedio($matricula_id){
		$puntajes = $this->getPuntajes();
		$puntajes = $puntajes[$matricula_id];

		$total = null;
		if(isset($puntajes)){
			$total = 0;
			foreach($puntajes As $curso_id => $nota){
				$total += $nota;
			}
			//$total = ($total > 0) ? round($total / count($puntajes)) : 0;
		}

		return $total;
	}

	public static $RANK = null;

	function getRank($matricula_id){

		
		
		if(!is_null(Concurso::$RANK)){
			$rank = Concurso::$RANK;
		}else{
			$matriculas = $this->grupo->getMatriculas();
	
		
			$promedios = array();
			foreach($matriculas As $matricula){
				$prueba = $matricula->getBestTestConcurso($this);

				$promedios[$matricula->id] = isset($prueba) ? $prueba->getPromedio() : 0;
			}
			asort($promedios, SORT_NUMERIC);
			
			$rank = array();
	        $i = 1;
			$ids = array_reverse(array_keys($promedios));
	        foreach($ids As $key => $id){
	            $promedio = $promedios[$id];
	            
	            if($promedio == $current_promedio){
	                $rank[$id] = $current_rank;
	            }else{
	                $rank[$id] = $i;
	                $i++;
	            }
	            
	            $current_promedio = $promedio;
	            $current_rank = $rank[$id];
	        }

	        Concurso::$RANK = $rank;
		}
		
        //print_r($rank);
        //echo $matricula_id;
        return $rank[$matricula_id];
	}

	function hasTiempoLimite(){
		return $this->tiempo > 0;
	}
}
