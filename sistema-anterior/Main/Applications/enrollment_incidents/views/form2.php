{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formEnrollmentIncident').niftyOverlay();
	$('#formEnrollmentIncident').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/enrollment_incidents/save', function(r){


				switch(parseInt(r.status)){
					case 1:
						zk.pageAlert({message: 'Se agregaron estrellas con éxito', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						//zk.goToUrl('/enrollment_incidents');
                        $.fancybox.close()
                        zk.reloadPage()
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

<div id="">

	<form class="form-horizontal" autocomplete="off" id="formEnrollmentIncident" data-target="#formEnrollmentIncident" data-toggle="overlay">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Dar/Quitar Estrellas</h3>
			</div>
			<div class="modal-body">
				{{ form.id|raw }}
                {{ form.enrollment_id }}
                {{ form.assignment_id }}
                <input type="hidden" name="type" value="2">
                <input type="hidden" name="description" value="Estrellas Agregadas">
                
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Estrellas <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.points }}</div>
				</div>
			</div>

			<div class="modal-footer">
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="$.fancybox.close()">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>
</div>
{% endblock %}
