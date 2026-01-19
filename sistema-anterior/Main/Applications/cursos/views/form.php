{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formCurso').niftyOverlay();
	$('#formCurso').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/cursos/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/cursos');
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

	$('#examen_mensual').on('change', function(){
		if(this.value == 'SI'){
			$('.examen_mensual').show();
		}else{
			$('.examen_mensual').hide();
		}
	});

	$('#examen_mensual').trigger('change');
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Cursos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/cursos">Cursos</a></li>
		<li class="active">Registro de Curso</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formCurso" data-target="#formCurso" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ curso.is_new_record() ? "Registrar" : "Editar" }} Curso</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
			
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nivel <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nivel_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nombre <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nombre }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Abreviatura <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.abreviatura }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descripción <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-5">{{ form.descripcion }}</div>
				</div>

				
				<!-- <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Añadir Examen Mensual <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.examen_mensual }}</div>
				</div>

				<div class="form-group form-group-sm examen_mensual">
				    <label class="col-sm-4 control-label" for="">Peso E. Mensual % <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.peso_examen_mensual }}</div>
				</div> -->

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Imagen </label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">
				    	{% if curso.imagen %}
				    	<p><img src="/Static/Archivos/{{ curso.imagen }}" style="max-height: 100px" alt=""></p>
				    	{% endif %}
				    	{{ form.imagen }}
				    	
				    </div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Link Libro</label>
				    <div class="col-sm-6 col-lg-6 col-xs-12">{{ form.link_libro }}</div>
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
