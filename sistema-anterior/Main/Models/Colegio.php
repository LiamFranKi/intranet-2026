<?php
class Colegio extends TraitConstants{
//	use Constants;

	static $pk = 'id';
	static $table_name = 'colegios';
	static $connection = 'admin';

	static $belongs_to = array(
		array(
			'pais',
			'class_name' => 'Pais',
		),
		array(
			'departamento',
			'class_name' => 'Departamento',
		),
		array(
			'provincia',
			'class_name' => 'Provincia',
		),
		array(
			'distrito',
			'class_name' => 'Distrito',
		),
	);
	static $has_many = array(
		array(
			'niveles',
			'class_name' => 'Nivel',
		),
		array(
			'turnos',
			'class_name' => 'Turno',
		),
		array(
			'alumnos',
			'class_name' => 'Alumno',
		),
		array(
			'sedes',
			'class_name' => 'Sede',
		),
	);
	static $has_one = array();

	static $validates_presence_of = array(
		array(
			'nombre',
		),
		array(
			'alias',
		),
	);
	static $validates_size_of = Array();
	static $validates_length_of = Array();
	static $validates_inclusion_of = Array();
	static $validates_exclusion_of = Array();
	static $validates_format_of = Array();
	static $validates_numericality_of = array();
	static $validates_uniqueness_of = array();

	function getComunicados(){
		$comunicados = Comunicado::find_all_by_colegio_id_and_estado($this->id, 'ACTIVO', array(

			'order' => 'fecha_hora DESC'
		));

		return $comunicados;
	}

	function getArchivosUsuario($tipo){
		$data = !empty($this->archivos_usuarios) ? unserialize(base64_decode($this->archivos_usuarios)) : array();
		return $data[$tipo];
	}

	function getVideoUsuario($tipo){
		$data = !empty($this->videos_usuarios) ? unserialize(base64_decode($this->videos_usuarios)) : array();
		return $data[$tipo];
	}

	function getVencimientoPension($pension){
		$data = !empty($this->pensiones_vencimiento) ? unserialize(base64_decode($this->pensiones_vencimiento)) : array();
		return $data[$pension];
	}

	function getVencimientoComedor($i){
		$fecha = $this->anio_activo.'-'.($i + 2).'-01';
		$vencimiento = date('Y-m-d', strtotime($fecha.' - 1 day'));
		return $vencimiento;
	}

	function getLoginBackground(){
		if(!empty($this->login_background) && file_exists($this->staticDirectory.'/Static/Image/Background/'.$this->login_background)){
			return $this->staticUrl.'/Static/Image/Background/'.$this->login_background;
		}

		return $this->staticUrl.'/Static/Image/Background/default.png';
	}

	function getAreas(){
		$areas = Area::all(array(
			'conditions' => 'colegio_id="'.$this->id.'"'
		));
		return $areas;
	}

	function getBloques(){
		$bloques = Bloque::all(array(
			'conditions' => 'colegio_id="'.$this->id.'"'
		));
		return $bloques;
	}

	function getAreasByNivel($nivel_id){
		$areas = Area::all(array(
			'conditions' => 'colegio_id="'.$this->id.'" AND nivel_id="'.$nivel_id.'"'
		));
		return $areas;
	}

	function getLoginInsignia(){
		if(!empty($this->login_insignia) && file_exists($this->staticDirectory.'/Static/Image/Insignias/'.$this->login_insignia)){
			return $this->staticUrl.'/Static/Image/Insignias/'.$this->login_insignia;
		}

		return $this->staticUrl.'/Static/Image/Insignias/default.png';
	}

	function getGrupos($anio = null, $sede_id = null){
		if(!isset($anio)) $anio = $this->anio_activo;
		if(is_null($sede_id)) $sede_id = 1;

		$grupos = Grupo::all(array(
			'conditions' => array('colegio_id="'.$this->id.'" AND anio="'.$anio.'" AND sede_id = "'.$sede_id.'"'),
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		));
		return $grupos;
	}

