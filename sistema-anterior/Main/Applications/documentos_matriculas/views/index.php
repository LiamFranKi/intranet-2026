{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaDocumentos_matriculas').dataTable();
	setMenuActive('documentos_matriculas');
});

function borrar_documento_matricula(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/documentos_matriculas/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Documentos Matrículas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Documentos Matrículas</a></li>
		<li class="active">Lista de Documentos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Documentos</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/documentos_matriculas/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaDocumentos_matriculas" class="table table-striped table-bordered">
				<thead>
					<tr>
						
						<th>nombre</th>
						
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for documento_matricula in documentos_matriculas %}
					<tr>
						
						<td>{{ documento_matricula.nombre }}</td>
						
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="/Static/Archivos/{{ documento_matricula.archivo }}" target="_blank">{{ icon('application_put') }} Descargar Archivo</a></li>
									<li><a href="#/documentos_matriculas/form/{{ sha1(documento_matricula.id) }}">{{ icon('register') }} Editar Documento</a></li>
									<li><a href="javascript:;" onclick="borrar_documento_matricula('{{ sha1(documento_matricula.id) }}')">{{ icon('delete') }} Borrar Documento</a></li>
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
