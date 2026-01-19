<?php
class Trabajo extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'trabajos';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'personal',
			'class_name' => 'Personal',
		),
		array(
			'grupo',
			'class_name' => 'Grupo',
		),
	);
	static $has_many = array();
	static $has_one = array();
	
	static $validates_presence_of = array(
		array(
			'descripcion',
		),
		array(
			'fecha_entrega',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();
	
	function after_create(){
		//@sendNotificationForTrabajo($this);
	}


	function removeFile($file){
		$archivos = $this->getFiles();
		//@unlink('./Static/Archivos/'.$archivos[$file]);
		unset($archivos[$file]);
		$this->archivos = base64_encode(serialize($archivos));
	}
	
	function getEntrega($matricula_id){
        $data = !empty($this->entregas) ? unserialize($this->entregas) : array();
        return in_array($matricula_id, $data);
   }
   
   function setView($alumno_id){
	   $data = !empty($this->visto) ? unserialize($this->visto) : array();
	   if(!in_array($alumno_id, $data)){
		   $data[] = $alumno_id;
		   $this->visto = serialize($data);
	   }
	   return $this->save();
   }
   
   function getView($alumno_id){
	   $data = !empty($this->visto) ? unserialize($this->visto) : array();
	   return in_array($alumno_id, $data);
   }
   
   function haveUploadedFile($alumno_id){
	   $data = !empty($this->archivos_subidos) ? unserialize($this->archivos_subidos) : array();
	   if(isset($data[$alumno_id])) return true;
	   return false;
   }
   
   function setUploadedFile($fileName, $alumno_id){
		$data = !empty($this->archivos_subidos) ? unserialize($this->archivos_subidos) : array();
		$data[$alumno_id] = $fileName;
		$this->archivos_subidos = serialize($data);
   }
   
   function getFile($alumno_id){
	   $data = !empty($this->archivos_subidos) ? unserialize($this->archivos_subidos) : array();
	   return $data[$alumno_id];
   }
   
   function canSendFile(){
	   $current = time();
	   $last = strtotime($this->fecha_entrega);
	   return ($last >= $current);
   }
}
