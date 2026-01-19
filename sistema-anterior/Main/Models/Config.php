<?php
class Config extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'config';
	static $connection = '';
	
	static $belongs_to = Array();
	static $has_many = Array();
	static $has_one = Array();
	
	static $validates_presence_of = array(
		array(
			'clave',
		),
		array(
			'valor',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array(
		array(
			'clave',
		),
	);


	static function get($key, $default = null){
		$config = Config::find_by_clave($key);
		
		if(!$config && !is_null($default)) return $default;

		return $config->valor;
	}

	static function set($key, $val = null){

		if(!is_array($key)){
			$data = [$key => $val];
		}else{
			$data = $key;
		}

		foreach($data As $key => $val){
			Config::table()->delete(['clave' => $key]);
			Config::create([
				'clave' => $key,
				'valor' => $val
			]);
		}
	}

}
