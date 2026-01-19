DEFINICIONES_GRADO = ['Grado', 'Años']
NIVELES = {}
{% for nivel in COLEGIO.niveles %}
NIVELES["{{ nivel.id }}"] = {{ json_encode(nivel.attributes()) }}
{% endfor %}