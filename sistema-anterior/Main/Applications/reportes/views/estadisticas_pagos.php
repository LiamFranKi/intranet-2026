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
			  element: 'bar-example',
			  resize: true,
			  grid: true,
			  data: {{ data|json_encode }}
			  /*[
			    { mes: '2006', a: 100, b: 90 },
			    { mes: '2007', a: 75,  b: 65 },
			    { mes: '2008', a: 50,  b: 40 },
			    { mes: '2009', a: 75,  b: 65 },
			    { mes: '2010', a: 50,  b: 40 },
			    { mes: '2011', a: 75,  b: 65 },
			    { mes: '2012', a: 100, b: 90 }
			  ]*/,
			  xkey: 'mes',
			  ykeys: ['a', 'b', 'c'],
			  labels: ['Total Neto', 'Total Pagado', 'Total Saldo']
			});
		});
		
		</script>
	</head>
	<body>
		<div class="block">
			<h2>COMPORTAMIENTO DE COBRANZA DE MARZO A DICIEMBRE (al {{ get.fecha }})</h2>
			<!--
			<pre>
				{{ print_r(data) }}
			</pre>
			-->
			<div id="bar-example" name="bar-example"></div>
			<table class="special">
				<tr>
					<th>AL {{ get.fecha }}</th>
					<th>Total Neto</th>
					<th>Total Pagado</th>
					<th>Total Saldo</th>
				</tr>
				{% for bar in data %}
				<tr>
					<td class="center">{{ bar.mes }}</td>
					<td class="center">{{ bar.a|number_format(2) }}</td>
					<td class="center">{{ bar.b|number_format(2) }}</td>
					<td class="center">{{ bar.c|number_format(2) }}</td>
				</tr>
				{% endfor %}
			</table>

		</div>

	</body>
</html>

