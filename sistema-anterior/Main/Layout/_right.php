<div id="page-head">
</div>
<div id="page-content">
    {% if USUARIO.is('ALUMNO') %}
        {% include '_avatars.php' %}
    {% endif %}

	{% include '_calendario.php' %}
	{% include '_publicaciones.php' %}

</div>