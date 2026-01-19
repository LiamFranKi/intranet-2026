{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	//$('#listaPagos').dataTable();
	setMenuActive('pagos');
});

function anular_pago(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/pagos/anular', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}

function borrar_pago(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/pagos/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}

function printSelected(){
	var data = $('input[name^="pago_id"]').serialize();
	if(data == ''){
		return zk.pageAlert({message: 'Seleccione al menos un pago.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
	}
	zk.printDocument('/pagos/imprimir?' + data + '&matricula_id={{ matricula.id }}')
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Pagos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/pagos">Pagos</a></li>
		<li class="active">Lista de Pagos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Pagos</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/pagos/form?matricula_id={{ get.matricula_id }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
						<button class="btn btn-default" onclick="printSelected()">{{ icon('printer') }} Imprimir Seleccionados</button>
					</div>
				</div>
			</div>

			<div class="alert alert-info text-center">
		        La información es referencial que se calcula a partir del costo seleccionado al momento de registrar la matrícula.
		    </div>
			<table class="special" style="width: 100%; text-align: center">
				<tr>
					<th>Monto a pagar {{ COLEGIO.moneda }}</th><td>  {{ matricula.getTotalPagar()|number_format(2) }}</td>
					<th>Monto pagado {{ COLEGIO.moneda }}</th><td> {{ matricula.getTotalPagado()|number_format(2) }}</td>
					<th>Monto que adeuda {{ COLEGIO.moneda }}</th><td>{{ matricula.getSaldo()|number_format(2) }}</td>
					<th>Otros Pagos {{ COLEGIO.moneda }}</th><td>{{ matricula.getOtrosPagos()|number_format(2) }}</td>
				</tr>
			</table>

			<table id="listaPagos" class="special">
				<thead>
					<tr>
						<th></th>
						<th>Tipo de Pago</th>
						<th>Monto</th>
					
						<th>Descripción</th>
					
						<th>Fecha - Hora</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for pago in pagos %}
					<tr>
						<td class="text-center"><label><input type="checkbox" name="pago_id[]" value="{{ pago.id }}" id="pago_id_{{ pago.id }}" /></label></td>
						<td class="text-center" style="{{ pago.estado == 'ANULADO' ? 'color: red' : '' }}">{{ pago.getTipoDescription() }}</td>
						<td class="text-center" style="{{ pago.estado_pago == 'PENDIENTE' ? 'color: red' : '' }}">{{ COLEGIO.moneda }} {{ (pago.monto + pago.mora)|number_format(2) }}{% if pago.mora > 0 %}<br /><small>Mora: S/. {{ pago.mora|number_format(2) }}</small>{% endif %}</td>
						
						<td>
							{{ pago.descripcion }}
							{% if pago.observaciones %}
							<br /><b>Observaciones:</b><br />{{ pago.observaciones }}
							{% endif %}
						</td>
					
						<td class="text-center">{{ pago.getFechaHora() }}
							{% if pago.estado_pago == "CANCELADO" %}
							<br /><small class="text-bold" style="font-weight: bold">Cancelado: {{ pago.fecha_cancelado }}</small>
							{% endif %}
						</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/impresiones?pago_id={{ sha1(pago.id) }}">{{ icon('printer') }} Impresiones</a></li>
                                    <li><a href="javascript:;" onclick="zk.printDocument('/pagos/imprimir_externo/{{ sha1(pago.id) }}')">{{ icon('printer') }} Imprimir Boleta Externa</a></li>
									<li><a href="#/pagos/form/{{ sha1(pago.id) }}">{{ icon('register') }} Editar Pago</a></li>
									<li><a href="javascript:;" onclick="anular_pago('{{ sha1(pago.id) }}')">{{ icon('delete') }} Anular Pago</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:;" onclick="borrar_pago('{{ sha1(pago.id) }}')">{{ icon('delete') }} Borrar Pago</a></li>
								</ul>
							</div>
						</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>

{% endblock %}
