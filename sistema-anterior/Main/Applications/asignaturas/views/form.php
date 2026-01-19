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

			$(_form).sendForm('/asignaturas/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/asignaturas');
					break;
					
					case 0:
						zk.pageAlert({message: 'No se pudieron guardar los datos', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
					break;

					case -1:
						zk.pageAlert({message: 'El grupo seleccionado no existe', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
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

	$('[rel="curso"]').on('change',function(){
		$('#curso_id').find('.item').remove();
		$.post('/info/get_cursos', $('#formAsignatura').serialize(),function(data){
			
			var selected;
            for(a=0;a<data.length;a++){
				{% if asignatura.curso_id %}
                selected = data[a][0] == {{ asignatura.curso_id }} ? 'selected' : '';
                {% endif %}
				$('#curso_id').append('<option value="'+data[a][0]+'" class="item" '+selected+'>'+data[a][1]+'</option>');
			}
		},'json');
	});
	
    $('#nivel_id').trigger('change');

	$('#formAsignatura').changeGradoOptions({
		value: '{{ asignatura.grupo.grado }}',
	});
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Asignaturas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/asignaturas">Asignaturas</a></li>
		<li class="active">Registro de Asignatura</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formAsignatura" data-target="#formAsignatura" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ asignatura.is_new_record() ? "Registrar" : "Editar" }} Asignatura</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Docente <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.personal_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Sede <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.sede_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nivel <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nivel_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Grado <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.grado }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Sección <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.seccion }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Turno <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.turno_id }}</div>
				</div>
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Año Académico <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.anio }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Curso <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.curso_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Link Libro</label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.link_libro }}</div>
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
