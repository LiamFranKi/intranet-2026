<?php
class HomeApplication extends Core\Application{
	
	function initialize(){
		$this->security('SecureSession', [
			'USUARIO_ID' => '*'
		]);
	}

	function dashboard_alumno(){
		$matricula = $this->USUARIO->alumno->getMatriculaByAnio($this->COLEGIO->anio_activo);
		$totalAsignaturas = !is_null($matricula) ? count($matricula->grupo->getAsignaturas()) : 0;
		$totalMatriculas = $this->USUARIO->alumno->getMatriculasCount();
		
		$examenes = Asignatura_Examen::all([
			'joins' => 'inner join asignaturas on asignaturas.id = asignaturas_examenes.asignatura_id',
			'conditions' => ['asignaturas.grupo_id = ? AND fecha_desde >= DATE(NOW())', $matricula->grupo->id]
		]);

		$tareas  = Asignatura_Tarea::all([
			'joins' => 'inner join asignaturas on asignaturas.id = asignaturas_tareas.asignatura_id',
			'conditions' => ['asignaturas.grupo_id = ? AND fecha_entrega >= DATE(NOW())', $matricula->grupo->id]
		]);

		$this->render([
			'totalAsignaturas' => $totalAsignaturas, 
			'totalMatriculas' => $totalMatriculas,
			'examenes' => $examenes,
			'tareas' => $tareas
		]);
	}