	function getGruposByNivel($nivel_id, $anio = null){
		if(!isset($anio)) $anio = $this->anio_activo;
		$grupos = Grupo::all(array(
			'conditions' => array('colegio_id="'.$this->id.'" AND anio="'.$anio.'" AND nivel_id="'.$nivel_id.'"'),
			'order' => 'nivel_id ASC, grado ASC, seccion ASC'
		));
		return $grupos;
	}

	function getCostos(){
		$costos = Costo::all(array(
			'conditions' => 'colegio_id="'.$this->id.'" AND tipo="GENERAL"'
		));

		return $costos;
	}

	function getGrupo($data, $create = false){
		$grupo = Grupo::find(array(
			'conditions' => 'colegio_id="'.$this->id.'" AND nivel_id="'.$data->nivel_id.'" AND grado="'.$data->grado.'" AND seccion="'.$data->seccion.'" AND anio="'.$data->anio.'" AND turno_id="'.$data->turno_id.'" AND sede_id = "'.$data->sede_id.'"'
		));
		if(!$grupo && $create === true){
			$grupo = Grupo::create(array(
				'colegio_id' => $this->id,
				'nivel_id' => $data->nivel_id,
				'grado' => $data->grado,
				'seccion' => $data->seccion,
				'anio' => $data->anio,
				'turno_id' => $data->turno_id,
				'sede_id' => $data->sede_id
			));
		}
		return $grupo;
	}

	function getOptionsNroPago(){
		$total = $this->total_pensiones;
		$options = array();
		$ciclo_pensiones = $this->getCicloPensiones();
		for($i=1; $i<=$total; $i++){
			$options[$i] = $ciclo_pensiones == 'Mes' ? $this->MESES[$i + $this->inicio_pensiones - 1] : ($ciclo_pensiones.' '.$i);
		}
		return $options;
	}

	function getOptionsCicloNotas(){
		$total = $this->total_notas;
		$options = array();
		$ciclo_notas = $this->getCicloNotas();
		for($i=1; $i<=$total; $i++){
			$options[$i] = $ciclo_notas == 'Mes' ? $this->MESES[$i + $this->inicio_notas - 1] : ($ciclo_notas.' '.$i);
		}
		return $options;
	}

	function getCicloNotasSingle($i){
		$ciclo_notas = $this->getCicloNotas();
		return $ciclo_notas == 'Mes' ? $this->MESES[$i + $this->inicio_notas - 1] : ($ciclo_notas.' '.$i);

		//return $this->CICLOS[$this->ciclo_notas];
	}

	function getCicloNotasSingleShort($i){
		$ciclo_notas = $this->getCicloNotas();
		$x_ciclo_notas = $ciclo_notas == 'Mes' ? $this->MESES[$i + $this->inicio_notas - 1] : ($ciclo_notas.' '.$i);
		if($ciclo_notas == 'Mes') return $x_ciclo_notas[0];
		return $x_ciclo_notas[0].' - '.$i;
	}

	function getCicloNotas(){
		return $this->CICLOS[$this->ciclo_notas];
	}

	function getCicloPensiones(){
		return $this->CICLOS[$this->ciclo_pensiones];
	}

	function getCicloPensionesSingle($i){
		$ciclo_pensiones = $this->getCicloPensiones();
		return ($ciclo_pensiones == 'Mes' ? $this->MESES[$i + $this->inicio_pensiones - 1] : ($ciclo_pensiones.' '.$i));
	}

