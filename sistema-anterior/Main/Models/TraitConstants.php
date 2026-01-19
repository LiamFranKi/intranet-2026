<?php
class TraitConstants extends ActiveRecord\Model{

	public $staticUrl = '';
	public $staticDirectory = '.';

	public $TIPOS_DOCUMENTO = array('DNI', 'Pasaporte', 'Partida de Nacimiento', 'Libreta Electoral', 'Libreta Militar', 'Carnet de Extranjeria', 'Licencia de Conducir', 'Tarjeta de Seguro', 'Otro');
	public $TIPOS_DOCUMENTO_INFOCORP = [0 => 1, 5 => 3, 1 => 4];
	public $TIPOS_DOCUMENTO_FACTURACION = [
		0 => "1",
		1 => "7", 
		5 => "4"
	];


	public $SEXOS = array('Masculino', 'Femenino');
	public $ESTADOS_CIVIL = array('Soltero', 'Casado', 'Divorciado', 'Conviviente', 'Viudo');
	public $MESES = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre');
	public $DIAS = array('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes');
	public $DIAS_ALL = array('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');

	public $LINEAS_CELULAR = array('Ninguno', 'Claro', 'Movistar');
	public $TIPOS_CONTRATO = array('Nombrado', 'Contratado', 'Servicios', 'Otros');
	public $GRADOS_INSTRUCCION = array('Primaria Incompleta', 'Primaria Completa', 'Secundaria Incompleta', 'Secundaria Completa', 'Superior Incompleta', 'Superior Completa');
	public $LENGUA_MATERNA = array('Castellano', 'Quechua', 'Aymara');
	public $SEGUNDA_LENGUA = array('Ninguna', 'Castellano', 'Quechua', 'Aymara');
	public $TIPO_SANGRE = array('A', 'B', 'AB', 'O');
	public $RELIGIONES = array('Anabaptismo','Anglicanismo','Budismo','Calvinismo','Catolicismo o Iglesia católica apostólica romana','Chiísmo o Shiísmo','Comunidad de Cristo (RLDS, ex Iglesia Reorganizada de Jesucristo de los Santos de los Últimos Días)','Cristianismo','Cristianismo primitivo','Cuáqueros (oficialmente: Sociedad de los Amigos)','Divisiones contemporáneas','Drusismo','Esenios','Fariseos','Iglesia Adventista del Séptimo Día','Iglesia Cristiana Integral','Iglesia Presbiteriana','Iglesia Valdense','Iglesia bautista','Iglesia de Jesucristo de los Santos de los Últimos Días (LDS)','Iglesia maronita','Iglesia ortodoxa copta','Iglesia ortodoxa etíope','Iglesia ortodoxa griega','Iglesias orientales','Islam','Ismailismo','Jansenismo','Jariyismo','Judaísmo','Judaísmo mesiánico','Judaísmo ortodoxo','Luteranismo','Metodismo','Milenaristas','Mormonismo','Movimiento rastafari','Nestorianismo','Pentecostalismo','Protestantismo','Sectas históricas','Sufismo','Sunismo','Testigos de Jehová','Unitarismo','Universalismo');
	public $ESTADO_NACIMIENTO = array('Normal', 'Con Complicaciones');
	public $ACTIVIDADES_NACIMIENTO = array('Levantó la cabeza', 'Se Sentó', 'Se Paró', 'Caminó', 'Controlo sus esfínteres', 'Habló las primeras palabras', 'Habló con fluidez');
	public $DISCAPACIDADES = array('DI' => 'Discapacidad Intelectual', 'DA' => 'Discapacidad Auditiva', 'DV' => 'Discapacidad Visual', 'DM' => 'Discapacidad Motora', 'OT' => 'Otra');
	public $TIPOS_USUARIO = array('ADMINISTRADOR', 'DIRECTOR', 'ALUMNO', 'APODERADO', 'DOCENTE', 'AUXILIAR', 'SECRETARIA', 'CAJERO', 'ENFERMERA', 'PROMOTORIA', 'COORDINADOR', 'PSICOLOGA');
    
    public const ALLOWED_USER_TYPES = ["ADMINISTRADOR", "ALUMNO", "APODERADO", "DOCENTE", "CAJERO", "SECRETARIA", "ASISTENCIA"];

	public $TIPOS_USUARIO_KEYS = array('ADMINISTRADOR' => -101, 'DIRECTOR' => -102, 'ALUMNO' => -103, 'APODERADO' => -104, 'DOCENTE' => -105, 'AUXILIAR' => -106, 'SECRETARIA' => -107, 'CAJERO' => -108, 'ENFERMERA' => -109, 'PROMOTORIA' => -110, 'COORDINADOR' => -111, 'PSICOLOGA' => -112);

