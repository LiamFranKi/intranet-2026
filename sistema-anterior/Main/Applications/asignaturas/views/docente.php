<script>
$(function(){
	setMenuActive('cursos_asignados');
});
function registrarNotas(asignatura_id){
	seleccionarCiclo(function(e, v, ciclo){
		fancybox('/notas/registrar?asignatura_id=' + asignatura_id + '&ciclo=' + ciclo);
	})
}
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
<div id="page-content">
	<div class="row">
		{% for asignatura in asignaturas %}
		<div class="col-lg-4 col-xs-6 col-sm-4">
			<div class="panel widget">
	            <div class="widget-header bg-primary">
	                <img class="widget-bg img-responsive" src="{{ asignatura.curso.getImagen() }}" alt="">
	            </div>
	            <div class="widget-body text-center" style="padding-top: 15px">
	                <!--<img alt="" class="widget-img img-circle img-border-light" src="{{ asignatura.personal.getFoto() }}">-->
	                <h4 class="text-main text-overflow" style="">{{ asignatura.curso.nombre }}</h4>
	                <p class="text-muted text-overflow mar-no">{{ asignatura.grupo.getNombreShort() }}</p>
	                <p>
	                	<div class="btn-group dropup">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li><a href="#/aula_virtual/index/{{ sha1(asignatura.id) }}">{{ icon('house') }} Aula Virtual</a></li>
                                <li><a href="#/asignaturas/lista_alumnos/{{ sha1(asignatura.id) }}">{{ icon('application_view_list') }} Lista de Alumnos</a></li>
                                <li><a href="javascript:;" onclick="fancybox('/aula_virtual/copy_form/{{ sha1(asignatura.id) }}')">{{ icon('page_copy') }} Copiar Contenido</a></li>
								<li class="divider"></li>
								{% if asignatura.grupo.nivel.calificacionCualitativa() %}
								<li><a href="#/asignaturas_criterios?asignatura_id={{ sha1(asignatura.id) }}">{{ icon('application_view_list') }} Criterios / Indicadores</a></li>
								{% endif %}

		                       	<li><a href="javascript:;" onclick="zk.printDocument('/grupos/imprimir_horario?grupo_id={{ asignatura.grupo_id }}&asignatura_id={{ asignatura.id }}')">{{ icon('calendar') }} Ver Horario</a></li>
		                       
								<li><a href="javascript:;" onclick="registrarNotas('{{ sha1(asignatura.id) }}')">{{ icon('application_form_edit') }} Registrar Notas</a></li>

								<li><a href="#/asignaturas/clase_zoom/{{ sha1(asignatura.id) }}">{{ icon('world') }} Link Aula Virtual</a></li>
								
								<li><a href="{{ asignatura.link_libro ? asignatura.link_libro : 'javascript:;' }}" target="_blank">{{ icon('book') }} Ver Libro</a></li>
								
								<li><a href="#/asignaturas/asistencia/{{ sha1(asignatura.id) }}">{{ icon('calendar') }} Registrar Asistencia</a></li>
								
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
</div>