<?php
/**
 *  DIRECTORY READER -> READS FILES
 */ 

class DirectoryReader{
    protected $path;
    protected $files = array();
    protected $directories = array();
    protected $list_content = '';

    /**
     *  @path -> the path is the main route  
     */

    function __construct($path = null){
       $this->setPath($path);
    }

    /**
     *  @extension -> if you want filter files five the name extension without .
     */

    function getAsList($extension = null){
        $this->read($this->path, $extension);
        return $this->list_content;
    }

    /**
     *  @extension -> if you want filter files five the name extension without .
     */

    function getArrayFiles($extension = null){
        $this->read($this->path, $extension);
        return $this->files;
    }
    
    function getFiles($type = 'single'){
		if($type == 'single') $this->readSingle($this->path);
		if($type == 'recursive') $this->read($this->path);
		return $this->files;
	}

	function getDirectories($type = 'single'){
		if($type == 'single') $this->readSingle($this->path, $extension);
		if($type == 'recursive') $this->read($this->path, $extension);
        return $this->directories;
	}

    /**
     *  @path -> the path is the main route
     *  @extension -> if you want filter files five the name extension without .
     */

	function readSingle($path, $extension = null){
		$directory = dir($path);
		while($file = $directory->read()){
			if($file == '.' | $file == '..') continue;
			$file_path = $directory->path.'/'.$file;
			if(isset($extension)){
                if(preg_match('/\.'.$extension.'$/', $file)){
                    $this->files[] = $file_path;
				}
			}else{
                $this->files[] = $file_path;
            }
            
            if(is_dir($file_path)){
				$this->directories[] = $file_path;
            }
		}
		return $files;
	}

    function read($path, $extension = null){
        $directory = dir($path);
        $this->list_content .= '<ul>';
        while($file = $directory->read()){
            if($file == '.' | $file == '..') continue;
            $file_path = $directory->path.'/'.$file;
            if(isset($extension)){
                if(preg_match('/\.'.$extension.'$/', $file)){
                    $this->files[] = $file_path;
                    $this->list_content .= '<li>'.$file.'</li>';
                }
            }else{
                $this->files[] = $file_path;
                $this->list_content .= '<li>'.$file.'</li>';
            }

            if(is_dir($file_path)){
				$this->directories[] = $file_path;
                $this->read($file_path, $extension);
            }
        }
        $this->list_content .= '</ul>';
    }
    
    function getFileNames($extension = null, $complete = true){
		$this->read($this->path, $extension);
		$files = $this->files;
		foreach($files As $key => $file){
			$info = pathinfo($file);
			$files[$key] = !$complete ? $info['filename'] : $info['basename'];
		}
		return $files;
	}
	
	function getDirectoryNames($type = 'single'){
		$directories = $this->getDirectories($type);
		foreach($directories As $key => $directory){
			$directory = str_replace('\\', '/', $directory);
			$directories[$key] = end(explode('/', $directory));
		}
		return $directories;
	}
    
    function setPath($path){
		$this->path = $path;
	}
	
	
}
