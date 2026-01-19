{% extends main_template %}
{% block main_content %}
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Apoderados</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/apoderados">Apoderados</a></li>
		<li class="active">Información de Apoderado</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Información de Apoderado</h3>
		</div>
		<div class="panel-body">
			<table class="special info" style="width: 100%">
				<tr>
					<th>APELLIDOS Y NOMBRES</th>
					<td>{{ apoderado.getFullName() }}</td>
				</tr>
				<tr>
					<th>Tipo de Documento</th>
					<td>{{ apoderado.getTipoDocumento() }}</td>
				</tr>
				<tr>
					<th>Nº de Documento</th>
					<td>{{ apoderado.nro_documento }}</td>
				</tr>
				<tr>
					<th>ESTADO CIVIL</th>
					<td>{{ apoderado.getEstadoCivil() }}</td>
				</tr>
				<tr>
					<th>DIRECCIÓN</th>
					<td>{{ apoderado.direccion }}</td>
				</tr>
				<tr>
					<th>EMAIL</th>
					<td>{{ apoderado.email }}</td>
				</tr>
				<tr>
					<th>TELÉFONO - FIJO</th>
					<td>{{ apoderado.telefono_fijo }}</td>
				</tr>
				<tr>
					<th>TELÉFONO - CELULAR</th>
					<td>{{ apoderado.telefono_celular }}</td>
				</tr>
				<tr>
					<th>CENTRO DE TRABAJO</th>
					<td>{{ apoderado.centro_trabajo_direccion }}</td>
				</tr>
				<tr>
					<th>OCUPACIÓN</th>
					<td>{{ apoderado.ocupacion }}</td>
				</tr>
	            <tr>
					<th>PARENTESCO</th>
					<td>{{ apoderado.getParentesco() }}</td>
				</tr>
			</table>
		</div>
	</div>
</div>
{% endblock %}