{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formGrupo').niftyOverlay();
	$('#formGrupo').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/grupos/save_costos', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						
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

	$('#formGrupo').changeGradoOptions({
		value: '{{ grupo.grado }}',
	});
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Grupos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/grupos">Grupos</a></li>
		<li class="active">Registro de Grupo</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formGrupo" data-target="#formGrupo" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Modificar Costos</h3>
			</div>
			<div class="panel-body">
			
				<table class="special">
					<tr>
						<th>Apellidos y Nombres</th>
						<th>Fecha de Registro</th>
						<th>Estado</th>
						<th>Matrícula</th>
						<th>Pensión</th>
						<th>Agenda</th>
					</tr>	
				
				{% for matricula in matriculas %}
				{% set alumno = matricula.alumno %}

					<tr>
					
						<td style="min-width: 200px"><a href="javascript:;" onclick="">{{ alumno.getFullName() }}</a></td>
					
						<td class="center">{{ matricula.getFechaRegistro() }}</td>
						<td style="color: {{ matricula.getEstado() == 'REGULAR' ? '#008040' : '#FF0000' }}" class="center">{{ matricula.getEstado() }}</td>
						<td class="center">
							<input type="text" name="costos[{{ matricula.id }}][matricula]" value="{{ matricula.costo.matricula }}" class="form-control text-center" style="width: 70px">
							<input type="hidden" name="costos[{{ matricula.id }}][costo_id]" value="{{ matricula.costo_id }}">
						</td>
						<td class="center">
							<input type="text" name="costos[{{ matricula.id }}][pension]" value="{{ matricula.costo.pension }}" class="form-control text-center" style="width: 70px">
						</td>
						<td class="center">
							<input type="text" name="costos[{{ matricula.id }}][agenda]" value="{{ matricula.costo.agenda }}" class="form-control text-center" style="width: 70px">
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
