{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formAsignatura_Archivo').niftyOverlay();
	$('#formAsignatura_Archivo').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/asignaturas_archivos/save', function(r){
				

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
		<h1 class="page-header text-overflow">Temas Interactivos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li class="active">Registro de Tema Interactivo</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formAsignatura_Archivo" data-target="#formAsignatura_Archivo" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ asignatura_archivo.is_new_record() ? "Registrar" : "Editar" }} Tema Interactivo</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.asignatura_id }}
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nombre <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.nombre }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Archivo </label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.archivo }}</div>
				</div>

                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">URL </label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.enlace }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Bimestre <small class="text-danger">*</small></label>
				    <div class="col-sm-4 col-xs-12 col-lg-2">{{ form.ciclo }}</div>
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
