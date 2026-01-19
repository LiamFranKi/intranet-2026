<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ COLEGIO.titulo_intranet }}</title>


    <!--STYLESHEET-->
    <!--=================================================-->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>
    <!--<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>-->
    <link href="/Static/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Static/css/nifty.min.css" rel="stylesheet">
    <link href="/Static/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!--=================================================-->

    <!--Pace - Page Load Progress Par [OPTIONAL]-->
    <link href="/Static/plugins/pace/pace.min.css" rel="stylesheet">
    <script src="/Static/plugins/pace/pace.min.js"></script>
    <style>
	#loginFormContainer{
		border-radius: 20px;
	}

	.cls-content .cls-content-sm {
		padding: 5px;
	}

	@media (min-width: 768px){
		.cls-content .cls-content-sm {
			width: 450px;
			padding: 10px;
		}
	}

	.form-control {
		border-radius: 20px;
		padding: 20px 20px;
	}

	.btn-primary {
		border-radius: 20px;
		font-weight: bold;
		padding: 10px 20px;
	}
	
	</style>
</head>

<!--TIPS-->
<body>
    <div id="container" class="cls-container">
        
		<!-- BACKGROUND IMAGE -->
		<!--===================================================-->
		<div id="bg-overlay" class="bg-img" style="background-image: url('/Static/Image/Fondos/{{ Config_get('login_fondo', 'background.jpg') }}');"></div>
		
		<!-- LOGIN FORM -->
		<!--===================================================-->
		<div class="cls-content">
		    <div class="cls-content-sm panel" id="loginFormContainer" data-toggle="overlay" data-target="#loginFormContainer">
		        <div class="panel-body">
		            <div class="mar-ver pad-btm">
		                <h1 class="h3">Inicio de Sesión</h1>
		                <p>Ingresa a tu cuenta</p>
		            </div>
		            <form id="loginForm">
		                <div class="form-group">
		                    <input type="text" name="usuario" class="form-control" placeholder="Usuario" autofocus>
		                </div>
		                <div class="form-group">
		                    <input type="password" name="password" class="form-control" placeholder="Contraseña">
		                </div>
						<!--
		                <div class="checkbox pad-btm text-left">
		                    <input id="remember" class="magic-checkbox" type="checkbox" name="recordar" />
		                    <label for="remember">Recordarme</label>
		                </div>
						-->
		                <button class="btn btn-primary btn-lg btn-block" type="submit">Iniciar Sesión</button>
		            </form>
		        </div>
		
		        <div class="pad-btm">
		            <a href="#" class="btn-link">¿Olvidó su contraseña?</a>
		            <!--<a href="pages-register.html" class="btn-link mar-lft">Crear Cuenta Nueva</a>-->
					<!--
		            <div class="media pad-top bord-top">
		                <div class="pull-right">
		                    <a href="#" class="pad-rgt"><i class="demo-psi-facebook icon-lg text-primary"></i></a>
		                    <a href="#" class="pad-rgt"><i class="demo-psi-twitter icon-lg text-info"></i></a>
		                    <a href="#" class="pad-rgt"><i class="demo-psi-google-plus icon-lg text-danger"></i></a>
		                </div>
		                <div class="media-body text-left text-bold text-main">
		                    Login with
		                </div>
		            </div>
		        	-->
		        </div>
		    </div>
		</div>
		<!--===================================================-->
		

		
		
    </div>
    <!--===================================================-->
    <!-- END OF CONTAINER -->


        
    <!--JAVASCRIPT-->
    <!--=================================================-->

    <!--jQuery [ REQUIRED ]-->
    <script src="/Static/js/jquery.min.js"></script>


    <!--BootstrapJS [ RECOMMENDED ]-->
    <script src="/Static/js/bootstrap.min.js"></script>


    <!--NiftyJS [ RECOMMENDED ]-->
    <script src="/Static/js/nifty.min.js"></script>

    <script src="/Static/js/zk.js"></script>

    <script>
	var token = $("meta[name='csrf-token']").attr("content");
	$(document).ajaxSend(function(e, xhr, options) {
		xhr.setRequestHeader("X-CSRF-Token", token);
	});
	</script>

	{% if deudas|length > 0 %}
	        
		<script>
		$(function(){
			zk.pageAlert({timer: 0, message: 'OPERACIÓN FALLIDA POR INCONVENIENTES EN LA PLATAFORMA', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			//zk.pageAlert({timer: 0, message: 'El acceso ha sido bloqueado por tener deudas pendientes:<br /> <b>{{ implode("<br />", deudas) }}</b>', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
		});
		</script>

	{% endif %}

	<script src="/Static/plugins/bootbox/bootbox.min.js"></script>

    <script src="/Static/js/usuarios/login.js"></script>
</body>
</html>
