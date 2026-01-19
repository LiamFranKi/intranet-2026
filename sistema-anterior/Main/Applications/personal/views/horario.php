{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formPersonal').niftyOverlay();
	$('#formPersonal').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/personal/save_horario', function(r){
				

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

function borrar_horario(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/personal/borrar_horario', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				history.back(-1)
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}
</script>

<div id="page-content">

	<form class="form-horizontal" id="formPersonal" data-target="#formPersonal" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ personal.is_new_record() ? "Registrar" : "Editar" }} Horario</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.inicio }}
				{{ form.fin }}
				{{ form.personal_id }}

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Información General</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Título <span class="text-danger">*</span></label>
						    <div class="col-sm-6 col-lg-6 col-xs-12">{{ form.titulo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Grupo <span class="text-danger">*</span></label>
						    <div class="col-sm-6 col-lg-6 col-xs-12">{{ form.grupo }}</div>
						</div>
					</div>
				</div>



			</div>
		
			<div class="modal-footer">
				<button class="btn btn-danger pull-left" onclick="borrar_horario('{{ horario.id }}')" type="button">Borrar Horario</button>
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>
</div>
{% endblock %}
