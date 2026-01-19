<style>
.notice-content img, .img-responsive{
    max-width: 800px !important;
}

.notice-content iframe{
    width: 100%;
    height: 600px;
}
</style>

{% for notice in notices %}
    {% if notice.isImage() %}
    <div class="text-center {{ notices|length > 1 ? 'mar-btm' : '' }} notice-content modal-800"><img src="/Static/Archivos/{{ notice.archivo }}" alt="{{ notice.descripcion }}" class=""></div>
    {% else %}
    <div class="panel panel-primary panel-bordered notice-content modal-800">
        <div class="panel-heading">
            <div class="panel-control">
                <em class=""><small>Publicado el: {{ notice.fecha_hora|date('d-m-Y h:i A') }}</small></em>
            </div>
            <h3 class="panel-title">{{ notice.descripcion }}</h3>
            
        </div>
        <div class="panel-body ">
            {% if notice.tipo == "TEXTO" %}
                {{ notice.contenido }}
            {% elseif notice.isImage() %}
            <img src="/Static/Archivos/{{ notice.archivo }}" alt="{{ notice.descripcion }}" class="img-responsive">
            {% else %}
            <iframe src="/Static/Archivos/{{ notice.archivo }}" frameborder="0"></iframe>
            {% endif %}

        </div>
    </div>
    {% endif %}
{% endfor %}