{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formCashAccount').niftyOverlay();
	$('#formCashAccount').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/cash_accounts/save', function(r){


				switch(parseInt(r.status)){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/cash_accounts');
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
		<h1 class="page-header text-overflow">Caja</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/cash_accounts">Caja</a></li>
		<li class="active">Registro de Cuenta</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formCashAccount" data-target="#formCashAccount" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ cashAccount.is_new_record() ? "Registrar" : "Editar" }} Cuenta</h3>
			</div>
			<div class="panel-body">
				{{ form.id|raw }}
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nombre <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.name }}</div>
				</div>
                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descripción</label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.description }}</div>
				</div>
                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Moneda <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.cash_currency_id }}</div>
				</div>
                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Tipo <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.cash_account_type_id }}</div>
				</div>

                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Privacidad <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-3">{{ form.privacy }}</div>
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
