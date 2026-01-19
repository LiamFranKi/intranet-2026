{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formCosto').niftyOverlay();
	$('#formCosto').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/costos/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/costos');
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
		<h1 class="page-header text-overflow">Costos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/costos">Costos</a></li>
		<li class="active">Registro de Costo</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formCosto" data-target="#formCosto" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ costo.is_new_record() ? "Registrar" : "Editar" }} Costo</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descripción <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.descripcion }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Costo Matrícula {{ COLEGIO.moneda }} <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.matricula }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Costos Pensión {{ COLEGIO.moneda }} <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.pension }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Costos Agenda {{ COLEGIO.moneda }} <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.agenda }}</div>
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
