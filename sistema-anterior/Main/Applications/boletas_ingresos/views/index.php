{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaBoletas_ingresos').dataTable();
	setMenuActive('boletas_ingresos');

	$('#searchForm').on('submit', function(e){
		e.preventDefault();
		zk.goToUrl('/boletas_ingresos?' + $(this).serialize())
	})

	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})
});

function borrar_boleta_ingreso(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/boletas_ingresos/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Ingresos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/boletas_ingresos">Ingresos</a></li>
		<li class="active">Lista de Ingresos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Ingresos</h3>
		</div>
		<div class="panel-body">
			<form class="form-inline text-center" id="searchForm">
				<div class="form-group"><input type="text" class="form-control calendar" name="desde" value="{{ desde }}" /></div>
				<div class="form-group"><input type="text" class="form-control calendar" name="hasta" value="{{ hasta }}" /></div>
				<div class="form-group"><button class="btn btn-primary">Buscar</button></div>
				<div class="form-group"><a href="#/boletas_ingresos/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a></div>
			</form>
		</div>
	</div>
	<div class="panel">
		
		<div class="panel-body">
			
			<table id="listaBoletas_ingresos" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Descripción</th>
						<th>Fecha</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for boleta_ingreso in boletas_ingresos %}
					<tr>
						<td>{{ boleta_ingreso.descripcion }}</td>
						<td class="text-center">{{ COLEGIO.getFecha(boleta_ingreso.fecha) }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/boletas_ingresos/detalles/{{ sha1(boleta_ingreso.id) }}">{{ icon('list') }} Ver Detalles</a></li>
									<li class="divider"></li>
									<li><a href="#/boletas_ingresos/form/{{ sha1(boleta_ingreso.id) }}">{{ icon('register') }} Editar Ingreso</a></li>
									<li><a href="javascript:;" onclick="borrar_boleta_ingreso('{{ sha1(boleta_ingreso.id) }}')">{{ icon('delete') }} Borrar Ingreso</a></li>
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
