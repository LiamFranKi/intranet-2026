{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaAlumnos').dataTable();
	setMenuActive('topico_atenciones');

	$('#searchForm').changeGradoOptions({
		showLabel: false,
		value: '{{ get.grado }}'
	});

	$('#searchForm').on('submit', function(e){
		e.preventDefault();
		zk.goToUrl('/topico_atenciones?' + $(this).serialize())
	})
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Psicología</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/pagos">Psicología</a></li>
		<li class="active">Lista de Alumnos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Buscar Alumnos</h3>
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
			

			<table id="listaAlumnos" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Apellidos y Nombres</th>
						
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for matricula in matriculas %}
					<tr>
						<td>{{ matricula.alumno.getFullName() }}</td>
						
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/topico_atenciones/alumno?alumno_id={{ matricula.alumno_id }}&tipo=ALUMNO">{{ icon('information') }} Atenciones Registradas - Alumno</a></li>
			                        <li><a href="#/topico_atenciones/alumno?alumno_id={{ matricula.alumno_id }}&tipo=APODERADO">{{ icon('information') }} Atenciones Registradas - Padres de Familia</a></li>
			                        <!--<li><a href="javascript:;" onclick="fancybox('/topico_atenciones/encuestas?alumno_id={{ matricula.alumno_id }}')">{{ icon('application_side_list') }} Encuestas Realizadas</a></li>-->
			                        {% if matricula.alumno.usuario %}
			                        <li><a href="#/mensajes/form?to={{ matricula.alumno.usuario.id }}">{{ icon('email') }} Enviar Recomendación</a></li>
			                        {% endif %}
			                        <li class="divider"></li>
			                        <li><a href="javascript:;" onclick="zk.printDocument('/reportes/topico_atenciones?alumno_id={{ matricula.alumno_id }}')">{{ icon('printer') }} Imprimir Atenciones</a></li>
												
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
