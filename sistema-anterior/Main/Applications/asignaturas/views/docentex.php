{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaAsignaturas').dataTable();
	setMenuActive('cursos_asignados');
});
function registrarNotas(asignatura_id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.goToUrl('/notas/registrar?asignatura_id=' + asignatura_id + '&ciclo=' + ciclo);
	})
}
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
			<h3 class="panel-title">Cursos Asignados</h3>
		</div>
			
		<div class="panel-body">
		
			<table id="listaAsignaturas" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Curso</th>
						<th>Grupo</th>
						
						
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for asignatura in asignaturas %}
					<tr>
						<td>{{ asignatura.curso.nombre }}</td>
						<td>{{ asignatura.grupo.getNombreShort() }}</td>
						
						
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/asignaturas_criterios?asignatura_id={{ sha1(asignatura.id) }}">{{ icon('application_view_list') }} Criterios / Indicadores</a></li>
	
			                       	<li><a href="javascript:;" onclick="zk.printDocument('/grupos/imprimir_horario?grupo_id={{ asignatura.grupo_id }}&asignatura_id={{ asignatura.id }}')">{{ icon('calendar') }} Ver Horario</a></li>
			                        <li class="divider"></li>
									<li><a href="javascript:;" onclick="registrarNotas('{{ sha1(asignatura.id) }}')">{{ icon('application_form_edit') }} Registrar Notas</a></li>
								
									<li><a href="#/asignaturas/asistencia/{{ sha1(asignatura.id) }}">{{ icon('calendar') }} Registrar Asistencia</a></li>
									
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
