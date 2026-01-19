<?php
class FilemanagerApplication extends Core\Application{
	
	function grupo($r){
		$permissions = explode(',', base64_decode($this->get->p));
		$permissions = array(
			'DELETE' => in_array('D', $permissions),
			'UPLOAD' => in_array('U', $permissions),
			'READ' => in_array('R', $permissions),
			'CREATE' => in_array('C', $permissions),
			'PUBLIC' => in_array('P', $permissions),
		);


		$this->render(array('permissions' => $permissions));
	}

	function index($r){
		$permissions = explode(',', base64_decode($this->get->p));
		$permissions = array(
			'DELETE' => in_array('D', $permissions),
			'UPLOAD' => in_array('U', $permissions),
			'READ' => in_array('R', $permissions),
			'CREATE' => in_array('C', $permissions),
			'PUBLIC' => in_array('P', $permissions),
		);

		if($permissions['PUBLIC']){
			$publicPersonal = $this->COLEGIO->getPublicSpacePersonal();
		}

		$this->render(array('permissions' => $permissions, 'publicPersonal' => $publicPersonal));
	}

	function files(){

		if($this->USUARIO->tipo != 'ADMINISTRADOR' && $this->USUARIO->tipo != 'DIRECTOR' && $this->post->token != $this->personal->getFileManagerToken()){
			$files = array();
		}else{
			if(!isset($this->post->base)){
				$directory = $this->personal->getFileManagerDirectory();
			}else{
				$directory = base64_decode($this->post->base);
			}
			//if(isset($this->post->base)) $directory .= $this->post->base;
			$files = globdir($directory);
			
			if(in_array($directory.'/NO_READABLE', $files) || !file_exists($directory.'/..')){
				$directory = $this->personal->getFileManagerDirectory();
				$files = globdir($directory);
			}

			array_unshift($files, $directory.'/..');

			foreach($files As $key => $file){

				if(is_dir($file)){
					$files[$key] = array(
						'filename' => basename($file),
						'type' => 'Folder',
						'size' => '',
						'folderBase' => base64_encode($file),
						'modifiedDate' => '',
						'filePath' => base64_encode($file),
					);
				}else{
					if(!preg_match('/\.\.$/', $file)){
						$files[$key] = array(
							'filename' => basename($file),
							'type' => 'File',
							'size' => FileSizeConvert(filesize($file)),
							'filePath' => base64_encode($file),
							'modifiedDate' => date('d-m-Y H:i', filemtime($file))
						);
					}
				}
			}
		}

		echo json_encode($files);
	}

	function initialize(){
		if(isset($this->params->id)){
			$this->personal = Personal::find([
				'conditions' => ['sha1(id) = ?', $this->params->id]
			]);
			if(!$this->personal){
				// PUBLIC
				$this->personal = new Personal(array(
					'id' => $this->params->id
				)); 
			}
			$this->context('personal', $this->personal);
		}
		if(isset($this->params->grupo_id)){
			$this->grupo = Grupo::find([
				'conditions' => ['sha1(id) = ?', $this->params->grupo_id]
			]);
			$this->context('grupo', $this->grupo);
		}
	}

	function download(){
		$fileData = base64_decode($this->get->f);
		if(file_exists($fileData)){
			//$data = file_get_contents($fileData);
			//ob_clean();
			//flush();
			//header('Content-Type: application/octet-stream');
			//header("Content-Transfer-Encoding: Binary"); 
			//header('Content-Disposition: attachment; filename='.basename($fileData));
			//header('Content-Type: application/octet-stream');
			//echo $data;
			header('Location: '.substr($fileData, 1));
			//header('Location: '.basename($fileData));
			//echo $fileData;
		}
	}
	
	function upload(){
		$this->crystal->load('Util:UploadHandler');
			
		$directory = !isset($this->post->base) ? $this->personal->getFileManagerDirectory() : base64_decode($this->post->base);

		$upload_handler = new UploadHandler(array(
			'upload_dir' => $directory.'/'
		));
	}

	function make_folder(){
		$directory = !isset($this->post->base) ? $this->personal->getFileManagerDirectory() : base64_decode($this->post->base);
		$r = @mkdir($directory.'/'.$this->post->name, 0777, true) ? 1 : 0;
		//echo $directory.'/'.$this->post->name;
		echo json_encode(array($r, $directory.'/'.$this->post->name));
	}

	function delete(){
		//$directory = !isset($this->post->id) ? $this->USUARIO->personal->getFileManagerDirectory() : $this->personal->getFileManagerDirectory();
		//echo $directory.'/'.$this->get->archivo;
        
        $shortenFrom = 'Files';

		$fileData = base64_decode($this->post->file);
        $realpath = str_replace('\\', '/', realpath($fileData));
        $pos = strpos($realpath, $shortenFrom);
        $shortenedPath = substr($realpath, $pos);
        $path = explode("/", $realpath);

        if ($pos === false) {
            echo json_encode(array(0, $fileData));
            return false;
        }

 

        $fileData = 'Static/'.$shortenedPath;

		
		if(is_dir($fileData)){
			$r = deleteAnyFile($fileData) ? 1 : 0;
		}else{
			$r = @unlink($fileData) ? 1 : 0;
		}
		echo json_encode(array($r, $fileData));
	}

	// FUNCTIONS FOR GROUP

	function group_files(){

		if($this->USUARIO->tipo != 'ADMINISTRADOR' && $this->USUARIO->tipo != 'DIRECTOR' && $this->post->token != $this->grupo->getFileManagerToken()){
			$files = array();
		}else{
			if(!isset($this->post->base)){
				$directory = $this->grupo->getFileManagerDirectory();
			}else{
				$directory = base64_decode($this->post->base);
			}
			//if(isset($this->post->base)) $directory .= $this->post->base;
			$files = globdir($directory);
			
			if(in_array($directory.'/NO_READABLE', $files) || !file_exists($directory.'/..')){
				$directory = $this->grupo->getFileManagerDirectory();
				$files = globdir($directory);
			}

			array_unshift($files, $directory.'/..');

			foreach($files As $key => $file){

				if(is_dir($file)){
					$files[$key] = array(
						'filename' => basename($file),
						'type' => 'Folder',
						'size' => '',
						'folderBase' => base64_encode($file),
						'modifiedDate' => '',
						'filePath' => base64_encode($file),
					);
				}else{
					if(!preg_match('/\.\.$/', $file)){
						$files[$key] = array(
							'filename' => basename($file),
							'type' => 'File',
							'size' => FileSizeConvert(filesize($file)),
							'filePath' => base64_encode($file),
							'modifiedDate' => date('d-m-Y H:i', filemtime($file))
						);
					}
				}
			}
		}

		echo json_encode($files);
	}

	function make_group_folder(){
		$directory = !isset($this->post->base) ? $this->grupo->getFileManagerDirectory() : base64_decode($this->post->base);
		$r = @mkdir($directory.'/'.$this->post->name, 0777, true) ? 1 : 0;
		//echo $directory.'/'.$this->post->name;
		echo json_encode(array($r, $directory.'/'.$this->post->name));
	}


	function group_upload(){
		$this->crystal->load('Util:UploadHandler');
			
		$directory = !isset($this->post->base) ? $this->grupo->getFileManagerDirectory() : base64_decode($this->post->base);

		$upload_handler = new UploadHandler(array(
			'upload_dir' => $directory.'/'
		));
	}
}
