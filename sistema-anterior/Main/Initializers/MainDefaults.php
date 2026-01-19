<?php
use Core\Initializer;

class MainDefaults extends Initializer{
	function initialize(){
		$params = $this->getApplicationObject()->params;
		$host = $_SERVER['HTTP_HOST'];

		$this->hook(function($class) use($colegio, $host){
			$class->set('main_template', $class->isXMLHttpRequest() ? '__empty.php' : '__index.php', true);
			
			$colegio = Colegio::first();
			$class->set('COLEGIO', $colegio, true);
			$class->set('HOST', $host, true);
			$class->set('ALERTAS_DATE', date('Y-m-d'), true);
			$settings = new Settings();
			$class->set('SETTINGS', $settings, true);

			if($class->session->active('USUARIO_ID')){
				$usuario = Usuario::find_by_id_and_colegio_id($class->session->USUARIO_ID, $class->COLEGIO->id);
				$class->set('USUARIO', $usuario, true);

				if($class->USUARIO && $class->USUARIO->is('ALUMNO')){
                    $matricula = Matricula::find([
                        'conditions' => 'matriculas.alumno_id = "'.$class->USUARIO->alumno_id.'" AND grupos.anio = "'.$class->COLEGIO->anio_activo.'"',
                        'joins' => ['grupo']
                    ]);
                    $class->set('MATRICULA', $matricula, true);
                }
			}


			$connection = ActiveRecord\Connection::parse_connection_url($settings->database_connections[$settings->environment]);
            $DB = new MySQLi($connection->host, $connection->user, $connection->pass, $connection->db, $connection->port);
           
            $class->set('DB', $DB, true);
		});

	}
}
