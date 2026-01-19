DEFINICIONES_GRADO = ['Grado', 'Años']
NIVELES = {}
{% for nivel in COLEGIO.niveles %}
NIVELES["{{ nivel.id }}"] = {{ json_encode(nivel.attributes()) }}
{% endfor %}

function seleccionarCiclo(callback, options){
	var x_data = '<b>SELECCIONE {{ COLEGIO.getCicloNotas()|upper }}: </b>' +
	'<select id="x_ciclo" class="input-sm">';
	{% for i in COLEGIO.getOptionsCicloNotas() %}
	x_data += '<option value="{{ _key }}">{{ i }}</option>';
	{% endfor %}
	x_data += '</select>';
	
	$.prompt(x_data, {submit: function(e, v){
		if(callback) callback(e, v, $('#x_ciclo').val());
	}});
}
