{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formAsignatura').niftyOverlay();
	$('#formAsignatura').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/asignaturas/save_clase_zoom', function(r){
				

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
		<h1 class="page-header text-overflow">Link Aula Virtual</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		
		<li class="active">Asignar Link</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formAsignatura" data-target="#formAsignatura" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Link Aula Virtual</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Link Aula Virtual</label>
				    <div class="col-sm-6 col-lg-4 col-xs-12">{{ form.aula_virtual }}</div>
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
