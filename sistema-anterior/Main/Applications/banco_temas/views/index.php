{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaBanco_temas').dataTable();
	setMenuActive('banco_temas');
});

function borrar_banco_tema(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/banco_temas/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Banco de Temas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/banco_temas">Banco de Temas</a></li>
		<li class="active">Banco de Temas</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Banco de Temas</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/banco_temas/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="listaBanco_temas" class="table table-striped table-bordered">
				<thead>
					<tr>
						
						<th>Nombre</th>
						<th>Curso</th>
						<th>Nivel</th>
						
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for banco_tema in banco_temas %}
					<tr>
						
						<td>{{ banco_tema.nombre }}</td>
						<td>{{ banco_tema.curso.nombre }}</td>
						<td class="text-center">{{ banco_tema.grado }}° {{ banco_tema.nivel.nombre }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									{% if banco_tema.archivo %}
									<li><a href="javascript:;" onclick="zk.printDocument('/Static/Archivos/{{ banco_tema.archivo }}')">{{ icon('printer') }} Ver PDF</a></li>
									<li class="divider"></li>
									{% endif %}
									<li><a href="#/banco_temas/form/{{ sha1(banco_tema.id) }}">{{ icon('register') }} Editar Tema</a></li>
									<li><a href="javascript:;" onclick="borrar_banco_tema('{{ sha1(banco_tema.id) }}')">{{ icon('delete') }} Borrar Tema</a></li>
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
