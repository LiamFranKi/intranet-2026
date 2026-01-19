{% set c = COLEGIO.getImpresionBoletas() %}
<!DOCTYPE html>
<html>
	<head>

		<meta charset="utf-8"/>
		<style>
		*{
			font-size: {{ c.tamano_letra }}px;
			font-family: {{ c.tipo_letra }};
		}

		#serie{
			font-size: 11px;
		}
		</style>
	</head>
	<body>
		{% set advance = 0 %}
		<div style="position: absolute; top: {{ c.nro_y + advance }}px; left: {{ c.nro_x }}px" id="nro">{{ boleta.numero }}</div>
		<div style="position: absolute; top: {{ c.nombre_y + advance }}px; left: {{ c.nombre_x }}px">{{ boleta.nombre }}</div>
		<div style="position: absolute; top: {{ c.fecha_y + advance }}px; left: {{ c.fecha_x }}px">{{ boleta.fecha|date('d-m-Y') }}</div>
		<div style="position: absolute; top: {{ c.documento_y + advance }}px; left: {{ c.documento_x }}px; ">{{ boleta.dni }}</div>
		{% set y = 0 %}
		{% for detalle in boleta.detalles %}
		<div style="position: absolute; top: {{ c.cantidad_y + y }}px; left: {{ c.cantidad_x }}px">{{ detalle.cantidad }}</div>
		<div style="position: absolute; top: {{ c.descripcion_y + y }}px; left: {{ c.descripcion_x }}px">{{ detalle.concepto.descripcion }}</div>
		<div style="position: absolute; top: {{ c.precio_y + y }}px; left: {{ c.precio_x }}px">{{ detalle.precio|number_format(2) }}</div>
		<div style="position: absolute; top: {{ c.subtotal_y + y }}px; left: {{ c.subtotal_x }}px">{{ detalle.getImporte()|number_format(2) }}</div>
		{% set y = y + 12 %}
		{% endfor %}
		{% if boleta.tipo_pago == "TARJETA" and not boleta.isServicio() %}
		<div style="position: absolute; top: {{ c.cantidad_y + y }}px; left: {{ c.cantidad_x }}px">1</div>
		<div style="position: absolute; top: {{ c.descripcion_y + y }}px; left: {{ c.descripcion_x }}px">COMISIÓN PAGO CON TARJETA</div>
		<div style="position: absolute; top: {{ c.precio_y + y }}px; left: {{ c.precio_x }}px">{{ boleta.getComisionPagoTarjeta()|number_format(2) }}</div>
		<div style="position: absolute; top: {{ c.subtotal_y + y }}px; left: {{ c.subtotal_x }}px">{{ boleta.getComisionPagoTarjeta()|number_format(2) }}</div>
		{% set y = y + 12 %}
		{% endif %}
		{% if boleta.transferencia_gratuita == 'SI' %}
		<div style="position: absolute; top: {{ c.subtotal_y + y }}px; left: 130px"><b>TRANSFERENCIA GRATUITA</b></div>
		{% endif %}
		<div style="position: absolute; top: {{ c.total_y + advance }}px; left: {{ c.total_x }}px">{{ boleta.getMontoTotal()|number_format(2) }}</div>

		<div style="position: absolute; top: {{ c.total_letras_y + advance }}px; left: {{ c.total_letras_x }}px">{{ letras }}</div>
		<!--<div style="position: absolute; top: 295px; left: 220px">{{ pago.fecha_hora|date('d-m-Y') }}</div>-->
        <div style="position: absolute; top: {{ c.vencimiento_y + advance }}px; left: {{ c.vencimiento_x }}px">{{ boleta.fecha|date('d-m-Y') }}</div>

	</body>
</html2