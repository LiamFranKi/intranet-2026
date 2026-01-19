{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaBoletas_categorias').dataTable();
	setMenuActive('boletas_categorias');
});

function borrar_boleta_categoria(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/boletas_categorias/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Categorias</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/boletas_categorias">Categorias</a></li>
		<li class="active">Lista de Categorias</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Categorias</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/boletas_categorias/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaBoletas_categorias" class="table table-striped table-bordered">
				<thead>
					<tr>
						
						<th>nombre</th>
						<th>descripción</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for boleta_categoria in boletas_categorias %}
					<tr>
					
						<td>{{ boleta_categoria.nombre }}</td>
						<td>{{ boleta_categoria.descripcion }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/boletas_categorias/form/{{ sha1(boleta_categoria.id) }}">{{ icon('register') }} Editar Categoría</a></li>
									<li><a href="javascript:;" onclick="borrar_boleta_categoria('{{ sha1(boleta_categoria.id) }}')">{{ icon('delete') }} Borrar Categoría</a></li>
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
