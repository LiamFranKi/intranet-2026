<script>
$(function(){
	$('#lmatriculas').dataTable();
});

function printNotas(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.printDocument('/notas/imprimir?ciclo=' + ciclo + '&matricula_id=' + id);
	});
}

function printNotasLetras(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.printDocument('/notas/imprimir_letras?ciclo=' + ciclo + '&matricula_id=' + id);
	});
}

function viewNotas(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.goToUrl('/notas/detallado?ciclo=' + ciclo + '&matricula_id=' + id);
	});
}

function printNotasExcel(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.printDocument('/notas/imprimir_excel?ciclo=' + ciclo + '&matricula_id=' + id);
	});
}

function printNotasAll(){
	seleccionarCiclo(function(e, v, ciclo){
		zk.printDocument('/notas/imprimir_grupo?ciclo=' + ciclo + '&grupo_id={{ grupo.id }}');
	});
}


</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Lista de Alumnos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/grupos">Grupos</a></li>
		<li class="active">Lista de Alumnos</li>
	</ol>
</div>
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Alumnos</h3>
		</div>
		<div class="panel-body">
			<table class="special">
			    <tr>
			        <th>TUTOR</th>
			        <td class="center">{% if grupo.tutor %}{{ grupo.tutor.getFullName()|upper }}{% else %}NO SE ASIGNO TUTOR{% endif %}</td>
			    </tr>
			</table>

			<table class="dataTable table table-striped table-bordered table-hover" id="lmatriculas">
				<thead>
					<tr>
						<th>Apellidos y Nombres</th>
						<th>Fecha de Registro</th>
						<th>Estado</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				{% for matricula in matriculas %}
				{% set alumno = matricula.alumno %}

					<tr>
						<td style="min-width: 200px"><a href="javascript:;" onclick="fancybox('/alumnos/ver_datos/{{ matricula.alumno_id }}')">{{ alumno.apellido_paterno|upper }} {{ alumno.apellido_materno|upper }} , {{ alumno.nombres|upper }}</a></td>
					
						<td class="text-center">{{ matricula.getFechaRegistro() }}</td>
						<td style="color: {{ matricula.getEstado() == 'REGULAR' ? '#008040' : '#FF0000' }}" class="text-center">{{ matricula.getEstado() }}</td>
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">

                                    <li><a href="javascript:;" onclick="zk.printDocument('/reportes/imprimir_matricula/{{ sha1(matricula.id) }}')">{{ icon('printer') }} Imprimir Datos</a></li>
                                    {% if USUARIO.is('ADMINISTRADOR') %}
                                        <li><a href="javascript:;" onclick="printNotas('{{ sha1(matricula.id) }}')">{{ icon('printer') }} Imprimir Notas - PDF</a></li>
                                        {% if grupo.nivel_id == 2 %}
                                        <li><a href="javascript:;" onclick="printNotasLetras('{{ sha1(matricula.id) }}')">{{ icon('printer') }} Imprimir Notas - Letras</a></li>
                                        {% endif %}
                                        <li><a href="javascript:;" onclick="printNotasExcel('{{ sha1(matricula.id) }}')">{{ icon('printer') }} Imprimir Notas - Excel</a></li>
                                        <li><a href="javascript:;" onclick="viewNotas('{{ sha1(matricula.id) }}')">{{ icon('list') }} Ver Notas Detalladas</a></li>
                                        <li class="divider"></li>
                                        <!--<li><a href="javascript:;" onclick="fancybox('/sanciones?matricula_id={{ matricula.id }}&back=true')">{{ icon('award_star_add') }} Méritos / Deméritos</a></li>-->
                                        <li><a href="javascript:;" onclick="fancybox('/enrollment_incidents/summary?enrollment_id={{ sha1(matricula.id) }}')">{{ icon('printer') }} Resumen de Incidentes</a></li>
                                        <li><a href="javascript:;" onclick="zk.printDocument('/matriculas/fotocheck/{{ sha1(matricula.id) }}')">{{ icon('printer') }} Imprimir Fotocheck</a></li>
                                        <li><a href="javascript:;" onclick="zk.printDocument('/reportes/imprimir_asistencia_matricula/{{ matricula.id }}')">{{ icon('calendar') }} Imprimir Asistencia</a></li>
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
