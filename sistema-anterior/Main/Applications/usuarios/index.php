<?php
class UsuariosApplication extends Core\Application{
	
	function initialize(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'USUARIO_ID' => 'password, save_password',
			'__exclude' => 'login, do_login, logout, no_auth'
		]);

	}

	function login(){
		$deudas = unserialize(base64_decode($this->session->DEUDAS));
		$this->session->destroy('DEUDAS');
		$this->render(array('deudas' => $deudas));
	}

	function do_login(){
		$usuario = Usuario::find_by_usuario_and_password($this->post->usuario, sha1($this->post->password));
		$code = 0;
		if($usuario){
			$code = -1;

			if($usuario->estado == "ACTIVO" 
				&& in_array($usuario->tipo, TraitConstants::ALLOWED_USER_TYPES)){
				$this->session->USUARIO_ID = $usuario->id;
				$this->session->{$usuario->tipo} = $usuario->tipo;
				$code = 1;
			}
		}

		$this->render_json(['code' => $code]);
	}


	function save_password(){
		if($this->post->password != $this->post->password2){
			echo json_encode([-1]);
			return false;
		}


		$this->USUARIO->password = sha1($this->post->password);
		$r = $this->USUARIO->save() ? 1 : 0;

		echo json_encode([$r]);
	}

	function logout(){
		$tipos = $this->USUARIO->TIPOS_USUARIO;
		foreach($tipos As $tipo){
			$this->session->destroy($tipo);
		}

		$this->session->destroy('USUARIO_ID');
		$this->redirect('/');
	}
}
