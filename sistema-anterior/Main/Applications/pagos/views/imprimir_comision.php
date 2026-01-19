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
		{% set advance = 0 %}
		{% for impresion in impresiones %}
			<div style="position: absolute; top: {{ 43 + advance }}px; left: 340px" id="nro">{{ impresion.getNumero() }}</div>
			<div style="position: absolute; top: {{ 64 + advance }}px; left: 120px">{{ impresion.pago.matricula.alumno.getFullName() }}</div>
			<div style="position: absolute; top: {{ 80 + advance }}px; left: 150px">{{ impresion.fecha_impresion|date('d-m-Y') }}</div>
			<div style="position: absolute; top: {{ 80 + advance }}px; left: 330px; font-size: 10px">{{ impresion.pago.matricula.alumno.nro_documento }}</div>
			{% set detalle_y = 105 %}
			{% for i in 1..1 %}
			<div style="position: absolute; top: {{ detalle_y + advance }}px; left: 100px">1</div>
			<div style="position: absolute; top: {{ detalle_y + advance }}px; left: 130px">COMISIÓN PAGO CON TARJETA</div>
			<div style="position: absolute; top: {{ detalle_y + advance }}px; left: 310px">{{ impresion.pago.getComisionPagoTarjeta()|number_format(2) }}</div>
			<div style="position: absolute; top: {{ detalle_y + advance }}px; left: 370px">{{ impresion.pago.getComisionPagoTarjeta()|number_format(2) }}</div>
			{% set detalle_y = detalle_y + 12 %}
			{% endfor %}
			
			<div style="position: absolute; top: {{ 277 + advance }}px; left: 340px">{{ impresion.pago.getComisionPagoTarjeta()|number_format(2) }}</div>

			<div style="position: absolute; top: {{ 285 + advance }}px; left: 120px">{{ numeroLetras(impresion.pago.getComisionPagoTarjeta()) }}</div>
			
			<div style="position: absolute; top: {{ 295 + advance }}px; left: 220px">Vencimiento: {{ impresion.getFechaCancelado()|date('d-m-Y') }}</div>
			
			{% set advance = advance + (295) + COLEGIO.impresiones_espaciado %}
		{% endfor %}
	</body>
</html>