<?php
class Asignatura_Tarea extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'asignaturas_tareas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Asignatura',
			'class_name' => 'Asignatura',
		),
	);
	static $has_many = array(
		array(
			'archivos',
			'class_name' => 'Asignatura_Tarea_Archivo',
			'foreign_key' => 'tarea_id',
		),
	);
	static $has_one = array();
	
	static $validates_presence_of = Array();
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = Array();
	static $validates_uniqueness_of = Array();

	function getArchivos(){
		return Asignatura_Tarea_Archivo::find_all_by_tarea_id($this->id);
	}

	function before_destroy(){
		foreach($this->getArchivos() As $archivo){
			$archivo->delete();
		}
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
	   $data = !empty($this->archivos) ? unserialize($this->archivos) : array();
	   if(isset($data[$alumno_id])) return true;
	   return false;
   }
   
   function setUploadedFile($fileName, $alumno_id){
		$data = !empty($this->archivos) ? unserialize($this->archivos) : array();
		$data[$alumno_id] = $fileName;
		$this->archivos = serialize($data);
   }

	function removeFile($alumno_id){
		$data = !empty($this->archivos) ? unserialize($this->archivos) : array();
		@unlink('./Static/archivos/'.$data[$alumno_id]);
		unset($data[$alumno_id]);
		$this->archivos = serialize($data);
		return $this->save();
	}
   
   function getFile($alumno_id){
	   $data = !empty($this->archivos) ? unserialize($this->archivos) : array();
	   return $data[$alumno_id];
   }

   function getFileName($alumno_id){
	   $data = !empty($this->archivos) ? unserialize($this->archivos) : array();
	   return explode('_', $data[$alumno_id])[0];
   }

   function getFileReal($alumno_id){
	   $data = !empty($this->archivos) ? unserialize($this->archivos) : array();
	   return explode('_', $data[$alumno_id])[1];
   }

	function getEntregasAlumno($alumno_id, $tipo = 'ALUMNO'){
		return Asignatura_Tarea_Entrega::find_all_by_alumno_id_and_tarea_id_and_tipo($alumno_id, $this->id, $tipo, [
			'order' => 'fecha_hora DESC'
		]);
	}

	function getNota(Matricula $matricula){
		return Asignatura_Tarea_Nota::find_by_tarea_id_and_matricula_id($this->id, $matricula->id);
	}
}
