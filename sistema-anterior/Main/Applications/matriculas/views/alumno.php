{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaMatriculas').dataTable();
	setMenuActive('matriculas_alumno');
});

function printNotas(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.printDocument('/notas/imprimir?ciclo=' + ciclo + '&matricula_id=' + id);
	});
}

function viewNotas(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.goToUrl('/notas/detallado?ciclo=' + ciclo + '&matricula_id=' + id);
	});
}

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Matriculas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/matriculas">Matriculas</a></li>
		<li class="active">Lista de Matriculas</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Matriculas</h3>
		</div>
		<div class="panel-body">
			

			<table id="listaMatriculas" class="table table-striped table-bordered">
				<thead>
					<tr>
						
						<th>Grupo</th>

					
						<th>Modalidad</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for matricula in matriculas %}
					<tr>
						
						<td>{{ matricula.grupo.getNombre() }}<br />{{ matricula.grupo.sede.nombre }}</td>
						
						<td class="text-center">{{ matricula.modalidad }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="javascript:;" onclick="printNotas('{{ sha1(matricula.id) }}')">{{ icon('list') }} Notas</a></li>
									<li><a href="javascript:;" onclick="viewNotas('{{ sha1(matricula.id) }}')">{{ icon('list') }} Notas Detalladas</a></li>
									<li><a href="javascript:;" onclick="zk.printDocument('/reportes/imprimir_asistencia_matricula/{{ matricula.id }}')">{{ icon('calendar') }} Asistencia</a></li>
									
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
