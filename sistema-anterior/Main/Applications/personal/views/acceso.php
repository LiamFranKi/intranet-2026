{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formPersonal').niftyOverlay();
	$('#formPersonal').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/personal/do_acceso', function(r){
				

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

	{% for tipo in ['APODERADO', 'ALUMNO'] %}
		$('#tipo').find('option[value="{{ tipo }}"]').remove();
	{% endfor %}


	$('#tipo').bind('change', function(){
		if(this.value == 'PERSONALIZADO') $('#cPermisos').show();
		if(this.value != 'PERSONALIZADO') $('#cPermisos').hide();
	});

	$('#tipo').trigger('change');
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Personal</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/personal">Personal</a></li>
		<li class="active">Información de Acceso</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formPersonal" data-target="#formPersonal" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Información de Acceso</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.personal_id }}
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
						    <label class="col-sm-4 control-label" for="">Solicitar Cambio</label>
						    <div class="col-sm-6 col-lg-2">{{ form.cambiar_password }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo</label>
						    <div class="col-sm-6 col-lg-4">{{ form.tipo }}</div>
						</div>

						<div class="form-group form-group-sm" id="cPermisos">
						    <label class="col-sm-4 control-label" for="">Permisos</label>
						    <div class="col-sm-6">
						    	<table class="special">
						    		<tr>
							    	{% for permiso in permisos %}
										<td><label class="checkbox-inline"><input type="checkbox" name="permisos[]" value="{{ _key }}" {{ usuario.hasPermiso(_key) ? 'checked' : '' }} /> {{ permiso }}</label></td>
										{% if loop.index % 3 == 0 %}
											</tr><tr>
										{% endif %}
									{% endfor %}
									</tr>
						    	</table>
						    </div>
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
