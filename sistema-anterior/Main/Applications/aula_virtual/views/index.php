<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Aula Virtual - {{ asignatura.curso.nombre }}</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="/">Cursos Asignados</a></li>
		<li class="active">Aula Virtual - {{ asignatura.grupo.getNombreShort2() }}</li>
	</ol>
</div>
<div id="page-content">
	{% include '_archivos.php' %}
	{% include '_tareas.php' %}
	{% include '_examenes.php' %}
	{% include '_videos.php' %}
	{% include '_enlaces.php' %}
</div>

