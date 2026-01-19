{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaNiveles').dataTable();
});

function borrar_nivel(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/niveles/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Niveles</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/niveles">Niveles</a></li>
		<li class="active">Lista de Niveles</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Niveles</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/niveles/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaNiveles" class="table table-striped table-bordered">
				<thead>
					<tr>
						
						<th>Nombre</th>
						<th>Tipo Calificacion</th>
						<th>Calificacion Final</th>
						<th>Nota Aprobatoria</th>
						<th>Nota Max.</th>
						<th>Nota Min.</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for nivel in niveles %}
					<tr>
						<td>{{ nivel.nombre|upper }}</td>
			
			
						<td class="text-center">{{ nivel.getTipoCalificacion() }}</td>
						
						{% if nivel.calificacionCuantitativa() %}
						<td class="text-center">{{ nivel.getTipoCalificacionFinal() }}</td>
						<td class="text-center">{{ nivel.nota_aprobatoria }}</td>
						<td class="text-center">{{ nivel.nota_maxima }}</td>
						<td class="text-center">{{ nivel.nota_minima }}</td>
						{% else %}
						<td class="text-center">-</td>
						<td class="text-center">-</td>
						<td class="text-center">-</td>
						<td class="text-center">-</td>
						{% endif %}
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/niveles/form/{{ sha1(nivel.id) }}">{{ icon('register') }} Editar Nivel</a></li>
									<li><a href="javascript:;" onclick="borrar_nivel('{{ sha1(nivel.id) }}')">{{ icon('delete') }} Borrar Nivel</a></li>
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
