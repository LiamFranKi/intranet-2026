<script>
$(function(){
    $('#exportBtn').on('click', function(){
        $(this).hide();
        exportarCanvasPDF("#summary", function(){
            $('#exportBtn').show();
        })
    })
})


</script>

<div id="" class="modal-800">

	<div class="modal-content" id="summary">
		<div class="modal-header">
			<h3 class="modal-title">Resumen de Incidentes/Estrellas/Asistencia</h3>
		</div>
		<div class="modal-body">

            <div class="mar-btm">
                <button class="btn btn-default btn-block" id="exportBtn">{{ icon('printer') }} Exportar PDF</button>
            </div>

            <div class="panel panel-primary panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title">Incidentes</h3>
                </div>
                <div class="panel-body">
                    {% if incidents|length > 0 %}
                    <table class="special">
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Descripción</th>
                            <th>Registrado Por</th>
                        </tr>
                        {% for incident in incidents %}
                        <tr>
                            <td class="text-center">{{ incident.created_at|date('d-m-Y h:i A') }}</td>
                            <td>{{ incident.description }}</td>
                            <td>{{ incident.worker.getFullName() }}</td>
                        </tr>
                        {% endfor %}
                    </table>
                    {% else %}
                    <p class="text-center">NO SE ENCONTRARON RESULTADOS</p>
                    {% endif %}
                </div>
            </div>

            <div class="panel panel-primary panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title">Estrellas</h3>
                </div>
                <div class="panel-body">
                    {% if stars|length > 0 %}
                    <table class="special">
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Registrado Por</th>
                        </tr>
                        {% for incident in stars %}
                        <tr>
                            <td class="text-center">{{ incident.created_at|date('d-m-Y h:i A') }}</td>
                            <td>{{ incident.points > 0 ? "Estrellas agregadas" : "Estrellas deducidas" }}</td>
                            <td class="text-center">{{ incident.points }}</td>
                            <td>{{ incident.worker.getFullName() }}</td>
                        </tr>
                        {% endfor %}
                    </table>
                    {% else %}
                    <p class="text-center">NO SE ENCONTRARON RESULTADOS</p>
                    {% endif %}
                </div>
            </div>

            <div class="panel panel-primary panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title">Asistencias</h3>
                </div>
                <div class="panel-body">
                    {% if attendances|length > 0 %}
                    <table class="special">
                        <tr>
                            <th>Fecha</th>
                            <th>Registro</th>
                            <th>Fecha</th>
                            <th>Registro</th>
                        </tr>
                        <tr>
                        {% for attendance in attendances %}
                        
                            <td class="text-center">{{ attendance.fecha|date('d-m-Y') }}</td>
                            <td class="text-center">{{ attendance.tipo }}</td>
                        {% if (_key + 1) % 2 == 0 %}
                        </tr><tr>
                        {% endif %}
                        {% endfor %}

                        </tr>
                    </table>
                    {% else %}
                    <p class="text-center">NO SE ENCONTRARON RESULTADOS</p>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>
</div>