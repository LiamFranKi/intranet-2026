{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	
	setMenuActive('grupos');
});

function borrar_grupo(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/grupos/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}

function registroNotas(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.printDocument('/notas/imprimir_cuantitativa_grupo?grupo_id='+ id +'&ciclo=' + ciclo );
	});
}
function recomendaciones(id){
	seleccionarCiclo(function(e, v, ciclo){
		zk.goToUrl('/tutoria/recomendaciones?ciclo=' + ciclo + '&grupo_id=' + id);
	});
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Grupos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/grupos">Grupos</a></li>
		<li class="active">Lista de Grupos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Grupos</h3>
		</div>
		<div class="panel-body">
            {% if USUARIO.is('ADMINISTRADOR') %}
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/grupos/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>
            {% endif %}

			{% for sede in sedes %}
			{% set grupos = COLEGIO.getGrupos(anio, sede.id) %}
			<script>
			$(function(){
				$('#listaGrupos-{{ sede.id }}').dataTable();
			})
			</script>
			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading">
					<h3 class="panel-title">{{ sede.nombre }}</h3>
				</div>
				<div class="panel-body">
					<table id="listaGrupos-{{ sede.id }}" class="table table-striped table-bordered">
						<thead>
							<tr>
								
								<th>Nivel</th>
								<th>Grado</th>
								<th>Sección</th>
								
								<th>Turno</th>
								<th>Alumnos</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							{% for grupo in grupos %}
							<tr>
								
								<td>{{ grupo.nivel.nombre }}</td>
								<td class="text-center">{{ grupo.getGrado() }}</td>
								<td class="text-center">{{ grupo.seccion }}</td>
								
								<td class="text-center">{{ grupo.turno.nombre }}</td>
								<td class="text-center">{{ grupo.getMatriculas()|length }}</td>
								
								<td class="text-center" style="width: 120px">
									<div class="btn-group dropup">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
										<ul class="dropdown-menu pull-right" role="menu">
                                            {% if USUARIO.is('ADMINISTRADOR') %}
											<li><a href="#/grupos/lista_alumnos/{{ sha1(grupo.id) }}">{{ icon('application_view_list') }} Lista de Alumnos</a></li>
											<li><a href="#/grupos_horarios?grupo_id={{ sha1(grupo.id) }}">{{ icon('calendar') }} Ver Horario</a></li>
                                            <li><a href="javascript:;" onclick="fancybox('/grupos/asistencia/{{ sha1(grupo.id) }}')">{{ icon('calendar') }} Registro de Asistencia</a></li>
                                            <li><a href="javascript:;" onclick="zk.printDocument('/grupos/codigos_qr/{{ sha1(grupo.id) }}')">{{ icon('printer') }} Imprimir Códigos QR</a></li>
											<!--<li><a href="#/filemanager/grupo?grupo_id={{ sha1(grupo.id) }}&token={{ grupo.getFileManagerToken() }}&p={{ base64_encode('R,D,C,U') }}&base=/">{{ icon('folder') }} Administrar Archivos</a></li>-->
											<li><a href="javascript:;" onclick="registroNotas('{{ sha1(grupo.id) }}')">{{ icon('printer') }} Registro de Notas</a></li>
											<li><a href="javascript:;" onclick="recomendaciones('{{ sha1(grupo.id) }}')">{{ icon('comment_add') }} Apreciaciones / Recomendaciones</a></li>
											<li><a href="#/grupos/modificar_costos/{{ sha1(grupo.id) }}">{{ icon('money_dollar') }} Modificar Costos</a></li>
											<li><a href="#/grupos/form/{{ sha1(grupo.id) }}">{{ icon('register') }} Editar Grupo</a></li>
											<li><a href="javascript:;" onclick="borrar_grupo('{{ sha1(grupo.id) }}')">{{ icon('delete') }} Borrar Grupo</a></li>
                                            {% elseif USUARIO.is('ASISTENCIA') %}
                                            <li><a href="#/grupos/lista_alumnos/{{ sha1(grupo.id) }}">{{ icon('application_view_list') }} Lista de Alumnos</a></li>
                                            <li><a href="javascript:;" onclick="fancybox('/grupos/asistencia/{{ sha1(grupo.id) }}')">{{ icon('calendar') }} Registro de Asistencia</a></li>
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
			
			{% endfor %}
		</div>
	</div>
</div>

{% endblock %}
