{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formBanco_Tema').niftyOverlay();
	$('#formBanco_Tema').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/banco_temas/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/banco_temas');
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

	$('#formBanco_Tema').changeGradoOptions({
		value: '{{ banco_tema.grado }}',
	});

	$('#nivel_id').on('change', function(){
		$.post('/info/get_cursos_nivel', {nivel_id: this.value}, function(r){
			$('#curso_id .item').remove();

			for(i in r){
				item = r[i]
				let selected = item.id == '{{ banco_tema.curso_id }}';

				$('#curso_id').append('<option class="item" value="'+ item.id +'" '+ (selected ? 'selected' : '') +'>'+ item.nombre +'</option>')
			}
		}, 'json')
	}).trigger('change')
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Banco de Temas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/banco_temas">Banco de emas</a></li>
		<li class="active">Registro de Tema</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formBanco_Tema" data-target="#formBanco_Tema" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ banco_tema.is_new_record() ? "Registrar" : "Editar" }} Tema</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nombre <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.nombre }}</div>
				</div>
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nivel <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nivel_id }}</div>
				</div>
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Grado <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.grado }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Curso <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.curso_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Archivo</label>
				    <div class="col-sm-6 col-lg-4 col-xs-12">{{ form.archivo }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Detalles</label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.detalles }}</div>
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
