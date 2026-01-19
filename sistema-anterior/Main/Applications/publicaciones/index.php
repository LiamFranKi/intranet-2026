<?php
class PublicacionesApplication extends Core\Application{
	
	function initialize(){
		$this->security('SecureSession', [
			'USUARIO_ID' => '*'
		]);
	}

	function lista($r){

		$per_page = 3;
		$current_page = $this->get->page;
		$offset = ($per_page * $current_page) - $per_page;

		if(!$this->USUARIO->is('ADMINISTRADOR')){
			$conditions = 'usuario_id = "'.$this->USUARIO->id.'" OR find_in_set(-1, privacidad)';
			if(!$this->USUARIO->is('APODERADO') && !$this->USUARIO->is('ALUMNO')){
				$conditions .= ' OR find_in_set(-2, privacidad)'; // personal administrativo
			}
			if($this->USUARIO->is('ALUMNO')){
				$matricula = $this->USUARIO->alumno->getMatriculaByAnio($this->COLEGIO->anio_activo);
				if($matricula)
					$conditions .= ' OR find_in_set('.$matricula->grupo_id.', privacidad)'; // grupo
			}
			if($this->USUARIO->is('DOCENTE')){
				$grupos = $this->USUARIO->personal->getGruposAsignados($this->COLEGIO->anio_activo);
				foreach($grupos As $grupo){
					$conditions .= ' OR find_in_set('.$grupo->id.', privacidad)'; // grupo
				}
			}
		}else{
			$conditions = 'id != 0';
		}

        $conditions .= ' AND YEAR(fecha_hora) = YEAR(NOW())';
		
		$publicaciones = Publicacion::all([
			'limit' => $per_page,
			'offset' => $offset,
			'conditions' => $conditions,
			'order' => 'fecha_hora DESC'
		]);

		$this->render(['publicaciones' => $publicaciones]);
	}

	function subir_foto(){
		$foto = uploadFile('x_imagen', ['jpg', 'jpeg', 'png'], './Static/Image/Publicaciones');
		$r = 0;
		if(!is_null($foto)){
			$imagen = '/Static/Image/Publicaciones/'.$foto['new_name'];
			$r = 1;
		}

		echo json_encode([$r, 'imagen' => $imagen]);
	}

	function save(){
		//print_r($this->post);
		$publicacion = new Publicacion([
			'colegio_id' => $this->COLEGIO->id,
			'usuario_id' => $this->USUARIO->id,
			'contenido' => $this->post->publicacion_contenido,
			'fecha_hora' => date('Y-m-d H:i:s'),
			'privacidad' => implode(',', $this->post->privacy)
			//'images' => base64_encode(serialize($this->post->images))
		]);

		if(isset($this->post->x_images)){
			$publicacion->images = base64_encode(serialize($this->post->x_images));
		}
		
		if(isset($this->post->x_archivos)){
			$publicacion->archivos = base64_encode(serialize($this->post->x_archivos));
		}

		$r = $publicacion->save() ? 1 : 0;

		echo json_encode([$r]);
	}

	function subir_archivo(){
		$archivo = uploadFileBlackList('x_archivo', ['php', 'exe', 'sh', 'bat'], './Static/Archivos');
		$r = 0;
		if(!is_null($archivo)){
			
			$r = 1;
			echo json_encode([1, 'nombre' => $archivo['real_name'], 'archivo' => $archivo['new_name']]);
			return true;
		}

		echo json_encode([$r]);
	}
}
