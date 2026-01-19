{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaBoletas_conceptos').dataTable();
	setMenuActive('boletas_conceptos');
});

function borrar_boleta_concepto(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/boletas_conceptos/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Conceptos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/boletas_conceptos">Conceptos</a></li>
		<li class="active">Lista de Conceptos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Conceptos</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/boletas_conceptos/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaBoletas_conceptos" class="table table-striped table-bordered">
				<thead>
					<tr>
						
						<th>categoría</th>
					
						<th>descripción</th>
						
						<th>stock</th>
						
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for boleta_concepto in boletas_conceptos %}
					<tr>
						
						<td>{{ boleta_concepto.categoria.nombre }} - {{ boleta_concepto.subcategoria.nombre }}</td>
						
						<td>{{ boleta_concepto.descripcion }}</td>
						
						<td class="text-center">{{ boleta_concepto.controlar_stock == "SI" ? boleta_concepto.stock : '-' }}</td>
						
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									{% if boleta_concepto.controlarStock() %}
									<li><a href="#/boletas_conceptos/actualizar_stock/{{ sha1(boleta_concepto.id) }}">{{ icon('calendar') }} Stock Inicial</a></li>

									<li class="divider"></li>
									{% endif %}
									<li><a href="#/boletas_conceptos/form/{{ sha1(boleta_concepto.id) }}">{{ icon('register') }} Editar Concepto</a></li>
									<li><a href="javascript:;" onclick="borrar_boleta_concepto('{{ sha1(boleta_concepto.id) }}')">{{ icon('delete') }} Borrar Concepto</a></li>
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
