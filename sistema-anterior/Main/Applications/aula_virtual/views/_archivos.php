{% if not USUARIO.is('ALUMNO') and not USUARIO.is('APODERADO') %}
<script>
function borrar_asignatura_archivo(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/asignaturas_archivos/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}

$(function(){
   
    $('.ordenTemas').sortable({
        update: function(e, u){
            //console.log($(u.item[0]))
            //console.log(e)
            //console.log($(this).sortable("toArray", {attribute: 'archivo_id'}))
            $.post('/asignaturas_archivos/update_orden', {data: $(this).sortable("toArray", {attribute: 'archivo_id'})});
        },
    });
   
})
</script>
{% endif %}
<div class="panel panel-primary panel-bordered">					
    <!--Panel heading-->
    <div class="panel-heading">
        <div class="panel-control">

            <!--Nav tabs-->
            <ul class="nav nav-tabs">
            	{% for i in 1..COLEGIO.total_notas %}
                <li class="{{ i == 1 ? 'active' : '' }}"><a data-toggle="tab" href="#archivos-tabs-box-{{ i }}">Bim. {{ i }}</a></li>
                {% endfor %}
                
            </ul>
            {% if (USUARIO.is('DOCENTE')) or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
            <div class="btn-group dropdown">
                <button data-toggle="dropdown" class="dropdown-toggle btn btn-default btn-active-primary">
                    <i class="caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="#/asignaturas_archivos/form?asignatura_id={{ sha1(asignatura.id) }}">{{ icon('register') }} Registrar Nuevo</a></li>
                </ul>
            </div>
            {% endif %}
        </div>
        <h3 class="panel-title">TEMAS INTERACTIVOS</h3>
    </div>

    <!--Panel body-->
    <div class="panel-body">

        <!--Tabs content-->
        <div class="tab-content">
        	{% for i in 1..COLEGIO.total_notas %}
            <div id="archivos-tabs-box-{{ i }}" class="tab-pane fade {{ i == 1 ? 'active in' : '' }}">
                {% set archivos = asignatura.getArchivos(i) %}
                {% if archivos|length > 0 %}
                <table class="special">
                	<tr>
                		<th>Nombre</th>
                		<th></th>
                	</tr>
                    <tbody class="ordenTemas">
                	{% for asignatura_archivo in archivos %}
                    
                        <tr archivo_id="{{ asignatura_archivo.id }}">
                        	<td>{{ asignatura_archivo.nombre }}</td>
                        	<td class="text-center" style="width: 120px">
                        		{% if (USUARIO.is('DOCENTE')) or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
                                <div class="btn-group dropup">
    								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
    								<ul class="dropdown-menu pull-right" role="menu">

                                        {% if asignatura_archivo.enlace %}
    									<li><a href="{{ asignatura_archivo.enlace }}" target="_blank">{{ icon('application_get') }} Abrir URL</a></li>
    									{% endif %}

    									{% if asignatura_archivo.archivo %}
    									<li><a href="/Static/Archivos/{{ asignatura_archivo.archivo }}" target="_blank">{{ icon('application_get') }} Ver Archivo</a></li>
    									{% endif %}

    									<li><a href="#/asignaturas_archivos/form/{{ sha1(asignatura_archivo.id) }}">{{ icon('register') }} Editar Tema</a></li>
    									<li><a href="javascript:;" onclick="borrar_asignatura_archivo('{{ sha1(asignatura_archivo.id) }}')">{{ icon('delete') }} Borrar Tema</a></li>
    								</ul>
    							</div>
                                
                        		{% else %} <!-- if USUARIO.is('ALUMNO') or USUARIO.is('APODERADO') -->
    							<a href="/Static/Archivos/{{ asignatura_archivo.archivo }}" target="_blank" class="btn btn-default">{{ icon('application_get') }} Ver Tema</a>
    							{% endif %}
    						</td>
                        </tr>
                    
                	{% endfor %}
                    </tbody>
                </table>
                {% else %}
                <p class="text-center">No se encontraron resultados</p>
                {% endif %}
            </div>
            {% endfor %}
        </div>
    </div>
</div>