    public $SEGUROS = [1 => "SIS", 2 => "ESSALUD", 3 => "EPS", 4 => "OTRO", 5 => "NO TIENE"];

	public $CICLOS = array('Mes', 'Bimestre', 'Trimestre', 'Semestre');

	public $TIPOS_CALIFICACION = array('Cualitativa', 'Cuantitativa');
	public $TIPOS_CALIFICACION_FINAL = array('Promedio', 'Porcentaje');

	public $APODERADO_PARENTESCOS = array('Padre', 'Madre', 'Apoderado');

	public $TIPOS_ADMINISTRADOR = array('ADMINISTRADOR_PRINCIPAL');
	public $ESTADOS_ADMINISTRADOR = array('Activo', 'Inactivo');

	public $SECCIONES = array('UNICA', 'A', 'B', 'C', 'D', 'F', 'G', 'H', 'EFICIENCIA', 'COMPAÑERISMO', 'PACIENCIA',
					'LEALTAD', 'HUMILDAD', 'ASERTIVIDAD', 'LIBERTAD', 'HONESTIDAD', 'SUPERACIÓN', 'RESILIENCIA',
					'RESPETO', 'PERSEVERANCIA', 'HUMILDAD', 'SOLIDARIDAD', 'VALENTIA', 'TOLERANCIA', 'FELICIDAD', 'AMISTAD');

	public $ESTADOS_MATRICULA = array('REGULAR', 'IRREGULAR', 'RETIRADO', 'TRASLADO', 'TEMPORAL');

	public $TIPOS_PAGO = array('Matrícula', 'Pensión', 'Otros', 'Comedor');

	public $ALUMNOS_ORDER = 'REPLACE(REPLACE(alumnos.apellido_paterno, "ñ", "nz"), "Ñ", "NZ") ASC, REPLACE(REPLACE(alumnos.apellido_materno, "ñ", "nz"), "Ñ", "NZ") ASC';

	public $DEFINICIONES_GRADO = array('Grado', 'Años');

	public $TIPOS_VEHICULO = array('Institución', 'Particular', 'Tercero');

	public $TALLER_CATEGORIAS = [1 => 'Niños', 2 => 'Adultos'];

