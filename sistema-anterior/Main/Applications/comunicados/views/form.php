{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formComunicado').niftyOverlay();
	$('#formComunicado').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$('#contenido').val(eContenido.getData());

			$(_form).sendForm('/comunicados/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/comunicados');
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

	$('#tipo').on('change', function(){
		if(this.value == 'TEXTO'){
			$('.x_comunicado_contenido').show();
			$('.x_comunicado_archivo').hide();
		}else{
			$('.x_comunicado_contenido').hide();
			$('.x_comunicado_archivo').show();
		}
	});
	$('#tipo').trigger('change');

	ckEditorVerySimple('#contenido', 'eContenido');
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Comunicados</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/comunicados">Comunicados</a></li>
		<li class="active">Registro de Comunicado</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formComunicado" data-target="#formComunicado" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ comunicado.is_new_record() ? "Registrar" : "Editar" }} Comunicado</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
		
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descripción <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-5">{{ form.descripcion }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Tipo <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.tipo }}</div>
				</div>

				<div class="form-group form-group-sm x_comunicado_contenido">
				    <label class="col-sm-4 control-label" for="">Contenido <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-8">{{ form.contenido }}</div>
				</div>

				<div class="form-group form-group-sm x_comunicado_archivo">
				    <label class="col-sm-4 control-label" for="">Archivo <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.archivo }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Fecha / Hora <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_hora }}</div>
				</div>


                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Mostrar en Inicio <small class="text-danger">*</small></label>
				    <div class="col-sm-4 col-xs-12 col-lg-2">{{ form.show_in_home }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Estado <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.estado }}</div>
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
