{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaCursos').dataTable();
	setMenuActive('cursos');
});

function borrar_curso(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/cursos/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Cursos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/cursos">Cursos</a></li>
		<li class="active">Lista de Cursos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Cursos</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/cursos/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaCursos" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>nombre</th>
						<th>abreviatura</th>
						<th>descripción</th>
						<th>nivel</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for curso in cursos %}
					<tr>
						<td>{{ curso.nombre }}</td>
						<td>{{ curso.abreviatura }}</td>
						<td>{{ curso.descripcion }}</td>
						<td>{{ curso.nivel.nombre }}</td>
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/cursos_criterios?curso_id={{ sha1(curso.id) }}">{{ icon('application_side_list') }} Criterios de Evaluación</a></li>
									<li class="divider"></li>
									<li><a href="#/cursos/form/{{ sha1(curso.id) }}">{{ icon('register') }} Editar Curso</a></li>
									<li><a href="javascript:;" onclick="borrar_curso('{{ sha1(curso.id) }}')">{{ icon('delete') }} Borrar Curso</a></li>
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
