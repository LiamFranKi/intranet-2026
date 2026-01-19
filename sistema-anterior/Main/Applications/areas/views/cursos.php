{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('.check').niftyCheck();
	$('#formArea').niftyOverlay();
	$('#formArea').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/areas/save_cursos', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/areas');
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

	<form class="form-horizontal" autocomplete="off" id="formArea" data-target="#formArea" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Lista de Cursos</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
			

				<table class="special table table-hover">
		        {% for curso in area.getCursosByNivel() %}
		            <tr>
		                <td>{{ curso.nombre }}</td>
		                <td class="center">
	                        <label class="form-checkbox form-normal form-primary check"><input type="checkbox" id="curso_{{ curso.id }}" name="curso[{{ curso.id }}]" value="{{ curso.id }}" {{ area.hasCurso(curso.id) ? 'checked' : '' }} /></label>
		                	<!--<label><input type="checkbox" name="curso[]" value="{{ curso.id }}" {{ area.HasCurso(curso.id) ? 'checked' : '' }} /></label>-->
		                </td>
		            </tr>
		        {% endfor %}
		        </table>

			
			</div>
		
			<div class="modal-footer">
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>
</div>
{% endblock %}
