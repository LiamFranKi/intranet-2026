{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formSede').niftyOverlay();
	$('#formSede').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/sedes/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/sedes');
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
		<h1 class="page-header text-overflow">Sedes</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/sedes">Sedes</a></li>
		<li class="active">Registro de Sede</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formSede" data-target="#formSede" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ sede.is_new_record() ? "Registrar" : "Editar" }} Sede</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
			
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nombre <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nombre }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Codigo Sunat <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.codigo_sunat }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Prefijo Boleta <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.prefijo_boleta }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Dirección <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.direccion }}</div>
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
