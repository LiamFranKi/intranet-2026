{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaImpresiones').dataTable();
	setMenuActive('impresiones');
});

function borrar_impresion(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/impresiones/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}

function restaurar_impresion(id){
	//if($('.impresion_activo').size() > 0) return $.prompt('Sólo se permite una impresión activa.');
	if(confirm('¿Está seguro de restaurar la impresión?'))
		$.post('/impresiones/change_estado', {id: id}, function(){
			zk.reloadPage()
		}, 'json');
}
function anular_boleta(id, tipo){
	if(confirm('¿Está seguro de anular la impresión?'))
		$.post('/impresiones/change_estado', {id: id}, function(){
			if(tipo == 'BOLETA')
				zk.printDocument('/impresiones/generar_json_rc_boleta/' + id);
			if(tipo == 'NOTA')
				zk.printDocument('/impresiones/generar_json_rc_nota/' + id);
	 
			zk.reloadPage()
		}, 'json');
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Impresiones</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/impresiones">Impresiones</a></li>
		<li class="active">Lista de Impresiones</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Impresiones</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-12 table-toolbar-left">
						<button class="btn btn-default" onclick="zk.printDocument('/pagos/imprimir/{{ sha1(pago.id) }}'); $.fancybox.reload()">{{ icon('printer') }} Imprimir Pago</button>
						{% if pago.mora > 0 %}
						<button class="btn btn-default" onclick="zk.printDocument('/pagos/imprimir_mora_nota_debito/{{ sha1(pago.id) }}'); $.fancybox.reload()">{{ icon('printer') }} Imprimir Mora - Nota Débito</button>
						{% endif %}
						{% if pago.forma_pago == "TARJETA" %}
						<button class="btn btn-default" onclick="zk.printDocument('/pagos/imprimir_comision/{{ sha1(pago.id) }}'); $.fancybox.reload()">{{ icon('printer') }} Imprimir Comisión</button>
						{% endif %}
					</div>
				</div>
			</div>

			<table id="listaImpresiones" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>TIPO</th>
						<th>TIPO DE DOCUMENTO</th>
						<th>Nº DE DOCUMENTO</th>
						<th>ESTADO</th>
						<th>FECHA IMPRESION</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for impresion in impresiones %}
					<tr>
						<td class="text-center">{{ impresion.getTipoPago() }}</td>
						<td class="text-center">{{ impresion.getTipoDocumento() }}</td>
						<td class="text-center">{{ impresion.getSerieNumero() }}</td>
						<td class="text-center impresion_{{ impresion.estado|lower }}" style="color: {{ impresion.estado == 'ANULADO' ? 'red' : 'green' }}">{{ impresion.estado }}</td>
						<td class="text-center">{{ impresion.fecha_impresion }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									{% if impresion.estado == 'ACTIVO' %}
										<li><a href="javascript:;" onclick="fancybox('/impresiones/form/{{ sha1(impresion.id) }}')">{{ icon('edit') }} Editar Impresión</a></li>
										{% if impresion.tipo_documento == 'BOLETA' %}
											<li><a title="Generar JSON" href="/impresiones/generar_json_boleta/{{ sha1(impresion.id) }}" download>{{ icon('font_go') }} Generar JSON</a></li>
											<!--<li><a title="Enviar JSON" href="/pagos/enviar_json_boleta/{{ impresion.id }}" download>{{ icon('table_go') }} Enviar JSON</a></li>-->
											<li class="divider"></li>
											<li><a href="javascript:;" onclick="anular_boleta('{{ sha1(impresion.id) }}', 'BOLETA')">{{ icon('delete') }} Anular Impresión</a></li>
										{% endif %}

										{% if impresion.tipo_documento == 'NOTA' %}
											<li><a title="Generar JSON" href="/impresiones/generar_json_nota_debito/{{ sha1(impresion.id) }}" download>{{ icon('font_go') }} Generar JSON</a>
											<li><a href="javascript:;" onclick="anular_boleta('{{ sha1(impresion.id) }}', 'NOTA')">{{ icon('delete') }} Anular Impresión</a></li>
										{% endif %}
										

									{% else %}
										<li><a href="javascript:;" title="Restaurar Impresión" onclick="restaurar_impresion('{{ sha1(impresion.id) }}')">{{ icon('add') }} Restaurar Impresión</a></li>
										{% if impresion.tipo_documento == 'BOLETA' %}
										<li><a title="Generar JSON RC" href="/impresiones/generar_json_rc_boleta/{{ sha1(impresion.id) }}" download>{{ icon('font_go') }} Generar JSON RC</a></li>
										{% endif %}
										{% if impresion.tipo_documento == 'NOTA' %}
										<li><a title="Generar JSON RC" href="/impresiones/generar_json_rc_nota/{{ sha1(impresion.id) }}" download>{{ icon('font_go') }} Generar JSON RC</a></li>
										{% endif %}
									{% endif %}
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
