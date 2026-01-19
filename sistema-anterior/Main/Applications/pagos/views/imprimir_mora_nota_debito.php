{% set nd = COLEGIO.getImpresionNotasDebito() %}
<!DOCTYPE html>

<html>
	<head>

		<meta charset="utf-8"/>
		<style>
		*{
			font-size: {{ nd.tamano }}px;
			font-family: Verdana;
		}
		</style>
	</head>
	<body>
		{% set advance = 0 %}
		<!-- WIDTH/HEIGHT -> 948/624-->
		{% for impresion in impresiones %}
			<!--<div style="position: relative; width: {{ nd.ancho }}px; height: {{ nd.alto }}px; {{ get.previa == 'true' ? 'border: 1px dashed #000;' : '' }} margin-top: {{ loop.index == 1 ? 0 : nd.espaciado }}px">-->
				<!--<img src="/Static/NotaDebito.jpg" style="position: absolute; top: 0px; left: 0px" />-->
				<div style="position: absolute; top: {{ advance + nd.nro_boleta_y }}px; left: {{ nd.nro_boleta_x }}px" id="nro">{{ impresion.getSerieNumero() }}</div>
				
				<div style="position: absolute; top: {{ advance + nd.fecha_y }}px; left: {{ nd.fecha_x }}px">{{ impresion.pago.fecha_cancelado|date('d-m-Y') }}</div>
				<div style="position: absolute; top: {{ advance + nd.fecha2_y }}px; left: {{ nd.fecha2_x }}px">{{ impresion.pago.fecha_cancelado|date('d-m-Y') }}</div>
				<div style="position: absolute; top: {{ advance + nd.fecha3_y }}px; left: {{ nd.fecha3_x }}px">{{ impresion.pago.fecha_cancelado|date('d-m-Y') }}</div>

				<div style="position: absolute; top: {{ advance + nd.nombre_y }}px; left: {{ nd.nombre_x }}px">{{ impresion.pago.matricula.alumno.getFullName() }}</div>

				<div style="position: absolute; top: {{ advance + nd.dni_y }}px; left: {{ nd.dni_x }}px">{{ impresion.pago.matricula.alumno.nro_documento }}</div>
				{% set boletaPago = impresion.pago.getActiveImpresion(false) %}
				<div style="position: absolute; top: {{ advance + nd.serie_pago_y }}px; left: {{ nd.serie_pago_x }}px">{{ boletaPago.getSerie() }}</div>
				<div style="position: absolute; top: {{ advance + nd.nro_pago_y }}px; left: {{ nd.nro_pago_x }}px">{{ boletaPago.getNumero() }}</div>

				<div style="position: absolute; top: {{ advance + nd.fecha_pago_y }}px; left: {{ nd.fecha_pago_x }}px">{{ boletaPago.fecha_impresion|date('d-m-Y') }}</div>
				
				<div style="position: absolute; top: {{ advance + nd.descripcion_y }}px; left: {{ nd.descripcion_x }}px">PENALIDAD POR ATRASO DE PAGO - BV {{ boletaPago.getSerie() }}-{{ boletaPago.getNumero() }}</div>
				<div style="position: absolute; top: {{ advance + nd.precio_unitario_y }}px; left: {{ nd.precio_unitario_x }}px">{{ impresion.pago.mora|number_format(2) }}</div>
				<div style="position: absolute; top: {{ advance + nd.importe_y }}px; left: {{ nd.importe_x }}px">{{ impresion.pago.mora|number_format(2) }}</div>

				<div style="position: absolute; top: {{ advance + nd.subtotal_y }}px; left: {{ nd.subtotal_x }}px">{{ impresion.pago.mora|number_format(2) }}</div>
				<div style="position: absolute; top: {{ advance + nd.igv_y }}px; left: {{ nd.igv_x }}px">0.00</div>
				<div style="position: absolute; top: {{ advance + nd.total_y }}px; left: {{ nd.total_x }}px">{{ impresion.pago.mora|number_format(2) }}</div>
				
				<div style="position: absolute; top: {{ advance + nd.documento_pago_y }}px; left: {{ nd.documento_pago_x }}px">BOLETA DE VENTA</div>
				<div style="position: absolute; top: {{ advance + nd.total_letras_y }}px; left: {{ nd.total_letras_x }}px">{{ impresion.pago.getLetrasMora() }}</div>
				
				<div style="position: absolute; top: {{ advance + nd.nombre2_y }}px; left: {{ nd.nombre2_x }}px">{{ impresion.pago.matricula.alumno.getFullName() }}</div>
				<div style="position: absolute; top: {{ advance + nd.dni2_y }}px; left: {{ nd.dni2_x }}px">{{ impresion.pago.matricula.alumno.nro_documento }}</div>
				

				{% set advance = advance + nd.alto + nd.espaciado %}
				<!--
				<div style="position: absolute; top: {{ 80 + advance }}px; left: 330px">{{ impresion.pago.matricula.alumno.nro_documento }}</div>
				{% set detalle_y = 105 %}
				{% for i in 1..1 %}
				<div style="position: absolute; top: {{ detalle_y + advance }}px; left: 100px">1</div>
				
				
				<div style="position: absolute; top: {{ detalle_y + advance }}px; left: 370px">{{ impresion.pago.mora|number_format(2) }}</div>
				{% set detalle_y = detalle_y + 12 %}
				{% endfor %}
				

				<div style="position: absolute; top: {{ 277 + advance }}px; left: 340px">{{ impresion.pago.mora|number_format(2) }}</div>

				<div style="position: absolute; top: {{ 285 + advance }}px; left: 120px">{{ impresion.pago.getLetrasMora() }}</div>
				<div style="position: absolute; top: {{ 295 + advance }}px; left: 220px">{{ impresion.fecha_impresion|date('d-m-Y') }}</div>-->
			<!--</div>-->
		{% endfor %}

		
	</body>
</html>