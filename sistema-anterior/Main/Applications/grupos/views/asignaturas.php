<script>
function registrarNotas(asignatura_id){
	seleccionarCiclo(function(e, v, ciclo){
		fancybox('/notas/registrar?asignatura_id=' + asignatura_id + '&ciclo=' + ciclo + '&readonly=true');
	})
}
</script>
<div class="modal-content modal-800">
    <div class="modal-header">
        <h3 class="modal-title">Lista de Asignaturas</h3>
    </div>
    <div class="modal-body">
        <table id="listaAsignaturas" class="special">
            <thead>
                <tr>
                    <th>Docente</th>
                    <th>Curso</th>
                    <th>Grupo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {% for asignatura in asignaturas %}
                <tr>
                    <td>{{ asignatura.personal.getFullName() }}</td>
                    <td>{{ asignatura.curso.nombre }}</td>
                    <td>{{ asignatura.grupo.getNombreShort2() }}</td>

                    <td class="text-center" style="width: 120px">
                        <div class="btn-group dropup">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                        
                                <li><a href="javascript:;" onclick="registrarNotas('{{ sha1(asignatura.id) }}')">{{ icon('application_form_edit') }} Registro de Notas</a></li>
                            
                            </ul>
                        </div>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>