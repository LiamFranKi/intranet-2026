{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaAsignaturas_enlaces').dataTable();
	setMenuActive('asignaturas_enlaces');
});

function borrar_asignatura_enlace(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/asignaturas_enlaces/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Asignaturas_enlaces</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/asignaturas_enlaces">Asignaturas_enlaces</a></li>
		<li class="active">Lista de Asignaturas_enlaces</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Asignaturas_enlaces</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/asignaturas_enlaces/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaAsignaturas_enlaces" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>id</th>
						<th>asignatura_id</th>
						<th>descripcion</th>
						<th>enlace</th>
						<th>trabajador_id</th>
						<th>fecha_hora</th>
						<th>ciclo</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for asignatura_enlace in asignaturas_enlaces %}
					<tr>
						<td>{{ asignatura_enlace.id }}</td>
						<td>{{ asignatura_enlace.asignatura_id }}</td>
						<td>{{ asignatura_enlace.descripcion }}</td>
						<td>{{ asignatura_enlace.enlace }}</td>
						<td>{{ asignatura_enlace.trabajador_id }}</td>
						<td>{{ asignatura_enlace.fecha_hora }}</td>
						<td>{{ asignatura_enlace.ciclo }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/asignaturas_enlaces/form/{{ sha1(asignatura_enlace.id) }}">{{ icon('register') }} Editar Asignatura_Enlace</a></li>
									<li><a href="javascript:;" onclick="borrar_asignatura_enlace('{{ sha1(asignatura_enlace.id) }}')">{{ icon('delete') }} Borrar Asignatura_Enlace</a></li>
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
