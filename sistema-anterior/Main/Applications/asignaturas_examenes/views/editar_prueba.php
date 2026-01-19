{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formAsignatura_Examen').niftyOverlay();
	$('#formAsignatura_Examen').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/asignaturas_examenes/save_prueba', function(r){
				

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
});
</script>

<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formAsignatura_Examen" data-target="#formAsignatura_Examen" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Editar Resultados</h3>
			</div>
			<div class="panel-body">
			
		

				<input type="hidden" name="id" value="{{ prueba.id }}">
		
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Puntaje</label>
				    <div class="col-sm-4"><input type="text" name="puntaje" value="{{ prueba.puntaje }}" class="form-control" /></div>
				</div>
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Correctas</label>
				    <div class="col-sm-4"><input type="text" name="correctas" value="{{ prueba.correctas }}" class="form-control" /></div>
				</div>
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Incorrectas</label>
				    <div class="col-sm-4"><input type="text" name="incorrectas" value="{{ prueba.incorrectas }}" class="form-control" /></div>
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
