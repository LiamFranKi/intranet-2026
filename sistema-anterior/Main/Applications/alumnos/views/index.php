{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaAlumnos').dataTable();
	setMenuActive('alumnos');

	$('#searchForm').on('submit', function(e){
		e.preventDefault();
		zk.goToUrl('/alumnos?' + $(this).serialize())
	})
});

function borrar_alumno(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/alumnos/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Alumnos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/alumnos">Alumnos</a></li>
		<li class="active">Lista de Alumnos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Alumnos</h3>
		</div>
		<div class="panel-body">
			<form id="searchForm" class="form-inline text-center">
				<div class="form-group form-group-sm">
					<input type="text" name="query" class="form-control" style="width: 300px" placeholder="Ingrese nombres, apellidos o DNI">
				</div>
				<div class="form-group">
					<button class="btn btn-primary">Buscar</button>
				</div>
			</form>
		</div>
	</div>
	<div class="panel">
		
		<div class="panel-body">
	
			<table id="listaAlumnos" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th></th>
						<th>Apellidos y Nombres</th>
						<th>Fecha de Nacimiento</th>
						<th>N° de Documento</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for alumno in alumnos %}
					<tr>
						<td class="text-center"><img src="{{ alumno.getFoto() }}" alt="Foto" class="img-circle img-sm"></td>
						<td>{{ alumno.getFullName() }}</td>
						<td class="text-center">{{ alumno.fecha_nacimiento }}</td>
						<td class="text-center">{{ alumno.nro_documento }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/alumnos/ver_datos/{{ sha1(alumno.id) }}">{{ icon('information') }} Ver Información</a></li>
									{% if alumno.usuario %}
									<li><a href="#/mensajes/form?to={{ alumno.usuario.id }}">{{ icon('email') }} Enviar Comunicado</a></li>
									{% endif %}
									<li><a href="#/matriculas?alumno_id={{ sha1(alumno.id) }}">{{ icon('page_edit') }} Matrículas Registradas</a></li>
									<li><a href="#/alumnos/apoderados/{{ sha1(alumno.id) }}">{{ icon('user_gray') }} Padres de Familia / Apoderados</a></li>
									<li><a href="#/pagos/historial?alumno_id={{ sha1(alumno.id) }}">{{ icon('list') }} Historial de Pagos</a></li>
									<li><a href="#/alumnos/acceso/{{ sha1(alumno.id) }}">{{ icon('lock') }} Información de Acceso</a></li>
									<li class="divider"></li>
									<li><a href="#/alumnos/form/{{ sha1(alumno.id) }}">{{ icon('register') }} Editar Alumno</a></li>
									<li><a href="javascript:;" onclick="borrar_alumno('{{ sha1(alumno.id) }}')">{{ icon('delete') }} Borrar Alumno</a></li>
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
