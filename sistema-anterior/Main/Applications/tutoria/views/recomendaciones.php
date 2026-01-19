<script>
$(function(){
	$('#frecomendaciones').submit(function(e){
		e.preventDefault();
		$(this).sendForm('/tutoria/do_recomendaciones' ,function(r){
			switch(parseInt(r[0])){
				case 1:
					zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				break;
				case 0:
					zk.pageAlert({message: 'No se pudieron guardar los notas', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
				break;
			}
		});
	});
});

function get_back(){
	seleccionarCiclo(function(e, v, ciclo){
		zk.goToUrl('/tutoria/recomendaciones?ciclo=' + ciclo + '&grupo_id={{ sha1(grupo.id) }}');
	});
}
</script>

<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Apreciaciones / Recomendaciones - {{ grupo.anio }}</h3>
		</div>
		<div class="panel-body">
			<div class="menu mar-btm ">
				<button class="btn btn-default" type="button" onclick="$('#frecomendaciones').trigger('submit')">{{ icon('table_save') }} Guardar Datos</button>
				<button class="btn btn-default" type="button" onclick="get_back()">{{ icon('calendar') }} Seleccionar {{ COLEGIO.getCicloNotas()|capitalize }}</button>
			</div>
			<form id="frecomendaciones" data-target="#frecomendaciones" data-toggle="overlay">
				<table id="lista_alumnos" class="special" style="width: 100%">
					<thead>
					<tr>
						<th style="width: 50px">Nº</th>
						<th style="width: 300px;">Apellidos y Nombres</th>
				        <th>Recomendación</th>
					</tr>
					
					</thead>
					<tbody>
					{% for matricula in matriculas %}
					{% set alumno = matricula.alumno %}
					<tr class="line-alumno">
						<td class="center">{{ loop.index }}</td>
						<td style="text-align: left; padding-left: 10px; min-width: 300px">{{ alumno.getFullName() }} </a> </td>
				        {% set recomendacion = matricula.getRecomendacion(get.ciclo) %}
				        <td class="center">
				            <input type="text" class="x_c center input-sm form-control" name="recomendaciones[{{ matricula.id }}]" value="{{ recomendacion.descripcion }}" style="width: 300px" />
				        </td>
					</tr>
					{% endfor %}
					</tbody>
				</table>
				<input type="hidden" name="ciclo" value="{{ get.ciclo }}" />

			</form>
		</div>		
	</div>
</div>

