{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	setMenuActive('grupos_asignados');
	$('#listaGrupos').dataTable();
});

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

function registroNotas(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.printDocument('/notas/imprimir_cuantitativa_grupo?grupo_id='+ id +'&ciclo=' + ciclo );
	});
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Grupos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;">Grupos</a></li>
		<li class="active">Lista de Grupos Asignados</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Grupos Asignados</h3>
		</div>
		<div class="panel-body">
			<table id="listaGrupos" class="table table-striped table-bordered">
				<thead>
					<tr>
						
						<th>Grado</th>
						<th>Sección</th>
						<th>Nivel</th>
						<th>Turno</th>
						<th>Año Académico</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for grupo in grupos %}
					<tr>
						
						<td class="text-center">{{ grupo.getGrado() }}</td>
						<td class="text-center">{{ grupo.seccion|upper }}</td>
						<td class="text-center">{{ grupo.nivel.nombre|upper }}</td>
						<td class="text-center">{{ grupo.turno.nombre|upper }}</td>
						<td class="text-center">{{ grupo.anio }}</td>
						
						<td class="text-center" style="width: 120px">
                            <span class="dropdown dropup">
								<button type="button" class="btn btn-default" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" >
									<li><a href="#/tutoria/lista_alumnos?grupo_id={{ sha1(grupo.id) }}">{{ icon('application_view_list') }} Lista de Alumnos</a></li>
                                    {% if USUARIO.personal_id == grupo.tutor_id %}
                                    <li><a href="javascript:;" onclick="fancybox('/grupos/asistencia/{{ sha1(grupo.id) }}')">{{ icon('calendar') }} Registro de Asistencia</a></li>
                                    <li><a href="javascript:;" onclick="fancybox('/grupos/asignaturas/{{ sha1(grupo.id) }}')">{{ icon('book_go') }} Asignaturas</a></li>
									<li><a href="#/mensajes/form?grupo_id={{ grupo.id }}">{{ icon('email') }} Enviar Comunicado</a></li>
									<li><a href="javascript:;" onclick="conducta('{{ sha1(grupo.id) }}')">{{ icon('register') }} Registrar Conducta</a></li>
                                    {% endif %}

                                    {% if grupo.nivel.isInicial() %}
                                    <li><a href="javascript:;" onclick="conducta('{{ sha1(grupo.id) }}')">{{ icon('register') }} Registrar Conducta</a></li>
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

{% endblock %}
