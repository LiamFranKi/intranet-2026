{% if USUARIO.is('ADMINISTRADOR') %}
	{% include 'menus/administrador.php' %}
{% endif %}
{% if USUARIO.is('ALUMNO') %}
	{% include 'menus/alumno.php' %}
{% endif %}
{% if USUARIO.is('APODERADO') %}
	{% include 'menus/apoderado.php' %}
{% endif %}
{% if USUARIO.is('DOCENTE') %}
	{% include 'menus/docente.php' %}
{% endif %}
{% if USUARIO.is('CAJERO') %}
	{% include 'menus/cajero.php' %}
{% endif %}
{% if USUARIO.is('SECRETARIA') %}
	{% include 'menus/secretaria.php' %}
{% endif %}
{% if USUARIO.is('ASISTENCIA') %}
	{% include 'menus/asistencia.php' %}
{% endif %}