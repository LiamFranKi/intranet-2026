<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Lista de Apoderados</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/alumnos">Alumnos</a></li>
		<li class="active">Lista de Apoderados</li>
	</ol>
</div>
<div id="page-content">
	
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Apoderados</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/apoderados/form?alumno_id={{ alumno.id }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			{% for apoderado in apoderados %}
			<table class="special" style="width: 100%">
				<tr>
					<th>Apellidos y Nombres</th>
					<td>{{ apoderado.getFullName() }}</td>
				</tr>
		        <tr>
		            <th style="width: 200px">Tipo de Documento</th><td>{{ apoderado.getTipoDocumento() }}</td>
		        </tr>
		        <tr>
					<th>Nº de Documento</th>
					<td>{{ apoderado.nro_documento }}</td>
		        </tr>
		        <tr>
		            <th>Estado Civil</th>
		            <td>{{ apoderado.getEstadoCivil() }}</td>
		        </tr>
		        <tr>
		            <th>Fecha de Nacimiento</th>
		            <td>{{ apoderado.getFechaNacimiento() }}</td>
		        </tr>
		        <tr>
		            <th>Teléfono Fijo</th>
		            <td>{{ apoderado.telefono_fijo }}</td>
		        </tr>
		        <tr>
		            <th>Teléfono Celular</th>
		            <td>{{ apoderado.telefono_celular }}</td>
		        </tr>
		        <tr>
		            <th>Dirección</th>
		            <td>{{ apoderado.direccion }}</td>
		        </tr>
		        <tr>
		            <th>Email</th>
		            <td>{{ apoderado.email }}</td>
		        </tr>
		        <tr>
		            <th>Centro de Trabajo</th>
		            <td>{{ apoderado.centro_trabajo_direccion }}</td>
		        </tr>
		       
		        <tr>
		            <th>Ocupación</th>
		            <td>{{ apoderado.ocupacion }}</td>
		        </tr>
		        <tr>
		            <th>Parentesco</th>
		            <td>{{ apoderado.getParentesco() }}</td>
		        </tr>
		    </table>
		    {% else %}
		    <p class="text-center"><b>NO SE ENCONTRARON RESULTADOS</b></p>
		    {% endfor %}
		</div>
	</div>

	{% if hermanos|length > 0 %}
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Hermanos</h3>
		</div>
		<div class="panel-body">
			<table class="special">
		        {% for hermano in hermanos %}
		        <tr>
		            <td><a href="#/alumnos/ver_datos/{{ sha1(hermano.id) }}">{{ hermano.getFullName() }}</a></td>
		        </tr>
		        {% endfor %}
		    </table>
		</div>
	</div>
	{% endif %}
</div>
	

