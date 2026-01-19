<?php
class Boleta_Categoria extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'boletas_categorias';
	static $connection = '';
	
	static $belongs_to = array();
	static $has_many = array(
		array(
			'conceptos',
			'class_name' => 'Boleta_Concepto',
			'foreign_key' => 'categoria_id',
		),
		array(
			'subcategorias',
			'class_name' => 'Boleta_Subcategoria',
			'foreign_key' => 'categoria_id',
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
}
