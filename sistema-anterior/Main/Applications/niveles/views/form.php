{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formNivel').niftyOverlay();
	$('#formNivel').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/niveles/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/niveles');
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

	$('#tipo_calificacion').on('change', function(){
		if(this.value == 0){
			$('.x_cuantitativa').hide();
		}else{
			$('.x_cuantitativa').show();
		}
	});
	$('#tipo_calificacion').trigger('change');
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Niveles</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/niveles">Niveles</a></li>
		<li class="active">Registro de Nivel</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formNivel" data-target="#formNivel" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ nivel.is_new_record() ? "Registrar" : "Editar" }} Nivel</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Información General</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Nombre <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nombre }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Abreviatura <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.abreviatura }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Código Modular <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.codigo_modular }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Sobre los Grados</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Definicion de Grado <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.definicion_grado }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Grado / Años - Mínimo <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.grado_minimo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Grado / Años - Máximo <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.grado_maximo }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Calificaciones</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo de Calificacion <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.tipo_calificacion }}</div>
						</div>

						<div class="form-group form-group-sm x_cuantitativa">
						    <label class="col-sm-4 control-label" for="">Tipo de Calificacion - Final <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.tipo_calificacion_final }}</div>
						</div>
							
						<div class="form-group form-group-sm x_cuantitativa">
						    <label class="col-sm-4 control-label" for="">Nota Máxima <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.nota_maxima }}</div>
						</div>

						<div class="form-group form-group-sm x_cuantitativa">
						    <label class="col-sm-4 control-label" for="">Nota Mínima <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.nota_minima }}</div>
						</div>

						<div class="form-group form-group-sm x_cuantitativa">
						    <label class="col-sm-4 control-label" for="">Nota Aprobatoria <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.nota_aprobatoria }}</div>
						</div>
					</div>
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
