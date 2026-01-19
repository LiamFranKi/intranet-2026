<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		{{ js('jquery-1.11.0') }}
		<link href="/Static/css/bootstrap.min.css" rel="stylesheet">
		<link href="/Static/css/nifty.min.css" rel="stylesheet">
		<link href="/Static/css/custom.css?v=<%=getToken%>" rel="stylesheet" />
		{{ vendor('Morris') }}
		
		<style>
		h2{
			font-size:  13px;
			color: black;
		}
		</style>
	</head>
	<body>
		{% for grupo in grupos %}
		{% if grupo.nivel_id == bloque.nivel_id %}
		<div class="panel panel-primary panel-bordered">
			<div class="panel-heading">
				<h3 class="panel-title">Cuadro de Datos</h3>
			</div>
			<div class="panel-body">
				
				<table class="special">
					<tr>
						<th colspan="3">BLOQUE {{ bloque.nombre|upper }}</th>
						<td class="center">{{ date()|date('d-m-Y') }}</td>
					</tr>
					<tr>
						<th>TUTOR</th>
						<td class="center">{{ grupo.tutor.getFullName() }}</td>
						<th>AULA</th>
						<td class="center">{{ grupo.getNombreShort() }}</td>
					</tr>
				</table>
				{% set matriculas = grupo.getMatriculas() %}
				<table class="special">
					<tr>
						<th rowspan="2"></th>
						<!--
						{% for nro in 1..2 %}
						<th colspan="{{ bloque.cursos|length + 1 }}">{{ COLEGIO.roman(nro) }}</th>
						{% endfor %}
						-->

						<tr>
							{% for nro in 1..2 %}
								{% for bc in bloque.cursos %}
									{% if grupo.hasCurso(bc.curso.id) %}
									<th>{{ bc.curso.abreviatura ? bc.curso.abreviatura : substr(bc.curso.nombre, 0, 3) }} - {{ COLEGIO.roman(nro) }}</th>
									{% endif %}
								{% endfor %}
								<th>PROM - {{ COLEGIO.roman(nro) }}</th>
							{% endfor %}
						</tr>
					</tr>
					
					{% for matricula in matriculas %}
					<tr>
						<td>{{ matricula.alumno.getFullName() }}</td>
						{% for nro in 1..2 %}
							{% set total = 0 %}
							{% set totalCursos = 0 %}
							{% for bc in bloque.cursos %}
							{% if grupo.hasCurso(bc.curso.id) %}
								{% set asignatura = grupo.getAsignaturaByCurso(bc.curso_id) %}
								{% set notaExamenMensual = matricula.getNotaExamenMensual(asignatura.id, nro, get.ciclo) %}
									<td class="center">{{ notaExamenMensual }}</td>
									{% set total = total + notaExamenMensual %}
									{% set puntajeCurso = bloque.puntaje(bc.curso_id, grupo.id, nro, notaExamenMensual) %}
								{% set totalCursos = totalCursos + 1 %}
							{% endif %}
							{% endfor %}
							{% set promedio = totalCursos > 0 ? (total / totalCursos) : 0 %}
							{% set promedioBloque = bloque.puntaje(-1, grupo.id, nro, promedio) %}
							<td class="center">{{ promedio }}</td>
						{% endfor %}
					</tr>
					{% endfor %}
					<tr>
						<th>PROMEDIO</th>
						{% for nro in 1..2 %}
							{% for bc in bloque.cursos %}
							{% if grupo.hasCurso(bc.curso.id) %}
								{% set puntajeCurso = bloque.puntaje(bc.curso_id, grupo.id, nro) %}
								<th>{{ count(matriculas) > 0 ? (puntajeCurso.total / count(matriculas)) : 0 }}</th>
							{% endif %}
							{% endfor %}
							{% set promedioBloque = bloque.puntaje(-1, grupo.id, nro) %}
							<th>{{ count(matriculas) > 0 ? (promedioBloque.total / count(matriculas)) : 0 }}</th>
						{% endfor %}
					</tr>
				</table>

				{% for nro in 1..2 %}
				<br />
				<h2>COMPARATIVO ENTRE CURSOS DEL BLOQUE {{ bloque.nombre|upper }} {{ nro }} ({{ grupo.getNombreShort() }})</h2>
				<div id="comparativo_cursos_{{ grupo.id }}_{{ nro }}"></div><br />
				<script>
				$(function(){
					Morris.Bar({
					  element: 'comparativo_cursos_{{ grupo.id }}_{{ nro }}',
					  resize: true,
					  grid: true,
					  data: [
					 		{% for bc in bloque.cursos %}
					 		{% if grupo.hasCurso(bc.curso.id) %}
								{% set puntajeCurso = bloque.puntaje(bc.curso_id, grupo.id, nro) %}
									{x_key: '{{ bc.curso.nombre }}', value: {{ count(matriculas) > 0 ? (puntajeCurso.total / count(matriculas)) : 0 }}},
							{% endif %}
							{% endfor %}
					  ],
					  xkey: 'x_key',
					  ykeys: ['value'],
					  labels: ['Total'],
					  xLabelAngle: 90
					});
				});
				</script>
				{% endfor %}
				
				<br />
				<h2>COMPARATIVO ENTRE BLOQUES 1 y 2 ({{ grupo.getNombreShort() }})</h2>
				<div id="comparativo_cursos_{{ grupo.id }}"></div><br />
				<script>
				$(function(){
					Morris.Bar({
					  element: 'comparativo_cursos_{{ grupo.id }}',
					  resize: true,
					  grid: true,
					  data: [
					 		{% for bc in bloque.cursos %}
					 		{% if grupo.hasCurso(bc.curso.id) %}
								{% set puntajeCurso1 = bloque.puntaje(bc.curso_id, grupo.id, 1) %}
								{% set puntajeCurso2 = bloque.puntaje(bc.curso_id, grupo.id, 2) %}
								{
									x_key: '{{ bc.curso.nombre }}', 
									bloque1: {{ count(matriculas) > 0 ? (puntajeCurso1.total / count(matriculas)) : 0 }},
									bloque2: {{ count(matriculas) > 0 ? (puntajeCurso2.total / count(matriculas)) : 0 }}
								},
							{% endif %}
							{% endfor %}
					  ],
					  xkey: 'x_key',
					  ykeys: ['bloque1', 'bloque2'],
					  labels: ['Bloque Nº 1', 'Bloque Nº 2'],
					  xLabelAngle: 90
					});
				});
				</script>

			</div>
		</div>

		{% endif %}
		{% endfor %}
		
		<!-- GRAFICO 1 -->
		<div class="panel panel-primary panel-bordered">
			<div class="panel-body">
			{% for nro in 1..2 %}
				<h2>COMPARATIVO ENTRE AULAS DEL BLOQUE {{ bloque.nombre|upper }} {{ nro }}</h2>
				<div id="comparativo_aulas_{{ nro }}"></div><br />
				<script>
				$(function(){
					Morris.Bar({
					  element: 'comparativo_aulas_{{ nro }}',
					  resize: true,
					  grid: true,
					  data: [
					  	{% for grupo in grupos %}
					  	{% if grupo.nivel_id == bloque.nivel_id %}
					  		{% set matriculas = grupo.getMatriculas() %}
						  	{% set promedioBloque = bloque.puntaje(-1, grupo.id, nro) %}
						  	{x_key: '{{ grupo.getNombreShort() }}', value: {{ count(matriculas) > 0 ? (promedioBloque.total / count(matriculas)) : 0 }}},
					  	{% endif %}
					  	{% endfor %}
					  ],
					  xkey: 'x_key',
					  ykeys: ['value'],
					  labels: ['Total'],
					  xLabelAngle: 90
					});
				});
				</script>
			{% endfor %}
			</div>
		</div>

	</body>
</html>