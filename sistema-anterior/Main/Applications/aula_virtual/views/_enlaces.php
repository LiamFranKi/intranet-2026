<script>
function borrar_asignatura_enlace(id){
    zk.confirm('¿Está seguro de borrar los datos?', function(){
        zk.sendData('/asignaturas_enlaces/borrar', {id: id}, function(r){
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
<div class="panel panel-info panel-bordered">					
    <!--Panel heading-->
    <div class="panel-heading">
        <div class="panel-control">

            <!--Nav tabs-->
            <ul class="nav nav-tabs">
            	{% for i in 1..COLEGIO.total_notas %}
                <li class="{{ i == 1 ? 'active' : '' }}"><a data-toggle="tab" href="#enlaces_tabs-box-{{ i }}">Bim. {{ i }}</a></li>
                {% endfor %}
                
            </ul>
            {% if USUARIO.is('DOCENTE') or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
            <div class="btn-group dropdown">
                <button data-toggle="dropdown" class="dropdown-toggle btn btn-default btn-active-primary">
                    <i class="caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="#/asignaturas_enlaces/form?asignatura_id={{ sha1(asignatura.id) }}">{{ icon('register') }} Registrar Nuevo</a></li>
                </ul>
            </div>
            {% endif %}
        </div>
        <h3 class="panel-title">ENLACES DE AYUDA</h3>
    </div>

    <!--Panel body-->
    <div class="panel-body">

        <!--Tabs content-->
        <div class="tab-content">
        	{% for i in 1..COLEGIO.total_notas %}
            <div id="enlaces_tabs-box-{{ i }}" class="tab-pane fade {{ i == 1 ? 'active in' : '' }}">
                {% set enlaces = asignatura.getEnlaces(i) %}
                {% if enlaces|length > 0 %}
                <table class="special">
                	<tr>
                		<th>Nombre</th>
                        <th style="width: 150px">Fecha </th>
                		<th></th>
                	</tr>
                	{% for enlace in enlaces %}
                    <tr>
                    	<td>{{ enlace.descripcion }}</td>
                        <td class="text-center">{{ video.fecha_hora|date('d-m-Y h:i A') }}</td>
                    	<td class="text-center" style="width: 120px">
                            {% if USUARIO.is('DOCENTE') or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									{% if enlace.enlace %}
									<li><a href="{{ enlace.enlace }}" target="_blank">{{ icon('application_get') }} Visitar Enlace</a></li>
									{% endif %}
									<li><a href="#/asignaturas_enlaces/form/{{ sha1(enlace.id) }}">{{ icon('register') }} Editar Enlace</a></li>
                                    <li><a href="javascript:;" onclick="borrar_asignatura_enlace('{{ sha1(enlace.id) }}')">{{ icon('delete') }} Borrar Enlace</a></li>
								</ul>
							</div>
                    		{% else %} <!-- if USUARIO.is('ALUMNO') or USUARIO.is('APODERADO') -->
                            <a class="btn btn-default" href="{{ enlace.enlace }}" target="_blank">{{ icon('application_get') }} Visitar Enlace</a>
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