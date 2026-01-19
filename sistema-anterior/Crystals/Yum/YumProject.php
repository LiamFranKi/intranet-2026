<?php
class YumProject {
	
	private $path;
	
	/**
	 * 	'var/www/index.php' => 'sha1'
	 */ 
	
	private $files;
	
	private $fs;
	private $project = 'main.yumprj';
	
	private $xfiles = array();
	
	function __construct($path){
		$this->path = $path;
		$this->unserializeFiles();
		
	}
	
	function isInitialized(){
		return file_exists($this->path.'/.yum/'.$this->project);
	}
	
	function parseFiles($path){
        $directory = dir($path);

        while($file = $directory->read()){
            if($file == '.' | $file == '..') continue;
            $file_path = $directory->path.'/'.$file;
			if(is_file($file_path)){
                $this->xfiles[] = $file_path;
            }else{
				//$this->directories[] = $file_path;
                $this->parseFiles($file_path, $extension);
            }
        }
	}
	
	function getFiles($type = null){
		$this->parseFiles($this->path);
		return $this->cleanFiles($this->xfiles);
	}
	
	function cleanFiles($collection){
		foreach($collection As $key => $file){
			if(preg_match('/^'.$this->getCleanPath().'\/\.yum/iU', $file)){
				unset($collection[$key]);
			}
		}
		
		return $collection;
	}
	
	function getCreatedFiles(){
		$files = $this->getFiles();
		$current_files = array_keys($this->getCurrentFiles());
		return array_diff($files, $current_files);
	}
	
	function getDeletedFiles(){
		$files = $this->getFiles();
		$current_files = array_keys($this->getCurrentFiles());
		return array_diff($current_files, $files);
	}
	
	function getModifiedFiles(){
		$files = $this->getFiles();
		$current_files = $this->getCurrentFiles();
		$modified = array();
		
		foreach($files As $file){
			if(isset($current_files[$file])){
				if($current_files[$file] != $this->getTokenFile($file)){
					if(!in_array($file, $modified)) $modified[] = $file;
				}
			}
		}
		
		return $modified;
	}
	
	function getCurrentFiles(){
		return $this->files;
	}
	
	function getCleanPath(){
		$path = str_replace('/', '\/', $this->path);
		return $path;
	}
	
	function initialize(){
		if(!file_exists($this->path.'/.yum/patch')){
			mkdir($this->path.'/.yum/patch', 0777, true);
			exec('attrib +h "'.$this->path.'/.yum"');
		}
		
		if(!file_exists($this->path.'/.yum/notes')){
			mkdir($this->path.'/.yum/notes', 0777, true);
			exec('attrib +h "'.$this->path.'/.yum"');
		}
		
		$this->files = array();
		$files = $this->getFiles();
		
		foreach($files As $file){
			$this->files[$file] = $this->getTokenFile($file);
		}
		
		return $this->saveProject();
	}
	
	function updateFiles(){
		 return $this->initialize();
	}
	
	function serializeFiles(){
		return base64_encode(serialize($this->files));
	}
	
	function saveProject(){
		if(file_put_contents($this->path.'/.yum/'.$this->project, $this->serializeFiles())){
			return true;
		}
		return false;
	}
	
	function unserializeFiles(){
		if($this->isInitialized()){
			$this->files = unserialize(base64_decode(file_get_contents($this->path.'/.yum/'.$this->project)));
			return true;
		}
		
		$this->files = array();
	}
	
	function saveInfo(){
		
	}
	
	function createPatch(){
		if(!file_exists($this->path.'/.yum/patch')) mkdir($this->path.'/.yum/patch', 0777);
		$zip = new ZipArchive();
		$r = 0;
		if($zip->open($this->path.'/.yum/patch/patch-'.time().'-'.date('Y-m-d').'.zip', ZIPARCHIVE::CREATE)) {
			$r = -1;
			if(count($this->getCreatedFiles()) > 0 | count($this->getModifiedFiles()) > 0 | count($this->getDeletedFiles()) > 0){
				$r = 1;
				foreach($this->getCreatedFiles() As $file){
					$new_file = preg_replace('/^\.\//', '', $file);
					$zip->addFile($file, $new_file);
				}
				
				foreach($this->getModifiedFiles() As $file){
					$new_file = preg_replace('/^\.\//', '', $file);
					$zip->addFile($file, $new_file);
				}
				
				// deleted files patch
				
				$delete_query = '';
				$deleted_files = $this->getDeletedFiles();
				sort($deleted_files);
				
				foreach($deleted_files As $file){
					$delete_query .= 'del /Q "'.$file.'"'.chr(10);
					if(count(@scandir(dirname($file))) <= 2){
						$delete_query .= 'rmdir /Q "'.dirname($file).'"'.chr(10);
					}
				}
				
				if(!empty($delete_query)){
					file_put_contents('delete_files.bat', $delete_query);
					$zip->addFile('delete_files.bat', 'delete_files.bat');
					
				}
				
			}
			
			$zip->close();
			@unlink('delete_files.bat');
		}
		
		return $r;
	}
	
	function getTokenFile($file){
		return sha1(file_get_contents($file));
	}
	
	/**
	 * 	Notes log functions 
	 */ 
	
