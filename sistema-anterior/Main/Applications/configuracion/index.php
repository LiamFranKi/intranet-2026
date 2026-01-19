<?php
class ConfiguracionApplication extends Core\Application{
	public $beforeFilter = array('__checkSession', '__checkObjetivos');

	function __checkSession(){
		$this->security('SecureSession', array(
			'ADMINISTRADOR' => '*',
			'DIRECTOR' => 'objetivos_matriculas_resultados',
			'PROMOTORIA' => 'objetivos_matriculas_resultados',
			'SECRETARIA' => 'objetivos_matriculas_resultados',
			'CAJERO' => 'objetivos_matriculas_resultados',
			'__exclude' => 'objetivos_matriculas_resultados'
		));
	}

	function __checkObjetivos(){
		if(preg_match('/^objetivos/', $this->params->Action)){
			$this->anio = $this->COLEGIO->anio_activo;
			$this->objetivo = $this->COLEGIO->getObjetivosMatriculas($this->anio);
			$this->context('anio', $this->anio);
			$this->context('objetivo', $this->objetivo);
		}
	}

	function index($r){
		$form = $this->__getForm($this->COLEGIO);

		$this->render(array('form' => $form));
	}



	function do_objetivos_matriculas(){
		$objetivo = Objetivo::find($this->post->objetivo_id);
		$objetivo->data = is_array($this->post->data) ? base64_encode(serialize($this->post->data)) : base64_encode(serialize(array()));
		$r = $objetivo->save() ? 1 : 0;
		echo json_encode(array($r));
	}

	function save(){
		if(!isset($this->post->rangos_mensajes)) $this->post->rangos_mensajes = array();

		$rangos_mensajes = base64_encode(serialize($this->post->rangos_mensajes));
		$this->COLEGIO->set_attributes(array(
			'titulo_intranet' => $this->post->titulo_intranet,
			'codigo_modular' => $this->post->codigo_modular,
			'resolucion_creacion' => $this->post->resolucion_creacion,
			'ugel_codigo' => $this->post->ugel_codigo,
			'ugel_nombre' => $this->post->ugel_nombre,
			'anio_activo' => $this->post->anio_activo,
            'anio_matriculas' => $this->post->anio_matriculas,
			'ciclo_pensiones' => $this->post->ciclo_pensiones,
			'inicio_pensiones' => $this->post->inicio_pensiones,
			'total_pensiones' => $this->post->total_pensiones,
			'moneda' => $this->post->moneda,
			'monto_adicional' => $this->post->monto_adicional,
			'ciclo_notas' => $this->post->ciclo_notas,
			'inicio_notas' => $this->post->inicio_notas,
			'total_notas' => $this->post->total_notas,
			'rangos_ciclos_notas' => serialize($this->post->rangos_ciclos_notas),
			'rangos_mensajes' => $rangos_mensajes,
			'rangos_letras_primaria' => base64_encode(serialize($this->post->rangos_notas_primaria)),
			'pensiones_vencimiento' => base64_encode(serialize($this->post->pensiones_vencimiento)),
			'dias_tolerancia' => $this->post->dias_tolerancia,
			'ruc' => $this->post->ruc,
			'razon_social' => $this->post->razon_social,
			'direccion' => $this->post->direccion,
			'comision_tarjeta_debito' => $this->post->comision_tarjeta_debito,
			'comision_tarjeta_credito' => $this->post->comision_tarjeta_credito,
			'clave_bloques' => $this->post->clave_bloques,
			'bloquear_deudores' => $this->post->bloquear_deudores,
		));

		$login_fondo = uploadFile('login_fondo', ['jpg', 'jpeg', 'png'], './Static/Image/Fondos');
		if(!is_null($login_fondo)){
			Config::set('login_fondo', $login_fondo['new_name']);
		}

		$libreta_fondo = uploadFile('libreta_fondo', ['jpg', 'jpeg', 'png'], './Static/Image/Fondos');
		if(!is_null($libreta_fondo)){
			Config::set('libreta_fondo', $libreta_fondo['new_name']);
		}

		$libreta_logo = uploadFile('libreta_logo', ['jpg', 'jpeg', 'png'], './Static/Image/Fondos');
		if(!is_null($libreta_logo)){
			Config::set('libreta_logo', $libreta_logo['new_name']);
		}

        $boleta_logo = uploadFile('boleta_logo', ['jpg', 'jpeg', 'png'], './Static/Archivos');
		if(!is_null($boleta_logo)){
			Config::set('boleta_logo', $boleta_logo['new_name']);
		}

		Config::set('texto_intranet', $this->post->texto_intranet);

		Config::set('recaudo_nro_cuenta', $this->post->recaudo_nro_cuenta);
		Config::set('recaudo_nro_sucursal', $this->post->recaudo_nro_sucursal);
		Config::set('recaudo_razon_social', $this->post->recaudo_razon_social);

		Config::set('email_notificacion_matricula_online', $this->post->email_notificacion_matricula_online);
        Config::set('titulo_formulario_matricula', $this->post->titulo_formulario_matricula);
        Config::set('remitente_emails', $this->post->remitente_emails);
        Config::set('email_matricula_apoderado', $this->post->email_matricula_apoderado);
        Config::set('link_consulta_facturas', $this->post->link_consulta_facturas);
        Config::set('show_birthday_window', $this->post->show_birthday_window);
        Config::set('enable_enrollment_form', $this->post->enable_enrollment_form);

		$r = -5;
		if($this->COLEGIO->is_valid()){
			$r = $this->COLEGIO->save() ? 1 : 0;
		}

		echo json_encode(array($r, 'errors' => $this->COLEGIO->errors->get_all()));
	}

