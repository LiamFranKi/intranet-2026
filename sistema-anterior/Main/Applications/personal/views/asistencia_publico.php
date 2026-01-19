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
	
    #hora{
        font-size: 2em;
    }

    #info-asistencia{
        font-weight: bold;
        font-size: 14px;
        margin-top: 20px;
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
		            <div class="mar-ver">
		                <h1 class="h3">
                        <h3 class="center" style="margin-bottom: 20px; font-size: 16px"><span id="hora"></span></h3>

                        </h1>
		                <p>Ingresa tu DNI</p>
		            </div>
		            <form id="fbuscar">
		                <div class="form-group">
		                    <input type="text" name="dni" id="dni" class="form-control" placeholder="DNI" autofocus>
		                </div>
		                
		                <button class="btn btn-primary btn-lg btn-block" type="submit">Registrar Asistencia</button>
		            </form>

                    <div id="info-asistencia" class="">

                        
                    </div>
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
	
    Number.prototype.padLeft = function (n,str){
        return Array(n-String(this).length+1).join(str||'0')+this;
    }
    function updateTime(){
        var date = new Date();
        var horas = date.getHours() > 12 ? date.getHours() - 12 : date.getHours();
        var format = date.getHours() > 12 ? 'PM' : 'AM';
        $('#hora').html(horas.padLeft(2, 0) + ':' + date.getMinutes().padLeft(2, 0) + ':' + date.getSeconds().padLeft(2, 0) + ' ' + format);
        setTimeout(updateTime, 1000);
    }

    $(function(){
        $('#fbuscar').submit(function(e){
            e.preventDefault();
            if($('#dni').val() == '') return zk.pageAlert({message: 'Ingrese su DNI', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating', floating_position: "top-center"});
            $.post('/personal/save_asistencia_publico', $(this).serialize(), function(r){
                $('#dni').val('');
                $('#info-asistencia').html(r)
            })
          
        });

        updateTime();
    });
    </script>
</body>
</html>
