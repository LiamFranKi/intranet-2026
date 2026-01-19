{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formInvoicePayment').niftyOverlay();
	$('#formInvoicePayment').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/invoice_payments/save', function(r){


				switch(parseInt(r.status)){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						$.fancybox.back()
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

<form class="form-horizontal" autocomplete="off" id="formInvoicePayment" data-target="#formInvoicePayment" data-toggle="overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Registro de Pago</h3>
        </div>
        <div class="modal-body">
            {{ form.id|raw }}
            {{ form.invoice_id|raw }}
            <div class="form-group form-group-sm">
                <label class="col-sm-4 control-label" for="">Monto S/ <small class="text-danger">*</small></label>
                <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.amount }}</div>
            </div>

            <div class="form-group form-group-sm">
                <label class="col-sm-4 control-label" for="">Comentarios</label>
                <div class="col-sm-8 col-xs-12 col-lg-8">{{ form.comments }}</div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="$.fancybox.back()">Cancelar</button>
            <button class="btn btn-primary" type="submit">Guardar Datos</button>
        </div>
    </div>
</form>

{% endblock %}