    function criterios(){
        $criterios_data = Config::get('criterios_globales');
        $criterios = !empty($criterios_data) ? json_decode($criterios_data) : [];

        $this->crystal->load('Form:*');
		$form = new Form(null, $this->COLEGIO->searchGrupoForm((array) $this->get, true));

        $this->render(['criterios' => $criterios, 'form' => $form]);
    }

    function save_criterios(){
        $conditions = "grupos.sede_id = '".$this->post->sede_id."'";
        if($this->post->nivel_id != "")
            $conditions .= " AND grupos.nivel_id = '".$this->post->nivel_id."'";

        if($this->post->grado != "")
            $conditions .= " AND grupos.grado = '".$this->post->grado."'";

        if($this->post->anio != "")
            $conditions .= " AND grupos.anio = '".$this->post->anio."'";

        $asignaturas = Asignatura::all(array(
			'conditions' => $conditions,
			'joins' => 'INNER JOIN grupos ON grupos.id = asignaturas.grupo_id'
		));
        $criterios = $this->post->criterio;
        if(count($criterios) > 0){
            foreach($asignaturas As $asignatura){
                Asignatura_Criterio::table()->delete(array(
                    'asignatura_id' => $asignatura->id
                ));
                foreach($criterios As $key_order => $d){
                    $criterio = Asignatura_Criterio::create(array(
                        'colegio_id' => $this->COLEGIO->id,
                        'descripcion' => $d['descripcion'],
                        'asignatura_id' => $asignatura->id,
                        'ciclo' => $d['ciclo'],
                        'abreviatura' => $d['abreviatura'],
                        'orden' => $key_order,
                        'peso' => $d['peso'],
                    ));

                    if($criterio->save() && $d['cuadros'] > 0){
                        Asignatura_Indicador::table()->delete(array(
                            'criterio_id' => $criterio->id
                        ));
                        $indicador = new Asignatura_Indicador(array(
                            'criterio_id' => $criterio->id,
                            'descripcion' => 'GENERAL',
                            'cuadros' => $d['cuadros']
                        ));
                        $indicador->save();
                    }
                }
            }

            Config::set("criterios_globales", json_encode($criterios));
        }
            

        //print_r($this->post);
        echo json_encode([1]);
    }

	function reiniciar_accesos(){
		$alumnos = Alumno::all();
		foreach($alumnos As $alumno){
			if(!$alumno->usuario)
				$alumno->generateUser();

			$alumno->resetUser();
		}

		$apoderados = Apoderado::all();
		foreach($apoderados As $apoderado){
			if(!$apoderado->usuario)
				$apoderado->generateUser();

			$apoderado->resetUser();
		}

		echo json_encode([1]);
	}

