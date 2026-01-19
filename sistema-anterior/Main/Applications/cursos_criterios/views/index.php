{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	//$('#listaCursos_criterios').dataTable();
	setMenuActive('cursos_criterios');

	$('#listaCriterios').sortable({
        update: function(e, u){
            $.post('/cursos_criterios/update_orden_criterios', {data: $(this).sortable("toArray", {attribute: 'criterio_id'})});
        },
    });
    $('.tip').tooltip();
});

function borrar_curso_criterio(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/cursos_criterios/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Criterios</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Cursos</a></li>
		<li class="active">Lista de Criterios</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Criterios - {{ curso.nombre }}</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/cursos_criterios/form?curso_id={{ get.curso_id }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>
			{% if cursos_criterios|length > 0 %}
			<div class="alert alert-danger text-center">Tenga cuidado al borrar un criterio, ya que esto afectará el registro de notas.</div>
			<table id="listaCursos_criterios" class="special">
				<thead>
					<tr>
						<th>Descripción</th>
			            {% if curso.nivel.calificacionPorcentual() %}
			            <th>Peso %</th>
			            {% endif %}
						<th></th>
					</tr>
				</thead>
				<tbody id="listaCriterios">
					{% for curso_criterio in cursos_criterios %}
					<tr criterio_id="{{ curso_criterio.id }}">
						 <td>{{ curso_criterio.descripcion }}</td>
			            {% if curso.nivel.calificacionPorcentual() %}
						<td class="text-center"><b>{{ curso_criterio.peso }}</b></td>
			            {% endif %}
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/cursos_criterios/form/{{ sha1(curso_criterio.id) }}">{{ icon('register') }} Editar Criterio</a></li>
									<li><a href="javascript:;" onclick="borrar_curso_criterio('{{ sha1(curso_criterio.id) }}')">{{ icon('delete') }} Borrar Criterio</a></li>
								</ul>
							</div>
						</td>
					</tr>
					{% endfor %}
				</tbody>
				<tfoot>
		            {% if curso.examen_mensual == 'SI' and curso.nivel.calificacionPorcentual() %}
		            <tr>
		                <td>EXAMEN MENSUAL</td>
		                <td class="center"><b>{{ curso.peso_examen_mensual }}</b></td>
		            </tr>
		            {% endif %}
		        </tfoot>
			</table>
			<div class="alert alert-info text-center">Puede modificar el orden de los criterios, arrastrando el criterio correspondiente al lugar deseado.</div>
			{% else %}
			<p class="text-center"><b>NO SE ENCONTRARON RESULTADOS</b></p>
			{% endif %}
		</div>
	</div>
</div>

{% endblock %}