	public $GRADOS = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6];

    public const CASH_ACCOUNT_PRIVACY = [1 => 'PÚBLICO', 2 => 'PRIVADO'];

    public const ALL_CICLES = [0 => 'TODOS', 1 => 'BIM. 1', 2 => 'BIM. 2', 3 => 'BIM. 3', 4 => 'BIM. 4'];
    public const CICLES = [1 => 'BIM. 1', 2 => 'BIM. 2', 3 => 'BIM. 3', 4 => 'BIM. 4'];

	function isActivo(){
		return ($this->getEstado() == 'Activo');
	}

	function roman($i){
		$romans = array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X');
		return $romans[$i - 1];
	}

	function getDiscapacidad(){
		return $this->DISCAPACIDADES[$this->discapacidad];
	}

	function getLenguaMaterna(){
		return $this->LENGUA_MATERNA[$this->lengua_materna];
	}

	function getSegundaLengua(){
		return $this->SEGUNDA_LENGUA[$this->segunda_lengua];
	}

	function getTipoDocumento(){
		return $this->TIPOS_DOCUMENTO[$this->tipo_documento];
	}

	function getSexo(){
		return $this->SEXOS[$this->sexo];
	}

	function getEstadoCivil(){
		return $this->ESTADOS_CIVIL[$this->estado_civil];
	}

	function getReligion(){
		return $this->RELIGIONES[$this->religion];
	}

	function getFechaNacimiento(){
		return $this->getFecha($this->fecha_nacimiento);
	}

	function getFechaInscripcion(){
		return $this->getFecha($this->fecha_inscripcion);
	}

	function getFechaIngreso(){
		return $this->getFecha($this->fecha_ingreso);
	}

	function getFechaRegistro(){
		return $this->getFecha($this->fecha_registro);
	}

	function getLineaCelular(){
		return $this->LINEAS_CELULAR[$this->linea_celular];
	}

	function getFullName(){
		return mb_strtoupper(trim($this->apellido_paterno).' '.trim($this->apellido_materno), 'utf-8').', '.ucwords(mb_strtolower(trim($this->nombres), 'utf-8'));
	}

	function getShortName(){
		$nombre = explode(' ', $this->nombres)[0];

		return ucwords(mb_strtolower($nombre.' '.$this->apellido_paterno, 'utf-8'));

	}

	function getFoto(){
		if(!empty($this->foto) && file_exists($this->staticDirectory.'/Static/Image/Fotos/'.$this->foto)){
			return $this->staticUrl.'/Static/Image/Fotos/'.$this->foto;
		}
		return $this->staticUrl.'/Static/Image/Fotos/default.png';
	}

	function getArchivo(){
		if(!empty($this->archivo) && file_exists($this->staticDirectory.'/Static/Documentos/'.$this->archivo)){
			return $this->staticUrl.'/Static/Documentos/'.$this->archivo;
		}
	}

	function getParentesco(){
		return $this->APODERADO_PARENTESCOS[$this->parentesco];
	}

	function uploadPhoto($image){
		if($image['error'] == UPLOAD_ERR_OK){
			$info = pathinfo($image['name']);
			$newName = getToken().'.'.$info['extension'];
			if(move_uploaded_file($image['tmp_name'], $this->staticDirectory.'/Static/Image/Fotos/'.$newName)){
				$this->foto = $newName;
				$this->save();
			}
		}
	}

	function uploadDocuments($documentos, $descripciones){
		if(count($documentos) > 0){
			for($key=0;$key<count($documentos);$key++){
				if($documentos['error'][$key] != 4){
					if(preg_match('/\.(.*)$/', $documentos['name'][$key], $s)){
						$newName = sha1(time()*rand(1,99)).'.'.$s[1];
						if(move_uploaded_file($documentos['tmp_name'][$key],$this->staticDirectory.'/Static/Documentos/'.$newName)){
							Alumno_Documento::create(Array(
								'descripcion' => $descripciones[$key],
								'archivo' => $newName,
								'alumno_id' => $this->id
							));
						}
					}
				}
			}
		}
	}

	function getFechaHora($fecha_hora = null){
		if(!isset($fecha_hora)) $fecha_hora = $this->fecha_hora;
		return date('d-m-Y h:i A', strtotime($fecha_hora));
	}

	function getFecha($fecha = null){
		if(!$fecha) $fecha = $this->fecha;
		return date('d-m-Y', strtotime($fecha));
	}

	function setFecha($fecha){
		return date('Y-m-d', strtotime($fecha));
	}

	function setFechaHora($fecha){
		return date('Y-m-d H:i', strtotime($fecha));
	}

	function parseFecha($fecha){

		//$fecha = $this->setFecha($fecha);
		$fecha = date('Y-m-d', strtotime($fecha));

		$fecha = explode('-', $fecha);
		return $fecha[2].' de '.$this->MESES[intval($fecha[1]) - 1].' del '.$fecha[0];
	}

	function parseFechaNoYear($fecha){

		//$fecha = $this->setFecha($fecha);
		$fecha = date('Y-m-d', strtotime($fecha));

		$fecha = explode('-', $fecha);
		return $fecha[2].' de '.$this->MESES[intval($fecha[1]) - 1];
	}

	function parseHora($hora = null){

		return date('h:i A', strtotime($hora));
	}

	function getGradoInstruccion(){
		return $this->GRADOS_INSTRUCCION[$this->grado_instruccion];
	}

	function getFiles(){
		$data = !empty($this->archivos) ? unserialize(base64_decode($this->archivos)) : array();
		return $data;
	}

	function getPortada(){
		if(!empty($this->portada) && file_exists('./Static/Image/Portadas/'.$this->portada)){
			return '/Static/Image/Portadas/'.$this->portada;
		}
		return '/Static/Image/Portadas/d5b0c4b99290efee72dc706f64e2dd8eef5cf29f.jpg';
	}

	function getAutoresString(){
		if(empty($this->autores)) return '';
		$autores_id = explode(', ', $this->autores);
		$autores = array();
		foreach($autores_id As $id){
			$autor = Libro_Autor::find_by_id($id);
			if($autor)
				$autores[] = $autor->nombres;
		}
		return implode(', ', $autores);
	}

	function getCategoriasString(){
		if(empty($this->categorias)) return '';
		$categorias_id = explode(', ', $this->categorias);
		$categorias = array();
		foreach($categorias_id As $id){
			$categoria = Libro_Categoria::find($id);
			if($categoria)
				$categorias[] = $categoria->nombre;
		}
		return implode(', ', $categorias);
	}
}
