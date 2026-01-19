<?php
class MensajesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'USUARIO_ID' => '*'
		]);
	}
	

	private function getMessages($conditions, $page){
        $per_page = 15;
        $offset = ($per_page * $page) - $per_page;

        $mensajes = Mensaje::all(array(
            'conditions' => $conditions,
            'order' => 'fecha_hora DESC',
            'limit' => $per_page,
            'offset' => $offset
        ));
        
        return $mensajes;
    }

    function form(){
    	if($this->get->to){
            $usuarios = Usuario::all(array(
                'conditions' => 'id IN ('.$this->get->to.')'
            ));
        }
        
        if($this->get->grupo_id){
			$usuarios = Usuario::all(array(
                'conditions' => 'matriculas.grupo_id = "'.$this->get->grupo_id.'"',
                'joins' => 'INNER JOIN matriculas ON matriculas.alumno_id = usuarios.alumno_id'
            ));
		}
		
		if($this->get->personal){
			$usuarios = Usuario::all(array(
                'conditions' => 'usuarios.tipo != "ALUMNO" AND usuarios.tipo != "APODERADO"',
                'joins' => 'INNER JOIN personal ON usuarios.personal_id = personal.id'
            ));
		}

		$this->render(['usuarios' => $usuarios]);
    }
    
    function index(){
    	$page = empty($this->get->page) ? 1 : $this->get->page;
        $mensajes = $this->getMessages('destinatario_id="'.$this->USUARIO->id.'" AND tipo="RECIBIDO" AND borrado="NO"', $page);
        $this->render('index', array('mensajes' => $mensajes, 'path' => '/mensajes', 'page' => $page));
    }

    function enviados(){
    	$page = empty($this->get->page) ? 1 : $this->get->page;
        $mensajes = $this->getMessages('remitente_id="'.$this->USUARIO->id.'" AND tipo="ENVIADO"  AND borrado="NO"', $page);
     
        $this->render('index', array('mensajes' => $mensajes, 'path' => '/mensajes/enviados', 'page' => $page));
    }
	
	/*function save(){
		$r = -5;
		$this->mensaje->set_attributes(array(
			'remitente_id' => $this->post->remitente_id,
			'destinatario_id' => $this->post->destinatario_id,
			'asunto' => $this->post->asunto,
			'mensaje' => $this->post->mensaje,
			'fecha_hora' => $this->post->fecha_hora,
			'estado' => $this->post->estado,
			'tipo' => $this->post->tipo,
			'borrado' => $this->post->borrado,
			'favorito' => $this->post->favorito,
		));
		
		if($this->mensaje->is_valid()){
			$r = $this->mensaje->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->mensaje->id, 'errors' => $this->mensaje->errors->get_all()));
	}*/
	function save(){
		 $data = array(
            'mensaje' => $this->post->mensaje,
            'asunto' => $this->post->asunto,
            'fecha_hora' => date('Y-m-d H:i'),
            'estado' => 'NO_LEIDO'
        );
        
        foreach($this->post->para As $destino){
            // envia el mensaje
            $enviado = new Mensaje($data);
            $enviado->remitente_id = $this->USUARIO->id;
            $enviado->destinatario_id = $destino;
            $enviado->tipo = 'RECIBIDO';
            $enviado->save();
            //if($this->__config->enviar_a_email){
            //    $enviado->sendToMail();
            //}
            
            if($enviado){
                sendNotificationForMensajeAPI($enviado);
            }

            /*
            foreach($archivos_subidos As $archivo){
				Mensaje_Archivo::create(array(
					'mensaje_id' => $enviado->id,
					'archivo' => $archivo[0],
					'nombre_archivo' => $archivo[1]
				));
			}
			*/
			
            // almacena el mensaje en enviados
            $e = new Mensaje($data);
            $e->remitente_id = $this->USUARIO->id;
            $e->destinatario_id = $destino;
            $e->tipo = 'ENVIADO';
            $e->save();
            /*
            foreach($archivos_subidos As $archivo){
				Mensaje_Archivo::create(array(
					'mensaje_id' => $e->id,
					'archivo' => $archivo[0],
					'nombre_archivo' => $archivo[1]
				));
			}
			*/
        }
        
        echo json_encode([1]);
	}

	function ver(){
		
		$this->mensaje->update_attributes(Array('estado' => 'LEIDO'));

		$this->render();
	}

	function borrar($r){
		$r = $this->mensaje->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'mensajes', true);
		$this->mensaje = !empty($this->params->id) ? Mensaje::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Mensaje();
		$this->context('mensaje', $this->mensaje); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->mensaje);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'remitente_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'destinatario_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'asunto' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'mensaje' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_hora' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'estado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'tipo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'borrado' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'favorito' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
