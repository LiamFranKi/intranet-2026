{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formTopico_Atencion').niftyOverlay();
	$('#formTopico_Atencion').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/topico_atenciones/save', function(r){
				

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
		<h1 class="page-header text-overflow">Atención en Psicología</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/topico_atenciones">Atenciones</a></li>
		<li class="active">Registro de Atención</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formTopico_Atencion" data-target="#formTopico_Atencion" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ topico_atencion.is_new_record() ? "Registrar" : "Editar" }} Atención</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.alumno_id }}
				{{ form.tipo }}

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Fecha / Hora <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_hora }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Motivo <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.motivo }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Tratamiento <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.tratamiento }}</div>
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
