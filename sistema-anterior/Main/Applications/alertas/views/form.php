{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formAlerta').niftyOverlay();
	$('#formAlerta').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/alertas/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/alertas');
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
		if(this.value == 'PENSION'){
			$('.only_pension').show();
		}else{
			$('.only_pension').hide();
		}
	});

	$('#tipo').trigger('change');
	
	$('.tip').tooltip();
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Alertas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/alertas">Alertas</a></li>
		<li class="active">Registro de Alerta</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formAlerta" data-target="#formAlerta" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ alerta.is_new_record() ? "Registrar" : "Editar" }} Alerta</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nombre <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nombre }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Contenido <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.contenido }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Tipo <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.tipo }}</div>
				</div>

				<div class="form-group form-group-sm only_pension">
				    <label class="col-sm-4 control-label" for="">Dias Antes / Después<small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.dias }}</div>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.position }}</div>
				</div>

			

				<div class="form-group form-group-sm only_pension">
				    <label class="col-sm-4 control-label" for="">Cantidad de Alertas<small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.cantidad }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Email Remitente <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.email_remitente }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Asunto <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.asunto }}</div>
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
