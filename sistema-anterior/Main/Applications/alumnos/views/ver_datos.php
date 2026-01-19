<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Alumnos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/alumnos">Alumnos</a></li>
		<li class="active">Información del Alumno</li>
	</ol>
</div>
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Información del Alumno</h3>
		</div>
		<div class="panel-body">
			{% include 'x_datos.php' %}
	
			<table class="special">
				{% for nivel in COLEGIO.niveles %}
					{% if alumno.getMatriculasNivel(nivel.id)|length > 0 %}
					<tr>
						<th style="width: 150px">{{ nivel.nombre|upper }}</th>
						<td>
							{% for matricula in alumno.getMatriculasNivel(nivel.id) %}
							<button class="btn btn-default" style="font-weight: bold">{{ matricula.grupo.getGrado() }} {{ matricula.grupo.seccion|upper }}</button>
							{% endfor %}
						</td>
					</tr>
					{% endif %}
				{% endfor %}
			</table>
		</div>
	</div>
</div>
	