	function addNote($file, $note){
		if(!file_exists($this->path.'/.yum/notes')){
			mkdir($this->path.'/.yum/notes', 0777, true);
		}
		
		$notes = $this->getFileNotes($file);
		
		$notes[] = array(
			'date' => date('Y-m-d'),
			'time' => date('H:i'),
			'note' => $note,
		);
		
		return $this->saveFileNotes($file, $notes);
	}
	
	function getFileNotes($file){
		if(file_exists($this->getNotesFile($file))){
			$notes = unserialize(base64_decode(file_get_contents($this->getNotesFile($file))));
			if(is_array($notes)) return $notes;
		}
		
		return array('__file' => $file);
	}
	
	function saveFileNotes($file, $notes){
		return file_put_contents($this->getNotesFile($file), base64_encode(serialize($notes))) ? true : false;
	}
	
	function getNotesFile($file){
		return $this->path.'/.yum/notes/'.sha1($file);
	}
	
	function getAllNotes(){
		$notes_files = scandir($this->path.'/.yum/notes');
		$notes = array();
		foreach($notes_files As $file){
			if($file == '.' | $file == '..') continue;
			$temp = unserialize(base64_decode(file_get_contents($this->path.'/.yum/notes/'.$file)));
			$filename = $temp['__file'];
			unset($temp['__file']);
			$notes[$filename] = $temp;
		}
		return $notes;
	}
	
	/**
	 *	DIRECTORY TREE 
	 */ 
	 
	function parseDirectory($data, $key, &$array){
		if($key >= count($data)){
			return;
		}
		$array[$data[$key]] = array();
		$this->parseDirectory($data, $key + 1, $array[$data[$key]]);
	}
	
	function getDirectoryTree(){
		$files = array_keys($this->getCurrentFiles());
		$directories = array();
		$final_dirs = array();
		foreach($files As $file){
			$directory = trim(preg_replace('/\.\/?/', '', dirname($file)));
			if(!isset($directories[$directory]) && !empty($directory)){
				$directories[$directory] = explode('/', $directory);
			}
		}

		$xdirectories = array();

		foreach($directories As $directory){
			foreach($directory As $key => $dirname){
				$this->parseDirectory($directory, 0, $array);
			}
			$xdirectories[] = $array;
		}
		
		$x = array();
		foreach($xdirectories As $directory){
			$x = array_merge_recursive($x, $directory);
		}
		
		return $x;

		//$this->getDirectoryList($x, '.', $content);

	}
	
	function getDirectoryList(){
		$this->parseDirectoryList($this->getDirectoryTree(), '_', $content);
		$content .= $this->getDirectoryFiles('.', $content);
		return $content;
	}
	
	function parseDirectoryList($array, $prev_key, &$content = ''){
		//$content .= '<ul>'."\n";
		foreach($array As $dirname => $subdirs){
			$content .= '<div class="accordion-group">
				<div class="accordion-heading">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion'.$prev_key.'" href="#collapse'.$prev_key.'_'.$dirname.'"><i class="icon-folder-close"></i> '.$dirname.'</a>
			</div>';
			
			$path = str_replace('_', '/', $prev_key.'_'.$dirname);
			$path = str_replace('//', $this->path.'/', $path);
			
			if(count($subdirs) > 0 | count($this->parseDirectoryFiles($path)) > 0){
				$content .= '<div id="collapse'.$prev_key.'_'.$dirname.'" class="accordion-body collapse">
			  <div class="accordion-inner">';
				if(count($subdirs) > 0){
					$this->parseDirectoryList($array[$dirname], $prev_key.'_'.$dirname, $content);
				}
				if(count($this->parseDirectoryFiles($path)) > 0){
					$this->getDirectoryFiles($path, $content);
				}
				
				$content .= '</div>
			</div>';
			}
			$content .= '</div>';
		}
		
		
		
		//$content .= '</ul>';
	}
	
	function getDirectoryFiles($path, &$content){
		//echo $path;
		$files = $this->parseDirectoryFiles($path);
		if(count($files) > 0){
			$content .= '<table class="special">';
			foreach($files As $file){
				/*$content .= '
					<tr>
						<td class="options" data-file="'.base64_encode($file).'"><i class="icon-file"></i> '.basename($file).'</td>
					</tr>';*/
				$content .= '
					<tr>
						<td class="options">
							<span class="dropup">
								<div data-toggle="dropdown">
									<i class="icon-file"></i> '.basename($file).'
								</div>
									<ul class="dropdown-menu">
										<li><a href="'.INSTALL_DIRECTORY.'/index.php/__admin/yum/add_note?file='.base64_encode($file).'"><i class="icon-pencil"></i> Add Note</a></li>
										<li><a href="'.INSTALL_DIRECTORY.'/index.php/__admin/yum/view_notes?file='.base64_encode($file).'"><i class="icon-retweet"></i> View All Notes</a></li>
									</ul>
								
							</span>
						</td>
					</tr>';
			}
			$content .= '</table>';
		}
		//$content .= $path;
	}
	
	function parseDirectoryFiles($path){
		$files = $this->getCurrentFiles();
		
		$x_files = array();
		foreach($files As $key => $file){
			
			if(dirname($key) == $path){
				$x_files[] = $key;
			}
		}
		
		return $x_files;
	}
}
