{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	//$('#listaAsignaturas_examenes_preguntas').dataTable();
	setMenuActive('asignaturas_examenes_preguntas');
});

function borrar_asignatura_examen_pregunta(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/asignaturas_examenes_preguntas/borrar', {id: id}, function(r){
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
<style>
figure img{
	max-width:  100%;
}
</style>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Exámenes / Preguntas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Exámenes</a></li>
		<li class="active">Lista de Preguntas</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Preguntas</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/asignaturas_examenes_preguntas/form?examen_id={{ get.examen_id }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			{% if asignaturas_examenes_preguntas|length > 0 %}
			<table id="listaAsignaturas_examenes_preguntas" class="table special">
				<thead>
					<tr>
						<th>Descripción</th>
                        <th>Tipo</th>
						<th></th>
					</tr>
				</thead>
				<tbody>

					{% for asignatura_examen_pregunta in asignaturas_examenes_preguntas %}
					<tr>
						<td>{{ asignatura_examen_pregunta.descripcion }}</td>
                        <td class="text-center">{{ asignatura_examen_pregunta.tipo }}</td>
				
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/asignaturas_examenes_preguntas/form/{{ sha1(asignatura_examen_pregunta.id) }}">{{ icon('register') }} Editar Pregunta</a></li>
									<li><a href="javascript:;" onclick="borrar_asignatura_examen_pregunta('{{ sha1(asignatura_examen_pregunta.id) }}')">{{ icon('delete') }} Borrar Pregunta</a></li>
								</ul>
							</div>
						</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>
			{% else %}
			<p class="text-center">NO SE ENCONTRARON RESULTADOS</p>
			{% endif %}
		</div>
	</div>
</div>

{% endblock %}
