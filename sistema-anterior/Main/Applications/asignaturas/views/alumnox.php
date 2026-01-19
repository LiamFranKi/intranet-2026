{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaAsignaturas').dataTable();
	setMenuActive('cursos_asignados');
});

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Cursos Asignados</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="/">Asignaturas</a></li>
		<li class="active">Cursos Asignados</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Cursos Asignados - {{ matricula.grupo.getNombre() }}</h3>
		</div>
			
		<div class="panel-body">
		
			<table id="listaAsignaturas" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Curso</th>
						<th>Docente</th>
						
						
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for asignatura in asignaturas %}
					<tr>
						<td>{{ asignatura.curso.nombre }}</td>
						<td>{{ asignatura.personal.getFullName() }}</td>
						
						
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="javascript:;" onclick="zk.printDocument('/reportes/imprimir_asistencia_asignatura_matricula?matricula_id={{ matricula.id }}&asignatura_id={{ asignatura.id }}')">{{ icon('printer') }} Imprimir Asistencia</a></li>
									<li><a href="#/mensajes/form?to={{ asignatura.personal.usuario.id }}">{{ icon('email') }} Enviar mensaje al docente</a></li>
								</ul>
							</div>
						</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>

{% endblock %}
