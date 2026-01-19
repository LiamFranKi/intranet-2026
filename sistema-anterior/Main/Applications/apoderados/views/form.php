{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formApoderado').niftyOverlay();
	$('#formApoderado').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/apoderados/save', function(r){
				

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

	$('#tipo_documento, #nro_documento').on('blur', function(){
		$.post('/info/apoderado', $('#formApoderado').serialize(), function(r){
			if(r){
				for(i in r){
					if(i != 'id'){
						$('[name="'+i+'"]').val(r[i]);
					}
				}
			}
		} , 'json');
	});

	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Apoderados</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/apoderados">Apoderados</a></li>
		<li class="active">Registro de Apoderado</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formApoderado" data-target="#formApoderado" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ apoderado.is_new_record() ? "Registrar" : "Editar" }} Apoderado</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				


				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Información General</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo de Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.tipo_documento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">N° de Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.nro_documento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Nombres <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nombres }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Apellido Paterno <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.apellido_paterno }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Apellido Materno <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.apellido_materno }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Parentesco <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.parentesco }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Estado Civil </label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.estado_civil }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha de Nacimiento </label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.fecha_nacimiento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Pais de Nacimiento </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.pais_nacimiento_id }}</div>
						</div>
					</div>
				</div>
				
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Información de Contacto</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Telefono Fijo </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.telefono_fijo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Telefono Celular </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.telefono_celular }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Dirección </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.direccion }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Email </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.email }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Centro de Trabajo </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.centro_trabajo_direccion }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Grado de Instruccion </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.grado_instruccion }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Ocupacion </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.ocupacion }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Información Adicional</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Vive </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.vive }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Vive con el estudiante? </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.vive_con_estudiante }}</div>
						</div>

					</div>
				</div>

				{% if get.alumno_id %}
				<input type="hidden" name="alumno_id" value="{{ get.alumno_id }}">
				{% endif %}
			</div>
		
			<div class="modal-footer">
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>
</div>
{% endblock %}
