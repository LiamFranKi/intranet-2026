{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formImpresion').niftyOverlay();
	$('#formImpresion').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/impresiones/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.reloadPage();
						$.fancybox.close()
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

<form class="form-horizontal" autocomplete="off" id="formImpresion" data-target="#formImpresion" data-toggle="overlay">
	<div class="modal-content">
		<div class="modal-header">
			<h3 class="modal-title">{{ impresion.is_new_record() ? "Registrar" : "Editar" }} Impresión</h3>
		</div>
		<div class="modal-body">
			{{ form.id|raw }}
		

			<div class="form-group form-group-sm">
				<label class="col-sm-4 control-label" for="">Fecha de Emisión <small class="text-danger">*</small></label>
				<div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_impresion }}</div>
			</div>

			<div class="form-group form-group-sm">
				<label class="col-sm-4 control-label" for="">Hora de Emisión <small class="text-danger">*</small></label>
				<div class="col-sm-6 col-xs-12 col-lg-4">{{ form.hora_impresion }}</div>
			</div>

			
		</div>
		<div class="modal-footer">
			<button class="btn btn-default" data-dismiss="modal" type="button" onclick="$.fancybox.close()">Cancelar</button>
			<button class="btn btn-primary" type="submit">Guardar Datos</button>
		</div>
	</div>

</form>

{% endblock %}
