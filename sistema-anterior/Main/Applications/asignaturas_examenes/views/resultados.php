<script>
function borrarPrueba(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/asignaturas_examenes/borrar_prueba', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.reloadPage();
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}

function volverCalificar(){
	if(confirm('¿Está seguro de volver a calificar? Se sobreescribirá cualquier modificación que haya hecho.')){
		$.post('/asignaturas_examenes/calificar', {id: '{{ sha1(asignatura_examen.id) }}'}, function(r){
			zk.pageAlert({message: 'Operación exitosa', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
			zk.reloadPage()
		}, 'json')
	}
}
</script>
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">RESULTADOS - {{ asignatura_examen.titulo }}</h3>
		</div>
		<div class="panel-body">

			{% if matriculas|length > 0%}
			<div class="mar-btm text-center">
				<button class="btn btn-default" onclick="zk.printDocument('/asignaturas_examenes/resultados_excel/{{ sha1(asignatura_examen.id) }}')">{{ icon('list') }} Descargar Excel</button>
				<button class="btn btn-default" onclick="volverCalificar()">{{ icon('register') }} Volver a Calificar</button>
			</div>
			<table class="special">
				<tr>
					<th>Examen</th>
					<td>{{ asignatura_examen.titulo }}</td>
				</tr>
				{% if asignatura_examen.puntajeGeneral() %}
				<tr>
					<th>Puntaje por respuesta correcta</th>
					<td>{{ asignatura_examen.puntos_correcta }} Punto(s)</td>
				</tr>
				{% endif %}
				{% if asignatura_examen.penalizarIncorrecta() %}
				<tr>
					<th>Penalización por Incorrecta</th>
					<td>{{ asignatura_examen.penalizacion_incorrecta }} Punto(s)</td>
				</tr>
				{% endif %}
			</table>
			<table class="special">
				<tr>
					<th style="width: 30px">Nº</th>
					<th>Apellidos y Nombres</th>

					<th>Puntaje</th>
					<th>Correctas</th>
					<th>Incorrectas</th>
					<th></th>
				</tr>
				{% for matricula in matriculas %}
				{% set prueba = matricula.getBestTestAula(asignatura_examen) %}
				<tr>
					<td class="text-center">{{ _key + 1 }}</td>
					<td>{{ matricula.alumno.getFullName() }}</td>
					<td class="text-center">{{ prueba ? prueba.puntaje : '-' }}</td>
					<td class="text-center">{{ prueba ? prueba.correctas : '-' }}</td>
					<td class="text-center">{{ prueba ? prueba.incorrectas : '-' }}</td>
					<td class="text-center">
						{% if USUARIO.is('DOCENTE') or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
							{% if prueba %}
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									{% if prueba.respuestas %}
									<li><a href="#/asignaturas_examenes/resultados_detalles/{{ sha1(prueba.id) }}">{{ icon('application_view_list') }} Ver Detalles</a></li>
									
									{% endif %}

									<li><a href="#/asignaturas_examenes/editar_prueba?prueba_id={{ sha1(prueba.id) }}">{{ icon('register') }} Editar Resultados</a></li>
									<li><a href="javascript:;" onclick="borrarPrueba('{{ prueba.id }}')">{{ icon('delete') }} Borrar Resultados</a></li>
									
								</ul>
							</div>
							{% else %}
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/asignaturas_examenes/editar_prueba?examen_id={{ asignatura_examen.id }}&matricula_id={{ matricula.id }}">{{ icon('register') }} Editar Resultados</a></li>
								</ul>
							</div>
							{% endif %}
						{% endif %}
					</td>
				</tr>
				{% endfor %}
			</table>
			{% else %}
			<p class="text-center"><b>NO SE ENCONTRARON RESULTADOS</b></p>
			{% endif %}
		</div>
	</div>
</div>