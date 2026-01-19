{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaPersonal').dataTable();
	setMenuActive('personal');
});

function borrar_personal(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/personal/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Personal</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/personal">Personal</a></li>
		<li class="active">Lista de Personal</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Personal</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/personal/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaPersonal" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th></th>
						<th>Apellidos y Nombres</th>
						<th>N° de Documento</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for persona in personal %}
					<tr>
						<td class="text-center"><img src="{{ persona.getFoto() }}" alt="Foto" class="img-circle img-sm"></td>
						<td>{{ persona.getFullName() }}</td>
						<td class="text-center">{{ persona.nro_documento }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/personal/ver_datos/{{ sha1(persona.id) }}">{{ icon('information') }} Ver Información</a></li>
									<li><a href="#/personal/horario_docente/{{ sha1(persona.id) }}">{{ icon('calendar') }} Ver Horario</a></li>
									<li><a href="#/personal/acceso/{{ sha1(persona.id) }}">{{ icon('lock') }} Información de Acceso</a></li>
									<li><a href="#/personal/form/{{ sha1(persona.id) }}">{{ icon('register') }} Editar Personal</a></li>
									<li><a href="javascript:;" onclick="borrar_personal('{{ sha1(persona.id) }}')">{{ icon('delete') }} Borrar Personal</a></li>
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
