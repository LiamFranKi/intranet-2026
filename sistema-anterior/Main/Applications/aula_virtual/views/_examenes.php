<script>
$(function(){
    $('.tip').tooltip({html: true});
})
function iniciarPrueba(id){
    if(confirm('¿Está seguro(a) de iniciar el examen?'))
        $.post('/asignaturas_examenes/iniciar_prueba', {matricula_id: '{{ matricula.id }}', id: id}, function(r){

            if(r[0] == -2){
                return zk.pageAlert({message: 'No se pudo iniciar la prueba.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});  
            }

            zk.goToUrl('/asignaturas_examenes/prueba/' + r.prueba_id + '&token=' + r.token + '&time=' + r.time);
        }, 'json');
}


{% if USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) or USUARIO.is('DOCENTE') %}
function cambiar_asignatura_examen(id){
	zk.confirm('¿Está seguro de cambiar el estado?', function(){
		zk.sendData('/asignaturas_examenes/cambiar_estado', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Estado cambiado correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron cambiar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}
function borrar_asignatura_examen(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/asignaturas_examenes/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}
{% endif %}
</script>
<div class="panel panel-primary panel-bordered">					
    <!--Panel heading-->
    <div class="panel-heading">
        <div class="panel-control">

            <!--Nav tabs-->
            <ul class="nav nav-tabs">
            	{% for i in 1..COLEGIO.total_notas %}
                <li class="{{ i == 1 ? 'active' : '' }}"><a data-toggle="tab" href="#examenes-tabs-box-{{ i }}">Bim. {{ i }}</a></li>
                {% endfor %}
                
            </ul>
            {% if USUARIO.is('DOCENTE') or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
            <div class="btn-group dropdown">
                <button data-toggle="dropdown" class="dropdown-toggle btn btn-default btn-active-primary">
                    <i class="caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="#/asignaturas_examenes/form?asignatura_id={{ sha1(asignatura.id) }}">{{ icon('register') }} Registrar Nuevo</a></li>
                </ul>
            </div>
            {% endif %}
        </div>
        <h3 class="panel-title">EXÁMENES</h3>
    </div>

    <!--Panel body-->
    <div class="panel-body">

        <!--Tabs content-->
        <div class="tab-content">
        	{% for i in 1..COLEGIO.total_notas %}
            <div id="examenes-tabs-box-{{ i }}" class="tab-pane fade {{ i == 1 ? 'active in' : '' }}">
                {% set examenes = asignatura.getExamenes(i) %}
                {% if examenes|length > 0 %}
                <table class="special">
                	<tr>
                		<th>Examen</th>
                        <th>Tiempo (Min.)</th>
                        <th>Preguntas</th>
                        <th>Estado</th>
                		<th></th>
                	</tr>
                	{% for asignatura_examen in examenes %}
                    <tr>
                    	<td>{{ asignatura_examen.titulo }}</td>
                        <td class="text-center">{{ asignatura_examen.tiempo == 0 ? 'ILIMITADO' : asignatura_examen.tiempo }}</td>      
                        <td class="text-center">{{ asignatura_examen.preguntas|length }}</td>
                        <td class="text-center">
                            <span class="label label-{{ asignatura_examen.estado == 'ACTIVO' ? 'success' : 'warning' }}">{{ asignatura_examen.estado }}</span>
                            <!-- <span class="tip" title="El examen se activará a la fecha/hora indicada">{{ parseFecha(asignatura_examen.fecha_desde) }} - {{ asignatura_examen.hora_desde|date('h:i A') }}</span> -->
                        </td>
                    	<td class="text-center" style="width: 120px">
                    		{% if USUARIO.is('ALUMNO') %}
                                {% if asignatura_examen.tipo == "VIRTUAL" %}
                                    {% set pruebaActiva = matricula.getPruebaActivaAula(asignatura_examen) %}
                                    {% if pruebaActiva and pruebaActiva.activa() %}
                                        <button class="btn btn-success btn tip" onclick="zk.goToUrl('/asignaturas_examenes/prueba/{{ sha1(pruebaActiva.id) }}&token={{ pruebaActiva.token }}&time={{ pruebaActiva.fecha_hora|strtotime }}');" {{ pruebaActiva.examen.hasTiempoLimite() ? 'title="Esta prueba terminará el: <br />'~parseFechaHora(pruebaActiva.expiracion)~'"' : '' }}>Continuar Prueba</button>
                                    {% else %}
                                        {% if matricula.canDoTestAula(asignatura_examen) %}
                                        <!-- title="Disponible hasta: {{ parseFechaHora(asignatura_examen.fecha_hasta~' '~asignatura_examen.hora_hasta) }}" -->
                                        <button class="btn btn-primary btn tip"  onclick="iniciarPrueba('{{ sha1(asignatura_examen.id) }}')">Iniciar Prueba</button>
                                        {% else %}
                                            {% set prueba = matricula.getBestTestAula(asignatura_examen) %}
                                            {% if prueba and asignatura_examen.estado == "INACTIVO" %}
                                            <button class="btn btn-primary tip i" title="Ver Resultados" onclick="zk.goToUrl('/asignaturas_examenes/resultados_detalles/{{ sha1(prueba.id) }}')">Resultados</button>
                                            {% endif %}
                                        {% endif %}
                                    {% endif %}
                                {% else %}
                                    {% if asignatura_examen.archivo_pdf and asignatura_examen.estado == "ACTIVO" %}
                                        <button class="btn btn-primary" onclick="zk.printDocument('/Static/Archivos/{{ asignatura_examen.archivo_pdf }}')">Ver PDF</button>
                                    {% else %}
                                    <button class="btn btn-primary" disabled>Ver PDF</button>
                                    {% endif %}
                                {% endif %}
                            {% elseif USUARIO.is('APODERADO') %}
                    		{% elseif USUARIO.is('DOCENTE') or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
                                    {% if asignatura_examen.tipo == "VIRTUAL" %}
									<li><a href="#/asignaturas_examenes_preguntas?examen_id={{ sha1(asignatura_examen.id) }}">{{ icon('layout') }} Preguntas / Alternativas</a></li>
                                    <li><a href="#/asignaturas_examenes/resultados/{{ sha1(asignatura_examen.id) }}">{{ icon('application_side_list') }} Ver Resultados</a></li>
                                    <li><a href="javascript:;" onclick="fancybox('/asignaturas_examenes/asignar/{{ sha1(asignatura_examen.id) }}')">{{ icon('page_go') }} Asignar a Registro</a></li>
                                    <li class="divider"></li>
                                    {% else %}
                                    <li><a href="javascript:;" onclick="zk.printDocument('/Static/Archivos/{{ asignatura_examen.archivo_pdf }}')">{{ icon('application_view_list') }} Ver PDF</a></li>
                                    {% endif %}

                                    <li><a href="javascript:;" onclick="cambiar_asignatura_examen('{{ sha1(asignatura_examen.id) }}')">{{ icon('lock') }} Habilitar /  Deshabilitar</a></li>
                                    <li class="divider"></li>
									<li><a href="#/asignaturas_examenes/form/{{ sha1(asignatura_examen.id) }}">{{ icon('register') }} Editar Examen</a></li>

                                    {% if USUARIO.is('ADMINISTRADOR') %}
									<li><a href="javascript:;" onclick="borrar_asignatura_examen('{{ sha1(asignatura_examen.id) }}')">{{ icon('delete') }} Borrar Examen</a></li>
                                    {% endif %}
								</ul>
							</div>
							{% endif %}
						</td>
                    </tr>
                	{% endfor %}
                </table>
                {% else %}
                <p class="text-center">No se encontraron resultados</p>
                {% endif %}
            </div>
            {% endfor %}
        </div>
    </div>
</div>