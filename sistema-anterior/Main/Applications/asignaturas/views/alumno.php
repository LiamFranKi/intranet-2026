{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaAsignaturas').dataTable();
	setMenuActive('cursos_asignados');
});

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Cursos Asignados</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="/">Asignaturas</a></li>
		<li class="active">Cursos Asignados</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	{% for asignatura in asignaturas %}
		<div class="col-lg-4 col-xs-12 col-sm-6">
			<div class="panel widget">
	            <div class="widget-header bg-primary">
	                <img class="widget-bg img-responsive" src="{{ asignatura.curso.getImagen() }}" alt="">
	            </div>
	            <div class="widget-body text-center" style="">
	                <img alt="" class="widget-img img-circle img-border-light" src="{{ asignatura.personal.getFoto() }}">
	                <h4 class="text-main text-overflow" style="">{{ asignatura.curso.nombre }}</h4>
	                <p class="text-muted text-overflow mar-no">{{ asignatura.personal.getFullName() }}</p>
	                <p>
	                	<div class="btn-group dropup">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
							<ul class="dropdown-menu" role="menu">
								<li><a href="#/aula_virtual/index/{{ sha1(asignatura.id) }}">{{ icon('house') }} Aula Virtual</a></li>
								{% if asignatura.aula_virtual %}
								<li><a href="{{ asignatura.aula_virtual }}" target="_blank">{{ icon('world_go') }} Link Aula Virtual</a></li>
								{% endif %}
								
								<li><a href="{{ asignatura.link_libro ? asignatura.link_libro : 'javascript:;' }}" target="_blank">{{ icon('book') }} Ver Libro</a></li>
								
								<li class="divider"></li>
								<li><a href="javascript:;" onclick="zk.printDocument('/reportes/imprimir_asistencia_asignatura_matricula?matricula_id={{ matricula.id }}&asignatura_id={{ asignatura.id }}')">{{ icon('printer') }} Imprimir Asistencia</a></li>
									<li><a href="#/mensajes/form?to={{ asignatura.personal.usuario.id }}">{{ icon('email') }} Enviar mensaje al docente</a></li>
							</ul>
						</div>
	                </p>
	            </div>
	        </div>
	    </div>
	    {% else %}
	    <div class="panel">
	    	<div class="panel-body text-center">NO SE ENCONTRARON RESULTADOS</div>
	    </div>
		{% endfor %}
</div>

{% endblock %}
