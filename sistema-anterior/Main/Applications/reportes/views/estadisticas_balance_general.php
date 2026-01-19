<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		{{ js('jquery-1.11.0') }}
		{{ vendor('Bootstrap') }}
		{{ css('custom') }}
		{{ vendor('Morris') }}
		

	</head>
	<body>
		
			{% for item in items %}
			<div class="block">
			<h2>BALANCE NIVEL ACADÉMICO - {{ item }} - POR AULA</h2>
			
			<div id="balance_nivel_aula_{{ _key }}"></div><br />
			<script>
			$(function(){
				Morris.Bar({
				  element: 'balance_nivel_aula_{{ _key }}',
				  resize: true,
				  grid: true,
				  data: [
				  	{% for grupo in grupos %}
				  	{x_key: '{{ grupo.getNombre() }}', value: {{ data_aula[grupo.id][item] }}},
				  	{% endfor %}
				  ],
				  xkey: 'x_key',
				  ykeys: ['value'],
				  labels: ['Total %'],
				  xLabelAngle: 90
				});
			});
			</script>

			<h2>BALANCE NIVEL ACADÉMICO - {{ item }} - GENERAL</h2>
			
			<div id="balance_nivel_general_{{ _key }}"></div><br />
			<script>
			$(function(){
				Morris.Bar({
				  element: 'balance_nivel_general_{{ _key }}',
				  resize: true,
				  grid: true,
				  data: [
				  	{% for grupo in grupos %}
				  	{x_key: '{{ grupo.getNombre() }}', value: {{ data_general[grupo.id][item] }}},
				  	{% endfor %}
				  ],
				  xkey: 'x_key',
				  ykeys: ['value'],
				  labels: ['Total %'],
				  xLabelAngle: 90
				});
			});
			</script>
			</div>
			{% endfor %}

		

	</body>
</html>

