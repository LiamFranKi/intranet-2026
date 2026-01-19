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
    <link href="/Static/plugins/bootstrap-validator/bootstrapValidator.min.css" rel="stylesheet">
    
    <link href="/Static/plugins/pace/pace.min.css" rel="stylesheet">
    <script src="/Static/plugins/pace/pace.min.js"></script>
    <script src="/scripts/values"></script>

    <style>
	#formContainer{
		border-radius: 20px;
	}

	.cls-content .cls-content-sm {
		padding: 5px;
	}

	@media (min-width: 768px){
		.cls-content .cls-content-sm {
			width: 550px;
			padding: 10px;
		}
	}

	.form-control {

        border: 1px solid #ccc;
	}

	.btn-primary {
		border-radius: 20px;
		font-weight: bold;
		padding: 10px 20px;
	}
	.cls-container {
        background-color: #ecf0f5;
        text-align: left
    }

    


    @media only screen and (max-width: 600px) {
        .cls-content .cls-content-sm, .cls-content .cls-content-lg {
            /* width: 70%; */
            min-width: 270px;
            margin: 0 auto;
            position: relative;
            background-color: transparent;
            border: 0;
            box-shadow: none;
            width: auto;
        }
    }
   
	</style>

    <!--JAVASCRIPT-->
    <!--=================================================-->

    <!--jQuery [ REQUIRED ]-->
    <script src="/Static/js/jquery.min.js"></script>


    <!--BootstrapJS [ RECOMMENDED ]-->
    <script src="/Static/js/bootstrap.min.js"></script>


    <!--NiftyJS [ RECOMMENDED ]-->
    <script src="/Static/js/nifty.min.js"></script>
    <script src="/Static/plugins/bootstrap-validator/bootstrapValidator.min.js"></script>
	<script src="/Static/plugins/bootstrap-validator/es_CL.js"></script>
    
    
    <script src="/Static/js/zk.js"></script>

    <script>
	var token = $("meta[name='csrf-token']").attr("content");
	$(document).ajaxSend(function(e, xhr, options) {
		//xhr.setRequestHeader("X-CSRF-Token", token);
	});
	</script>

	<script src="/Static/plugins/bootbox/bootbox.min.js"></script>
    <script>
    function limpiar(){
        $('input').val('');
        $('#formMatricula').data('bootstrapValidator').resetForm()
        clearArea()
    }

    $(function(){
        $('#formMatricula').niftyOverlay();
        $('#formMatricula').bootstrapValidator({
            fields: {},
            onSuccess: function(e){
                e.preventDefault();
                _form = e.target;
                $('button[type="submit"]').attr('disabled', false);
                let firmaBlanca = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAACWCAYAAABkW7XSAAAAAXNSR0IArs4c6QAABGJJREFUeF7t1AEJAAAMAsHZv/RyPNwSyDncOQIECEQEFskpJgECBM5geQICBDICBitTlaAECBgsP0CAQEbAYGWqEpQAAYPlBwgQyAgYrExVghIgYLD8AAECGQGDlalKUAIEDJYfIEAgI2CwMlUJSoCAwfIDBAhkBAxWpipBCRAwWH6AAIGMgMHKVCUoAQIGyw8QIJARMFiZqgQlQMBg+QECBDICBitTlaAECBgsP0CAQEbAYGWqEpQAAYPlBwgQyAgYrExVghIgYLD8AAECGQGDlalKUAIEDJYfIEAgI2CwMlUJSoCAwfIDBAhkBAxWpipBCRAwWH6AAIGMgMHKVCUoAQIGyw8QIJARMFiZqgQlQMBg+QECBDICBitTlaAECBgsP0CAQEbAYGWqEpQAAYPlBwgQyAgYrExVghIgYLD8AAECGQGDlalKUAIEDJYfIEAgI2CwMlUJSoCAwfIDBAhkBAxWpipBCRAwWH6AAIGMgMHKVCUoAQIGyw8QIJARMFiZqgQlQMBg+QECBDICBitTlaAECBgsP0CAQEbAYGWqEpQAAYPlBwgQyAgYrExVghIgYLD8AAECGQGDlalKUAIEDJYfIEAgI2CwMlUJSoCAwfIDBAhkBAxWpipBCRAwWH6AAIGMgMHKVCUoAQIGyw8QIJARMFiZqgQlQMBg+QECBDICBitTlaAECBgsP0CAQEbAYGWqEpQAAYPlBwgQyAgYrExVghIgYLD8AAECGQGDlalKUAIEDJYfIEAgI2CwMlUJSoCAwfIDBAhkBAxWpipBCRAwWH6AAIGMgMHKVCUoAQIGyw8QIJARMFiZqgQlQMBg+QECBDICBitTlaAECBgsP0CAQEbAYGWqEpQAAYPlBwgQyAgYrExVghIgYLD8AAECGQGDlalKUAIEDJYfIEAgI2CwMlUJSoCAwfIDBAhkBAxWpipBCRAwWH6AAIGMgMHKVCUoAQIGyw8QIJARMFiZqgQlQMBg+QECBDICBitTlaAECBgsP0CAQEbAYGWqEpQAAYPlBwgQyAgYrExVghIgYLD8AAECGQGDlalKUAIEDJYfIEAgI2CwMlUJSoCAwfIDBAhkBAxWpipBCRAwWH6AAIGMgMHKVCUoAQIGyw8QIJARMFiZqgQlQMBg+QECBDICBitTlaAECBgsP0CAQEbAYGWqEpQAAYPlBwgQyAgYrExVghIgYLD8AAECGQGDlalKUAIEDJYfIEAgI2CwMlUJSoCAwfIDBAhkBAxWpipBCRAwWH6AAIGMgMHKVCUoAQIGyw8QIJARMFiZqgQlQMBg+QECBDICBitTlaAECBgsP0CAQEbAYGWqEpQAAYPlBwgQyAgYrExVghIgYLD8AAECGQGDlalKUAIEDJYfIEAgI2CwMlUJSoCAwfIDBAhkBAxWpipBCRAwWH6AAIGMgMHKVCUoAQIGyw8QIJARMFiZqgQlQMBg+QECBDICBitTlaAECBgsP0CAQEbAYGWqEpQAgQdWMQCX4yW9owAAAABJRU5ErkJggg==";
                let firma = document.querySelector('#canvas_firma').toDataURL("image/jpg");
                if(firma == firmaBlanca){
                    zk.pageAlert({message: 'Ingrese su firma primero.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
                    return false;
                }

                $('#apoderado_firma_digital').val(firma)
                $(_form).sendForm('/alumnos/save_matricula_online', function(r){
                    switch(parseInt(r[0])){
                        case 1:
                            limpiar();
                            zk.pageAlert({message: 'Los datos de su matrícula han sido registrados correctamente.', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
                        break;
                        
                        case 0:
                            zk.pageAlert({message: 'No se pudieron guardar los datos', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
                        break;

                        case -1:
                            zk.pageAlert({message: 'El alumno ya se encuentra registrado.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
                        break;

                        case -2:
                            zk.pageAlert({message: 'No se pudo subir la foto del DNI. Intente nuevamente.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
                        break;
                        
                        case -5:
                            zk.formErrors(_form, r.errors);
                        break;
                        
                        default:
                            
                        break;
                    }
                    
                });
            }
        });

        $('#tipo_documento').on('change', function(){
            if(this.value == 0){
                bloquearCampos()
            }else{
                desbloquearCampos()
            }
            
        }).trigger('change')

        $('#apoderado_tipo_documento').on('change', function(){
            if(this.value == 0){
                bloquearCamposApoderado()
            }else{
                desbloquearCamposApoderado()
            }
            
        }).trigger('change')
        

        $('#nro_documento').on('blur', function(){
            $.get('/info/dni/' + this.value, function(r){
                if($('#tipo_documento').val() == 0){
                    if(r.error){
                        desbloquearCampos()
                    }else{
                        bloquearCampos();
                        
                        $('#apellido_paterno').val(r.apellidoPaterno)
                        $('#apellido_materno').val(r.apellidoMaterno)
                        $('#nombres').val(r.nombres)

                        $('#formMatricula').data('bootstrapValidator').revalidateField("apellido_paterno")
                        $('#formMatricula').data('bootstrapValidator').revalidateField("apellido_materno")
                        $('#formMatricula').data('bootstrapValidator').revalidateField("nombres")
                    }

                    
                }
            }, 'json')
        })

        $('#apoderado_nro_documento').on('blur', function(){
            $.get('/info/dni/' + this.value, function(r){
                if($('#apoderado_tipo_documento').val() == 0){
                    if(r.error){
                        desbloquearCamposApoderado()
                    }else{
                        bloquearCamposApoderado();

                        $('#apoderado_apellido_paterno').val(r.apellidoPaterno)
                        $('#apoderado_apellido_materno').val(r.apellidoMaterno)
                        $('#apoderado_nombres').val(r.nombres)

                        $('#formMatricula').data('bootstrapValidator').revalidateField("apoderado_apellido_paterno")
                        $('#formMatricula').data('bootstrapValidator').revalidateField("apoderado_apellido_materno")
                        $('#formMatricula').data('bootstrapValidator').revalidateField("apoderado_nombres")
                    }
                }
            }, 'json')
        })

        $('#formMatricula').changeGradoOptions({
            value: '1',
        });
    })

    function bloquearCamposApoderado(){
        $('#apoderado_apellido_paterno').attr('readonly', true);
        $('#apoderado_apellido_materno').attr('readonly', true);
        $('#apoderado_nombres').attr('readonly', true);
    }

    function bloquearCampos(){
        $('#apellido_paterno').attr('readonly', true);
        $('#apellido_materno').attr('readonly', true);
        $('#nombres').attr('readonly', true);
    }

    function desbloquearCamposApoderado(){
        $('#apoderado_apellido_paterno').attr('readonly', false);
        $('#apoderado_apellido_materno').attr('readonly', false);
        $('#apoderado_nombres').attr('readonly', false);
    }

    function desbloquearCampos(){
        $('#apellido_paterno').attr('readonly', false);
        $('#apellido_materno').attr('readonly', false);
        $('#nombres').attr('readonly', false);
    }
    </script>
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
		    <div class="cls-content-sm panel" id="formContainer" >
		        <div class="panel-body">
		            <div class="mar-ver pad-btm text-center">
		                <h1 class="h3">{{ Config_get('titulo_formulario_matricula') }}</h1>
		                <!--<p>Ingrese los dato</p>-->
		            </div>
		            <form id="formMatricula" class="form-horizontal" data-toggle="overlay" data-target="#formMatricula">
                        <table class="table">
                            {% for archivo in archivos %}
                            <tr>
                                <td class="form-group"><input type="checkbox" name="aceptar{{ archivo.id }}" required></td><td>Reconozco haber leído y aceptado: <a href="/Static/Archivos/{{ archivo.archivo }}" target="_blank"><b>{{ archivo.nombre }}</b></a>.</td>
                            </tr>
                            {% endfor %}
                            
                        </table>

                        <div class="panel panel-primary panel-bordered">
                            <div class="panel-heading">
                                <h3 class="panel-title">Datos del Postulante</h3>
                            </div>
                            <div class="panel-body">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Tipo de Documento <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.tipo_documento }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">N° de Documento <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nro_documento }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Apellido Paterno <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.apellido_paterno }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Apellido Materno <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.apellido_materno }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Nombres <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.nombres }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Fecha de Nacimiento <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_nacimiento }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Seguro <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.seguro_id }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Foto DNI <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.foto_dni }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Nivel a Postular <small class="text-danger">*</small></label>
                                    <div class="col-sm-4 col-lg-4">{{ fm.nivel_id }}</div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Grado a Postular <small class="text-danger">*</small></label>
                                    <div class="col-sm-4 col-lg-4">{{ fm.grado }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-primary panel-bordered">
                            <div class="panel-heading">
                                <h3 class="panel-title">Datos del Apoderado</h3>
                                <div class="pull-right"></div>
                            </div>
                            <div class="panel-body">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Tipo de Documento <small class="text-danger">*</small></label>
                                    <div class="col-sm-4 col-lg-4">{{ fa.tipo_documento }}</div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">N° de Documento <small class="text-danger">*</small></label>
                                    <div class="col-sm-4 col-lg-4">{{ fa.nro_documento }}</div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Apellido Paterno <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-lg-6">{{ fa.apellido_paterno }}</div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Apellido Materno <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-lg-6">{{ fa.apellido_materno }}</div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Nombres <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-lg-6">{{ fa.nombres }}</div>
                                </div>
                                
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Email <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-lg-6">{{ fa.email }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Teléfono <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-lg-4">{{ fa.telefono_celular }}</div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Dirección <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-lg-6">{{ fa.direccion }}</div>
                                </div>
                                <style>
                                canvas {
                                    width: 250px;
                                    height: 150px;
                                    border: 1px solid #000
                                }
                                </style>
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-4 control-label" for="">Firma Digital <small class="text-danger">*</small></label>
                                    <div class="col-sm-6 col-lg-6 text-center">
                                        <canvas id="canvas_firma"></canvas> 
                                        <button class="btn btn-purple" style="display: block" type="button" onclick="clearArea()">Limpiar</button>
                                    </div>
                                </div>

                            </div>
                        </div>

						<input type="hidden" name="apoderado_firma_digital" id="apoderado_firma_digital" />
		                <button class="btn btn-primary btn-lg btn-block" type="submit">Generar Matrícula</button>
		            </form>
		        </div>
		
		    </div>
		</div>
    </div>
    <script src="/Static/js/custom/firma_digital.js"></script>
</body>
</html>
