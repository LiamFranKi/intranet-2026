{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	//$('#listaAsignaturas_criterios').dataTable();
	setMenuActive('asignaturas_criterios');
});

function borrar_asignatura_criterio(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/asignaturas_criterios/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}

function cargar_criterios(){
  
	zk.confirm('Se añadirán los criterios por defecto.', function(){
		$.post('/asignaturas_criterios/cargar_criterios', {asignatura_id: '{{ sha1(asignatura.id) }}'}, function(){
			zk.pageAlert({message: 'Datos cargados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
			zk.reloadPage();
		});
	})
}

$(function(){
    $('#listaCriterios').sortable({
        update: function(e, u){
            $.post('/asignaturas_criterios/update_orden_criterios', {data: $(this).sortable("toArray", {attribute: 'criterio_id'})});
        },
    });
    
    $('.tip').tooltip();
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Criterios</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/asignaturas">Asignaturas</a></li>
		<li class="active">Lista de Criterios</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Criterios - {{ asignatura.curso.nombre }}</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-12 table-toolbar-left">
						<a href="#/asignaturas_criterios/form?asignatura_id={{ get.asignatura_id }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
						<button class="btn btn-default" onclick="cargar_criterios()">{{ icon('application_go') }} Cargar Criterios por Defecto</button>
					</div>
				</div>
			</div>
			 {% if asignaturas_criterios|length > 0 %}
    		<div class="alert alert-danger text-center">Tenga cuidado al borrar un criterio, ya que esto afectará el registro de notas.</div>
			<table id="listaAsignaturas_criterios" class="special">
				<thead>
					<tr>
						<th>Descripción</th>
			            {% if asignatura.grupo.nivel.calificacionPorcentual() %}
			            <th>Peso %</th>
			            {% endif %}
                        <th>Bimestre</th>
						<th></th>
					</tr>
				</thead>
				<tbody id="listaCriterios">
					{% for asignatura_criterio in asignaturas_criterios %}
					<tr criterio_id="{{ asignatura_criterio.id }}">
						<td>{{ asignatura_criterio.descripcion }}</td>
			            {% if asignatura.grupo.nivel.calificacionPorcentual() %}
						<td class="text-center"><b>{{ asignatura_criterio.peso }}</b></td>
			            {% endif %}
                        <td class="text-center">{{ asignatura_criterio.ciclo == 0 ? 'TODOS' : asignatura_criterio.ciclo }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									{% if asignatura.grupo.nivel.calificacionCuantitativa() %}
									<li><a href="#/asignaturas_indicadores?criterio_id={{ sha1(asignatura_criterio.id) }}">{{ icon('application_view_list') }} Indicadores</a></li>
									{% endif %}
									<li><a href="#/asignaturas_criterios/form/{{ sha1(asignatura_criterio.id) }}">{{ icon('register') }} Editar Criterio</a></li>
									<li><a href="javascript:;" onclick="borrar_asignatura_criterio('{{ sha1(asignatura_criterio.id) }}')">{{ icon('delete') }} Borrar Criterio</a></li>
								</ul>
							</div>
						</td>
					</tr>
					{% endfor %}
				</tbody>
				<tfoot>
		            {% if asignatura.curso.examen_mensual == 'SI' and asignatura.curso.nivel.calificacionPorcentual() %}
		            <tr>
		                <td>EXAMEN MENSUAL</td>
		                <td class="text-center"><b>{{ asignatura.curso.peso_examen_mensual }}</b></td>
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
