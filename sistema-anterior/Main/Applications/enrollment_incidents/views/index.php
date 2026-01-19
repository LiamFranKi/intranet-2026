{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaEnrollmentIncidents').dataTable();
	setMenuActive('enrollmentIncidents');
});

function borrarEnrollmentIncident(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/enrollment_incidents/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				$.fancybox.reload()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});
			}
		});
	});
}
</script>

<div id="" class="modal-800">

	<div class="modal-content">
		<div class="modal-header">
			<h3 class="modal-title">Reporte de Incidentes</h3>
		</div>
		<div class="modal-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="javascript:;" onclick="fancybox('/enrollment_incidents/form?enrollment_id={{ params.enrollment_id }}&assignment_id={{ params.assignment_id }}')" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaEnrollmentIncidents" class="table table-striped table-bordered">
				<thead>
					<tr>
                        <th>Fecha / Hora</th>
						<th>Descripción</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for enrollmentIncident in enrollmentIncidents %}
					<tr>
                        <td class="text-center">{{ enrollmentIncident.created_at|date('Y-m-d h:i A') }}</td>
						<td>{{ enrollmentIncident.description }}</td>

						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="javascript:;" onclick="fancybox('/enrollment_incidents/form/{{ sha1(enrollmentIncident.id) }}')">{{ icon('register') }} Editar Incidente</a></li>
									<li><a href="javascript:;" onclick="borrarEnrollmentIncident('{{ sha1(enrollmentIncident.id) }}')">{{ icon('delete') }} Borrar Incidente</a></li>
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
