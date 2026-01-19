<style>
figure img{
	max-width: 100%;
}
.nav-tabs a{
	font-size:  12px;
}
.nav-tabs>.active>a {
    background-color: transparent;
    box-shadow: inset 0 -2px 0 0 #1e3a57 !important;
    color: inherit;
}

.image-style-align-center{
	text-align:  center;
}
</style>

<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Resultados</h3>
		</div>
		<div class="panel-body">
			<table class="special">
				<tr>
					<th>Alumno</th>
					<td>{{ prueba.matricula.alumno.getFullName() }}</td>
				</tr>
			

				{% if examen.puntajeGeneral() %}
				<tr>
					<th>Puntaje por respuesta correcta</th>
					<td>{{ examen.puntos_correcta }} Punto(s)</td>
				</tr>
				{% endif %}
				{% if examen.penalizarIncorrecta() %}
				<tr>
					<th>Penalización por Incorrecta</th>
					<td>{{ examen.penalizacion_incorrecta }} Punto(s)</td>
				</tr>
				{% endif %}

				<tr>
					<th>Puntaje</th>
					<td><b>{{ prueba.puntaje }}</b></td>
				</tr>
				<tr>
					<th>Correctas</th>
					<td>{{ prueba.correctas }}</td>
				</tr>
				<tr>
					<th>Incorrectas</th>
					<td>{{ prueba.incorrectas }}</td>
				</tr>
			</table>

			<table class="special">
				<tr>
					<td class="text-center" style="background-color: green; color: white"> Marcada correctamente</td>
					<td class="text-center" style="background-color: red; color: white"> Marcada incorrectamente</td>
					<td class="text-center" style="background-color: blue; color: white"> Respuesta correcta</td>
				</tr>
			</table>

			<div >
			{% if preguntas|length > 0 %}
			{% set respuestas = prueba.getRespuestas() %}
				<table style="width: 100%">
					<tbody id="listaPreguntas">
					{% for pregunta in preguntas %}
					<tr pregunta_id="{{ pregunta.id }}" id="pregunta_{{ pregunta.id }}">
						<td>
							<table class="special">
                                {% if pregunta.tipo == 'ALTERNATIVAS' %}
								<tr>
									<th style="width: 50px">{{ loop.index }}</th>
									<td class="preguntaDescripcion descripcionPregunta">
										{{ pregunta.descripcion }}

										{% if not examen.puntajeGeneral() %}
										<p class="pull-right"><small class="text-bold">{{ pregunta.puntos }} Punto(s)</small></p>
										{% endif %}
									</td>
								</tr>
								
								<tr>
									<td colspan="2">
										<table class="special" style="margin-bottom: 0px">
											{% for alternativa in pregunta.getAlternativas() %}
											<tr>
												<td class="text-center" style="width: 50px; {{ respuestas[pregunta.id] == alternativa.id and alternativa.correcta == 'SI' ? 'background-color: green' : (respuestas[pregunta.id] == alternativa.id ? 'background-color: red' : (alternativa.correcta == 'SI' ? 'background-color: blue' : '')) }}"><input type="radio" class="respuesta" name="respuestas[{{ pregunta.id }}]" value="{{ alternativa.id }}" disabled {{ respuestas[pregunta.id] == alternativa.id ? 'checked' : '' }}></td>
												<td>{{ alternativa.descripcion }}</td>
											</tr>
											{% else %}
											<tr>
												<td class="text-center"><b>NO HAY ALTERNATIVAS</b></td>
											</tr>
											{% endfor %}
										</table>
									</td>
								</tr>
								{% endif %}

                                {% if pregunta.tipo == "COMPLETAR" %}
                                <tr>
									<th style="width: 50px">{{ loop.index }}</th>
									<th class="preguntaDescripcion descripcionPregunta">
										Completa el texto

										{% if not examen.puntajeGeneral() %}
										<p class="pull-right"><small class="text-bold">{{ pregunta.puntos }} Punto(s)</small></p>
										{% endif %}
									</th>
								</tr>
                                <tr>
                                    <td class="text-center" style="{{ pregunta.checkRespuesta(respuestas[pregunta.id]) ? 'background-color: green' : 'background-color: red' }}">
                                        <input type="radio" class="respuesta"  disabled checked>
                                    </td>
                                    <td>
                                        {{ pregunta.getFormCompletar(respuestas[pregunta.id]) }} <br />
                                        <i>Respuesta:</i> {{ pregunta.getTextoRespuestaCompletar() }}
                                    </td>
                                </tr>
                                {% endif %}
		
							</table>
						</td>
					</tr>
					{% endfor %}
					</tbody>
				</table>
			{% else %}
			<p class="text-center"><b>NO SE ENCONTRARON PREGUNTAS</b></p>
			{% endif %}
			</div>

		</div>
	</div>
</div>