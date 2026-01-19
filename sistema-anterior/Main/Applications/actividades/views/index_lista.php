{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaActividades').dataTable();
	setMenuActive('actividades');
});

function borrar_actividad(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/actividades/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Actividades</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/actividades">Actividades</a></li>
		<li class="active">Lista de Actividades</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Actividades</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/actividades/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaActividades" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>id</th>
						<th>colegio_id</th>
						<th>descripcion</th>
						<th>lugar</th>
						<th>detalles</th>
						<th>fecha_inicio</th>
						<th>fecha_fin</th>
						<th>usuario_id</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for actividad in actividades %}
					<tr>
						<td>{{ actividad.id }}</td>
						<td>{{ actividad.colegio_id }}</td>
						<td>{{ actividad.descripcion }}</td>
						<td>{{ actividad.lugar }}</td>
						<td>{{ actividad.detalles }}</td>
						<td>{{ actividad.fecha_inicio }}</td>
						<td>{{ actividad.fecha_fin }}</td>
						<td>{{ actividad.usuario_id }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/actividades/form/{{ sha1(actividad.id) }}">{{ icon('register') }} Editar Actividad</a></li>
									<li><a href="javascript:;" onclick="borrar_actividad('{{ sha1(actividad.id) }}')">{{ icon('delete') }} Borrar Actividad</a></li>
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
