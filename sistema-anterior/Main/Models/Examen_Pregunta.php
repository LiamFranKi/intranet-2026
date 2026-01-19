<?php
class Examen_Pregunta extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'examenes_preguntas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'examen',
			'class_name' => 'Examen',
		),
	);
	static $has_many = array(
		array(
			'alternativas',
			'class_name' => 'Examen_Pregunta_Alternativa',
			'foreign_key' => 'pregunta_id',
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

	function getAlternativas(){
		$alternativas = Examen_Pregunta_Alternativa::find_all_by_pregunta_id($this->id, array(
			'order' => 'id ASC'
		));
		return $alternativas;
	}

}
