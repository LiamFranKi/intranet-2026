<script>
$(function(){
    $('#lmatriculas').dataTable();
});
</script>
<div id="page-content">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">Lista de Alumnos</h3>
        </div>
        <div class="panel-body">
            <table class="dataTable table table-striped table-bordered table-hover" id="lmatriculas">
                <thead>
                    <tr>
                        <th>Apellidos y Nombres</th>
                        <th>Fecha de Registro</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                {% for matricula in matriculas %}
                {% set alumno = matricula.alumno %}

                    <tr>
                        <td style="min-width: 200px"><a href="javascript:;" onclick="fancybox('/alumnos/ver_datos/{{ matricula.alumno_id }}')">{{ alumno.apellido_paterno|upper }} {{ alumno.apellido_materno|upper }} , {{ alumno.nombres|upper }}</a></td>
                    
                        <td class="text-center">{{ matricula.getFechaRegistro() }}</td>
                        <td style="color: {{ matricula.getEstado() == 'REGULAR' ? '#008040' : '#FF0000' }}" class="text-center">{{ matricula.getEstado() }}</td>
                        <td class="text-center" style="width: 120px">
                            <div class="btn-group dropup">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    <li><a href="#/mensajes/form?to={{ alumno.usuario.id }}&asunto=Recomendación:">{{ icon('email') }} Enviar Recomendación</a></li>
                                    <li><a href="#/alumnos/ver_datos/{{ sha1(alumno.id) }}">{{ icon('user') }} Ver Información</a></li>
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
