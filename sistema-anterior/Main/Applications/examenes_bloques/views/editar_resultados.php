<script>
$(function(){
    $('#formResultados').niftyOverlay();
	$('#formResultados').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/examenes_bloques/save_resultados', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
                        $.fancybox.reload();
					break;
					
					case 0:
						zk.pageAlert({message: 'No se pudieron guardar los datos', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
					break;
					
					case -5:
						zk.formErrors(_form, r.errors);
					break;
					
					default:
						
					break;
				}
				
			});
		}
	});
})
</script>

<form id="formResultados" autocomplete="off" data-target="#formResultados" data-toggle="overlay">
    <div class="modal-menu bg-primary">
        <button class="btn btn-default" onclick="">{{ icon('bullet_disk') }} Guardar Resultados</button>
    </div>
    <div class="">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Resultados</h3>
            </div>
            <div class="modal-body" style="overflow: auto">
                {% if matriculas|length > 0%}
                <table class="special">
                    <tr>
                        <th>Examen</th>
                        <td>{{ examen.titulo }} - Bimestre {{ compartido.ciclo }} - {{ COLEGIO.roman(compartido.nro) }}</td>
                    </tr>
                    
                    <tr>
                        <th>Puntaje por respuesta correcta</th>
                        <td>{{ examen.puntos_correcta }} Punto(s)</td>
                    </tr>
                </table>
                
                <table class="special">
                    <tr>
                        <th style="width: 30px">Nº</th>
                        <th>Apellidos y Nombres</th>

                        {% for curso in cursos %}
                            {% if not get.curso_id or (get.curso_id == curso.id) %}
                                <th class="nombreCurso" style="font-size: 10px">{{ curso.nombre }}</th>
                            {% endif %}
                        {% endfor %}
                        
                    </tr>
                    {% for matricula in matriculas %}

                    {% set prueba = matricula.getBestTestBloque(compartido) %}

                    {% set resultados = prueba.getResultados() %}
                    <tr>
                        <td class="text-center">{{ _key + 1 }}</td>
                        <td>{{ matricula.alumno.getFullName() }} </td>
                        {% for curso in cursos %}
                        {% if not get.curso_id or (get.curso_id == curso.id) %}
                        <td class="text-center">
                            <input type="text" name="resultados[{{ matricula.id }}][{{ curso.id }}][puntaje]" style="width: 70px" class="text-center" value="{{ not is_null(resultados[curso.id].puntaje) ? resultados[curso.id].puntaje : '' }}" />
                            <input type="hidden" name="resultados[{{ matricula.id }}][{{ curso.id }}][correctas]" value="{{ resultados[curso.id].correctas }}">
                            <input type="hidden" name="resultados[{{ matricula.id }}][{{ curso.id }}][incorrectas]" value="{{ resultados[curso.id].incorrectas }}">
                        </td>
                        {% endif %}
                        {% endfor %}
                    </tr>

                    {% endfor %}
                </table>
                
                {% else %}
                <p class="center"><b>NO SE ENCONTRARON RESULTADOS</b></p>
                {% endif %}
            </div>	
        </div>
    </div>

    <input type="hidden" name="compartido_id" value="{{ compartido.id }}" />
</form>