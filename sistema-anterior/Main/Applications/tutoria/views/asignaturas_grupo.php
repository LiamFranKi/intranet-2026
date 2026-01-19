<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Asignaturas - {{ grupo.getNombreShort() }}</h3>
		</div>
		<div class="panel-body">
			<table class="special">
				<tr>
					<th>Curso</th>
					<th>Docente</th>
					<th></th>
				</tr>
				{% for asignatura in grupo.asignaturas %}
				<tr>
					<td>{{ asignatura.curso.nombre }}</td>
					<td>{{ asignatura.personal.getFullName() }}</td>

					<td class="center">
						<a class="btn btn-default" href="#/aula_virtual/index/{{ sha1(asignatura.id) }}">{{ icon('house') }} Aula Virtual</a>
					</td>
					
				</tr>
				{% endfor %}
			</table>
		</div>
	</div>
</div>