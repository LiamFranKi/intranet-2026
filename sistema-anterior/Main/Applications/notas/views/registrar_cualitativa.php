{% if asignatura.grupo.registro_habilitado == 'SI' or USUARIO.is(['ADMINISTRADOR', 'SECRETARIA']) %}
<script>
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
var ALLOWED = ['AD','A', 'B', 'C'];
var VALUES = {'AD': 4, 'A': 3, 'B': 2, 'C': 1};
var MAP = {1: 'C', 2: 'B', 3: 'A', 4: 'AD'};

$(function(){
    $('.criterio').bind('blur', function(e){
        current_value = this.value.toUpperCase().trim();
        // revisa si el valor está permitido
        if(current_value != '' && $.inArray(current_value, ALLOWED) == -1){
            _field = this;
            /*return $.prompt('Sólo puede ingresar las letras: ' + ALLOWED.join(', '), {submit: function(){
				
			}});*/
			_field.value = '';
				_field.focus();
            return alert('Sólo puede ingresar las letras: ' + ALLOWED.join(', '));

        }
        
        matricula_id = $(this).data('matricula');
        criterios = document.querySelectorAll('input[data-matricula="' + matricula_id + '"]');
        total = 0;
        total_criterios = 0;
        for(i in criterios){
            if($.isNumeric(i)){
                nota = $(criterios[i]).val().toUpperCase().trim();
                if(nota != ''){
                    total_criterios++;
                    total += VALUES[nota];
                }
            }
        }
        
        if(total_criterios > 0){
            promedio = (total/total_criterios).round(0);
            $('#promedio_' + matricula_id).val(MAP[promedio]);
        }
    });
    
    $('#fnotas').niftyOverlay();
    $('#fnotas').bootstrapValidator({
		//container: 'tooltip',
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$(_form).sendForm('/notas/save_cualitativa', function(r){
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

	$('.tip').tooltip();
	{% if readonly %}
	$('input[type="text"]').prop('disabled', true);
	{% endif %}

    //$('.line-alumno td>.criterio').trigger('blur')
});
/* $.each($('.line-alumno'), function(i, obj){
    $(obj).find('input').first().trigger('blur');
}); */
</script>

<form id="fnotas" data-toggle="overlay" data-target="#fnotas">
<div style="min-width: 600px">

 	<div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">{{ curso.nombre }} - {{ COLEGIO.getCicloNotasSingle(get.ciclo) }} - {{ asignatura.grupo.anio }}</h3>
        </div>
        <div class="modal-body" style="overflow: auto">
        	<div class="mar-btm">
        		{% if not readonly %}
			    <button class="btn btn-default" type="button" onclick="$('#fnotas').trigger('submit')">{{ icon('table_save') }} Guardar Datos</button>
			    {% endif %}
			    <button class="btn btn-default" type="button" onclick="get_back()">{{ icon('calendar') }} Seleccionar {{ COLEGIO.getCicloNotas()|capitalize }}</button>
        	</div>

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
			    Ingrese las notas entre AD-A-B-C.
			</div>

			<table id="lista_alumnos" class="special" style="width: 100%">
				<thead>
				<tr>
					<th style="width: 20px">Nº</th>
					<th style="width: 300px;">Apellidos y Nombres</th>
			        {% if criterios|length > 0 %}
			            {% for criterio in criterios %} 
			            <!--<th style="padding: 3px 5px;text-align: center;">{{ criterio.descripcion }}</th>-->
			            <th style="padding: 3px 5px;text-align: center;">C{{ loop.index }}</th>
			            {% endfor %}
			            <th style="padding-left: 0;text-align: center">Prom</th>
			        {% endif %}
				</tr>
				
				</thead>
				<tbody>
				{% for matricula in matriculas %}
				{% set alumno = matricula.alumno %}
				<tr class="line-alumno">
					<td style="min-width: 20px">{{ loop.index }}</td>
					<td style="text-align: left; padding-left: 10px; min-width: 300px">{{ alumno.apellido_paterno|upper }} {{ alumno.apellido_materno|upper }}, {{ alumno.nombres|upper }} </a> </td>
					{% if criterios|length > 0 %}
			        
			        {% for criterio in criterios %} 
			        {% set nota = matricula.getNota(asignatura.id, criterio.id, get.ciclo) %}
			            <td class="center form-group">
			                <center><input title="{{ criterio.descripcion }}" style="text-transform: uppercase; width: 35px" data-x="{{ _key }}" data-matricula="{{ matricula.id }}" type="text" class="x_c text-center input-small criterio tip" name="notas[{{ criterio.id }}][{{ matricula.id }}]" value="{{ nota }}" maxlength="2" /></center>
			            </td>
					{% endfor %}
					{% set promedio = matricula.getPromedio(asignatura.id, get.ciclo) %}
					<td class="center form-group" style="width: 80px">
						<center><input type="text" readonly class="text-center" style="width: 40px; text-transform: uppercase" id="promedio_{{ matricula.id }}" name="promedio[{{ matricula.id }}]" value="{{ promedio }}" /></center>
					</td>
			        {% endif %}
				</tr>
				{% endfor %}
				</tbody>
			</table>
        </div>
    </div>

	
    <input type="hidden" name="ciclo" value="{{ get.ciclo }}" />
	<input type="hidden" name="asignatura_id" value="{{ asignatura.id }}" />
	
</div>

</form>
{% else %}
<script>
$(function(){
    $.prompt('El registro de notas no está habilitado', {
        submit: function(){
            $.fancybox.close()
        }
    })
})
</script>
{% endif %}

