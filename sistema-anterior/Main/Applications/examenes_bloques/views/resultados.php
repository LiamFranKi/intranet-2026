<script>
function calificar_prueba(id){
	$.post('/examenes_bloques/calificar_prueba', {prueba_id: id}, function(r){
		zk.pageAlert({message: 'Prueba calificada correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
		zk.reloadPage()
	}, 'json');
}

function borrar_prueba(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/examenes_bloques/borrar_prueba', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}
</script>
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Resultados</h3>
		</div>
		<div class="panel-body" style="overflow: auto">
			{% if matriculas|length > 0%}
			<table class="special">
				<tr>
					<th>Examen</th>
					<td>{{ examen.titulo }} - Bimestre {{ compartido.ciclo }} - {{ COLEGIO.roman(compartido.nro) }}</td>
				</tr>
				
				<tr>
					<th>Puntaje por respuesta correcta</th>
					<td>{{ examen.puntos_correcta }} Punto(s)</td>
				</tr>
				
				
			</table>
			<table class="special">
				<tr>
					<th style="width: 30px">Nº</th>
					<th>Apellidos y Nombres</th>

					{% for curso in cursos %}
					{% if not get.curso_id or (get.curso_id == curso.id) %}
						<th class="nombreCurso" style="font-size: 10px">{{ curso.nombre }}</th>
					{% endif %}
					{% endfor %}
					<th></th>
				</tr>
				{% for matricula in matriculas %}

				{% set prueba = matricula.getBestTestBloque(compartido) %}

				{% set resultados = prueba.getResultados() %}
				<tr>
					<td class="text-center">{{ _key + 1 }}</td>
					<td>{{ matricula.alumno.getFullName() }} </td>
					{% for curso in cursos %}
					{% if not get.curso_id or (get.curso_id == curso.id) %}
					<td class="text-center">
						
						{{ not is_null(resultados[curso.id].puntaje) ? resultados[curso.id].puntaje : '-' }}</td>
					{% endif %}
					{% endfor %}
					<td class="text-center" style="width: 120px">
						{% if prueba %}
						<div class="btn-group dropup">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
							<ul class="dropdown-menu pull-right" role="menu">
								
								<li><a href="#/examenes_bloques/resultados_respuestas/{{ sha1(prueba.id) }}">{{ icon('application_view_list') }} Ver Resultados</a></li>
								<li><a href="javascript:;" onclick="calificar_prueba('{{ sha1(prueba.id) }}')">{{ icon('application_edit') }} Volver a Calificar</a></li>
								<li><a href="javascript:;" onclick="borrar_prueba('{{ sha1(prueba.id) }}')">{{ icon('delete') }} Borrar Prueba</a></li>
								
							</ul>
						</div>
						{% else %}
						<button class="btn btn-danger">No Disponible</button>
						{% endif %}
					</td>
					
						<!--
						{% if prueba %}
						<button class="btn btn-default xtip" title="Ver Detalles" onclick="fancybox('/examenes_bloques/resultados_respuestas/{{ prueba.id }}?curso_id={{ get.curso_id }}&asignatura_id={{ get.asignatura_id }}')">{{ icon('list') }}</button>
						{% if USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
						<button class="btn btn-default xtip" title="Volver a Calificar" onclick="calificar_prueba('{{ prueba.id }}')">{{ icon('application_edit') }}</button>
						<button class="btn btn-default xtip" title="Borrar Prueba" onclick="borrar_prueba('{{ prueba.id }}')">{{ icon('delete') }}</button>
						{% endif %}
						{% else %}
						<button class="btn btn-danger">No Disponible</button>
						{% endif %}
					-->
					
				</tr>

				{% endfor %}
			</table>
			{% else %}
			<p class="center"><b>NO SE ENCONTRARON RESULTADOS</b></p>
			{% endif %}
		</div>	
	</div>
</div>