	function searchGrupoForm($values = [], $showAll = false){
		
		$options = array(
			'sede_id' => array(
				'type' => 'select',
				'__options' => array($this->sedes, 'id', '$object->nombre'),
				'__dataset' => true,
				'class' => 'form-control'
			),
			'nivel_id' => array(
				'type' => 'select',
				'__options' => array($this->niveles, 'id', '$object->nombre'),
				'__dataset' => true,
				'class' => 'form-control'
			),
			'grado' => array(
				'type' => 'select',
				'class' => 'form-control'
			),
			'seccion' => array(
				'type' => 'select',
				'__options' => array_combine($this->SECCIONES, $this->SECCIONES),
				'__label' => 'Sección',
				':size' => 'col-sm-4',
				//'__first' => array('', '-- Seleccione --'),
				'data-bv-notempty' => 'true',
				'class' => 'form-control'
			),
			'anio' => array(
				'type' => 'text',
				'__label' => 'Año Académico',
				':size' => 'col-sm-3',
				'data-bv-integer' => 'true',
				'value' => $this->anio_activo,
				'data-bv-notempty' => 'true',
				'class' => 'form-control',
				'style' => 'width: 100px'
			),
			'turno_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->turnos, 'id', '$object->nombre'),
				'__label' => 'Turno',
				':size' => 'col-sm-4',
				//'__first' => array('', '-- Turno --'),
				'data-bv-notempty' => 'true',
				'class' => 'form-control'
			),
			'bloque_id' => array(
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->getBloques(), 'id', '$object->nombre." - ".$object->nivel->nombre'),
				'__label' => 'Bloque',
				'style' => 'width: 200px',
				'class' => 'form-control'
			),
			'modalidad' => [
				'type' => 'select',
				'__options' => ['PRESENCIAL' => 'PRESENCIAL', 'VIRTUAL' => 'VIRTUAL', 'SEMIPRESENCIAL' => 'SEMIPRESENCIAL'],
				'__first' => ['', '-- Tipo --'],
				'class' => 'form-control'
			]
		);

		if(!is_null($values['sede_id']))
			$options['sede_id']['value'] = $values['sede_id'];
		if(!is_null($values['nivel_id']))
			$options['nivel_id']['value'] = $values['nivel_id'];
		if(!is_null($values['grado']))
			$options['grado']['value'] = $values['grado'];
		if(!is_null($values['seccion']))
			$options['seccion']['value'] = $values['seccion'];
		if(!is_null($values['turno_id']))
			$options['turno_id']['value'] = $values['turno_id'];
		if(!is_null($values['anio']))
			$options['anio']['value'] = $values['anio'];

        if($showAll){
            $options['nivel_id']['__first'] = ["", "-- Todos --"];
            $options['grado']['__first'] = ["", "-- Todos --"];
            $options['seccion']['__first'] = ["", "-- Todos --"];
        }

		return $options;
	}

	function searchAlumnos($query = '', $limit = 50){
		if(empty($query)){
			$alumnos = Alumno::all(Array(
				'limit' => $limit,
				'conditions' => 'colegio_id="'.$this->id.'"',
				'order' => 'id DESC'
			));
		}else{
			$alumnos = Alumno::all(Array(
				'conditions' => 'colegio_id="'.$this->id.'" AND MATCH(apellido_paterno,apellido_materno,nombres) AGAINST("'.$query.'" IN BOOLEAN MODE) OR nombres LIKE "%'.$query.'%" OR apellido_paterno LIKE "%'.$query.'%" OR apellido_materno LIKE "%'.$query.'%" OR nro_documento LIKE "%'.$query.'%"'
			));
		}
		return $alumnos;
	}

	function searchApoderados($query = ''){
		if(empty($query)){
			$apoderados = Apoderado::all(Array(
				'limit' => 50,
				'conditions' => 'colegio_id="'.$this->id.'"',
				'order' => 'id DESC'
			));
		}else{
			$apoderados = Apoderado::all(Array(
				'conditions' => 'colegio_id="'.$this->id.'" AND MATCH(apellido_paterno,apellido_materno,nombres) AGAINST("'.$query.'" IN BOOLEAN MODE) OR nombres LIKE "%'.$query.'%" OR apellido_paterno LIKE "%'.$query.'%" OR apellido_materno LIKE "%'.$query.'%" OR nro_documento LIKE "%'.$query.'%"'
			));
		}
		return $apoderados;
	}

	function getCursos(){
		$cursos = Curso::all(array(
			'conditions' => 'colegio_id="'.$this->id.'"',
			'order' => 'orden ASC'
		));
		return $cursos;
	}

	function getCursosByNivel($nivel_id){
		$cursos = Curso::all(array(
			'conditions' => 'colegio_id="'.$this->id.'" AND nivel_id="'.$nivel_id.'"',
			'order' => 'orden ASC'
		));
		return $cursos;
	}

	function getNiveles(){
		$niveles = Nivel::all(array(
			'conditions' => 'colegio_id="'.$this->id.'"'
		));
		return $niveles;
	}

	function getActividades($from, $to){
		if(!isset($from) || !isset($to)){
			$actividades = Actividad::all(array(
				'conditions' => 'colegio_id="'.$this->id.'" AND MONTH(fecha_inicio) = "'.date('m').'"'
			));
		}else{

			$actividades = Actividad::all(array(
				'conditions' => 'colegio_id="'.$this->id.'" AND fecha_inicio BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
			));
		}

		return $actividades;
	}


	function getActividadesPersonal($from, $to, $personal_id, $tipo){
		if(!isset($from) || !isset($to)){
			$actividades = Actividad_Personal::all(array(
				'conditions' => 'colegio_id="'.$this->id.'" AND MONTH(fecha_inicio) = "'.date('m').'"'
			));
		}else{

			$actividades = Actividad_Personal::all(array(
				'conditions' => 'colegio_id="'.$this->id.'" AND fecha_inicio BETWEEN DATE("'.$from.'") AND DATE("'.$to.'")'
			));
		}

		$filtered = array();
		foreach($actividades As $actividad){
			$permissions = $actividad->getPermisos();
			if(in_array($personal_id, $permissions) || $tipo == 'ADMINISTRADOR' || $tipo == 'DIRECTOR'){
			//if(true){
				$filtered[] = $actividad;
			}
		}

		return $filtered;
	}

	function getTotalMatriculas($anio){
		$matriculas = Matricula::count(array(
			'conditions' => 'matriculas.colegio_id="'.$this->id.'" AND grupos.anio="'.$anio.'"',
			'joins' => array('grupo')
		));
		return $matriculas;
	}

	function getRutas($estado = null){
		$rutas = Ruta::all(array(
			'conditions' => 'colegio_id="'.$this->id.'"'.(isset($estado) ? ' AND estado="'.$estado.'"' : '')
		));
		return $rutas;
	}

	function getVehiculos($estado = null){
		$vehiculos = Vehiculo::all(array(
			'conditions' => 'colegio_id="'.$this->id.'"'.(isset($estado) ? ' AND estado="'.$estado.'"' : '')
		));
		return $vehiculos;
	}

	function getLibrosCategorias(){
		$categorias = Libro_Categoria::find_all_by_colegio_id($this->id, array(
			'order' => 'nombre ASC'
		));
		return $categorias;
	}

	function getLibrosAutores(){
		$autores = Libro_Autor::find_all_by_colegio_id($this->id, array(
			'order' => 'nombres ASC'
		));
		return $autores;
	}

	function getLibrosEditoriales(){
		$editoriales = Libro_Editorial::find_all_by_colegio_id($this->id, array(
			'order' => 'nombre ASC'
		));
		return $editoriales;
	}

	function parseLibrosConditions($params){
		$conditions = array(
			'conditions' => 'colegio_id="'.$this->id.'"',
			'order' => 'id DESC'
		);

		if(!empty($params->query)){
			$conditions['conditions'] .= ' AND MATCH(titulo, resumen, indice) AGAINST("'.$params->query.'" IN BOOLEAN MODE)';
		}

		if(!empty($params->editorial_id)){
			$conditions['conditions'] .= ' AND editorial_id="'.$params->editorial_id.'"';
		}

		if(!empty($params->categoria_id)) $conditions['conditions'] .= ' AND categoria_id="'.$params->categoria_id.'"';
		if(!empty($params->autor_id)) $conditions['conditions'] .= ' AND autor_id="'.$params->autor_id.'"';

		return $conditions;
	}

	function getLibrosVirtuales($params, $offset = 0, $per_page = 5){
		$conditions = $this->parseLibrosConditions($params);

		$count = Libro_Virtual::count($conditions);

		$conditions['offset'] = $offset;
		$conditions['limit'] = $per_page;

		$libros = Libro_Virtual::all($conditions);
		return array($libros, $count);
	}

	function getLibros($params, $offset = 0, $per_page = 5){
		$conditions = $this->parseLibrosConditions($params);

		$count = Libro::count($conditions);

		$conditions['offset'] = $offset;
		$conditions['limit'] = $per_page;

		$libros = Libro::all($conditions);
		return array($libros, $count);
	}

	function officeIntegrated(){
		return $this->integrar_office == 'SI';
	}

	function hasCalendar(){
		return !empty($this->direccion_calendario);
	}

	function getRangosCiclosNotas(){
		$data = empty($this->rangos_ciclos_notas) ? array() : unserialize($this->rangos_ciclos_notas);
		return $data;
	}


	function getInfracciones(){
		$infracciones = Infraccion::find_all_by_colegio_id($this->id);
		return $infracciones;
	}

	function getInfraccionesCategorias(){
		$categorias = Infraccion_Categoria::find_all_by_colegio_id($this->id, array(
			'order' => 'nombre ASC'
		));
		return $categorias;
	}

	function getPersonal(){
		$personal = Personal::find_all_by_colegio_id($this->id, array(
			'order' => 'apellidos ASC'
		));
		return $personal;
	}

	function getCajaCategorias(){
		return Caja_Categoria::find_all_by_colegio_id($this->id, array(
			'order' => 'nombre ASC'
		));
	}

	function getCajaConceptos(){
		return Caja_Concepto::find_all_by_colegio_id($this->id, array(
			'order' => 'descripcion ASC'
		));
	}


	function getCajaIngresos($categoria_id, $mes, $anio){
		return $this->getCajaRegistros($categoria_id, $mes, $anio, 'INGRESO');
	}
	function getCajaEgresos($categoria_id, $mes, $anio){
		return $this->getCajaRegistros($categoria_id, $mes, $anio, 'EGRESO');
	}

	function getCajaRegistros($categoria_id, $mes, $anio, $tipo){
		$registros = Caja_Registro::all(array(
			'conditions' => 'colegio_id="'.$this->id.'" AND categoria_id="'.$categoria_id.'" AND MONTH(fecha) = "'.$mes.'" AND YEAR(fecha) = "'.$anio.'" AND tipo="'.$tipo.'"'
		));
		return $registros;
	}

	function getCajaIngresosByDates($categoria_id, $from, $to){
		return $this->getCajaRegistrosByDates($categoria_id, $from, $to, 'INGRESO');
	}
	function getCajaEgresosByDates($categoria_id, $from, $to){
		return $this->getCajaRegistrosByDates($categoria_id, $from, $to, 'EGRESO');
	}

	function getCajaRegistrosByDates($categoria_id, $from, $to, $tipo){
		$registros = Caja_Registro::all(array(
			'conditions' => 'colegio_id="'.$this->id.'" AND categoria_id="'.$categoria_id.'" AND fecha BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND tipo="'.$tipo.'"'
		));
		return $registros;
	}

	function getAlertas($filters = array()){
		$conditions = 'colegio_id="'.$this->id.'"';
		if(isset($filters['tipo'])){
			$conditions .= ' AND tipo="'.$filters['tipo'].'"';
		}
		if(isset($filters['estado'])){
			$conditions .= ' AND estado="'.$filters['estado'].'"';
		}
		$alertas = Alerta::all(array(
			'conditions' => $conditions,
			'order' => 'nombre ASC'
		));
		return $alertas;
	}

	function getAlertasActivas(){
		$alertas = Alerta::all(array(
			'conditions' => 'colegio_id="'.$this->id.'" AND estado="ACTIVO"',
			'order' => 'nombre ASC'
		));
		return $alertas;
	}

	function getCurrentDirector(){
		// by username
		$usuario = Usuario::first(array(
			'conditions' => 'tipo="DIRECTOR"'
		));
		if(!is_null($usuario)) return $usuario->personal;

		// by personal

		$personal = Personal::find(array(
			'conditions' => 'cargo LIKE "%director%"'
		));

		if(!is_null($personal)) return $personal;
	}

	function getPublicSpacePersonal(){
		// by username
		/*
		$usuario = Usuario::first(array(
			'conditions' => 'tipo="DIRECTOR"'
		));
		*/
		//if(!is_null($usuario)) return $usuario->personal;
		//return null;
		return new Personal(array(
			'id' => -500
		));
	}

	function createSpacePersonal($id){
		if(!file_exists('./Static/Files/'.$id)) mkdir('./Static/Files/'.$id, 0777);
		return new Personal(array(
			'id' => $id
		));
	}

	function getTrabajadores(){
		$trabajadores = Trabajador::find_all_by_colegio_id($this->id, array(
			'order' => 'apellidos ASC'
		));
		return $trabajadores;
	}

	function getBoletasCategorias(){
		return Boleta_Categoria::find_all_by_colegio_id($this->id, array(
			'order' => 'nombre ASC'
		));
	}

	function getBoletasSubcategorias(){
		return Boleta_Subcategoria::find_all_by_colegio_id($this->id, array(
			'order' => 'nombre ASC'
		));
	}

	function getBoletasConceptos(){
		return Boleta_Concepto::find_all_by_colegio_id($this->id, array(
			'order' => 'descripcion ASC'
		));
	}

	function getBoletasConceptosEstandar(){
		return Boleta_Concepto::find_all_by_colegio_id_and_ocultar_and_categoria_id_and_controlar_stock($this->id, 'NO', 2, 'SI', array(

			'order' => 'descripcion ASC'
		));
	}

	function getBoletasConceptosAntiguo(){
		return Boleta_Concepto::find_all_by_colegio_id_and_ocultar_and_categoria_id_and_controlar_stock($this->id, 'SI', 2, 'SI', array(

			'order' => 'descripcion ASC'
		));
	}

	function getBoletaConceptosIngresos($concepto_id, $mes, $anio){
		$ingresos = Boleta_Detalle::all(array(
			'conditions' => 'boletas.colegio_id="'.$this->id.'" AND concepto_id="'.$concepto_id.'" AND YEAR(boletas.fecha) = "'.$anio.'" AND MONTH(boletas.fecha) = "'.$mes.'" AND boletas.transferencia_gratuita = "NO"',
			'joins' => '
				INNER JOIN boletas ON boletas.id = boletas_detalles.boleta_id
			'
		));

		return $ingresos;
	}

	function getBoletaConceptosIngresosByDates($concepto_id, $from, $to){
		$ingresos = Boleta_Detalle::all(array(
			'conditions' => 'boletas.colegio_id="'.$this->id.'" AND concepto_id="'.$concepto_id.'" AND boletas.fecha_pago BETWEEN DATE("'.$from.'") AND DATE("'.$to.'") AND estado = "ACTIVO" and estado_pago="CANCELADO" AND boletas.transferencia_gratuita = "NO"',
			'joins' => '
				INNER JOIN boletas ON boletas.id = boletas_detalles.boleta_id
			'
		));

		return $ingresos;
	}

	function getBoletas($params){
		//print_r($params);
		//AND boletas.transferencia_gratuita = "NO"
		$conditions = 'colegio_id="'.$this->id.'"';
		if($params->estado) $conditions .= ' AND estado="'.$params->estado.'"';
		if($params->fecha1 && $params->fecha2){
			$params->fecha1 = date('Y-m-d', strtotime($params->fecha1));
			$params->fecha2 = date('Y-m-d', strtotime($params->fecha2));
			$conditions .= ' AND fecha BETWEEN DATE("'.$params->fecha1.'") AND DATE("'.$params->fecha2.'")';
		}

		return Boleta::all(array(
			'order' => 'id ASC',
			'conditions' => $conditions
		));
	}

	function getBoletasIngresos($params){
		//print_r($params);
		$conditions = 'colegio_id="'.$this->id.'"';
		if($params->estado) $conditions .= ' AND estado="'.$params->estado.'"';
		if($params->fecha1 && $params->fecha2){
			$params->fecha1 = date('Y-m-d', strtotime($params->fecha1));
			$params->fecha2 = date('Y-m-d', strtotime($params->fecha2));
			$conditions .= ' AND fecha BETWEEN DATE("'.$params->fecha1.'") AND DATE("'.$params->fecha2.'")';
		}

		return Boleta_Ingreso::all(array(
			'order' => 'id ASC',
			'conditions' => $conditions
		));
	}

	function getObjetivosMatriculas($anio){
		$objetivo = Objetivo::find_by_colegio_id_and_anio($this->id, $anio);
		if(!$objetivo){
			$objetivo = Objetivo::create(array(
				'colegio_id' => $this->id,
				'anio' => $anio,
				'data' => base64_encode(serialize(array()))
			));
		}

		return $objetivo;
	}

	function getSaldoCaja($mes, $anio){
		// INGRESOS CAJA
		$ingresos = Caja_Registro::find(array(
			'select' => 'SUM(monto_total) As total',
			'conditions' => 'MONTH(fecha) = "'.$mes.'" AND YEAR(fecha) = "'.$anio.'" AND colegio_id="'.$this->id.'" AND tipo="INGRESO"',
		));
		// INGRESOS FACTURACIÓN
		$ingresos_facturacion = Boleta_Detalle::find(array(
			'select' => 'SUM(cantidad*precio) As total',
			'conditions' => 'MONTH(boletas.fecha) = "'.$mes.'" AND YEAR(boletas.fecha) = "'.$anio.'" AND boletas.colegio_id="'.$this->id.'"',
			'joins' => '
				INNER JOIN boletas ON boletas.id = boletas_detalles.boleta_id
			'
		));

		$egresos = Caja_Registro::find(array(
			'select' => 'SUM(monto_total) As total',
			'conditions' => 'MONTH(fecha) = "'.$mes.'" AND YEAR(fecha) = "'.$anio.'" AND colegio_id="'.$this->id.'" AND tipo="EGRESO"',
		));

		return $ingresos->total + $ingresos_facturacion->total - $egresos->total;
	}

	function getSaldoCajaUntilDate($fecha){
		$defaultSaldo = Caja_Saldo::find(array(
			'conditions' => 'colegio_id = "'.$this->id.'" AND fecha < DATE("'.$fecha.'")',
			'order' => 'id DESC',
			'limit' => 1
		));


		$fechaCondition = 'fecha < DATE("'.$fecha.'")';
		$fechaCondition2 = 'fecha_pago < DATE("'.$fecha.'")';

		$defaultMonto = 0;
		if(!is_null($defaultSaldo)){
			$fechaCondition .= 'AND fecha > DATE("'.$defaultSaldo->fecha.'")';
			$fechaCondition2 .= 'AND fecha_pago > DATE("'.$defaultSaldo->fecha.'")';
			$defaultMonto = $defaultSaldo->monto;
		}

		// INGRESOS CAJA
		$ingresos = Caja_Registro::find(array(
			'select' => 'SUM(monto_total) As total',
			'conditions' => $fechaCondition.' AND colegio_id="'.$this->id.'" AND tipo="INGRESO"',
		));
		// INGRESOS FACTURACIÓN
		$ingresos_facturacion = Boleta_Detalle::find(array(
			'select' => 'SUM(cantidad*precio) As total',
			'conditions' => $fechaCondition2.' AND boletas.colegio_id="'.$this->id.'" AND boletas.estado = "ACTIVO" AND boletas.estado_pago="CANCELADO"',
			'joins' => '
				INNER JOIN boletas ON boletas.id = boletas_detalles.boleta_id
			'
		));

		$egresos = Caja_Registro::find(array(
			'select' => 'SUM(monto_total) As total',
			'conditions' => $fechaCondition.' AND colegio_id="'.$this->id.'" AND tipo="EGRESO"',
		));

		return $defaultMonto + $ingresos->total + $ingresos_facturacion->total - $egresos->total;
	}

	function getSaldoCajaUntil($mes, $anio){

		$total = 0;
		for($i=1;$i<$mes;++$i){
			$saldo = $this->getSaldoCaja($i, $anio);
			$total += $saldo;
		}
		return $total;
	}

	function getRangosMensajes(){
		$rangos = empty($this->rangos_mensajes) ? array() : unserialize(base64_decode($this->rangos_mensajes));
		return $rangos;
	}

	function getRangoMensajeByNota($nota){
		$rangos = $this->getRangosMensajes();
		foreach($rangos As $rango){
			$xRango = explode('-', $rango['rango']);
			if($rango['rango'] == $nota) return $rango['mensaje'];
			if(count($xRango) == 2){
				if($nota >= $xRango[0] && $nota <= $xRango[1]) return $rango['mensaje'];
			}
		}
		return '';
	}

	function getRangosNotasPrimaria(){
		$rangos = empty($this->rangos_letras_primaria) ? array() : unserialize(base64_decode($this->rangos_letras_primaria));
		return $rangos;
	}

	function getLetraNotaPrimariaByNota($nota){
		$rangos = $this->getRangosNotasPrimaria();
		foreach($rangos As $rango){
			$xRango = explode('-', $rango['rango']);
			if($rango['rango'] == $nota) return $rango['letra'];
			if(count($xRango) == 2){
				if($nota >= $xRango[0] && $nota <= $xRango[1]) return $rango['letra'];
			}
		}
		return '-';
	}

	function getTodayBirthdays(){
		$personal = Personal::all(array(
			'conditions' => 'DAY(fecha_nacimiento) = DAY(NOW()) AND MONTH(fecha_nacimiento) = MONTH(NOW())  AND colegio_id="'.$this->id.'"'
		));

		$nombres = array();

		foreach($personal As $persona){
			$nombres[] = $persona->getFullName();
		}

		return $nombres;
	}

	function getMonthBirthdays($mes = null){
		$mesCondition = 'MONTH(NOW())';

		if(!is_null($mes)) $mesCondition = $mes;

		$personal = Personal::all(array(
			'conditions' => 'MONTH(fecha_nacimiento) = '.$mesCondition.' AND colegio_id="'.$this->id.'" AND fecha_nacimiento != "1969-12-31"',
			'order' => 'fecha_nacimiento ASC'
		));

		$nombres = array();

		foreach($personal As $persona){
			$nombres[] = array(
				'fecha' => $persona->fecha_nacimiento,
				'nombre' => $persona->getFullName()
			);
		}

		return $nombres;
	}

	function getImpresionNotasDebito(){
		$data = !empty($this->impresion_notas_debito) ? unserialize(base64_decode($this->impresion_notas_debito)) : array();
		return $data;
	}

	function getImpresionBoletas(){
		$data = !empty($this->impresion_boletas) ? unserialize(base64_decode($this->impresion_boletas)) : array();
		return $data;
	}

	function getTalleres(){
		$grupos_talleres = Grupo_Taller::all(array('conditions' => 'colegio_id="'.$this->id.'"'));
		return $grupos_talleres;
	}
}
