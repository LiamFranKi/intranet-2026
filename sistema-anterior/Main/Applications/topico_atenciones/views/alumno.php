{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaTopico_atenciones').dataTable();
	setMenuActive('topico_atenciones');
});

function borrar_topico_atencion(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/topico_atenciones/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Atenciones en Psicología</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/topico_atenciones">Psicología</a></li>
		<li class="active">Lista de Atenciones</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Atenciones</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/topico_atenciones/form?alumno_id={{ get.alumno_id }}&tipo={{ get.tipo }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaTopico_atenciones" class="table table-striped table-bordered">
				<thead>
					<tr>
					
						<th>fecha / hora</th>
						<th>motivo</th>
						<th>tratamiento</th>
						<th>personal</th>
					
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for topico_atencion in topico_atenciones %}
					<tr>
					
					
						<td>{{ topico_atencion.fecha_hora|date('d-m-Y h:i A') }}</td>
						<td>{{ topico_atencion.motivo }}</td>
						<td>{{ topico_atencion.tratamiento }}</td>
						<td>{{ topico_atencion.personal.getFullName() }}</td>
					
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/topico_atenciones/form/{{ sha1(topico_atencion.id) }}">{{ icon('register') }} Editar Atención</a></li>
									<li><a href="javascript:;" onclick="borrar_topico_atencion('{{ sha1(topico_atencion.id) }}')">{{ icon('delete') }} Borrar Atención</a></li>
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
