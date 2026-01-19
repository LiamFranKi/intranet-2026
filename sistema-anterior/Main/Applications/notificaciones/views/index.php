{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaNotificaciones').dataTable();
	setMenuActive('notificaciones');
});

function borrar_notificacion(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/notificaciones/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Notificaciones</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/notificaciones">Notificaciones</a></li>
		<li class="active">Lista de Notificaciones</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Notificaciones</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/notificaciones/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaNotificaciones" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>usuario</th>
						<th>asunto</th>
						<th>fecha / hora</th>
						<th>estado</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for notificacion in notificaciones %}
					<tr>
						<td>{{ notificacion.usuario.getFullName() }}</td>
						<td>{{ notificacion.asunto }}</td>
						<td class="text-center">{{ notificacion.fecha_hora }}</td>
						<td class="text-center">{{ notificacion.estado }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<!--<li><a href="#/notificaciones/form/{{ sha1(notificacion.id) }}">{{ icon('register') }} Editar Notificacion</a></li>-->
									<li><a href="javascript:;" onclick="borrar_notificacion('{{ sha1(notificacion.id) }}')">{{ icon('delete') }} Borrar Notificacion</a></li>
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
