{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaExamenes_bloques').dataTable();
	$('#listaExamenes_archivados').dataTable();
	setMenuActive('examenes_bloques');
});

function borrar_examen_bloque(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/examenes_bloques/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}

function archivar_examen(id){
	if(confirm('¿Está seguro de archivar el examen?')){
		$.post('/examenes_bloques/archivar', {id: id}, function(){
			zk.reloadPage()
		}, 'json');
	}
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Examenes de Bloques</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/examenes_bloques">Examenes Bloques</a></li>
		<li class="active">Lista de Examenes Bloques</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<div class="panel-control">

                <!--Nav tabs-->
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#tabs-box-1" aria-expanded="true">Activos</a></li>
                    <li class=""><a data-toggle="tab" href="#tabs-box-2" aria-expanded="false">Archivados</a></li>
                </ul>

            </div>
			<h3 class="panel-title">Lista de Exámenes</h3>
		</div>
		<div class="panel-body">
			 <div class="tab-content">
                <div id="tabs-box-1" class="tab-pane fade active in">
					<div class="pad-btm form-inline">
						<div class="row">
							<div class="col-sm-6 table-toolbar-left">
								<a href="#/examenes_bloques/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
							</div>
						</div>
					</div>

					<table id="listaExamenes_bloques" class="table table-striped table-bordered">
						<thead>
							<tr>
								<th>titulo</th>
								<th>Bloque</th>
								<th>estado</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							{% for examen_bloque in examenes_bloques %}
							<tr>
								<td>{{ examen_bloque.titulo }}</td>
								<td class="text-center">{{ examen_bloque.bloque.getNombre() }}</td>
								<td class="text-center">{{ examen_bloque.estado }}</td>
								
								<td class="text-center" style="width: 120px">
									<div class="btn-group dropup">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
										<ul class="dropdown-menu pull-right" role="menu">
											<li><a href="#/examenes_bloques_preguntas?examen_id={{ sha1(examen_bloque.id) }}">{{ icon('layout') }} Preguntas / Alternativas</a></li>
											<li><a href="#/examenes_bloques_compartidos?examen_id={{ sha1(examen_bloque.id) }}">{{ icon('application_side_list') }} Pruebas Realizadas</a></li>
											<li><a href="javascript:;" onclick="archivar_examen('{{ sha1(examen_bloque.id) }}')">{{ icon('report') }} Archivar Examen</a></li>
											<li class="divider"></li>
											<li><a href="#/examenes_bloques/form/{{ sha1(examen_bloque.id) }}">{{ icon('register') }} Editar Examen</a></li>
											<li><a href="javascript:;" onclick="borrar_examen_bloque('{{ sha1(examen_bloque.id) }}')">{{ icon('delete') }} Borrar Examen</a></li>
										</ul>
									</div>
								</td>
							</tr>
							{% endfor %}
						</tbody>
					</table>
				</div>
				<div id="tabs-box-2" class="tab-pane fade">
					<table id="listaExamenes_archivados" class="special table table-striped table-bordered">
						<thead>
							<tr>
								<th>titulo</th>
								<th>Bloque</th>
								<th>estado</th>
								<th style="width: 120px"></th>
							</tr>
						</thead>
						<tbody>
							{% for examen_bloque in archivados %}
							<tr>
								<td>{{ examen_bloque.titulo }}</td>
								<td class="text-center">{{ examen_bloque.bloque.getNombre() }}</td>
								<td class="text-center">{{ examen_bloque.estado }}</td>
								
								<td class="text-center" style="width: 120px">
									<div class="btn-group dropup">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
										<ul class="dropdown-menu pull-right" role="menu">
											<li><a href="#/examenes_bloques_preguntas?examen_id={{ sha1(examen_bloque.id) }}">{{ icon('layout') }} Preguntas / Alternativas</a></li>
											<li><a href="#/examenes_bloques_compartidos?examen_id={{ sha1(examen_bloque.id) }}">{{ icon('application_side_list') }} Pruebas Realizadas</a></li>
											<li class="divider"></li>
											<li><a href="#/examenes_bloques/form/{{ sha1(examen_bloque.id) }}">{{ icon('register') }} Editar Examen</a></li>
											<li><a href="javascript:;" onclick="borrar_examen_bloque('{{ sha1(examen_bloque.id) }}')">{{ icon('delete') }} Borrar Examen</a></li>
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
	</div>
</div>

{% endblock %}
