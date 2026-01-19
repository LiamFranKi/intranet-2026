{% if asignatura.grupo.registro_habilitado == 'SI' or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
<script>
var NOTA_APROBATORIA = parseFloat('{{ asignatura.grupo.nivel.nota_aprobatoria }}');
var NOTA_MINIMA = parseFloat('{{ asignatura.grupo.nivel.nota_minima }}');
var NOTA_MAXIMA = parseFloat('{{ asignatura.grupo.nivel.nota_maxima }}');

Number.prototype.round = function(places) {
    return +(Math.round(this + "e+" + places)  + "e-" + places);
}

function get_back(){
	if(confirm('¿Está seguro de volver atrás?')){
        seleccionarCiclo(function(e, v, ciclo){
           fancybox('/notas/registrar?readonly={{ readonly ? "true" : "" }}&asignatura_id={{ get.asignatura_id }}&ciclo=' + ciclo);
        });
    }
}

</script>
<script>
var CRITERIOS = parseInt('{{ criterios|length }}');

function updatePromedioFinal(matricula_id){
    criterios = document.querySelectorAll('input[data-matricula="' + matricula_id + '"]');
    var total = 0;
    total_criterios = 0;
    for(i in criterios){
        if($.isNumeric(i)){
            nota = $(criterios[i]).val();
            if(nota != ''){
                // cambia el color de texto
                if(nota >= NOTA_APROBATORIA) $(criterios[i]).css('color', 'blue');
                if(nota < NOTA_APROBATORIA) $(criterios[i]).css('color', 'red');
                total_criterios++;
                {% if asignatura.grupo.nivel.calificacionPorcentual() %}
                    // PESO
                    peso = parseFloat($(criterios[i]).data('peso'));
                    total += parseFloat(nota) * peso / 100;

                    console.log(peso, total);
                {% else %}
                    // PROMEDIO
                    total += parseFloat(nota);
                {% endif %}
            }
        }
    }

    $('#promedio_' + matricula_id).val('');
    $('#promedio_' + matricula_id).css('color', 'black');
    if(total_criterios > 0){
        {% if asignatura.grupo.nivel.calificacionPorcentual() %}
            // PESO
            if(total > 0 && total != NaN){
                $('#promedio_' + matricula_id).val(total.round(0));
                if(total.round(0) >= NOTA_APROBATORIA) $('#promedio_' + matricula_id).css('color', 'blue');
                if(total.round(0) < NOTA_APROBATORIA) $('#promedio_' + matricula_id).css('color', 'red');
            }

        {% else %}
            // PROMEDIO
            $('#promedio_' + matricula_id).val((total/total_criterios).round(0));
        {% endif %}

    }
}

function updatePromedioCriterio(sender){
    var criterioField = $(sender).parent().find('.criterio').first();
    var indicadores = $(sender).parent().find('.indicador');
    var total_indicadores_suma = 0;
    var total_indicadores = 0;

    for(i in indicadores){
        if($.isNumeric(i)){
            nota = $(indicadores[i]).val();
            if(nota != ''){
                // cambia el color de texto
                //if(nota >= NOTA_APROBATORIA) $(indicadores[i]).css('color', 'blue');
                //if(nota < NOTA_APROBATORIA) $(indicadores[i]).css('color', 'red');
                total_indicadores++;
                total_indicadores_suma += parseFloat(nota);
            }
        }
    }
    criterioField.val('');
    criterioField.css('color', 'black');
    if(total_indicadores_suma > 0 && total_indicadores_suma != NaN){
        criterioField.val((total_indicadores_suma/total_indicadores).round(0));
    }

    matricula_id = criterioField.data('matricula');
    updatePromedioFinal(matricula_id);
    //criterioField.val(0);
}

$(function(){
    $('.criterio, .indicador').bind('blur', function(e){
        current_value = parseFloat(this.value);
        // revisa si el valor está permitido
        if(this.value != ''){
			if(current_value < NOTA_MINIMA || current_value > NOTA_MAXIMA || !$.isNumeric(current_value)){
				_field = this;
                _field.value = '';
                _field.focus();

				/*return $.prompt('Ingrese valores de: ' + NOTA_MINIMA + ' a ' + NOTA_MAXIMA, {submit: function(){
					
				}});*/
                return alert('Ingrese valores de: ' + NOTA_MINIMA + ' a ' + NOTA_MAXIMA);
			}
		}

        matricula_id = $(this).data('matricula');
        updatePromedioFinal(matricula_id);

    });
    $('#fnotas').niftyOverlay();
    $('#fnotas').bootstrapValidator({
		//container: 'tooltip',
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$(_form).sendForm('/notas/save_cuantitativa', function(r){
				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Notas guardadas correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
					break;
					case 0:
						zk.pageAlert({message: 'No se pudieron guardar las notas', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
					break;
				}
			});
		}
	});

	{% if readonly %}
	$('input[type="text"]').prop('disabled', true);
	{% endif %}

   /*  $.each($('.line-alumno'), function(i, obj){
        $(obj).find('.indicador').first().trigger('blur');
        $(obj).find('.criterio').first().trigger('blur');
    }); */
    $('.line-alumno td>.indicador:first-child').trigger('blur')
});


</script>

<form id="fnotas" data-toggle="overlay" data-target="#fnotas">
    <div id="">

        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{{ curso.nombre }} - {{ COLEGIO.getCicloNotasSingle(get.ciclo) }} - {{ asignatura.grupo.anio }}</h3>
            </div>
            <div class="modal-body" style="overflow: auto">
                <div class="mar-btm">
                    <button class="btn btn-default" type="button" onclick="get_back()">{{ icon('calendar') }} Seleccionar {{ COLEGIO.getCicloNotas()|capitalize }}</button>
                    {% if not readonly %}
                    <button class="btn btn-default" type="button" onclick=" $('#fnotas').trigger('submit')">{{ icon('table_save') }} Guardar Datos</button>
                    {% endif %}
                    <button class="btn btn-default" type="button" onclick="zk.printDocument('/notas/imprimir_cuantitativa/{{ asignatura.id }}?ciclo={{ get.ciclo }}')">{{ icon('printer') }} Imprimir</button>    
                </div>
                
                <div>
                    <style>
                    #lista_alumnos th{
                        padding-left: 0;
                        text-align: center;
                    }

                    #lista_alumnos td{
                        text-align: center;
                    }
                    </style>


                    <div class="alert alert-info text-center">
                        Ingrese notas de: {{ asignatura.grupo.nivel.nota_minima }} - {{ asignatura.grupo.nivel.nota_maxima }}. {% if asignatura.grupo.nivel.calificacionPromedio() %}Si deja en blanco un criterio este no se contabiliza en el promedio. {% endif %}
                    </div>
                    <table id="lista_alumnos" class="special">
                        <thead>
                        <tr>
                            <th style="width: 20px">Nº</th>
                            <th style="width: 300px;">Apellidos y Nombres</th>
                            {% set width = 320 %}
                            {% if criterios|length > 0 %}
                                {% for criterio in criterios %}
                                {% set indicadores = criterio.getIndicadores() %}
                                {% set width = width + (indicadores|length > 0 ? 200 : 150) %}
                                <th style="padding: 3px 5px;text-align: center;">{{ criterio.descripcion }}
                                    {% if asignatura.grupo.nivel.calificacionPorcentual() %}<br />( {{ criterio.peso }}% ){% endif %}
                                </th>
                                {% endfor %}

                                {% if asignatura.grupo.nivel.calificacionPorcentual() and asignatura.curso.examenMensual() %}
                                {% set width = width + 200 %}
                                <th style="padding: 3px 5px;text-align: center;">Examen Mensual <br />( {{ asignatura.curso.peso_examen_mensual }}% )</th>
                                {% endif %}
                                {% set width = width + 100 %}
                                <th style="padding-left: 0;text-align: center">Prom.</th>
                            {% endif %}
                        </tr>

                        </thead>
                        <tbody>

                        {% for matricula in matriculas %}
                        {% set alumno = matricula.alumno %}
                        <tr class="line-alumno">
                            <td style="width: 20px">{{ loop.index }}</td>
                            <td style="text-align: left; padding-left: 10px; width: 300px">
                            <!-- onclick="fancybox('/notas/registrar_detalles?matricula_id={{ matricula.id }}&asignatura_id={{ asignatura.id }}&ciclo={{ get.ciclo }}')" -->
                            <a href="javascript:;" >{{ matricula.alumno.getFullName() }}</a> </td>
                            {% if criterios|length > 0 %}

                                {% for criterio in criterios %}
                                    {% set indicadores = criterio.getIndicadores() %}
                                    <td class="form-group text-center" style="width: {{ count(indicadores) > 0 ? 200 : '150' }}px">
                                        {% for indicador in indicadores %}
                                            {% for i in 0..(indicador.cuadros - 1) %}
                                                {% set nota = matricula.getNotaDetalle(asignatura.id, get.ciclo, criterio.id, indicador.id, i) %}
                                                <input type="text" onblur="updatePromedioCriterio(this)" data-subcriterio="{{ indicador.id }}" class="indicador nota_{{ indicador.id }} text-center" name="nota[{{ matricula.id }}][{{ criterio.id }}][{{ indicador.id }}][{{ _key }}]" value="{{ nota }}" style="width: 30px; display: inline-block" />
                                            {% endfor %}
                                        {% endfor %}
                                        <!-- CRITERIO -->
                                        {% set nota = matricula.getNota(asignatura.id, criterio.id, get.ciclo) %}
                                        <input  data-x="{{ _key }}" style="width: 30px; background: #FDE9D9" data-peso="{{ criterio.peso }}"  data-matricula="{{ matricula.id }}" type="text" class="x_c text-center input-small criterio" name="notas[{{ criterio.id }}][{{ matricula.id }}]" value="{{ nota > 0 ? nota|number_format(0) : '' }}" {{ indicadores|length > 0 ? 'readonly':'' }} />
                                    </td>
                                {% endfor %}

                                {% if asignatura.grupo.nivel.calificacionPorcentual() and asignatura.curso.examenMensual() %}
                                <td class="form-group text-center" style="width: 200px">
                                    {% for i in 1..2 %}
                                    {% set nota = matricula.getNotaExamenMensual(asignatura.id, i, get.ciclo) %}
                                        <input data-x="{{ _key }}" style="width: 30px" type="text" onblur="updatePromedioCriterio(this)" class="x_c text-center input-small indicador" name="notas_examen[{{ matricula.id }}][{{ i }}]" value="{{ nota > 0 ? nota|number_format(0) : '' }}" />
                                    {% endfor %}

                                    {% set notaExamenMensual = matricula.getPromedioExamenMensual(asignatura, get.ciclo, false) %}
                                    <!-- PROMEDIO EXAMEN MENSUAL -->
                                    <input style="width: 30px; background: #FDE9D9" data-peso="{{ asignatura.curso.peso_examen_mensual }}"  data-matricula="{{ matricula.id }}" type="text" class="x_c text-center input-small criterio" value="{{ notaExamenMensual }}" readonly />

                                </td>

                                {% endif %}
                                <td class="center" style="width: 70px">
                                    <center><input readonly type="text" class="text-center" style="width: 30px" id="promedio_{{ matricula.id }}" name="promedio[{{ matricula.id }}]" /></center>
                                </td>

                            {% endif %}
                        </tr>
                        {% endfor %}
                        </tbody>
                    </table>

                    <script>
                    $(function(){
                        $('#lista_alumnos').css('width', '{{ width }}px');
                    })
                    </script>
                    <input type="hidden" name="ciclo" value="{{ get.ciclo }}" />
                    <input type="hidden" name="asignatura_id" value="{{ asignatura.id }}" />

                </div>
            </div>
        </div>
    </div>


</form>
{% else %}
<div id="page-content">
    <div class="panel">
        <div class="panel-body">
            <div class="alert alert-danger">EL REGISTRO NO ESTÁ HABILITADO</div>
        </div>
    </div>
</div>
{% endif %}
