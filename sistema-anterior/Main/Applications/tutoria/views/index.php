<script>
function recomendaciones(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.goToUrl('/tutoria/recomendaciones?ciclo=' + ciclo + '&grupo_id=' + id);
	});
}

function conducta(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.goToUrl('/tutoria/conducta?ciclo=' + ciclo + '&grupo_id=' + id);
	});
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Tutoría</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		
		<li class="active">Tutoría</li>
	</ol>
</div>				

<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Tutoría</h3>
		</div>
		<div class="panel-body">
			{% if grupos_tutor|length > 0 %}
			<table class="special">
				<tr>
					<th>Grado</th>
					<th>Sección</th>
					<th>Nivel</th>
					<th>Turno</th>
					<th>Año Académico</th>
					<th></th>
				</tr>
				{% for grupo in grupos_tutor %}
				<tr>
					<td class="center">{{ grupo.getGrado() }}</td>
					<td class="center">{{ grupo.seccion|upper }}</td>
					<td class="center">{{ grupo.nivel.nombre|upper }}</td>
					<td class="center">{{ grupo.turno.nombre|upper }}</td>
					<td class="center">{{ grupo.anio }}</td>
					<td class="center" style="width: 120px">
						<span class="dropdown dropdown">
							<button class="btn btn-default" data-toggle="dropdown">Opciones <i class="caret"></i></button>
							<ul class="dropdown-menu pull-right">
								<li><a href="#/tutoria/lista_alumnos?grupo_id={{ sha1(grupo.id) }}">{{ icon('application_view_list') }} Lista de Alumnos</a></li>
								<li><a href="#/grupos/lista_alumnos/{{ sha1(grupo.id) }}">{{ icon('calendar') }} Notas / Asistencia</a></li>
								<li><a href="#/mensajes/form?grupo_id={{ grupo.id }}">{{ icon('email') }} Enviar Comunicado</a></li>
								<li><a href="#/grupos/asistencia/{{ sha1(grupo.id) }}">{{ icon('calendar') }} Registrar Asistencia</a></li>
								<li><a href="javascript:;" onclick="recomendaciones('{{ sha1(grupo.id) }}')">{{ icon('comment_add') }} Apreciaciones / Recomendaciones</a></li>
								<li><a href="#/tutoria/asignaturas_grupo/{{ sha1(grupo.id) }}">{{ icon('book_go') }} Lista de Asignaturas</a></li>

								<li><a href="javascript:;" onclick="conducta('{{ sha1(grupo.id) }}')">{{ icon('register') }} Registrar Conducta</a></li>
							</ul>
						</span>
					</td>
				</tr>
				{% endfor %}
			</table>
			{% else %}
			<center><b>NO TIENE GRUPOS ASIGNADOS</b></center>
			{% endif %}
		</div>
	</div>
</div>
