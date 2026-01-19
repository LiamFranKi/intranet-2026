{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaGrupos_horarios').dataTable();
	setMenuActive('grupos_horarios');
});

function borrar_grupo_horario(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/grupos_horarios/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Horario</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/grupos">Grupos</a></li>
		<li class="active">Lista de Horarios</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Horarios</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-12 table-toolbar-left">
						{% if USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
						<a href="#/grupos_horarios/form?grupo_id={{ get.grupo_id }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
						{% endif %}
						<button class="btn btn-default" onclick="zk.printDocument('/grupos/imprimir_horario?grupo_id={{ grupo.id }}')">{{ icon('printer') }} Imprimir Vertical</button>
						<button class="btn btn-default" onclick="zk.printDocument('/grupos/imprimir_horario_horizontal?grupo_id={{ grupo.id }}')">{{ icon('printer') }} Imprimir Horizontal</button>
					</div>
				</div>
			</div>

			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
			{% for dia in grupo.DIAS %}
			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading" role="tab" id="horario_{{ dia }}_header">
					<h3 class="panel-title">
						<a style="color: white" data-toggle="collapse" data-parent="#accordion" href="#horario_{{ dia }}_content" aria-expanded="false" aria-controls="horario_{{ dia }}_content">
							<b>{{ dia|upper }}</b>
						</a>
					</h3>
				</div>
				<div id="horario_{{ dia }}_content" class="panel-collapse collapse {{ loop.index == 1 ? 'in' : '' }}" role="tabpanel" aria-labelledby="horario_{{ dia }}_header">
					<div class="panel-body">
						{% set horarios = grupo.getHorarios(_key) %}
						{% if horarios|length > 0 %}
						<table class="special">
							<tr>
								<th>Hora</th>
								<th>Asignatura</th>
								<th></th>
							</tr>
							{% for horario in horarios %}
							<tr>
								
									<td class="center">{{ horario.hora_inicio }} - {{ horario.hora_final }}</td>
								
								{% if horario.asignatura_id < 0 %}
									{% if horarioItems[horario.asignatura_id] %}
										<td class="center">{{ horarioItems[horario.asignatura_id] }}</td>
									{% else %}
										<td class="center">{{ horario.descripcion }}</td>
									{% endif %}
								{% else %}
									<td class="center">{{ horario.asignatura.curso.nombre }}</td>
								{% endif %}
								{% if USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
								<td class="center" onclick="borrar_grupo_horario('{{ sha1(horario.id) }}')"><button class="btn-default btn">{{ icon('delete') }}</button></td>
								{% endif %}
							</tr>
							{% endfor %}
						</table>
						{% else %}
						<p class="center"><b>NO SE ENCONTRARON RESULTADOS</b></p>
						{% endif %}
					</div>
				</div>
			</div>
			{% endfor %}
		</div>
		</div>
	</div>
</div>

{% endblock %}
