<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Detalles de Ingreso</h3>
		</div>
		<div class="panel-body">
			<table class="special">
				<tr>
					<th style="width: 200px">Descripción</th>
					<td>{{ boleta_ingreso.descripcion }}</td>
				</tr>
				<tr>
					<th style="width: 200px">Fecha</th>
					<td>{{ COLEGIO.parseFecha(boleta_ingreso.fecha) }}</td>
				</tr>
			</table>

			<table class="special">
				<thead>
					<tr>
						<th>Categoría</th>
						<th>Concepto</th>
						<th>Cant.</th>
					</tr>
				</thead>
				<tbody>
				{% for detalle in boleta_ingreso.detalles %}
				<tr>
					<td class="center">{{ detalle.categoria.nombre }}</td>
					<td class="center">{{ detalle.concepto.descripcion }}</td>
					<td class="center">{{ detalle.cantidad }}</td>
				</tr>
				{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>