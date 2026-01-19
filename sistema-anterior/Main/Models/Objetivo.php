<?php
class Objetivo extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'objetivos';
	static $connection = '';
	
	static $belongs_to = Array();
	static $has_many = Array();
	static $has_one = Array();
	
	static $validates_presence_of = Array();
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = Array();
	static $validates_uniqueness_of = Array();

	function getData(){
		$data = !empty($this->data) ? unserialize(base64_decode($this->data)) : array();
		return $data;
	}

	function get($param, $grupo_id){
		$data = $this->getData();
		// check for default values
		$defaults = null;
		if($param == 'falta_captar_antiguos'){
			
			$defaults = $this->get('se_esperan_antiguos', $grupo_id) - $this->get('alumnos_antiguos', $grupo_id);
			//echo $defaults;
		}
		if($param == 'falta_captar_nuevos')
			$defaults = $this->get('vacantes', $grupo_id) - ($this->get('falta_captar_antiguos', $grupo_id) + $this->getTotalMatriculados($grupo_id));
		
		if($param == 'total_antiguos'){
			$defaults = $this->get('pasan_anterior', $grupo_id) - $this->get('se_retiran', $grupo_id);
		}

		if($param == 'antiguos_matriculados'){
			//$defaults = $this->getAntiguosMatriculados($grupo_id);
		}

		if(isset($defaults))
			return ($defaults >= 0 ? $defaults : '');
		
		
		//echo $data[$param][$grupo_id];
		return $data[$param][$grupo_id];
	}

	function getMatriculados($grupo_id){
		$matriculas = Matricula::all([
			'conditions' => 'grupo_id = "'.$grupo_id.'" AND (estado="0")'
		]);
		$antiguos = 0;
		$nuevos = 0;
		foreach($matriculas As $matricula){
			$lastMatricula = Matricula::find([
				'conditions' => 'matriculas.alumno_id = "'.$matricula->alumno_id.'" AND grupos.anio = "'.($matricula->grupo->anio - 1).'"',
				'joins' => 'inner join grupos on grupos.id = matriculas.grupo_id'
			]);

			if($lastMatricula){
				++$antiguos;
			}else{
				++$nuevos;
			}
		}

		return [
			'nuevos' => $nuevos,
			'antiguos' => $antiguos
		];
	}

	function getTotalMatriculados($grupo_id){
		//$grupo = Grupo::find($grupo_id);
		//return count($grupo->getMatriculas());
		$total = $this->get('alumnos_antiguos', $grupo_id) + $this->get('alumnos_nuevos', $grupo_id);
		return $total;
	}

	function getEstado($grupo_id){
		$estado = $this->getTotalMatriculados($grupo_id) == $this->get('vacantes', $grupo_id) ? 'NO VACANTE' : '';
		return $estado;
	}
}
