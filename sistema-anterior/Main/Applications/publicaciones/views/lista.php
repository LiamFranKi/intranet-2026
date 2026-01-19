{% for publicacion in publicaciones %}
<div class="comments media-block">
    <a class="media-left" href="#"><img class="img-circle img-sm" alt="Foto" src="{{ publicacion.usuario.getFoto() }}"></a>
    <div class="media-body">
        <div class="comment-header">
            <a href="#" class="media-heading box-inline text-main text-semibold">{{ publicacion.usuario.getFullName() }}</a>
            <p class="text-muted text-sm">{{ getLongAgo(publicacion.fecha_hora) }}</p>
        </div>
        

    </div>
    <div class="media-body">
    	<p>{{ strip_tags(publicacion.contenido)|nl2br }}</p>
		
    	{% set images = publicacion.getImages() %}
		{% set offset = images|length - 3 %}
		{% if images|length > 0 %}
		<div class="row">
			{% for image in images %}
				{% if loop.index <= 3 or detalles %}
				<div class="col-sm-{{ detalles ? '3' : (images|length == 1 or loop.index % 3 == 0 ? '12' : '6') }}"><a href="javascript:;" onclick="$.fancybox({href: '{{ image }}'})" class="thumbnail"><img src="{{ image }}" class="img-responsive inline-block" alt="image"/></a></div>
				{% endif %}
			{% endfor %}
			
		</div>
		{% endif %}

		{% set archivos = publicacion.getFiles() %}
		{% if archivos|length > 0 %}
		<div class="row">
			<div class="col-sm-12">
				<table class="special">
				{% for archivo in archivos %}
					<tr>
						<td><a href="/Static/Archivos/{{ archivo }}" download="{{ _key }}">{{ _key }}</a></td>
					</tr>
				{% endfor %}
				</table>
			</div>
		</div>
		{% endif %}
    </div>
</div>
{% endfor %}
{% if publicaciones|length > 0 %}
<button class="btn btn-primary btn-block" onclick="postsApp.loadPage('{{ get.page + 1 }}', this)">Cargar Más...</button>
{% endif %}