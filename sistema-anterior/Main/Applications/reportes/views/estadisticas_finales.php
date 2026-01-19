<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		{{ js('jquery-1.11.0') }}
		{{ vendor('Bootstrap') }}
		{{ css('custom') }}
		{{ vendor('Morris') }}
		
		<script>
		$(function(){
			Morris.Bar({
			  element: 'rendimiento',
			  resize: true,
			  grid: true,
			  data: {{ rendimiento.data|json_encode }},
			  xkey: 'x_key',
			  ykeys: ['value'],
			  labels: ['Total %'],
			  formatter: function (y) { return y + "%" }
			  //xLabelAngle: 35
			});
		});
		</script>
	</head>
	<body>
		<div class="block">
		
			<h2>ESTADÍSTICAS FINALES - {{ grupo.getNombre()|upper }}</h2>
			
			<h3>Nivel Académico %</h3>
			<div id="rendimiento" name="rendimiento"></div>
			<h3>Promedios</h3>
			<div id="promedios" name="promedios"></div>

			{% for asignatura in asignaturas %}	
			<h3>{{ asignatura.curso.nombre }}</h3>
			<script>
			$(function(){
				Morris.Bar({
				  element: 'asignatura_{{ asignatura.id }}',
				  resize: true,
				  grid: true,
				  data: [
				  	{% for matricula in matriculas %}
				    { x_key: '{{ matricula.alumno.getFullName() }}', value: {{ promediosFinales[asignatura.id][matricula.id] }} },
				    {% endfor %}
				  ],
				  xkey: 'x_key',
				  ykeys: ['value'],
				  labels: ['Promedio'],
				  xLabelAngle: 90
				});
			});
			</script>
			<div id="asignatura_{{ asignatura.id }}" name="asignatura_{{ asignatura.id }}"></div>
			{% endfor %}
			
			<script>

			$(function(){
				Morris.Bar({
				  element: 'promedios',
				  resize: true,
				  grid: true,
				  data: [
					{% for asignatura in asignaturas %}
					{% set promedioAsignaturaGrupo = promediosAsignatura[asignatura.id] %}
					{x_key: '{{ asignatura.curso.nombre }}', value: {{ promedioAsignaturaGrupo }}},
					{% endfor %}
				  ],
				  xkey: 'x_key',
				  ykeys: ['value'],
				  labels: ['Promedio'],
				  xLabelAngle: 90
				});
			});
			</script>
		</div>

	</body>
</html>

