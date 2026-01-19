{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formAsignatura_Tarea').niftyOverlay();
	$('#formAsignatura_Tarea').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/asignaturas_tareas/save', function(r){
				

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
	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})
});
function borrarArchivo(id, sender){
	if(confirm('¿Está seguro de borrar el archivo?')){
		$.post('/asignaturas_tareas/borrar_archivo', {archivo_id: id}, function(r){
			$(sender).parent().parent().remove();
		}, 'json')
	}
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Tareas Virtuales</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		
		<li class="active">Registro de Tarea</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formAsignatura_Tarea" data-target="#formAsignatura_Tarea" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ asignatura_tarea.is_new_record() ? "Registrar" : "Editar" }} Tarea</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.asignatura_id }}

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Titulo <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.titulo }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descripción</label>
				    <div class="col-sm-7 col-xs-12 col-lg-6">{{ form.descripcion }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Fecha de Entrega <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_entrega }}</div>
				</div>


				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Bimestre <small class="text-danger">*</small></label>
				    <div class="col-sm-4 col-xs-12 col-lg-2">{{ form.ciclo }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Archivos Adjuntos</label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">

				    	{% if asignatura_tarea.getArchivos()|length > 0 %}
				    	<table class="special">
				    		{% for archivo in asignatura_tarea.getArchivos() %}
				    		<tr>
				    			<td><a href="/Static/Archivos/{{ archivo.archivo }}" target="_blank" download="{{ archivo.nombre }}">{{ archivo.nombre }}</a></td>
				    			<td>
				    				<button class="btn btn-danger btn-sm" type="button" onclick="borrarArchivo('{{ archivo.id }}', this)"><i class="fa fa-remove"></i></button>
				    			</td>
				    		</tr>
				    		{% endfor %}
				    	</table>
				    	{% endif %}
				    	<input type="file" name="archivos[]" class="form-control" multiple>
				    	
				    </div>
				</div>

                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">URL </label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.enlace }}</div>
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
