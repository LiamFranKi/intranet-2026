{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formBoleta_Concepto').niftyOverlay();
	$('#formBoleta_Concepto').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/boletas_conceptos/do_actualizar_stock', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/boletas_conceptos');
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
		<h1 class="page-header text-overflow">Conceptos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/boletas_conceptos">Conceptos</a></li>
		<li class="active">Actualizar Stock</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formBoleta_Concepto" data-target="#formBoleta_Concepto" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Actualizar Stock</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Cantidad Inicial <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.stock_inicial }}</div>
				</div>

			
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Precio Unit. Inicial <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.precio_inicial }}</div>
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
