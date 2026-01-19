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
				return $.fancybox.back();
			}
			$.prompt.close();
		}
	})
}


</script>
<script>
var CRITERIOS = parseInt('{{ criterios|length }}');

$(function(){
    $('.indicador').bind('blur', function(e){
        current_value = parseFloat(this.value);
        // revisa si el valor está permitido
        if(this.value != '' && (!$.isNumeric(this.value) || current_value < NOTA_MINIMA || current_value > NOTA_MAXIMA)){
            _field = this;
            return $.prompt('Ingrese valores de: ' + NOTA_MINIMA + ' a ' + NOTA_MAXIMA, {submit: function(){
				_field.value = '';
				_field.focus();
			}});
        }
        
        subcriterio_id = $(this).data('subcriterio');
        subcriterios = document.querySelectorAll('.nota_' + subcriterio_id);
        total = 0;
        total_criterios = 0;
        for(i in subcriterios){
            if($.isNumeric(i)){
                nota = $(subcriterios[i]).val();
                if(nota != ''){
                    // cambia el color de texto
                    if(nota >= NOTA_APROBATORIA) $(subcriterios[i]).css('color', 'blue');
                    if(nota < NOTA_APROBATORIA) $(subcriterios[i]).css('color', 'red');
                    total_criterios++;
                    total += parseFloat(nota);
                }
            }
        }
        
        if(total_criterios > 0){
            promedio = (total/total_criterios).round(0);
            $('#promedio_' + subcriterio_id).val(promedio);
            if(promedio >= NOTA_APROBATORIA) $('#promedio_' + subcriterio_id).css('color', 'blue');
            if(promedio < NOTA_APROBATORIA) $('#promedio_' + subcriterio_id).css('color', 'red');
		}
    });
    
    $('#fnotas').bootstrapValidator({
		onSuccess: function(e, v){
			e.preventDefault();
			_form = e.target;
			$(_form).sendForm('/notas/save_detalles', function(r){
				switch(parseInt(r[0])){
					case 1:
						$.prompt('Notas guardadas correctamente', {submit: function(){
							$.fancybox.back();
						}});
					break;
					case 0:
						$.prompt('No se pudieron guardar las notas');
					break;
				}
			});
		}
	});
});

$.each($('.line-alumno'), function(i, obj){
    $(obj).find('input').first().trigger('blur');
});
</script>

<form id="fnotas">
    <input type="hidden" name="matricula_id" value="{{ get.matricula_id }}" />
    <input type="hidden" name="ciclo" value="{{ get.ciclo }}" />
    <input type="hidden" name="asignatura_id" value="{{ get.asignatura_id }}" />
    
    <div class="menu">
        <button class="btn btn-default" type="button" onclick="$('#fnotas').trigger('submit')">{{ icon('table_save') }} Guardar Datos</button>
        <button class="btn btn-default" type="button" onclick="get_back()">{{ icon('application_view_list') }} Volver a la Lista de Alumnos</button>
    </div>
    
    <div class="block" style="width: 800px">
        <table class="special">
            <tr>
                <th>Área</th>
                <td>{{ asignatura.curso.nombre }}</td>
            </tr>
            <tr>
                <th>Estudiante</th>
                <td>{{ matricula.alumno.getFullName() }}</td>
            </tr>
            <tr>
                <th>Grupo</th>
                <td>{{ matricula.grupo.getNombre() }} - {{ COLEGIO.getCicloNotasSingle(get.ciclo)|upper }}</td>
            </tr>
        </table>
    </div>
    {% if criterios|length > 0 %}
    <div class="block">
        {% for criterio in criterios %}
        {% set indicadores = criterio.getIndicadores() %}
        <table class="special">
            <thead>
                <tr>
                    <th colspan="{{ indicadores|length }}">{{ criterio.descripcion }}</th>
                </tr>
                <tr>
                {% for indicador in indicadores %}
                    <th>{{ indicador.descripcion }}</th>
                {% endfor %}
                </tr>
            </thead>
            <tbody>
                <tr>
                {% for indicador in indicadores %} 
                    <td class="center line-alumno">
                    {% for i in 0..(indicador.cuadros - 1) %}
                    {% set nota = matricula.getNotaDetalle(asignatura.id, get.ciclo, criterio.id, indicador.id, i) %}
                    <input type="text" data-subcriterio="{{ indicador.id }}" class="indicador nota_{{ indicador.id }} center" name="nota[{{ matricula.id }}][{{ criterio.id }}][{{ indicador.id }}][{{ _key }}]" value="{{ nota }}" style="width: 50px; display: inline-block" />
                    {% endfor %}
                    <input type="text" id="promedio_{{ indicador.id }}" name="promedio[{{ matricula.id }}][{{ criterio.id }}][{{ indicador.id }}]" class="center" style="width: 50px; display: inline-block" readonly />
                    </td>
                {% endfor %}
                </tr>
            </tbody>
        </table>
        {% endfor %}
    </div>
    {% endif %}
</form>
