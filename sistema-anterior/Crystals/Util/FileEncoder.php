<?php
class FileEncoder{
	
	private $files = array();
	private $directory;
	
	function __construct($directory){
		$this->directory = $directory;
		$this->fetchFiles($directory);
	}
	
	protected function fetchFiles($directory){
		$dir = dir($directory);
		while($file = $dir->read()){
			if($file == '.' || $file == '..') continue;
			if(is_dir($directory.'/'.$file)){
				$this->fetchFiles($directory.'/'.$file);
			}
			
			if(is_file($directory.'/'.$file)){
				$this->files[] = $directory.'/'.$file;
			}
		}
	}

	function encodeFiles($from, $to, $pattern){	
		foreach($this->files As $file){
			if(preg_match($pattern, $file)){
				$original = file_get_contents($file);
				$new = mb_convert_encoding($original, $to, $from);
				if(!empty($new)) file_put_contents($file, $new);
			}
		}
	}
}
