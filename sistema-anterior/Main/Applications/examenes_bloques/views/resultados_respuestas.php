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
			<h3 class="panel-title">Detalle de Resultados</h3>
		</div>
		<div class="panel-body">
			<table class="special">
				<tr>
					<th>ALUMNO</th>
					<td>{{ prueba.matricula.alumno.getFullName() }}</td>
				</tr>
			</table>

			<table class="special">
				<tr>
					<th>Respuesta Marcada</th>
					<th>Respuesta Correcta</th>
					<th>Marcada Correctamente</th>
				</tr>
				<tr>
					<td class="text-center"><button class="btn btn-danger">X</button></td>
					<td class="text-center"><button class="btn btn-primary">X</button></td>
					<td class="text-center"><button class="btn btn-success">X</button></td>
				</tr>
			</table>

			{% if examen.getCursosID()|length > 0 %}
				{% set cursos = examen.getCursos() %}

				<div>

				  <!-- Nav tabs -->
				  <ul class="nav nav-tabs" role="tablist">
				  	{% for curso in cursos %}
					{% if not get.curso_id or (get.curso_id == curso.id) %}
				    <li role="presentation" class="{{ loop.index == 1 or (get.curso_id == curso.id)? 'active' : '' }}"><a href="#curso_{{ loop.index }}" aria-controls="home" role="tab" data-toggle="tab"><b>{{ curso.nombre|upper }}</b></a></li>
				    {% endif %}
				    {% endfor %}

				  </ul>

				  <!-- Tab panes -->
				  <div class="tab-content">
				  	{% for curso in cursos %}
				  	{% if not get.curso_id or (get.curso_id == curso.id) %}
				    <div role="tabpanel" class="tab-pane fade {{ loop.index == 1 or (get.curso_id == curso.id)? 'in active' : '' }}" id="curso_{{ loop.index }}" style="padding-top: 20px">
				    	{% set resultados = prueba.getResultados() %}
						<table class="special">
							<tr>
								<th>PUNTAJE</th>
								<th>Correctas</th>
								<th>Incorrectas</th>
							</tr>
							<tr>
								<td class="text-center">{{ resultados[curso.id].puntaje }}</td>
								<td class="text-center">{{ resultados[curso.id].correctas }}</td>
								<td class="text-center">{{ resultados[curso.id].incorrectas }}</td>
							</tr>
						</table>

				    	{% set preguntas = prueba.getPreguntas(curso.id) %}
						{% if preguntas|length > 0 %}
						<table class="special">
							{% for pregunta in preguntas %}
							<tr>
								<th style="width: 40px">{{ loop.index }}</th>
								<td style="padding: 10px 10px 5px 10px; width: 568px">{{ pregunta.descripcion }}</td>
								<td style="vertical-align: top; width: 250px">
									<table class="special">
										<!--
										<tr>
											<th colspan="2">ALTERNATIVAS</th>
										</tr>
										-->
										{% set alternativas = pregunta.getAlternativas() %}
										{% for alternativa in alternativas %}
										<tr>
											<td class="text-center {{ respuestas[pregunta.id] == alternativa.id and alternativa.correcta() ? 'btn-success' : (respuestas[pregunta.id] == alternativa.id ? 'btn-danger' : (alternativa.correcta() ? 'btn-primary' : '')) }}" style="width: 50px"><input disabled type="radio" class="respuesta" name="respuestas[{{ pregunta.id }}]" value="{{ alternativa.id }}" {{ respuestas[pregunta.id] == alternativa.id ? 'checked' : '' }}></td>
											<td>{{ alternativa.descripcion }}</td>
										</tr>
										{% endfor %}
									</table>
								</td>
							</tr>
							{% endfor %}
						</table>
						{% else %}
						<p class="text-center"><b>NO SE REGISTRARON PREGUNTAS PARA ESTE CURSO</b></p>
				    	{% endif %}
				    </div>
				    {% endif %}
				    {% endfor %}
				    
				  </div>

				</div>
			{% else %}
			<p class="text-center"><b>NO SE ENCONTRARON CURSOS</b></p>
			{% endif %}
		</div>
	</div>
</div>