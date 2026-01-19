{% extends main_template %}
{% block main_content %}
<script>
var xtable;
$(function(){
	xtable = $('#listaBoletas').dataTable();
	setMenuActive('boletas');

	$('#searchForm').on('submit', function(e){
		e.preventDefault();
		zk.goToUrl('/boletas?' + $(this).serialize())
	})

	//$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true});
	$('#all').change(function(){
	    var cells = xtable.fnGetNodes();
	    $( cells ).find(':checkbox').prop('checked', $(this).is(':checked'));
	});
});

function borrar_boleta(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/boletas/borrar', {id: id}, function(r){
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

function restaurar_boleta(id){
	if(confirm('¿Está seguro de restaurar los datos?'))
		$.post('/boletas/borrar', {id: id}, function(){
			zk.reloadPage()
		}, 'json');
}

function getChecked(){
	var cells = xtable.fnGetNodes();
	//zk.printDocument('/boletas/generar_all?'+ );

    $.post('/boletas/generar_all', $( cells ).find(':checked').serialize(), function(r){
    	
    	//console.log(r)
    	if(r.file){
    		zk.printDocument('/Static/Temp/'+ r.file)
    	}
    }, 'json')
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Boletas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/boletas">Boletas</a></li>
		<li class="active">Lista de Boletas</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Registros</h3>
		</div>
		<div class="panel-body">
			<form class="form-inline text-center" id="searchForm">
				<div class="form-group"><input type="date" style="width: 150px" class="form-control calendar" name="fecha1" value="{{ fecha1 }}" /></div>
				<div class="form-group"><input type="date" style="width: 150px" class="form-control calendar" name="fecha2" value="{{ fecha2 }}" /></div>
				
				<div class="form-group">
					<select name="tipo" class="form-control input-sm">
						<option value="">-- Tipo --</option>
						<option value="VENTAS" {{ get.tipo == "VENTAS" ? 'selected' : '' }}>Ventas</option>
						<option value="PAGOS" {{ get.tipo == "PAGOS" ? 'selected' : '' }}>Pensiones / Matrículas</option>
						<!--<option value="TALLERES" {{ get.tipo == "TALLERES" ? 'selected' : '' }}>Talleres</option>-->
					</select>
				</div>
				

				<div class="form-group">
					<select name="estado" class="form-control input-sm">
						<option value="ACTIVO" {{ get.estado == "ACTIVO" ? "selected" : "" }}>Activos</option>
						<option value="ANULADO" {{ get.estado == "ANULADO" ? "selected" : "" }}>Anulados</option>
					</select>
				</div>	

				<div class="form-group"><button class="btn btn-primary">Buscar</button></div>
				<div class="form-group"><a href="#/boletas/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a></div>
			</form>
		</div>
	</div>
	<div class="panel">
		
		<div class="panel-body">
			{% if (boletas|length + impresiones|length + matriculasTalleres|length) > 0 %}
			<p>
				<button class="btn btn-default" onclick="getChecked()">{{ icon('font_go') }} Generar JSON Seleccionados</button>
			</p>
			{% endif %}
			
			<table id="listaBoletas" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>
							<input type="checkbox" id="all" class="check" />
						</th>
						<th>nombre</th>
						<th>fecha</th>
						
						<th>serie - Nº</th>
						<th>json</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for boleta in boletas %}
					<tr>
						<td class="text-center">
							<input type="checkbox" class="check" name="impresiones[VENTAS][]" value="{{ boleta.id }}" />
						</td>
						<td>{{ boleta.nombre }}</td>
						<td class="text-center">{{ boleta.fecha }}</td>
						
						<td class="text-center">BOLETA<br />{{ boleta.getCurrentSerie() }} - {{ boleta.getCurrentNumero() }}</td>
						<td class="text-center">{{ boleta.json_generado }}</td>
						
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="javascript:;" onclick="{{ not boleta.isImpreso() ? 'if(confirm(\'¿Está seguro de imprimir la boleta?, Una vez impresa no podrá modificar los datos.\'))' : '' }} zk.printDocument('/boletas/imprimir/{{ sha1(boleta.id) }}')">{{ icon('printer') }} Imprimir Boleta</a></li>
									<li><a href="/boletas/json/{{ sha1(boleta.id) }}?check=true" target="blank">{{ icon('font_go') }} Generar JSON</a></li>
									{% if boleta.isServicio() and boleta.tipo_pago == "TARJETA" %}
									<li><a href="javascript:;" onclick="zk.printDocument('/boletas/imprimir_comision/{{ sha1(boleta.id) }}')">{{ icon('printer') }} Imprimir Comisión</a></li>
									{% endif %}

                                    <li><a href="javascript:;" onclick="zk.printDocument('/boletas/imprimir_externo/{{ sha1(boleta.id) }}')">{{ icon('printer') }} Imprimir Boleta Externa</a></li>
									
                                    
                                    <li><a href="javascript:;" onclick="fancybox('/invoice_payments?invoice_id={{ sha1(boleta.id) }}')">{{ icon('application_view_list') }} Pagos Registrados</a></li>
                                    
                                    {% if boleta.estado == 'ACTIVO' %}
									<li><a href="#/boletas/form/{{ sha1(boleta.id) }}">{{ icon('register') }} Editar Boleta</a></li>
									<li><a href="javascript:;" onclick="borrar_boleta('{{ sha1(boleta.id) }}')">{{ icon('delete') }} Borrar Boleta</a></li>
									{% else %}
									<li class="divider"></li>
									<li><a href="javascript:;" onclick="restaurar_boleta('{{ sha1(boleta.id) }}')">{{ icon('add') }} Restaurar Datos</a></li>
									{% endif %}

								</ul>
							</div>
						</td>
					</tr>
					{% endfor %}


					{% for impresion in impresiones %}
					{% set pago = impresion.pago %}
					<tr>
						<td class="text-center">
							
							<input type="checkbox" class="check" name="impresiones[{{ impresion.tipo_documento }}][]" value="{{ impresion.id }}" />

						</td>
						<td>{{ pago.matricula.alumno.getFullName() }}</td>
						<td class="text-center">{{ impresion.fecha_impresion }}</td>
						
						<td class="text-center">{{ impresion.tipo_documento }}<br />{{ impresion.getSerie() }} - {{ impresion.getNumero() }}</td>
						<td class="text-center">{{ impresion.json_generado }}</td>
						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									
									{% if impresion.estado == 'ACTIVO' %}
								
										{% if impresion.tipo_documento == 'BOLETA' %}
											<li><a title="Generar JSON" href="/impresiones/generar_json_boleta/{{ sha1(impresion.id) }}?check=true" download>{{ icon('font_go') }} Generar JSON</a></li>
											<!--<li><a title="Enviar JSON" href="/pagos/enviar_json_boleta/{{ impresion.id }}" download>{{ icon('table_go') }} Enviar JSON</a></li>-->
											<li class="divider"></li>
											<li><a href="javascript:;" onclick="anular_boleta('{{ sha1(impresion.id) }}', 'BOLETA')">{{ icon('delete') }} Anular Impresión</a></li>
										{% endif %}

										{% if impresion.tipo_documento == 'NOTA' %}
											<li><a title="Generar JSON" href="/impresiones/generar_json_nota_debito/{{ sha1(impresion.id) }}" download>{{ icon('font_go') }} Generar JSON</a>
											<li><a href="javascript:;" onclick="anular_boleta('{{ sha1(impresion.id) }}', 'NOTA')">{{ icon('delete') }} Anular Impresión</a></li>
										{% endif %}
										<li><a href="javascript:;" onclick="zk.printDocument('/pagos/imprimir_externo/{{ sha1(pago.id) }}')">{{ icon('printer') }} Imprimir Boleta Externa</a></li>

									{% else %}
										<li><a href="javascript:;" title="Restaurar Impresión" onclick="restaurar_impresion('{{ sha1(impresion.id) }}')">{{ icon('add') }} Restaurar Impresión</a></li>
										{% if impresion.tipo_documento == 'BOLETA' %}
										<li><a title="Generar JSON RC" href="/impresiones/generar_json_rc_boleta/{{ sha1(impresion.id) }}" download>{{ icon('font_go') }} Generar JSON RC</a></li>
										{% endif %}
										{% if impresion.tipo_documento == 'NOTA' %}
										<li><a title="Generar JSON RC" href="/impresiones/generar_json_rc_nota/{{ sha1(impresion.id) }}" download>{{ icon('font_go') }} Generar JSON RC</a></li>
										{% endif %}
									{% endif %}								

									<li><a href="#/pagos/form/{{ sha1(pago.id) }}">{{ icon('register') }} Editar Datos</a></li>
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
