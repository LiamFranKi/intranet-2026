{% if personal %}
    {% if asistencia %}
        {{ personal.getFullName() }} - {{ asistencia.tipo }}: {{ asistencia.hora_real|date('h:i A') }}
    {% else %}
    <div class="alert alert-warning text-center">Ya registró la entrada y salida</div>
    {% endif %}
{% else %}
    <div class="alert alert-danger text-center">No se encontró el DNI.</div>
{% endif %}