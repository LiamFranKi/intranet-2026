{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaAsignaturas').dataTable();
	setMenuActive('asignaturas');

	$('#searchForm').changeGradoOptions({
		showLabel: false,
		value: '{{ get.grado }}'
	});

	$('#searchForm').on('submit', function(e){
		e.preventDefault();
		zk.goToUrl('/asignaturas?' + $(this).serialize())
	})
});

function borrar_asignatura(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/asignaturas/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}

function registrarNotas(asignatura_id){
	seleccionarCiclo(function(e, v, ciclo){
		fancybox('/notas/registrar?asignatura_id=' + asignatura_id + '&ciclo=' + ciclo);
	})
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Asignaturas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/asignaturas">Asignaturas</a></li>
		<li class="active">Lista de Asignaturas</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Asignaturas</h3>
		</div>
		<div class="panel-body">
			<form id="searchForm" class="form-inline text-center">
				<div class="form-group">
					{{ form.sede_id }}
				</div>
				<div class="form-group">
					{{ form.nivel_id }}
				</div>
				<div class="form-group">
					{{ form.grado }}
				</div>
				<div class="form-group">
					{{ form.seccion }}
				</div>
				<div class="form-group">
					{{ form.turno_id }}
				</div>
				<div class="form-group">
					{{ form.anio }}
				</div>
				<div class="form-group">
					<button class="btn btn-primary">Buscar</button>
				</div>
			</form>
		</div>
	</div>
	<div class="panel">
		
		<div class="panel-body">
		
			<table id="listaAsignaturas" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Docente</th>
						<th>Curso</th>
						<th>Grupo</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for asignatura in asignaturas %}
					<tr>
						<td>{{ asignatura.personal.getFullName() }}</td>
						<td>{{ asignatura.curso.nombre }}</td>
						<td>{{ asignatura.grupo.getNombreShort2() }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
                                    {% if USUARIO.is('ADMINISTRADOR') %}
									<li><a href="#/asignaturas_criterios?asignatura_id={{ sha1(asignatura.id) }}">{{ icon('application_view_list') }} Criterios / Indicadores</a></li>
									<li><a href="#/aula_virtual/index/{{ sha1(asignatura.id) }}">{{ icon('house') }} Aula Virtual</a></li>
                                    <li><a href="#/asignaturas/lista_alumnos/{{ sha1(asignatura.id) }}">{{ icon('application_view_list') }} Lista de Alumnos</a></li>
                                    <li><a href="javascript:;" onclick="fancybox('/aula_virtual/copy_form/{{ sha1(asignatura.id) }}')">{{ icon('page_copy') }} Copiar Contenido</a></li>
									<li><a href="javascript:;" onclick="registrarNotas('{{ sha1(asignatura.id) }}')">{{ icon('application_form_edit') }} Registrar Notas</a></li>
									<li><a href="#/asignaturas/asistencia/{{ sha1(asignatura.id) }}">{{ icon('calendar') }} Registrar Asistencia</a></li>
									<li><a href="#/asignaturas/form/{{ sha1(asignatura.id) }}">{{ icon('register') }} Editar Asignatura</a></li>
									<li><a href="javascript:;" onclick="borrar_asignatura('{{ sha1(asignatura.id) }}')">{{ icon('delete') }} Borrar Asignatura</a></li>
                                    {% elseif USUARIO.is('ASISTENCIA') %}
                                    <li><a href="#/aula_virtual/index/{{ sha1(asignatura.id) }}">{{ icon('house') }} Aula Virtual</a></li>
                                    {% endif %}
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
