{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formPago').niftyOverlay();
	$('#formPago').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/pagos/save', function(r){
				

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

	$('#tipo').bind('change', function(){
		if(this.value == 1 || this.value == 3){
			$('.nro_pago').show();
		}else{
			$('.nro_pago').hide();
		}
	});

	$('#tipo').trigger('change');

	$('#forma_pago').bind('change', function(){
		if(this.value == 'TARJETA'){
			return $('.tipo_tarjeta').show();
		}
		return $('.tipo_tarjeta').hide();
	});
	$('#forma_pago').trigger('change');

	$('#estado_pago').bind('change', function(){
		if(this.value == 'PENDIENTE'){
			return $('.cFechaPago').hide();
		}
		$('.cFechaPago').show();
	});

	$('#estado_pago').trigger('change');

	//$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Pagos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/pagos">Pagos</a></li>
		<li class="active">Registro de Pago</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formPago" data-target="#formPago" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ pago.is_new_record() ? "Registrar" : "Editar" }} Pago</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.matricula_id }}

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Monto / Mora <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-6 col-lg-2">{{ form.monto }}</div>
				    <div class="col-sm-6 col-xs-6 col-lg-2">{{ form.mora }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Forma de Pago <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.forma_pago }}</div>
				    <div class="col-sm-6 col-xs-12 col-lg-3 tipo_tarjeta">{{ form.tipo_tarjeta }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Tipo de Pago<small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.tipo }}</div>
				    <div class="col-sm-6 col-xs-12 col-lg-3 nro_pago">{{ form.nro_pago }}</div>
				</div>

	
				

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Fecha / Hora<small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_hora }}</div>
				</div>

				

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descripción</label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.descripcion }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Observaciones</label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.observaciones }}</div>
				</div>

		

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Estado Pago <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.estado_pago }}</div>
				</div>

				<div class="form-group form-group-sm cFechaPago">
				    <label class="col-sm-4 control-label" for="">Fecha Cancelado <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_cancelado }}</div>
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
