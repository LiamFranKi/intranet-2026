<!DOCTYPE html>
<html>
	<head>

		<meta charset="utf-8"/>
		<style>
		*{
			font-size: 8px;
			font-family: Verdana;
		}

		#serie{
			font-size: 11px;
		}
		</style>
	</head>
	<body>
		<div style="position: absolute; top: 43px; left: 340px" id="nro">{{ boleta.comision_numero }}</div>
		<div style="position: absolute; top: 64px; left: 120px">{{ boleta.nombre }}</div>
		<div style="position: absolute; top: 80px; left: 150px">{{ boleta.fecha|date('d-m-Y') }}</div>
		<div style="position: absolute; top: 80px; left: 330px; font-size: 10px">{{ boleta.dni }}</div>
		{% set y = 105 %}
		
		<div style="position: absolute; top: {{ y }}px; left: 100px">1</div>
		<div style="position: absolute; top: {{ y }}px; left: 130px">COMISION PAGO CON TARJETA</div>
		<div style="position: absolute; top: {{ y }}px; left: 310px">{{ boleta.getComisionPagoTarjeta()|number_format(2) }}</div>
		<div style="position: absolute; top: {{ y }}px; left: 370px">{{ boleta.getComisionPagoTarjeta()|number_format(2) }}</div>
	
		
		<div style="position: absolute; top: 277px; left: 340px">{{ boleta.getComisionPagoTarjeta()|number_format(2) }}</div>

		<div style="position: absolute; top: 285px; left: 120px">{{ numeroLetras(boleta.getComisionPagoTarjeta()) }}</div>
		<!--<div style="position: absolute; top: 295px; left: 220px">{{ pago.fecha_hora|date('d-m-Y') }}</div>-->
        <div style="position: absolute; top: 295px; left: 220px">{{ boleta.fecha|date('d-m-Y') }}</div>

	</body>
</html2