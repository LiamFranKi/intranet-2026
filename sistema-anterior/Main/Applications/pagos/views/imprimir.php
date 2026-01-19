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
	
		{% for impresion in impresiones %}
			<div style="position: absolute; top: {{ c.nro_y + advance }}px; left: {{ c.nro_x }}px" id="nro">{{ impresion.getNumero() }}</div>
			<div style="position: absolute; top: {{ c.nombre_y + advance }}px; left: {{ c.nombre_x }}px">{{ impresion.pago.matricula.alumno.getFullName() }}</div>
			<div style="position: absolute; top: {{ c.fecha_y + advance }}px; left: {{ c.fecha_x }}px">{{ impresion.fecha_impresion|date('d-m-Y') }}</div>
			<div style="position: absolute; top: {{ c.documento_y + advance }}px; left: {{ c.documento_x }}px; ">{{ impresion.pago.matricula.alumno.nro_documento }}</div>
			{% set detalle_y = 105 %}

			<div style="position: absolute; top: {{ c.cantidad_y + advance }}px; left: {{ c.cantidad_x }}px">1</div>
			<div style="position: absolute; top: {{ c.descripcion_y + advance }}px; left: {{ c.descripcion_x }}px">{{ impresion.pago.getDescription() }}</div>

			<div style="position: absolute; top: {{ c.grupo_y + advance }}px; left: {{ c.grupo_x }}px">{{ impresion.pago.matricula.grupo.getNombre() }}</div>

			<div style="position: absolute; top: {{ c.precio_y + advance }}px; left: {{ c.precio_x }}px">{{ impresion.pago.monto|number_format(2) }}</div>
			<div style="position: absolute; top: {{ c.subtotal_y + advance }}px; left: {{ c.subtotal_x }}px">{{ impresion.pago.monto|number_format(2) }}</div>
			
			
			
			<div style="position: absolute; top: {{ c.total_y + advance }}px; left: {{ c.total_x }}px">{{ impresion.pago.getMonto()|number_format(2) }}</div>

			<div style="position: absolute; top: {{ c.total_letras_y + advance }}px; left: {{ c.total_letras_x }}px">{{ impresion.pago.getLetras() }}</div>
			
			<div style="position: absolute; top: {{ c.vencimiento_y + advance }}px; left: {{ c.vencimiento_x }}px">Vencimiento: {{ impresion.getFechaCancelado()|date('d-m-Y') }}</div>
			
			{% set advance = advance + (295) + COLEGIO.impresiones_espaciado %}
		{% endfor %}

	</body>
</html>