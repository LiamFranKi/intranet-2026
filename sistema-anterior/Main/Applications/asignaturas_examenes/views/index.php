{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaAsignaturas_examenes').dataTable();
	setMenuActive('asignaturas_examenes');
});

function borrar_asignatura_examen(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/asignaturas_examenes/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Asignaturas_examenes</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Asignaturas_examenes</a></li>
		<li class="active">Lista de Asignaturas_examenes</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Asignaturas_examenes</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/asignaturas_examenes/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaAsignaturas_examenes" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>id</th>
						<th>trabajador_id</th>
						<th>titulo</th>
						<th>tipo_puntaje</th>
						<th>puntos_correcta</th>
						<th>penalizar_incorrecta</th>
						<th>penalizacion_incorrecta</th>
						<th>tiempo</th>
						<th>intentos</th>
						<th>estado</th>
						<th>orden_preguntas</th>
						<th>fecha_desde</th>
						<th>fecha_hasta</th>
						<th>hora_desde</th>
						<th>hora_hasta</th>
						<th>asignatura_id</th>
						<th>ciclo</th>
						<th>preguntas_max</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for asignatura_examen in asignaturas_examenes %}
					<tr>
						<td>{{ asignatura_examen.id }}</td>
						<td>{{ asignatura_examen.trabajador_id }}</td>
						<td>{{ asignatura_examen.titulo }}</td>
						<td>{{ asignatura_examen.tipo_puntaje }}</td>
						<td>{{ asignatura_examen.puntos_correcta }}</td>
						<td>{{ asignatura_examen.penalizar_incorrecta }}</td>
						<td>{{ asignatura_examen.penalizacion_incorrecta }}</td>
						<td>{{ asignatura_examen.tiempo }}</td>
						<td>{{ asignatura_examen.intentos }}</td>
						<td>{{ asignatura_examen.estado }}</td>
						<td>{{ asignatura_examen.orden_preguntas }}</td>
						<td>{{ asignatura_examen.fecha_desde }}</td>
						<td>{{ asignatura_examen.fecha_hasta }}</td>
						<td>{{ asignatura_examen.hora_desde }}</td>
						<td>{{ asignatura_examen.hora_hasta }}</td>
						<td>{{ asignatura_examen.asignatura_id }}</td>
						<td>{{ asignatura_examen.ciclo }}</td>
						<td>{{ asignatura_examen.preguntas_max }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/asignaturas_examenes/form/{{ sha1(asignatura_examen.id) }}">{{ icon('register') }} Editar Asignatura_Examen</a></li>
									<li><a href="javascript:;" onclick="borrar_asignatura_examen('{{ sha1(asignatura_examen.id) }}')">{{ icon('delete') }} Borrar Asignatura_Examen</a></li>
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
