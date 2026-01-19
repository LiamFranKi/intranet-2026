{% extends main_template %}
{% block main_content %}
<script>
$(function(){

	setMenuActive('pagos');
	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})
	$('#limp').dataTable();
});

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Historial - Archivos Importados</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/pagos">Pagos</a></li>
		<li class="active">Historial - Archivos Importados</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Historial - Archivos Importados</h3>
		</div>
		<div class="panel-body">
			<table id="limp" class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>Fecha</th>
						<th>Archivo</th>
						<th>Cantidad de Pagos</th>
						<th>Estado</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				{% for importacion in importaciones %}
					<tr>
						<td class="text-center">{{ importacion.fecha|date('Y-m-d') }}</td>
						<td class="text-center">{{ importacion.archivo }}</td>
						<td class="text-center">{{ importacion.getPagos()|length }}</td>
						<td class="text-center">{{ importacion.getEstado() }}</td>
						<td class="text-center" style="width: 140px">
							<a class="btn-default btn" href="#/pagos/detalles_historial?historial_id={{ importacion.id }}">{{ icon('list') }}</a>
							<button class="btn-default btn" onclick="zk.printDocument('/pagos/imprimir_historial?historial_id={{ importacion.id }}')">{{ icon('printer') }}</button>
						</td>
					</tr>
				{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>

{% endblock %}