	private function __getForm($o){
		$this->crystal->load('Form:*');
		$options = array(
			'titulo_intranet' => array(
				'__label' => 'Título / Nombre del Colegio',
				'class' => 'tip form-control',
				'title' => 'Aparecera como título del intranet y el nombre en documentos'
			),
			'bloquear_deudores' => [
				'class' => 'form-control'
			],
			'codigo_modular' => array(
				'__label' => 'Código Modular',
				':size' => 'col-lg-2 col-xs-12',
				'class' => 'form-control'
			),
			'resolucion_creacion' => array(
				'__label' => 'Resolución de Creación',
				':size' => 'col-sm-4',
				'class' => 'form-control'
			),
			'login_fondo' => [
				'class' => 'form-control',
				'type' => 'file'
			],
			'ugel_codigo' => array(
				'__label' => 'Código (UGEL - DRE)',
				':size' => 'col-sm-4',
				'class' => 'form-control'
			),
			'ugel_nombre' => array(
				'__label' => 'Nombre (UGEL - DRE)',
				':size' => 'col-sm-6',
				'class' => 'form-control'
			),
			'ciclo_pensiones' => array(
				'__label' => 'Ciclo de Pensiones',
				'class' => 'tip form-control',
				'title' => 'Seleccione cada cuanto se cobra una pensión.',
				'type' => 'select',
				'__options' => $o->CICLOS,
				':size' => 'col-sm-4'
			),
			'inicio_pensiones' => array(
				'__label' => 'Inicio de Cobros',
				'class' => 'tip form-control',
				'title' => 'Seleccione el mes en el que se inician los cobros.',
				'type' => 'select',
				'__options' => $o->MESES,
				':size' => 'col-sm-4'
			),
			'total_pensiones' => array(
				'__label' => 'Total Pensiones',
				'min' => 1,
				'data-bv-integer' => 'true',
				'class' => 'tip form-control',
				':size' => 'col-sm-3',
				'title' => 'Coloque la cantidad de pensiones que se cobrarán',
				'type' => 'text'
			),
			'monto_adicional' => array(
				'__label' => 'Monto Adicional S/.',
				'data-bv-numeric' => 'true',
				':size' => 'col-sm-4',
				'class' => ' form-control'
			),
			'moneda' => array(
				'data-bv_integer' => 'true',
				'class' => 'tip form-control',
				':size' => 'col-sm-3',
				'title' => 'Ingrese el prefijo de moneda que utiliza. (Ej.: S/., US$)',
			),
			'ciclo_notas' => array(
				'__label' => 'Ciclo de Notas',
				'class' => 'tip form-control',
				'title' => 'Seleccione cada cuanto se registran las notas.',
				'type' => 'select',
				'__options' => $o->CICLOS,
				':size' => 'col-sm-4'
			),
			'inicio_notas' => array(
				'__label' => 'Inicio de Registro',
				'class' => 'tip form-control',
				'title' => 'Seleccione el mes en el que se inicia el registro.',
				'type' => 'select',
				'__options' => $o->MESES,
				':size' => 'col-sm-4'
			),
			'total_notas' => array(
				'__label' => 'Total Ciclos',
				'min' => 1,
				'data-bv-integer' => 'true',
				'class' => 'tip form-control',
				':size' => 'col-sm-3',
				'title' => 'Coloque la cantidad de ciclos',
				'type' => 'text'
			),
			'anio_activo' => array(
				'__label' => 'Año Activo',
				'data-bv-integer' => 'true',
				'class' => 'tip form-control',
				':size' => 'col-sm-3',
				'title' => 'Coloque el año académico',
				'type' => 'text'
			),
            'anio_matriculas' => array(
				'__label' => 'Año Matrículas',
				'data-bv-integer' => 'true',
				'class' => 'tip form-control',
				':size' => 'col-sm-3',
				'title' => 'Coloque el año académico',
				'type' => 'text'
			),
			'dias_tolerancia' => [
				'class' => 'form-control',
				'data-bv-integer' => 'true'
			],
			'ruc' => [
				'class' => 'form-control',
			],
			'razon_social' => [
				'class' => 'form-control',
			],
			'direccion' => [
				'class' => 'form-control',
			],
            'link_consulta_facturas' => [
				'class' => 'form-control',
                'value' => Config::get('link_consulta_facturas')
			],
			'comision_tarjeta_debito' => [
				'class' => 'form-control',
				'data-bv-numeric' => 'true'
			],
			'comision_tarjeta_credito' => [
				'class' => 'form-control',
				'data-bv-numeric' => 'true'
			],
			'clave_bloques' => [
				'class' => 'form-control',
			],
		);
		return new Form($o, $options);
	}

}
