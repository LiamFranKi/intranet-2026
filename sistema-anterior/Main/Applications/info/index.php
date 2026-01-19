<?php
class InfoApplication extends Core\Application{
	
	function dni(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Allow: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }

		header('Content-Type: application/json');
		
		$data = file_get_contents('http://api.apis.net.pe/v1/dni?numero='.$this->params->id);
		echo $data;
		if(empty($data)){
			echo json_encode(array('error'=>'NOT FOUND'));
		}
	}

	function get_departamentos(){
		$pais = Pais::find($this->post->pais_id);
		$departamentos = Departamento::all(array(
			'conditions' => 'codigo LIKE "'.$pais->codigo.'-%"'
		));
		$data = array();
		foreach($departamentos As $departamento){
			$data[] = $departamento->attributes();
		}
		
		echo json_encode($data);
	}
	
	function get_provincias(){
		$data = array();
		if(!empty($this->post->departamento_id)){
			$departamento = Departamento::find_by_id($this->post->departamento_id);
			$provincias = Provincia::all(array(
				'conditions' => 'codigo LIKE "'.$departamento->codigo.'%"'
			));
			
			foreach($provincias As $provincia){
				$data[] = $provincia->attributes();
			}
		}
		echo json_encode($data);
	}
	
	function get_distritos(){
		$data = array();
		if(!empty($this->post->provincia_id)){
			$provincia = Provincia::find_by_id($this->post->provincia_id);
			$distritos = Distrito::all(array(
				'conditions' => 'codigo LIKE "'.$provincia->codigo.'%"'
			));
			
			foreach($distritos As $distrito){
				$data[] = $distrito->attributes();
			}
		}
		echo json_encode($data);
	}
	
	function personal(){
        $personal = Personal::all(Array(
			'conditions' => 'colegio_id="'.$this->COLEGIO->id.'" AND (MATCH(personal.nombres,personal.apellidos) AGAINST("'.$this->get->term.'" IN BOOLEAN MODE) OR apellidos LIKE "%'.$this->get->term.'%" OR nombres LIKE "%'.$this->get->term.'%")',
		));
		$data = array();
		foreach($personal As $persona){
            $data[] = array('value' => $persona->id, "text" => $persona->getFullName());
        }
        echo json_encode($data);
    }
    
    function get_cursos(){
		$cursos = Curso::all(Array(
			'conditions' => Array('colegio_id="'.$this->COLEGIO->id.'" AND nivel_id="'.$this->post->nivel_id.'"'),
            'order' => 'nombre ASC'
		));

		$data = array();
		$grupo = $this->COLEGIO->getGrupo($this->post);
		//if($grupo)
			foreach($cursos As $curso){
	            $asignatura = Asignatura::count(array(
	                'conditions' => 'asignaturas.colegio_id="'.$this->COLEGIO->id.'"AND curso_id="'.$curso->id.'" AND grupo_id="'.$grupo->id.'"',
	                // AND  AND grupos.grado="'.$this->post->grado.'" AND grupos.seccion="'.$this->post->seccion.'" AND grupos.nivel_id="'.$this->post->nivel_id.'" AND grupos.anio="'.$this->post->anio.'" AND grupos.turno_id="'.$this->post->turno_id.'"' ,
	                //'joins' => array('grupo')
	            ));
	            //echo $asignatura;
	            //if($this->post->id != "" || $asignatura <= 0)
	            if($this->post->id != "" || $asignatura <= 0)
	                $data[] = array($curso->id, mb_strtoupper($curso->nombre, 'UTF-8'));
			}
		echo json_encode($data);
	}

	function get_cursos_nivel(){
		$cursos = Curso::all(Array(
			'conditions' => Array('colegio_id="'.$this->COLEGIO->id.'" AND nivel_id="'.$this->post->nivel_id.'"'),
            'order' => 'nombre ASC'
		));
		$data = array();
		foreach($cursos AS $curso){
			$data[] = $curso->attributes();
		}
		echo json_encode($data);
	}

	function get_cursos_bloque(){
		$cursos = Bloque_Curso::all(Array(
			'conditions' => Array('bloque_id="'.$this->post->bloque_id.'"'),
		));
		$data = array();
		foreach($cursos AS $curso){
			if($curso->curso)
				$data[] = $curso->curso->attributes();
		}
		echo json_encode($data);
	}
	
	function apoderado(){
		$apoderado = Apoderado::find_by_tipo_documento_and_nro_documento($this->post->tipo_documento, $this->post->nro_documento);
		if($apoderado) echo $apoderado->to_json();
	}
	
	function alumno(){
		$alumnos = $this->COLEGIO->searchAlumnos($this->get->term);
		$data = array();
		foreach($alumnos As $alumno){
			$data[] = array(
            'value' => $alumno->id,
            'text' => $alumno->getFullName(),
            
            );
		}
        echo json_encode($data);
	}

	function info_alumno(){
		if(isset($this->post->dni)){
			$alumno = Alumno::find(array(
				'conditions' => 'nro_documento="'.$this->post->dni.'" AND colegio_id="'.$this->COLEGIO->id.'"'
			));
			if($alumno){
				echo json_encode(array('nombres' => $alumno->getFullName(), 'id' => $alumno->id));
				return;
			}
		}
		if(isset($this->get->q)){
			$alumnos = Alumno::all(array(
				'conditions' => 'MATCH(nombres,apellido_paterno, apellido_materno) AGAINST("'.$this->get->q.'" IN BOOLEAN MODE)'
			));
			$data = '';
			foreach($alumnos As $alumno){
				$data .= $alumno->getFullName().'|'.$alumno->nro_documento."\n";
			}
			echo $data;
		}

	}

	function info_alumno_full(){
		if(isset($this->post->dni)){
			$alumno = Alumno::find(array(
				'conditions' => 'nro_documento="'.$this->post->dni.'" AND colegio_id="'.$this->COLEGIO->id.'"'
			));
			if($alumno){
				echo json_encode($alumno->attributes());
				return;
			}else{
				$matricula = Grupo_Taller_Matricula::find_by_dni($this->post->dni);
				if($matricula){
					echo json_encode($matricula->attributes());
					return;
				}
			}
		}
	}

	function info_alumno_docente(){
		if(isset($this->post->dni)){
			$alumno = Alumno::find(array(
				'conditions' => 'nro_documento="'.$this->post->dni.'" AND colegio_id="'.$this->COLEGIO->id.'"'
			));
			if($alumno){
				echo json_encode(array('nombres' => $alumno->getFullName(), 'id' => $alumno->id, 'tipo' => 'ALUMNO'));
				return true;
			}
		}
		
		if(isset($this->post->dni)){
			$personal = Personal::find(array(
				'conditions' => 'nro_documento="'.$this->post->dni.'" AND colegio_id="'.$this->COLEGIO->id.'"'
			));
			if($personal){
				echo json_encode(array('nombres' => $personal->getFullName(), 'id' => $personal->id, 'tipo' => 'DOCENTE'));
			}
		}
	}

	function matricula(){
		$conditions = 'matriculas.colegio_id="'.$this->COLEGIO->id.'" AND MATCH(alumnos.apellido_paterno, alumnos.apellido_materno, alumnos.nombres) AGAINST("'.$this->get->term.'" IN BOOLEAN MODE)';
		if(isset($this->get->anio)){
			$conditions .= ' AND grupos.anio = "'.$this->get->anio.'"';
		}
		$matriculas = Matricula::all(array(
			'conditions' => $conditions,
			'joins' => array('alumno', 'grupo')
		));

		$data = array();
		foreach($matriculas As $matricula){
			$data[] = array('value' => $matricula->id, 'text' => $matricula->alumno->getFullName());
		}
		echo json_encode($data);
	}
	
	function usuario(){
        $alumnos = Alumno::all(Array(
			'conditions' => Array('colegio_id="'.$this->COLEGIO->id.'" AND MATCH(apellido_paterno, apellido_materno, nombres) AGAINST("'.$this->get->term.'" IN BOOLEAN MODE)'),
		));
		
        $personal = Personal::all(Array(
			'conditions' => Array('colegio_id="'.$this->COLEGIO->id.'" AND MATCH(apellidos, nombres) AGAINST("'.$this->get->term.'" IN BOOLEAN MODE)'),
		));
		
		$apoderados = Apoderado::all(Array(
			'conditions' => Array('colegio_id="'.$this->COLEGIO->id.'" AND MATCH(apellido_paterno, apellido_materno, nombres) AGAINST("'.$this->get->term.'" IN BOOLEAN MODE)'),
		));
        
        $data = array();
        
        foreach($alumnos As $alumno){
            if($alumno->usuario)
                $data[] = array('value' => $alumno->usuario->id, "text" => $alumno->getFullName().' - Alumno');
        }
        
        foreach($apoderados As $apoderado){
            if($apoderado->usuario)
                $data[] = array('value' => $apoderado->usuario->id, "text" => $apoderado->getFullName().' - Padre de Familia');
        }
        
        foreach($personal As $persona){
            if($persona->usuario)
                $data[] = array('value' => $persona->usuario->id, "text" => $persona->getFullName().' - '.$persona->cargo);
        }
        
        echo json_encode($data);
    }

    function caja_conceptos(){
    	$conceptos = Caja_Concepto::find_all_by_categoria_id($this->post->categoria_id, array(
    		'order' => 'descripcion ASC'
    	));
    	$data = array();
    	foreach($conceptos As $concepto){
    		$data[] = $concepto->attributes();
    	}
    	echo json_encode($data);
    }

    function meritos_demeritos(){
    	$conceptos = Infraccion::find_all_by_categoria_id_and_tipo($this->post->categoria_id, $this->post->tipo, array(
    		'order' => 'descripcion ASC'
    	));
    	$data = array();
    	foreach($conceptos As $concepto){
    		$data[] = $concepto->attributes();
    	}
    	echo json_encode($data);
    }
}
