<script>
var xc;
$(function(){
	$('#fsasistencia').submit(function(e){
		e.preventDefault();
		//searchForm('/grupos/lista_alumnos_asistencia', $(this).serialize(), 'resultados_asistencia');
		//zk.goToUrl('/grupos/asistencia/{{ sha1(grupo.id) }}?' + $(this).serialize());
        fancybox('/grupos/asistencia/{{ sha1(grupo.id) }}?' + $(this).serialize());
	});
	
	/* $('#x_fecha').datepicker({format: 'yyyy-mm-dd', autoclose: true}) */
	
	$('#x_fecha').bind('change', function(){
		$('#fsasistencia').trigger('submit');
	});
});

function setA(matricula_id, valor, sender){
	$(sender).parent().find('button')
		.removeClass('btn-primary')
		.removeClass('btn-info')
		.removeClass('btn-warning')
		.removeClass('btn-danger')
		.removeClass('active')
		.addClass('btn-default');
		
	switch(valor){
		case "PRESENTE":
			$(sender).addClass('btn-primary');
		break;
		
		case "TARDANZA_JUSTIFICADA":
		case "TARDANZA":
			$(sender).addClass('btn-info');
		break;

		case "FALTA_JUSTIFICADA":
			$(sender).addClass('btn-warning');
		break;

		case "FALTA":
			$(sender).addClass('btn-danger');
		break;
	}

	$.post('/grupos/save_asistencia',{grupo_id:'{{ grupo.id }}', matricula_id: matricula_id, asistencia: valor, fecha:'{{ fecha }}'},function(r){
		if(parseInt(r[0]) == 0){
			$.prompt('No se pudo guardar la asistencia');
		}
	},'json');
}
</script>

<div id="" style="min-width: 700px">
	<div class="modal-content">
		<div class="modal-header">
			<h3 class="modal-title">Registro de Asistencia</h3>
		</div>
		<div class="modal-body">
			<form id="fsasistencia" class="text-center form form-inline">
				<label>Seleccione Fecha:</label>
				<input type="date" name="fecha" id="x_fecha" value="{{ fecha|date('Y-m-d') }}" class="form-control input-sm" />
				<!-- <button class="btn btn-primary input-sm">Seleccionar Fecha</button> -->
				<button type="button" class="btn btn-default input-sm" onclick="zk.printDocument('/reportes/imprimir_asistencia/{{ grupo.id }}?' + $(this).parent().serialize())">{{ icon('printer') }} Imprimir Asistencia</button>
			</form>
		</div>
	</div>

	<div class="modal-content mar-top">
		<div class="modal-body">
			<table class="special">
				<tr>
					<th>Nº</th>
					<th>Apellidos y Nombres</th>
					<th style="padding: 3px 0; text-align: center">
		                <div class="btn-group" data-toggle="buttons-radio">
		                    <button class="btn btn-primary btn-sm" onclick="$('.presente').trigger('click')">Presente</button>
		                    
		                    
		                    <button class="btn btn-info btn-sm" onclick="$('.tardanza_injustificada').trigger('click')">Tardanza</button>
                            <button class="btn btn-info btn-sm" onclick="$('.tardanza_justificada').trigger('click')">TJ</button>
		                    <button class="btn btn-danger btn-sm" onclick="$('.falta_injustificada').trigger('click')">Falta</button>
                            <button class="btn btn-warning btn-sm" onclick="$('.falta_justificada').trigger('click')">FJ</button>
		                    
		                    
		                </div>
		            </th>
				</tr>
				{% for matricula in matriculas %}
				{% set alumno = matricula.alumno %}
				{% set asistencia = matricula.getAsistencia(fecha) %}
					<tr>
						<td class="center">{{ loop.index }}</td>
						<td>{{ alumno.getFullName() }}</td>
						<td>
							<center>
							
		                    <div class="btn-group" data-toggle="buttons-radio">
		                        <button type="button" onclick="setA('{{ matricula.id }}', 'PRESENTE', this)" class="btn-sm presente btn {{ asistencia.tipo == 'PRESENTE' ? 'active btn-primary' : 'btn-default' }}">Presente</button>
		                        <button type="button" onclick="setA('{{ matricula.id }}', 'TARDANZA', this)" class="btn-sm tardanza_injustificada btn {{ asistencia.tipo == 'TARDANZA' ? 'active btn-info' : 'btn-default' }}">Tardanza</button>
                                <button type="button" onclick="setA('{{ matricula.id }}', 'TARDANZA_JUSTIFICADA', this)" class="btn-sm tardanza_justificada btn {{ asistencia.tipo == 'TARDANZA_JUSTIFICADA' ? 'active btn-info' : 'btn-default' }}">TJ</button>
		                        
		                        <button type="button" onclick="setA('{{ matricula.id }}', 'FALTA', this)" class="btn-sm falta_injustificada btn {{ asistencia.tipo == 'FALTA' ? 'active btn-danger' : 'btn-default' }}">Falta</button>
		                        <button type="button" onclick="setA('{{ matricula.id }}', 'FALTA_JUSTIFICADA', this)" class="btn-sm falta_justificada btn {{ asistencia.tipo == 'FALTA_JUSTIFICADA' ? 'active btn-warning' : 'btn-default' }}">FJ</button>
		                        
		                        
		                    </div>
							</center>
						</td>
					</tr>
				{% endfor %}
			</table>
		</div>
	</div>
</div>