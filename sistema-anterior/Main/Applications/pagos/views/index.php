{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaPagos').dataTable();
	setMenuActive('pagos');

	$('#searchForm').changeGradoOptions({
		showLabel: false,
		value: '{{ get.grado }}'
	});

	$('#searchForm').on('submit', function(e){
		e.preventDefault();
		zk.goToUrl('/pagos?' + $(this).serialize())
	})
});

function borrar_pago(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/pagos/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Pagos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/pagos">Pagos</a></li>
		<li class="active">Lista de Pagos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Buscar Alumnos</h3>
		</div>
		<div class="panel-body">
			<form id="searchForm" class="form-inline text-center">
				<div class="form-group">
					{{ form.sede_id }}
				</div>
				<div class="form-group">
					{{ form.nivel_id }}
				</div>
				<div class="form-group">
					{{ form.grado }}
				</div>
				<div class="form-group">
					{{ form.seccion }}
				</div>
				<div class="form-group">
					{{ form.turno_id }}
				</div>
				<div class="form-group">
					{{ form.anio }}
				</div>
				<div class="form-group">
					<button class="btn btn-primary">Buscar</button>
				</div>
			</form>
		</div>
	</div>
	<div class="panel">
		
		<div class="panel-body">
			

			<table id="listaPagos" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Apellidos y Nombres</th>
						<th>Costo</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for matricula in matriculas %}
					<tr>
						<td>{{ matricula.alumno.getFullName() }}</td>
						<td>{{ matricula.costo.getMontosCorto() }}</td>
						
						<td class="text-center" style="width: 120px">
							<a class="btn btn-primary" href="#/pagos/detalles?matricula_id={{ sha1(matricula.id) }}">Ver Pagos</a>
						</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>

{% endblock %}
