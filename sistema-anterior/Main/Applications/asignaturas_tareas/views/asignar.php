<script>
    $(function() {
        $('#formAsignar').niftyOverlay();
        $('#formAsignar').bootstrapValidator({
            fields: {},
            onSuccess: function(e) {
                e.preventDefault();
                _form = e.target;
                if(!confirm('¿Está seguro de asignar las notas?')){
                    return;
                }
                $('button[type="submit"]').attr('disabled', false);

                $(_form).sendForm('/asignaturas_tareas/do_asignar', function(r) {


                    switch (parseInt(r[0])) {
                        case 1:
                            zk.pageAlert({
                                message: 'Datos guardados correctamente',
                                title: 'Operación Exitosa',
                                icon: 'check',
                                type: 'success',
                                container: 'floating'
                            });
                            $.fancybox.close()
                            break;

                        case 0:
                            zk.pageAlert({
                                message: 'No se pudieron guardar los datos',
                                title: 'Operación Fallida',
                                icon: 'bolt',
                                type: 'danger',
                                container: 'floating'
                            });

                            break;

                        case -5:
                            zk.formErrors(_form, r.errors);
                            break;

                        default:

                            break;
                    }

                });
            }
        });
    })
</script>
<form id="formAsignar" data-target="#formAsignar" data-toggle="overlay">
    <div class="modal-content modal-800">
        <div class="modal-header">
            <div class="modal-title">Asignar a Registro</div>
        </div>
        <div class="modal-body">
            <input type="hidden" name="tarea_id" value="{{ asignatura_tarea.id }}" />
            <input type="hidden" name="asignatura_id" value="{{ asignatura.id }}" />
            <input type="hidden" name="ciclo" value="{{ asignatura_tarea.ciclo }}" />

            <table class="special">
                <tr>
                    <th>Tarea</th>
                    <td>{{ asignatura_tarea.titulo }}</td>
                </tr>
                <tr>
                    <th>Asignatura</th>
                    <td>{{ asignatura.curso.nombre }}</td>
                </tr>
                <tr>
                    <th>Bimestre</th>
                    <td>{{ asignatura_tarea.ciclo }}</td>
                </tr>

                <tr>
                    <th>Criterio</th>
                    <td>
                        <select name="criterio_id" class="form-control">
                            {% for criterio in criterios %}
                                {% if criterio.getIndicadores()|length > 0 %}
                                {% for indicador in criterio.getIndicadores() %}
                                    <option value="{{ criterio.id }}_{{ indicador.id }}">{{ criterio.descripcion }} - {{ indicador.descripcion }}</option>
                                {% endfor %}
                                {% endif %}
                            {% endfor %}
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Cuadro</th>
                    <td>
                        <select name="cuadro" class="form-control" style="width: 60px">
                            {% for i in 1..10 %}
                            <option value="{{ i-1 }}">{{ i }}</option>
                            {% endfor %}
                        </select>
                    </td>
                </tr>
            </table>
            <div class="alert alert-warning text-center">Se reemplazarán las notas en el registro del cuadro seleccionado</div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="$.fancybox.close()">Cancelar</button>
            <button class="btn btn-primary" type="submit">Guardar Datos</button>
        </div>
    </div>
</form>