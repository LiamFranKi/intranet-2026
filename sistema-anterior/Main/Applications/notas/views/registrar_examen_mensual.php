<script>

var NOTA_APROBATORIA = parseFloat('{{ asignatura.grupo.nivel.nota_aprobatoria }}');
var NOTA_MINIMA = parseFloat('{{ asignatura.grupo.nivel.nota_minima }}');
var NOTA_MAXIMA = parseFloat('{{ asignatura.grupo.nivel.nota_maxima }}');

Number.prototype.round = function(places) {
    return +(Math.round(this + "e+" + places)  + "e-" + places);
}

function get_back(){
	$.prompt('¿Está seguro de volver atrás?', {
		buttons: {
			"Si": 0,
			"No": 1
		},
		submit: function(e, v){
			if(v == 0){
				return fancybox('/home/seleccionar_ciclo?callback={{ encode_x('/notas/registrar_examen_mensual?asignatura_id='~get.asignatura_id) }}')
			}
			$.prompt.close();
		}
	})
}

</script>
<script>
var CRITERIOS = parseInt('{{ criterios|length }}');

$(function(){
    $('#fnotas').bootstrapValidator({
		//container: 'tooltip',
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$(_form).sendForm('/notas/save_examen_mensual', function(r){
				switch(parseInt(r[0])){
					case 1:
						$.prompt('Nota guardadas correctamente');
					break;
					case 0:
						$.prompt('No se pudieron guardar las notas');
					break;
				}
			});
		}
	});

	$('.criterio').bind('blur', function(e){
        current_value = parseFloat(this.value);
        // revisa si el valor está permitido
        if(this.value != ''){
			if(current_value < NOTA_MINIMA || current_value > NOTA_MAXIMA || !$.isNumeric(current_value)){
				_field = this;
				return $.prompt('Ingrese valores de: ' + NOTA_MINIMA + ' a ' + NOTA_MAXIMA, {submit: function(){
					_field.value = '';
					_field.focus();
				}});
			}
		}

		matricula_id = $(this).data('matricula');
		criterios = document.querySelectorAll('input[data-matricula="' + matricula_id + '"]');
		total = 0;
		total_criterios = 0;
		for(i in criterios){
            if($.isNumeric(i)){
                nota = $(criterios[i]).val();
                if(nota != ''){
                    // cambia el color de texto
                    if(nota >= NOTA_APROBATORIA) $(criterios[i]).css('color', 'blue');
                    if(nota < NOTA_APROBATORIA) $(criterios[i]).css('color', 'red');
                    total_criterios++;
					total += parseFloat(nota);
                }
            }
        }
        console.log(total);
        promedio = total_criterios > 0 ? (total/total_criterios).round(0) : '';
        $('#promedio_' + matricula_id).val(promedio);
	});
});

$.each($('.line-alumno'), function(i, obj){
    $(obj).find('input').first().trigger('blur');
});
</script>
<div class="menu">
    <button class="btn btn-default" type="button" onclick=" $('#fnotas').trigger('submit')">{{ icon('table_save') }} Guardar Datos</button>
    <button class="btn btn-default" type="button" onclick="get_back()">{{ icon('calendar') }} Seleccionar {{ COLEGIO.getCicloNotas()|capitalize }}</button>
    
</div>

<form id="fnotas">
<div class="block" style="width: 700px">
<h2>{{ curso.nombre }} - {{ COLEGIO.getCicloNotasSingle(get.ciclo) }} - {{ asignatura.grupo.anio }}</h2>

<style>
#lista_alumnos th{
	padding-left: 0;
	text-align: center;
}

#lista_alumnos td{
	text-align: center;
}
</style>


<div class="alert alert-info center">
    Ingrese notas de: {{ asignatura.grupo.nivel.nota_minima }} - {{ asignatura.grupo.nivel.nota_maxima }}. {% if asignatura.grupo.nivel.calificacionPromedio() %}Si deja en blanco un criterio este no se contabiliza en el promedio. {% endif %}
</div>
<table id="lista_alumnos" class="special" style="width: 100%">
	<thead>
	<tr>
		<th style="width: 20px">Nº</th>
		<th style="width: 300px;">Apellidos y Nombres</th>
   
        {% for i in 1..2 %} 
        <th style="padding: 3px 5px;text-align: center;">
        	{{ COLEGIO.roman(i) }}
        </th>
        {% endfor %}
        <th style="padding-left: 0;text-align: center">Promedio</th>
	</tr>
	
	</thead>
	<tbody>
	{% for matricula in matriculas %}
	{% set alumno = matricula.alumno %}
	<tr class="line-alumno">
		<td style="min-width: 20px">{{ loop.index }}</td>
		<td style="text-align: left; padding-left: 10px; min-width: 300px">
			{{ matricula.alumno.getFullName() }}
		</td>
        {% for i in 1..2 %} 
        {% set nota = matricula.getNotaExamenMensual(asignatura.id, i, get.ciclo) %}
            <td class="form-group center">
                <center><input data-x="{{ _key }}" style="width: 50px" data-matricula="{{ matricula.id }}" type="text" class="x_c center input-small criterio" name="notas_examen[{{ matricula.id }}][{{ i }}]" value="{{ nota > 0 ? nota|number_format(0) : '' }}" /></center>
            </td>
		{% endfor %}
		
		<td class="center" style="width: 80px">
			<center><input readonly type="text" class="center" style="width: 50px" id="promedio_{{ matricula.id }}" name="promedio[{{ matricula.id }}]" /></center>
		</td>

	</tr>
	{% endfor %}
	</tbody>
</table>
    <input type="hidden" name="ciclo" value="{{ get.ciclo }}" />
	<input type="hidden" name="asignatura_id" value="{{ asignatura.id }}" />

</div>
</form>
