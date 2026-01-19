{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formExamen_Bloque').niftyOverlay();
	$('#formExamen_Bloque').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/examenes_bloques/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/examenes_bloques');
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

	$('#bloque_id').bind('change', function(){
		$('#cursos').find('.item').remove();
		$.post('/info/get_cursos_bloque', {bloque_id: this.value}, function(r){
			console.log(r)
			for(i in r){
				curso = r[i];
				$('#cursos').append('<option class="item" value="'+ curso.id +'" '+ ({{ examen_bloque.getCursosID()|json_encode }}.includes(curso.id.toString()) ? 'selected' : '') +'>'+ curso.nombre +'</option>');
			}

			
		}, 'json');
	});
	$('#bloque_id').trigger('change');
	$('.tip').tooltip();
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Examenes Bloques</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/examenes_bloques">Exámenes Bloques</a></li>
		<li class="active">Registro de Examen</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formExamen_Bloque" data-target="#formExamen_Bloque" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ examen_bloque.is_new_record() ? "Registrar" : "Editar" }} Examen</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Título <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.titulo }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Bloque <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.bloque_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Cursos <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.cursos }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Grado <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.grado }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Puntos <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.puntos_correcta }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Preguntas Max <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.preguntas_max }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Estado <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.estado }}</div>
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
