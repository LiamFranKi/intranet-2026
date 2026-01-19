<script>
$(function(){
	$('#lmatriculas').dataTable();
});

function viewNotas(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.goToUrl('/notas/detallado?ciclo=' + ciclo + '&matricula_id=' + id);
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

            <div class="row" style="margin-bottom: 15px;">
                <div class="col-lg-3 col-sm-3 text-center">
                    <strong>ALUMNOS</strong>
                    <div>
                        <a class="btn btn-default" href="/asignaturas/lista_alumnos_pdf/{{ sha1(asignatura.id) }}" target="_blank">{{ icon('page_white_acrobat') }} Exportar PDF</a>
                        <a class="btn btn-default" href="/asignaturas/lista_alumnos_excel/{{ sha1(asignatura.id) }}" target="_blank">{{ icon('page_white_excel') }} Exportar Excel</a>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-3 text-center">
                    <strong>APODERADOS</strong>
                    <div>
                        <a class="btn btn-default" href="/asignaturas/lista_apoderados_pdf/{{ sha1(asignatura.id) }}" target="_blank">{{ icon('page_white_acrobat') }} Exportar PDF</a>
                        <a class="btn btn-default" href="/asignaturas/lista_apoderados_excel/{{ sha1(asignatura.id) }}" target="_blank">{{ icon('page_white_excel') }} Exportar Excel</a>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-3 text-center">
                    <strong>ALUMNOS/APODERADOS</strong>
                    <div>
                        <a class="btn btn-default" href="/asignaturas/lista_alumnos_apoderados_pdf/{{ sha1(asignatura.id) }}" target="_blank">{{ icon('page_white_acrobat') }} Exportar PDF</a>
                        <a class="btn btn-default" href="/asignaturas/lista_alumnos_apoderados_excel/{{ sha1(asignatura.id) }}" target="_blank">{{ icon('page_white_excel') }} Exportar Excel</a>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-3 text-center">
                    <strong>REGISTRO ACADÉMICO</strong>
                    <div>
                        <a class="btn btn-default" href="/reportes/imprimir_lista_alumnos_registro_auxiliar?grupo_id={{ sha1(asignatura.grupo_id) }}" target="_blank">{{ icon('page_white_acrobat') }} Exportar PDF</a>
                        <a class="btn btn-default" href="/reportes/imprimir_lista_alumnos_registro_auxiliar_excel?grupo_id={{ sha1(asignatura.grupo_id) }}" target="_blank">{{ icon('page_white_excel') }} Exportar Excel</a>
                    </div>
                </div>
            </div>

			<table class="dataTable table table-striped table-bordered table-hover" id="lmatriculas">
				<thead>
					<tr>
						<th>Apellidos y Nombres</th>
						<th>Fecha de Registro</th>
						<th>Estado</th>
                        <th>Estrellas</th>
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
                        <td class="text-center text-bold">{{ matricula.getStarsAmount() }}</td>
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">

                                    <li><a href="javascript:;" onclick="fancybox('/enrollment_incidents?enrollment_id={{ sha1(matricula.id) }}&assignment_id={{ sha1(asignatura.id) }}')">{{ icon('application_view_list') }} Reporte de Incidentes</a></li>
                                    
                                    <li><a href="javascript:;" onclick="fancybox('/enrollment_incidents/form2?enrollment_id={{ sha1(matricula.id) }}&assignment_id={{ sha1(asignatura.id) }}')">{{ icon('star') }} Dar/Quitar Estrellas</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:;" onclick="fancybox('/enrollment_incidents/summary?enrollment_id={{ sha1(matricula.id) }}')">{{ icon('printer') }} Resumen de Incidentes</a></li>
                                    <li><a href="javascript:;" onclick="zk.printDocument('/reportes/imprimir_asistencia_matricula/{{ matricula.id }}')">{{ icon('calendar') }} Imprimir Asistencia</a></li>
                                    <li><a href="javascript:;" onclick="viewNotas('{{ sha1(matricula.id) }}')">{{ icon('list') }} Ver Notas Detalladas</a></li>
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
