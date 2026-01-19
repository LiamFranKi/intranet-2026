{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formCashAccountFlow').niftyOverlay();
	$('#formCashAccountFlow').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/cash_account_flows/save', function(r){


				switch(parseInt(r.status)){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/cash_account_flows?cashAccountId={{ sha1(cashAccountFlow.cash_account_id) }}');
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
		<h1 class="page-header text-overflow">Movimientos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;">Movimientos</a></li>
		<li class="active">Registro de Movimientos</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formCashAccountFlow" data-target="#formCashAccountFlow" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ cashAccountFlow.is_new_record() ? "Registrar" : "Editar" }} Movimiento</h3>
			</div>
			<div class="panel-body">
				{{ form.id|raw }}
                {{ form.cash_account_id|raw }}
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descripción <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.description }}</div>
				</div>
                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Rubro <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.entry }}</div>
				</div>
                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Fecha / Hora <small class="text-danger">*</small></label>
				    <div class="col-sm-3 col-xs-12 col-lg-2">{{ form.date }}</div>
                    <div class="col-sm-3 col-xs-12 col-lg-2">{{ form.time }}</div>
				</div>

                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Tipo <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.type }}</div>
				</div>

                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Monto <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.amount }}</div>
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
