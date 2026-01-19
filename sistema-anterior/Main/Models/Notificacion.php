<?php
class Notificacion extends ActiveRecord\Model{
	
	static $pk = 'id';
	static $table_name = 'notificaciones';
	static $connection = '';
	
	static $belongs_to = array(
		array(
			'Usuario',
			'class_name' => 'Usuario',
		),
		array(
			'Destinatario',
			'class_name' => 'Usuario',
			'foreign_key' => 'destinatario_id'
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

	function after_create(){
		$this->send();
	}

	function send(){
        /*
		if($this->para == 'TODOS'){
			$devices = Usuario_Device::all();
		}

		if($this->para == 'USUARIO'){
			$usuario = $this->destinatario;
			$devices = $usuario->getApiDevices();
			print_r($usuario->devices);
		}

		foreach($devices As $device){
			sendNotificationToDeviceAPI($device, array(
				'data' => array(
					'tipo' => 'NOTIFICACION',
					'notificacion_id' => (int) $this->id,
					'title' => 'Colegios Vanguard Schools',
					'body' => $this->asunto,
				)
			));
		}
        */
        // 1. URL del endpoint al que enviar la solicitud POST
        $url = Config::get('api_url').'/notifications/send_all'; // ¡Reemplaza con tu URL real!

        // 2. Datos a enviar en la solicitud POST
        // Para JSON, es mejor un array asociativo que luego se codifica
        $postData = [
            'title' => 'Nueva Notificación',
            'summary' => $this->asunto,
            'body' => $this->contenido
        ];

        // Codificar los datos a formato JSON
        $jsonPostData = json_encode($postData);

        // 3. Inicializar una sesión cURL
        $ch = curl_init();

        // 4. Configurar las opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $url); // Establecer la URL
        curl_setopt($ch, CURLOPT_POST, true); // Indicar que es una solicitud POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPostData); // Adjuntar los datos POST

        // Configurar encabezados HTTP (muy importantes para JSON)
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json', // Indica que estamos enviando JSON
            'Content-Length: ' . strlen($jsonPostData) // Longitud de los datos
        ]);

        // Configurar para devolver la respuesta como una cadena en lugar de imprimirla directamente
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Opcional: Deshabilitar la verificación SSL si tienes problemas con certificados
        // En producción, es recomendable tener esto en 'true' o manejar los certificados correctamente
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        // 5. Ejecutar la solicitud cURL
        $response = curl_exec($ch);

        // 6. Verificar si hubo errores
        if (curl_errno($ch)) {
            //echo 'Error cURL: ' . curl_error($ch);
        } else {
            // 7. Decodificar y mostrar la respuesta
            //echo "Respuesta del servidor:\n";
            $responseData = json_decode($response, true); // Decodificar la respuesta JSON a un array asociativo
            //print_r($responseData);
        }

        // 8. Cerrar la sesión cURL
        curl_close($ch);


		Notificacion::table()->update(['estado' => 'ENVIADO'], ['id' => $this->id]);
		//$this->update_attributes(['estado' => 'ENVIADO']);
        
	}
}
