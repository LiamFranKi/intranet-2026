<?php
class Boleta_Subcategoria extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'boletas_subcategorias';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'categoria',
			'class_name' => 'Boleta_Categoria',
			'foreign_key' => 'categoria_id',
		),
	);
	static $has_many = array();
	static $has_one = array();
	
	static $validates_presence_of = Array();
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = Array();
	static $validates_uniqueness_of = Array();
}
