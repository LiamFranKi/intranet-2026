{% extends main_template %}
{% block main_content %}
<script>
$(function(){

	setMenuActive('pagos');
	
});

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Detalles Archivo Importado</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/pagos">Pagos</a></li>
		<li class="active">Detalles Archivo Importado</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Detalles Archivo Importado</h3>
		</div>
		<div class="panel-body">
			<table class="special">
				<tr>
					<th>Archivo</th>
					<td>{{ historial.archivo }}</td>
				</tr>
				<tr>
					<th>Fecha</th>
					<td>{{ COLEGIO.getFecha(historial.fecha) }}</td>
				</tr>
			</table>

			<table class="special">
				<tr>
					
					<th>Alumno</th>
					<th>Descripción</th>
					<th>Fecha Pago</th>
					<th>Monto</th>
					<th>Mora</th>
				</tr>
				{% for pago in pagos %}
				<tr>
					<td>{{ pago.matricula.alumno.getFullName() }}</td>

					<td class="center">{{ pago.getDescription() }}</td>
					<td class="center">{{ pago.fecha_cancelado }}</td>
					<td class="center">{{ pago.monto|number_format(2) }}</td>
					<td class="center">{{ pago.mora|number_format(2) }}</td>
				</tr>
				{% endfor %}
			</table>
		</div>
	</div>
</div>

{% endblock %}
