{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaComunicados').dataTable();
	setMenuActive('comunicados');
});

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Comunicados</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/comunicados">Comunicados</a></li>
		<li class="active">Lista de Comunicados</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Comunicados</h3>
		</div>
		<div class="panel-body">
			
			<table id="listaComunicados" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Descripción</th>
						<th>Fecha / Hora</th>
						<th>Estado</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for comunicado in comunicados %}
					<tr>
						<td>{{ comunicado.descripcion }}</td>
						<td class="text-center">{{ comunicado.fecha_hora|date('d-m-Y h:i A') }}</td>
						<td class="text-center">{{ comunicado.estado }}</td>
						
						<td class="text-center" style="width: 120px">
							{% if comunicado.archivo %}
							<a href="/Static/Archivos/{{ comunicado.archivo }}" class="btn btn-primary" target="_blank">Descargar</a>
							{% endif %}
						</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>

{% endblock %}
