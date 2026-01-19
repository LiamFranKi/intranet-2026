{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formAlumno').niftyOverlay();
	$('#formAlumno').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/alumnos/save_perfil', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						history.back(-1)
					break;
					
					case 0:
						zk.pageAlert({message: 'No se pudieron guardar los datos', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
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

});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Alumnos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/alumnos">Alumnos</a></li>
		<li class="active">Registro de Alumno</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formAlumno" data-target="#formAlumno" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ alumno.is_new_record() ? "Registrar" : "Editar" }} Alumno</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Información General</h3>
					</div>
					<div class="panel-body">
						<!-- <div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo de Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.tipo_documento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">N° de Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.nro_documento }}</div>
						</div> -->

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Apellido Paterno <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.apellido_paterno }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Apellido Materno <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.apellido_materno }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Nombres <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nombres }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Sexo <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.sexo }}</div>
						</div>

					

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Email</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.email }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Domicilio</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.domicilio }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Foto</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">
						    	<p><img src="{{ alumno.getFoto() }}" style="width: 120px" /></p>
						    	{{ form.foto }}
							</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Religión </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.religion }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha de Nacimiento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_nacimiento }}</div>
						</div>	
					</div>
				</div>

			</div>
		
			<div class="modal-footer">
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>
</div>
{% endblock %}
