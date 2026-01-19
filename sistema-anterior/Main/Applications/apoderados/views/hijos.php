<script>
$(function(){
	$('#listaAlumnos').dataTable();
	setMenuActive('hijos_{{ get.tipo }}');
})
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Alumnos a Cargo</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/apoderados">Apoderados</a></li>
		<li class="active">Lista de Alumnos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Alumnos a Cargo</h3>
		</div>
	
		
		<div class="panel-body">
			

			<table id="listaAlumnos" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Apellidos y Nombres</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for alumno in alumnos %}
                    {% set matricula = alumno.getLastMatriculaByYear() %}
					<tr>
						<td>{{ alumno.getFullName() }}</td>
						<td class="text-center" style="width: 150px">
							<span class="dropdown dropup">
								<button class="btn btn-default" data-toggle="dropdown">Opciones <i class="caret"></i></button>
								<ul class="dropdown-menu pull-right ">
									{% if get.tipo == 'NORMAL' %}
									<li><a href="#/asignaturas/alumno?alumno_id={{ sha1(alumno.id) }}">{{ icon('book_next') }} Cursos Asignados</a></li>
									<li><a href="#/matriculas/alumno?alumno_id={{ sha1(alumno.id) }}">{{ icon('layout_edit') }} Notas y Asistencia</a></li>
                                    <li><a href="#/alumnos/editar_perfil/{{ sha1(alumno.id) }}">{{ icon('register') }} Editar Datos</a></li>
                                    {% if matricula %}
                                    <li><a href="javascript:;" onclick="zk.printDocument('/matriculas/fotocheck/{{ sha1(matricula.id) }}')">{{ icon('page') }} Imprimir Fotocheck</a></li>
                                    {% endif %}
									{% endif %}
									{% if get.tipo == 'PAGOS' %}
									<li><a href="#/pagos/historial?alumno_id={{ sha1(alumno.id) }}">{{ icon('application_side_tree') }} Pagos Realizados</a></li>
									{% endif %}
									{% if get.tipo == 'PSICOLOGIA' %}
									<li><a href="javascript:;" onclick="zk.printDocument('/reportes/topico_atenciones?alumno_id={{ alumno.id }}')">{{ icon('shield') }} Atenciones en Psicología</a></li>
									{% endif %}
								</ul>
							</span>
						</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>