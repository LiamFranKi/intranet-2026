<?php
class Carpeta extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'carpetas';
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

	function checkGrupo($grupo_id){
		return in_array($grupo_id, $this->getPermisos());
	}

	function getPermisos(){
		$permisos = !empty($this->permisos) ? unserialize(base64_decode($this->permisos)) : array();
		return $permisos;
	}

	function getFileManagerToken(){
		return sha1(base64_encode(base64_encode($this->id)));
	}

	function validFileManagerToken($token){
		return $this->getFileManagerToken() == $token;
	}

	function getFileManagerDirectory(){
		$directory = './Static/CustomFiles/'.$this->id;
		if(!file_exists($directory)) mkdir($directory, 0777, true);
		return $directory;
	}
}
