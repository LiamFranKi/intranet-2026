{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaAlertas').dataTable();
	setMenuActive('alertas');
});

function borrar_alerta(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/alertas/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Alertas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/alertas">Alertas</a></li>
		<li class="active">Lista de Alertas</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Alertas</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/alertas/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaAlertas" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Nombre</th>
						<th>Tipo</th>
						<th>Estado</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for alerta in alertas %}
					<tr>
						<td>{{ alerta.nombre }}</td>
						<td class="text-center">{{ alerta.tipo }}</td>
						<td class="text-center">{{ alerta.estado }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/alertas/form/{{ sha1(alerta.id) }}">{{ icon('register') }} Editar Alerta</a></li>
									<li><a href="javascript:;" onclick="borrar_alerta('{{ sha1(alerta.id) }}')">{{ icon('delete') }} Borrar Alerta</a></li>
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
