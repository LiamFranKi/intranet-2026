{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	//$('#listaExamenes_bloques_preguntas').dataTable();
	setMenuActive('examenes_bloques_preguntas');
});

function borrar_examen_bloque_pregunta(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/examenes_bloques_preguntas/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Examenes Bloques - Preguntas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Examenes Bloques</a></li>
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
						<a href="#/examenes_bloques_preguntas/form?examen_id={{ get.examen_id }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>
			
			<div class="alert alert-danger text-center">Tenga cuidado al borrar alguna pregunta y/o alternativa, ya que puede afectar a las pruebas realizadas.</div>

			<div class="panel-group accordion" id="listaCursos">
				{% for curso in cursos %}
	            <div class="panel panel-bordered panel-primary">
	
	                
	                <div class="panel-heading">
	                    <h4 class="panel-title">
	                        <a data-parent="#listCursos" data-toggle="collapse" href="#curso-{{ curso.id }}">{{ curso.nombre }}</a>
	                    </h4>
	                </div>
	
	                <!--Accordion content-->
	                <div class="panel-collapse collapse" id="curso-{{ curso.id }}">
	                    <div class="panel-body">
	                    	{% set preguntas = examen.getPreguntas(curso.id) %}
	                    	{% if preguntas|length > 0 %}
	                        <table id="listaExamenes_bloques_preguntas" class="table table-striped table-bordered">
								
								<tbody>
									{% for examen_bloque_pregunta in preguntas %}
									<tr>
									
										<td>{{ examen_bloque_pregunta.descripcion }}</td>
								
										
										<td class="text-center" style="width: 120px">
											<div class="btn-group dropup">
												<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
												<ul class="dropdown-menu pull-right" role="menu">
													<li><a href="#/examenes_bloques_preguntas/form/{{ sha1(examen_bloque_pregunta.id) }}">{{ icon('register') }} Editar Pregunta</a></li>
													<li><a href="javascript:;" onclick="borrar_examen_bloque_pregunta('{{ sha1(examen_bloque_pregunta.id) }}')">{{ icon('delete') }} Borrar Pregunta</a></li>
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
	            {% endfor %}
	        </div>

			
		</div>
	</div>
</div>

{% endblock %}
