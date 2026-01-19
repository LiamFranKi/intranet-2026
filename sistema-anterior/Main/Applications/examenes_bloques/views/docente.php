<script>
$(function(){
	$('#listaExamenes_bloques').dataTable();
	$('#listaExamenes_archivados').dataTable();
	setMenuActive('examenes_bloques');
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Examenes de Bloques</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/examenes_bloques/docente">Examenes Bloques</a></li>
		<li class="active">Lista de Examenes Bloques</li>
	</ol>
</div>

<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Exámenes</h3>
		</div>
		<div class="panel-body">
			 <div class="tab-content">
                <div id="tabs-box-1" class="tab-pane fade active in">
					

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