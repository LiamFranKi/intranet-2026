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

			$(_form).sendForm('/alumnos/do_acceso', function(r){
				

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
		<li class="active">Información de Acceso</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formAlumno" data-target="#formAlumno" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Información de Acceso</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.alumno_id }}
				
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Información de Acceso</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Usuario</label>
						    <div class="col-sm-6 col-lg-4">{{ form.usuario }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Contraseña</label>
						    <div class="col-sm-6 col-lg-4">{{ form.password }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Estado</label>
						    <div class="col-sm-6 col-lg-2">{{ form.estado }}</div>
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
