{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaGrupos_horarios').dataTable();
	setMenuActive('grupos_horarios');
});

function borrar_grupo_horario(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/grupos_horarios/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Horario</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/grupos">Grupos</a></li>
		<li class="active">Lista de Horarios</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Horarios</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/grupos_horarios/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaGrupos_horarios" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>id</th>
						<th>grupo_id</th>
						<th>asignatura_id</th>
						<th>personal_id</th>
						<th>descripcion</th>
						<th>dia</th>
						<th>hora_inicio</th>
						<th>hora_final</th>
						<th>tipo</th>
						<th>anio</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for grupo_horario in grupos_horarios %}
					<tr>
						<td>{{ grupo_horario.id }}</td>
						<td>{{ grupo_horario.grupo_id }}</td>
						<td>{{ grupo_horario.asignatura_id }}</td>
						<td>{{ grupo_horario.personal_id }}</td>
						<td>{{ grupo_horario.descripcion }}</td>
						<td>{{ grupo_horario.dia }}</td>
						<td>{{ grupo_horario.hora_inicio }}</td>
						<td>{{ grupo_horario.hora_final }}</td>
						<td>{{ grupo_horario.tipo }}</td>
						<td>{{ grupo_horario.anio }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/grupos_horarios/form/{{ sha1(grupo_horario.id) }}">{{ icon('register') }} Editar Grupo_Horario</a></li>
									<li><a href="javascript:;" onclick="borrar_grupo_horario('{{ sha1(grupo_horario.id) }}')">{{ icon('delete') }} Borrar Grupo_Horario</a></li>
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
