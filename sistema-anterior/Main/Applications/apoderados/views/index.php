{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaApoderados').dataTable();
	setMenuActive('apoderados');

	$('#searchForm').on('submit', function(e){
		e.preventDefault();
		zk.goToUrl('/apoderados?' + $(this).serialize())
	})
});

function borrar_apoderado(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/apoderados/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Apoderados</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/apoderados">Apoderados</a></li>
		<li class="active">Lista de Apoderados</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Apoderados</h3>
		</div>
		<div class="panel-body text-center">
			<form class="form-inline" id="searchForm">
				<div class="form-group">
					<input type="text" name="query" class="form-control input-sm " style="width: 400px" placeholder="Puede buscar por: nombres, apellidos, nro documento">
				</div>
				<button type="submit" class="btn btn-primary">Buscar</button>
			</form>
		</div>
	</div>
	<div class="panel">
		
		<div class="panel-body">
			

			<table id="listaApoderados" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Apellidos y Nombres</th>
			
						<th>Documento</th>
						
						<th>parentesco</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for apoderado in apoderados %}
					<tr>
						<td><a href="javascript:;" onclick="fancybox('/apoderados/ver_datos/{{ apoderado.id }}')">{{ apoderado.getFullName() }}</a></td>
			
						<td class="text-center">{{ apoderado.getTipoDocumento() }} - {{ apoderado.nro_documento }}</td>
						
						<td class="text-center">{{ apoderado.getParentesco() }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/familias?apoderado_id={{ sha1(apoderado.id) }}">{{ icon('user') }} Alumnos a Cargo</a></li>
									<li class="divider"></li>
									<li><a href="#/apoderados/ver_datos/{{ sha1(apoderado.id) }}">{{ icon('information') }} Ver Información</a></li>
									<li><a href="#/apoderados/acceso/{{ sha1(apoderado.id) }}">{{ icon('lock') }} Información de Acceso</a></li>
									<li class="divider"></li>
									<li><a href="#/apoderados/form/{{ sha1(apoderado.id) }}">{{ icon('register') }} Editar Apoderado</a></li>
									<li><a href="javascript:;" onclick="borrar_apoderado('{{ sha1(apoderado.id) }}')">{{ icon('delete') }} Borrar Apoderado</a></li>
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
