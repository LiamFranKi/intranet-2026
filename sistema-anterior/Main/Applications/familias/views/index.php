{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaFamilias').dataTable();
	setMenuActive('familias');
});

function borrar_familia(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/familias/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Alumnos a Cargo</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/apoderados">Apoderados</a></li>
		<li class="active">Lista de Alumnos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Alumnos</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/familias/form?apoderado_id={{ get.apoderado_id }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaFamilias" class="table table-striped table-bordered">
				<thead>
					<tr>
						
						<th>alumno</th>
						
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for familia in familias %}
					<tr>
						
						<td>{{ familia.alumno.getFullName() }}</td>
						
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<!--<li><a href="#/familias/form/{{ sha1(familia.id) }}">{{ icon('register') }} Editar Familia</a></li>-->
									<li><a href="javascript:;" onclick="borrar_familia('{{ sha1(familia.id) }}')">{{ icon('delete') }} Quitar Alumno</a></li>
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