	function dashboard_apoderado(){
		$this->render();
	}
	function dashboard_docente(){

		$asignaturas = Asignatura::all(Array(
			'conditions' => Array('personal_id="'.$this->USUARIO->personal_id.'" AND grupos.anio="'.$this->COLEGIO->anio_activo.'"'),
			'joins' => array('grupo')

		));

		$examenes = Asignatura_Examen::all([
			'joins' => 'inner join asignaturas on asignaturas.id = asignaturas_examenes.asignatura_id
			inner join grupos on grupos.id = asignaturas.grupo_id',
			'conditions' => ['asignaturas.personal_id = ? AND fecha_desde >= DATE(NOW()) AND grupos.anio = ?', $this->USUARIO->personal_id, $this->COLEGIO->anio_activo]
		]);

		$tareas  = Asignatura_Tarea::all([
			'joins' => 'inner join asignaturas on asignaturas.id = asignaturas_tareas.asignatura_id
			inner join grupos on grupos.id = asignaturas.grupo_id',
			'conditions' => ['asignaturas.personal_id = ? AND fecha_entrega >= DATE(NOW()) AND grupos.anio = ?', $this->USUARIO->personal_id, $this->COLEGIO->anio_activo]
		]);

		$alumnos = $this->DB->query('SELECT distinct alumnos.id, COUNT(*) AS total from alumnos
			inner join matriculas on matriculas.alumno_id = alumnos.id
            inner join grupos on grupos.id = matriculas.grupo_id
			inner join asignaturas on asignaturas.grupo_id = grupos.id
            where asignaturas.personal_id = "'.$this->USUARIO->personal_id.'" and grupos.anio = "'.$this->COLEGIO->anio_activo.'"');

		$alumnos = $alumnos->fetch_object()->total;

		$this->render([
			'examenes' => $examenes,
			'tareas' => $tareas,
			'asignaturas' => $asignaturas,
			'alumnos' => $alumnos
		]);
	}

	function dashboard(){
		$totalHombres = Matricula::count([

			'joins' => 'inner join alumnos on alumnos.id = matriculas.alumno_id
						inner join grupos on grupos.id = matriculas.grupo_id',
			'conditions' => 'alumnos.sexo = 0 AND grupos.anio = "'.$this->COLEGIO->anio_activo.'"'
		]);

		$totalMujeres = Matricula::count([
			'joins' => 'inner join alumnos on alumnos.id = matriculas.alumno_id
						inner join grupos on grupos.id = matriculas.grupo_id',
			'conditions' => 'alumnos.sexo = 1 AND grupos.anio = "'.$this->COLEGIO->anio_activo.'"'
		]);

		$totalTrabajadores = Personal::count();
		$totalGrupos = Grupo::count([
			'conditions' => 'anio = "'.$this->COLEGIO->anio_activo.'"'
		]);


		$sqlTotalMatriculas = $this->DB->query('SELECT grupos.anio, alumnos.sexo, COUNT(matriculas.id) as total FROM `matriculas` inner join alumnos on alumnos.id = matriculas.alumno_id inner join grupos on grupos.id = matriculas.grupo_id group by grupos.anio, alumnos.sexo');
		$dataTotalMatriculas = [];
		$key = 0;
		while($totalMatricula = $sqlTotalMatriculas->fetch_object()){
			if($totalMatricula->sexo == 0)
				$dataTotalMatriculas[$totalMatricula->anio]['hombres'] = $totalMatricula->total;
			if($totalMatricula->sexo == 1)
				$dataTotalMatriculas[$totalMatricula->anio]['mujeres'] = $totalMatricula->total;

			$dataTotalMatriculas[$totalMatricula->anio]['total'] += $totalMatricula->total;
			++$key;
		}

		// INGRESOS
		$sqlIngresosAnuales = $this->DB->query('SELECT Year(fecha_cancelado) AS anio, SUM(monto + mora) AS total FROM `pagos` where estado_pago = "CANCELADO" AND fecha_cancelado != "0000-00-00" GROUP BY YEAR(fecha_cancelado)');
		$dataIngresosAnuales = [];
		while($ingresoAnual = $sqlIngresosAnuales->fetch_object()){
			$dataIngresosAnuales[$ingresoAnual->anio] = $ingresoAnual->total;
		}


		$sqlTotalAlumnosGrado = $this->DB->query('select nivel_id, grado, COUNT(*) as total from matriculas inner join grupos on grupos.id = matriculas.grupo_id where grupos.anio = '.$this->COLEGIO->anio_activo.' AND estado = 0 group by grupos.nivel_id, grupos.grado');
		$dataTotalAlumnosGrado = [];
		while($totalAlumnosGrado = $sqlTotalAlumnosGrado->fetch_object()){
			$dataTotalAlumnosGrado[$totalAlumnosGrado->nivel_id][$totalAlumnosGrado->grado] = $totalAlumnosGrado->total;
		}


		$sqlIngresosDeudasMes = $this->DB->query('SELECT nro_pago, estado_pago, SUM(monto+mora) as total FROM `pagos`
		inner join matriculas on matriculas.id = pagos.matricula_id
		inner join grupos on grupos.id = matriculas.grupo_id
		where grupos.anio = "'.$this->COLEGIO->anio_activo.'" and pagos.tipo = 1
		group by pagos.nro_pago, estado_pago');
		
		$dataIngresosDeudasMes = [];
		while($ingresoDeudaMes = $sqlIngresosDeudasMes->fetch_object()){
			$dataIngresosDeudasMes[$ingresoDeudaMes->nro_pago][$ingresoDeudaMes->estado_pago] = $ingresoDeudaMes->total;
		}

		//print_r($dataIngresosDeudasMes);
		
		
		$niveles = Nivel::all();

		$this->render([
			'totalHombres' => $totalHombres, 
			'totalMujeres' => $totalMujeres, 
			'totalTrabajadores' => $totalTrabajadores,
			'totalGrupos' => $totalGrupos,
			'dataTotalMatriculas' => $dataTotalMatriculas,
			'dataIngresosAnuales' => $dataIngresosAnuales,
			'dataTotalAlumnosGrado' => $dataTotalAlumnosGrado,
			'dataIngresosDeudasMes' => $dataIngresosDeudasMes,
			'niveles' => $niveles
		]);
	}

	function index($r){
		if($this->USUARIO->is(['ADMINISTRADOR', 'SECRETARIA'])){
			$url = '/home/dashboard';
		}
		if($this->USUARIO->is('ALUMNO')){
			$url = '/home/dashboard_alumno';
		}
		if($this->USUARIO->is('APODERADO')){
			$url = '/apoderados/hijos?tipo=NORMAL';
		}
		if($this->USUARIO->is('DOCENTE')){
			$url = '/home/dashboard_docente';
		}
        if($this->USUARIO->is('CAJERO')){
			$url = '/cash_accounts';
		}
        if($this->USUARIO->is('ASISTENCIA')){
			$url = '/grupos';
		}

		if($this->COLEGIO->bloquear_deudores == "SI"){
			$deudas = $this->USUARIO->getDeudas();
            //print_r($deudas);
			if(count($deudas) > 0 && $this->USUARIO->is('ALUMNO')){
				$this->session->DEUDAS = base64_encode(serialize($deudas));
				return header('Location: /usuarios/logout');
			}
		}

        $totalNotices = Comunicado::count([
            'conditions' => 'estado = "ACTIVO" AND show_in_home = 1'
        ]);
		

		$this->render(['url' => $url, 'totalNotices' => $totalNotices]);
	}


	function upload_image(){
        $imagen = uploadFile('upload', ['png', 'jpg', 'jpeg'], './Static/editor-images');
        if(!is_null($imagen)){
            echo json_encode([
                'url' => Config::get('current_url').'/Static/editor-images/'.$imagen['new_name']
            ]);
        }else{
            echo json_encode([
                'error' => [
                    'message' => 'No se pudo subir la imagen.'
                ]
            ]);
        }
    }

	function __NoSession(){
		header('Location: /login');
	}
}
