<script>
function borrar_asignatura_tarea(id){
    zk.confirm('¿Está seguro de borrar los datos?', function(){
        zk.sendData('/asignaturas_tareas/borrar', {id: id}, function(r){
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
<div class="panel panel-purple panel-bordered">					
    <!--Panel heading-->
    <div class="panel-heading">
        <div class="panel-control">

            <!--Nav tabs-->
            <ul class="nav nav-tabs">
            	{% for i in 1..COLEGIO.total_notas %}
                <li class="{{ i == 1 ? 'active' : '' }}"><a data-toggle="tab" href="#tareas_tabs-box-{{ i }}">Bim. {{ i }}</a></li>
                {% endfor %}
                
            </ul>
            {% if USUARIO.is('DOCENTE') or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
            <div class="btn-group dropdown">
                <button data-toggle="dropdown" class="dropdown-toggle btn btn-default btn-active-primary">
                    <i class="caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="#/asignaturas_tareas/form?asignatura_id={{ sha1(asignatura.id) }}">{{ icon('register') }} Registrar Nuevo</a></li>
                </ul>
            </div>
            {% endif %}
        </div>
        <h3 class="panel-title">TAREAS VIRTUALES</h3>
    </div>

    <!--Panel body-->
    <div class="panel-body">

        <!--Tabs content-->
        <div class="tab-content">
        	{% for i in 1..COLEGIO.total_notas %}
            <div id="tareas_tabs-box-{{ i }}" class="tab-pane fade {{ i == 1 ? 'active in' : '' }}">
                {% set tareas = asignatura.getTareas(i) %}
                {% if tareas|length > 0 %}
                <table class="special">
                	<tr>
                		<th>Nombre</th>
                        <th style="width: 150px">Fecha de Registro</th>
                        <th style="width: 150px">Fecha de Entrega</th>
                		<th></th>
                	</tr>
                	{% for tarea in tareas %}
                    <tr>
                    	<td>{{ tarea.titulo }}</td>
                        <td class="text-center">{{ tarea.fecha_hora|date('d-m-Y') }}</td>
                        <td class="text-center">{{ tarea.fecha_entrega|date('d-m-Y') }}</td>
                    	<td class="text-center" style="width: 120px">
                            {% if USUARIO.is('DOCENTE') or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/asignaturas_tareas/detalles/{{ sha1(tarea.id) }}">{{ icon('information') }} Ver Detalles</a></li>
                                    <li><a href="#/asignaturas_tareas/entrega/{{ sha1(tarea.id) }}">{{ icon('button_ok') }} Marcar Entregas</a></li>
                                    <li><a href="javascript:;" onclick="fancybox('/asignaturas_tareas/asignar/{{ sha1(tarea.id) }}')">{{ icon('page_go') }} Asignar a Registro</a></li>
									<li><a href="#/asignaturas_tareas/form/{{ sha1(tarea.id) }}">{{ icon('register') }} Editar Tarea</a></li>
                                    <li><a href="javascript:;" onclick="borrar_asignatura_tarea('{{ sha1(tarea.id) }}')">{{ icon('delete') }} Borrar Tarea</a></li>
								</ul>
							</div>
                    		{% else %} <!-- if USUARIO.is('ALUMNO') or USUARIO.is('APODERADO') -->
                            <a class="btn btn-default" href="#/asignaturas_tareas/detalles/{{ sha1(tarea.id) }}">{{ icon('information') }} Ver Detalles</a>
                    		
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