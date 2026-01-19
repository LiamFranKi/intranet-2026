<?php
class Alerta extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'alertas';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'colegio',
			'class_name' => 'Colegio',
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

	function pension(){
		return $this->tipo == 'PENSION';
	}

	function alreadySent($nroCuota, $fecha){
		$data = $this->getSenders();
		return isset($data[$nroCuota]['daysSent'][$fecha]);
	}

	function updateDaySent($nroCuota, $fecha){
		$data = $this->getSenders();
		$data[$nroCuota]['daysSent'][$fecha] = 'TRUE';
		$this->updateData($data);
	}

	function canBeSent($nroCuota){
		return $this->getTotalSent($nroCuota) < $this->cantidad;
	}

	function getTotalSent($nroCuota){
		$data = $this->getSenders();
		return count($data[$nroCuota]['daysSent']);
	}

	function getSenders(){
		$data = empty($this->senders) ? array() : unserialize(base64_decode($this->senders));
		return $data;
	}

	function updateData($data){
		$this->senders = base64_encode(serialize($data));
		$this->save();
	}

	function sendToDevice($apoderado, $matricula, $fecha, $merito_demerito = ''){
		$template = $this->parse($apoderado, $matricula->alumno, '', $fecha, $merito_demerito);
		$template = nl2br($template);

		$notificacion = Notificacion::create([
			'para' => 'USUARIO',
			'usuario_id' => 1176,
			'destinatario_id' => $apoderado->usuario->id,
			'asunto' => $this->asunto,
			'fecha_hora' => date('Y-m-d H:i:s'),
			'estado' => 'NO ENVIADO',
			'contenido' => $template,
		]);
	}

	function sendOther($apoderado, $matricula, $fecha, $merito_demerito = ''){
		$template = $this->parse($apoderado, $matricula->alumno, '', $fecha, $merito_demerito);
		$headers = 'Content-Type: text/plain; charset=utf-8;'."\r\n";
		$headers .= 'From: Colegios VanguardSchools <'.$this->email_remitente.'>'."\r\n";
		//$ms = '===================================='."\n\r";
		$ms = $template."\r\n";
		//$ms .= '===================================='."\n\r";
		if(empty($apoderado->email)) return false;
		return @mail($apoderado->email, $this->asunto,$ms,$headers);
	}

	function send($apoderado, $matricula, $mes, $fecha){
		$template = $this->parse($apoderado, $matricula->alumno, $mes, $fecha, '');
		$headers = 'Content-Type: text/plain; charset=utf-8;'."\r\n";
		$headers .= 'From: Colegios VanguardSchools <'.$this->email_remitente.'>'."\r\n";
		//$ms = '===================================='."\n\r";
		$ms = $template."\r\n";
		//$ms .= '===================================='."\n\r";
		if(empty($apoderado->email)) return false;
		return @mail($apoderado->email, $this->asunto,$ms,$headers);

	}

	function parse($apoderado, $alumno, $mes, $fecha, $merito_demerito){
		$contenido = $this->contenido;
		$contenido = str_replace('%NombreApoderado%', $apoderado->getFullName(), $contenido);
		$contenido = str_replace('%NombreAlumno%', $alumno->getFullName(), $contenido);
		$contenido = str_replace('%pension%', $this->colegio->getCicloPensionesSingle($mes), $contenido);
		$contenido = str_replace('%fecha%', $this->colegio->parseFecha($fecha), $contenido);
		$contenido = str_replace('%MeritoDemerito%', $merito_demerito, $contenido);
		return $contenido;
	}
	
}
