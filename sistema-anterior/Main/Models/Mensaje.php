<?php
class Mensaje extends TraitConstants{
//	use Constants;
	
	static $pk = 'id';
	static $table_name = 'mensajes';

	static $belongs_to = array(
		array(
			'Remitente',
			'class_name' => 'Usuario',
			'foreign_key' => 'remitente_id',
		),
		array(
			'Destinatario',
			'class_name' => 'Usuario',
			'foreign_key' => 'destinatario_id',
		),
	);
	static $has_many = array(
		array(
			'Archivos',
			'class_name' => 'Mensaje_Archivo',
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
    
    function sendToMail(){
        $from = $this->remitente->getEmail();
        $to = $this->destinatario->getEmail();
        
        if(!empty($from) && !empty($to)){
            $headers = 'From: '.$this->remitente->getFullName().' <'.$from.'>'."\n\r";
            $headers .= 'Content-Type: text/html; charset=utf-8'."\n\r";
            @mail($to, $this->asunto, $this->mensaje, $headers);
        }
    }
    
    function getExtract(){
		return substr(convert_html_to_text($this->mensaje), 0, 60).'...';
	}
}
