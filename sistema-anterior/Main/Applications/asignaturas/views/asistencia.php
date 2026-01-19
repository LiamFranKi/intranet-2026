<script>
$(function(){
	$('#searchForm').submit(function(e){
		e.preventDefault();
		zk.goToUrl('/asignaturas/asistencia?' + $(this).serialize());
	});
	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})
})

function setAsistencia(matricula_id, valor, sender){
	$(sender).parent().find('button')
		.removeClass('btn-primary')
		.removeClass('btn-success')
		.removeClass('btn-danger')
		.removeClass('active')
		.addClass('btn-default');
		
	switch(valor){
		case "PRESENTE":
			$(sender).addClass('btn-primary');
		break;
		case "TARDANZA":
			$(sender).addClass('btn-success');
		break;
		case "FALTA":
			$(sender).addClass('btn-danger');
		break;
	}
	
	
	
	$.post('/asignaturas/save_asistencia',{asignatura_id:'{{ asignatura.id }}', matricula_id: matricula_id, asistencia: valor, fecha:'{{ fecha }}'},function(r){
		if(parseInt(r[0]) == 0){
			zk.pageAlert({message: 'No se pudo guardar la asistencia', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
		}
	},'json');
}
</script>
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Registrar Asistencia - {{ asignatura.grupo.getNombre() }}</h3>
		</div>
		<div class="panel-body">
			<form id="searchForm" class="form form-inline text-center">
				<label>Seleccione Fecha:</label>
				<input type="text" name="fecha" value="{{ fecha }}" class="form-control calendar" />
				<button class="btn btn-primary input-sm">Seleccionar Fecha</button>
				<button type="button" class="btn btn-default " onclick="zk.printDocument('/reportes/imprimir_asistencia_asignatura/?' + $(this).parent().serialize())">{{ icon('printer') }} Imprimir Asistencia</button>
				<input type="hidden" name="id" value="{{ params.id }}" />
			</form>
		</div>
	</div>

	<div class="panel">
		<div class="panel-body">
			<div class="alert alert-info">
		        Haga clic en el botón correspondiente que quiera registrar. <br />Para modificar un registro sólo haga clic en el nuevo registro y el cambio se realizará automaticamente.
		    </div>
			<table class="special">
				<tr>
					<th>Nº</th>
					<th>Apellidos y Nombres</th>
					<th style="padding: 3px 0; text-align: center">
		                <div class="btn-group" data-toggle="buttons-radio">
		                    <button class="btn btn-primary" onclick="$('.presente').trigger('click')">Presente</button>
		                    <button class="btn btn-success" onclick="$('.tardanza').trigger('click')">Tardanza</button>
		                    <button class="btn btn-danger" onclick="$('.falta').trigger('click')">Falta</button>
		                </div>
		            </th>
				</tr>
				{% for matricula in matriculas %}
				{% set alumno = matricula.alumno %}
				{% set asistencia = matricula.getAsistenciaAsignatura(asignatura.id, fecha) %}
					<tr>
						<td class="center">{{ loop.index }}</td>
						<td>{{ alumno.getFullName() }}</td>
						<td>
							<center>
							
		                    <div class="btn-group" data-toggle="buttons-radio">
		                        <button type="button" onclick="setAsistencia('{{ matricula.id }}', 'PRESENTE', this)" class="presente btn {{ asistencia.tipo == 'PRESENTE' ? 'active btn-primary' : 'btn-default' }}">Presente</button>
		                        <button type="button" onclick="setAsistencia('{{ matricula.id }}', 'TARDANZA', this)" class="tardanza btn {{ asistencia.tipo == 'TARDANZA' ? 'active btn-success' : 'btn-default' }}">Tardanza</button>
		                        <button type="button" onclick="setAsistencia('{{ matricula.id }}', 'FALTA', this)" class="falta btn {{ asistencia.tipo == 'FALTA' ? 'active btn-danger' : 'btn-default' }}">Falta</button>
		                    </div>
							</center>
						</td>
					</tr>
				{% endfor %}
			</table>
		</div>
	</div>
</div>