{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaExamenes_bloques_compartidos').dataTable();
	setMenuActive('examenes_bloques_compartidos');
});

function borrar_examen_bloque_compartido(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/examenes_bloques_compartidos/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Examenes Bloques / Compartidos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Examenes Bloques</a></li>
		<li class="active">Lista de Compartidos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Compartidos</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/examenes_bloques_compartidos/form?examen_id={{ get.examen_id }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaExamenes_bloques_compartidos" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Grupo</th>
						<th>Bimestre</th>
						<th>Tiempo (Min.)</th>
						
						<th>Expiración</th>
						<th>Estado</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for compartido in examenes_bloques_compartidos %}
					<tr>
						<td>{{ compartido.grupo.getNombreShortSede() }} </td>
						<td class="text-center">Bimestre {{ compartido.ciclo }} - {{ COLEGIO.roman(compartido.nro) }}</td>
						<td class="text-center">{{ compartido.tiempo }} min.<br />{{ compartido.intentos }} Intentos</td>
						
						<td class="text-center">{{ COLEGIO.getFechaHora(compartido.expiracion) }}</td>
						<td class="text-center" style="color: {{ compartido.activo() ? 'green' : 'blue' }}; font-weight: bold">{{ compartido.getEstado()|upper }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/examenes_bloques/resultados?compartido_id={{ sha1(compartido.id) }}">{{ icon('application_view_list') }} Ver Resultados</a></li>
									<li><a href="javascript:;" onclick="fancybox('/examenes_bloques/editar_resultados?compartido_id={{ sha1(compartido.id) }}')">{{ icon('register') }} Editar Resultados</a></li>
									{% if USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
									<li><a href="#/examenes_bloques_compartidos/form/{{ sha1(compartido.id) }}">{{ icon('register') }} Editar Compartido</a></li>
									<li><a href="javascript:;" onclick="borrar_examen_bloque_compartido('{{ sha1(compartido.id) }}')">{{ icon('delete') }} Borrar Compartido</a></li>
									{% endif %}
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
