<div class="modal-content modal-800">
	<div class="modal-header">
		<h3 class="modal-title">Detalles de la Actividad</h3>
	</div>
	<div class="modal-body">
		<table class="special">
			<tr>
				<th>Descripción</th>
				<td>{{ actividad.descripcion }}</td>
			</tr>
			<tr>
				<th>Lugar</th>
				<td>{{ actividad.lugar }}</td>
			</tr>
			<tr>
				<th>Detalles</th>
				<td>{{ actividad.detalles }}</td>
			</tr>
			<tr>
				<th>Desde / Hasta</th>
				<td>
				{%  if actividad.fecha_inicio == actividad.fecha_fin %}
                	{{ actividad.fecha_inicio|date('d-m-Y') }}
				{% else %}
					{{ actividad.fecha_inicio|date('d-m-Y') }} / {{ actividad.fecha_fin|date('d-m-Y') }}
				{% endif %}

				</td>
			</tr>
		</table>
	</div>
</div>