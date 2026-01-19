<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Personal</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/personal">Personal</a></li>
		<li class="active">Ver Información</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Información de Personal</h3>
		</div>
		<div class="panel-body">
			<table style="width: 100%">
				<tr>
					<td valign="top" style="width: 200px"><img style="width: 200px; border: 1px solid #ccc; padding: 3px" style="border: 1px solid #ccc" src="{{ personal.getFoto() }}" /></td>
					<td valign="top" style="padding-left: 10px; padding-top: 0px">
						<table class="special">
							<tr>
								<th style="width: 200px">APELLIDOS Y NOMBRES</th>
								<td>{{ personal.apellidos|upper }}, {{ personal.nombres|upper }}</td>
							</tr>
							<tr>
								<th>Tipo de Documento</th>
								<td>{{ personal.getTipoDocumento() }}</td>
							</tr>
		                    <tr>
								<th>SEXO</th>
								<td>{{ personal.getSexo() }}</td>
							</tr>

		                    <tr>
		                        <th>Estado Civil</th>
		                        <td>{{ personal.getEstadoCivil() }}</td>
		                    </tr>
							<tr>
								<th>DIRECCIÓN</th>
								<td>{{ personal.direccion }}</td>
							</tr>
							<tr>
								<th>TELÉFONO FIJO</th>
								<td>{{ personal.telefono_fijo }}</td>
							</tr>
		                    <tr>
		                        <th>Teléfono Celular</th>
		                        <td>{{ personal.telefono_celular }} - {{ personal.getLineaCelular() }}</td>
		                    </tr>
							<tr>
								<th>EMAIL</th>
								<td>{{ personal.email }}</td>
							</tr>
							<tr>
								<th>CARGO</th>
								<td>{{ personal.cargo }}</td>
							</tr>
							<tr>
								<th>FECHA DE NACIMIENTO</th>
								<td>{{ personal.getFechaNacimiento() }}</td>
							</tr>
							<tr>
								<th>FECHA DE INGRESO</th>
								<td>{{ personal.getFechaIngreso() }}</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>	
	</div>
</div>
