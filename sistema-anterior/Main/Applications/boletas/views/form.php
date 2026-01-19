{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
var boletasCategorias = {
	{% for categoria in COLEGIO.getBoletasCategorias() %}
	"{{ categoria.id }}": {
		"id": "{{ categoria.id }}",
		"nombre": "{{ categoria.nombre }}",
		"conceptos": {
			{% for concepto in categoria.conceptos %}
			{% if concepto.ocultar == 'NO' %}
			"{{ concepto.id }}": {{ concepto.attributes()|json_encode }},
			{% endif %}
			{% endfor %}
		}
	},
	{% endfor %}
};

var comisiones = {
	"CREDITO": {{ COLEGIO.comision_tarjeta_credito }},
	"DEBITO": {{ COLEGIO.comision_tarjeta_debito }},
};

var detalleIndex = 0;

function setDetails(sender){
	if(sender.value != '-'){
		var detailsIndex = $(sender).parent().parent().data('index');
		var categoria_id = $('#categoria_' + detailsIndex).val();
		var concepto_id = sender.value;
		var concepto = boletasCategorias[categoria_id]["conceptos"][concepto_id];
		if(concepto.controlar_stock == "SI"){
			$(sender).parent().next().html(concepto.stock);

			$(sender).parent().next().next().find('input').first().data('stock', concepto.stock);
			//console.log($(sender).parent().next().next().find('input'));
			{% if boleta.is_new_record() %}
			$(sender).parent().next().next().find('input').first().val(0);
			updateImporte(detailsIndex);
			{% endif %}
		}else{
			$(sender).parent().next().html('-');
		}
	}
	
	//console.log(concepto);
}

function setConceptos(sender, defaults, index){
	categoria_id = sender.value;

	conceptoSelect = '<select id="concepto_'+ categoria_id +'_'+ index +'" onclick="" onchange="setDetails(this)" name="concepto_id[]" class="input-sm form-control">';
	conceptoSelect += '<option value="-">-</option>';
	if(categoria_id != '-'){
		conceptos = boletasCategorias[categoria_id].conceptos;
		for(i in conceptos){
			concepto = conceptos[i];
			conceptoSelect += '<option value="'+ concepto.id +'" '+ (concepto.id == defaults ? 'selected' : '') +'>'+ concepto.descripcion +'</option>';
		}
	}
	conceptoSelect += '</select>';
	$(sender).parent().next().html(conceptoSelect);

	$('#concepto_'+ categoria_id +'_'+ index).trigger('change');
}

function updateTotal(){
	var importes = $('.detalleImporte');
	//console.log(importes);
	var total = 0;
	for(i in importes){
		if($.isNumeric(i)){
			var importe = parseFloat($(importes[i]).html());
			//console.log(importe);
			total += importe;
		}
	}
	$('#montoTotal').html(total);
	updateComision();
	//console.log(total);
}

function updateImporte(index){
	var stock = $('#cantidad_' + index).data('stock');
	var cantidad = parseInt($('#cantidad_' + index).val());

	//console.log('stock: ' + stock);
	//console.log('cantidad: ' + cantidad);
	{% if boleta.is_new_record() %}
	if(stock != undefined && parseInt(stock) < cantidad){
		alert('No hay stock disponible');
		$('#cantidad_' + index).val(0);
		$('#cantidad_' + index).focus();
	}
	{% endif %}
	

	var precio = parseFloat($('#precio_' + index).val());
	var total = cantidad * precio;
	$('#importe_' + index).html(total);
	updateTotal();

}

function updateComision(){
	var total = parseFloat($('#montoTotal').html());
	var comision = 0;
	if(total > 0){
		var porcentaje = comisiones[$('#tipo_tarjeta').val()];
		comision = total * porcentaje / 100;
	}

	$('#montoComision').html(comision);
	$('#montoTotalFinal').html(total + comision);
}



function agregarDetalle(defaults){
	defaults = $.extend({categoria_id: -1, detalle_id: -1, precio: 0, cantidad: 1}, defaults);
	categoriaSelect = '<select id="categoria_'+ detalleIndex +'" onchange="setConceptos(this, \''+ (defaults['concepto_id']) +'\', '+ detalleIndex +')" name="categoria_id[]" class="input-sm form-control">'
	categoriaSelect += '<option value="-">-</option>';
	for(i in boletasCategorias){
		categoria = boletasCategorias[i];
		categoriaSelect += '<option value="'+ categoria.id +'" '+ (categoria.id == defaults.categoria_id ? 'selected' : '') +'>'+ categoria.nombre +'</option>';
	}
	categoriaSelect += '</select>';
	data = '<tr class="detalle" id="detalle_'+ detalleIndex +'" data-index="'+ detalleIndex +'">'+
		'<td class="center">'+ categoriaSelect +'</td>' +
		'<td class="center"></td>'+
		'<td class="center" style="font-weight: bold"></td>'+
		'<td class="center"><input id="cantidad_'+ detalleIndex +'" min="0" onblur="updateImporte('+ detalleIndex +')" onchange="updateImporte('+ detalleIndex +')" type="text" name="cantidad[]" value="'+ defaults.cantidad +'" class="input-sm form-control" style="width: 50px" /></td>'+
		'<td class="center"><input id="precio_'+ detalleIndex +'" onblur="updateImporte('+ detalleIndex +')" onchange="updateImporte('+ detalleIndex +')" type="text" name="precio[]" value="'+ defaults.precio +'" class="input-sm form-control" style="width: 70px" /></td>'+
		'<td class="center detalleImporte" id="importe_'+ detalleIndex +'" style="font-weight: bold">'+ (defaults.cantidad * defaults.precio) +'</td>'+
		'<td class="center"><button class="btn-default btn" type="button" onclick="$(this).parent().parent().remove(); updateTotal();">{{ icon('delete') }}</button></td>'

	'</tr>';

	$('#listaDetalle').append(data);
	$('#categoria_' + detalleIndex).trigger('change');

	updateTotal();
	++detalleIndex;
}
$(function(){
	$('#formBoleta').niftyOverlay();
	$('#formBoleta').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/boletas/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/boletas');
					break;
					
					case 0:
						zk.pageAlert({message: 'No se pudieron guardar los datos', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
					break;
					
					case -5:
						zk.formErrors(_form, r.errors);
					break;
					
					default:
						
					break;
				}
				
			});
		}
	});

	$('.tip').tooltip({html: true});
	$('#dni').bind('blur', function(){
		$.post('/info/info_alumno_docente', {dni: $('#dni').val()}, function(r){
			if(r.id && r.tipo == 'ALUMNO'){
				$('#nombre').val(r.nombres);
				$('#tipo').val('ALUMNO');
			}

			if(r.id && r.tipo == 'DOCENTE'){
				$('#nombre').val(r.nombres);
				$('#tipo').val('DOCENTE');
			}
		}, 'json');
	});
	
	$('#tipo_tarjeta').on('change', function(){
		updateComision();
	})

	$('#tipo_pago').bind('change', function(){
		if(this.value == 'TARJETA'){
			$('#tipo_tarjeta').trigger('change');
			$('.xcomision').show();
			return $('#tipo_tarjeta').show();
		}

		$('.xcomision').hide();
		return $('#tipo_tarjeta').hide();
	});


	$('#tipo_pago').trigger('change');

	$('#serie').on('change', function(){
		$.post('/boletas/get_current_numero', {serie: this.value, sede_id: $('#sede_id').val()}, function(r){
			$('#numero').val(r.numero);
		}, 'json')
	});
	
	$('#sede_id').on('change', function(){
		{% if boleta.is_new_record() %}
		$('#serie').trigger('change')
		{% endif %}
	}).trigger('change');

	{% for detalle in boleta.detalles %}
		agregarDetalle({categoria_id: '{{ detalle.categoria_id }}', concepto_id: '{{ detalle.concepto_id }}', cantidad: '{{ detalle.cantidad }}', precio: '{{ detalle.precio }}'})
	{% endfor %}

	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Registro de Ventas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/boletas">Registro de Ventas</a></li>
		<li class="active">Registro de Venta / Servicio</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formBoleta" data-target="#formBoleta" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ boleta.is_new_record() ? "Registrar" : "Editar" }} Venta / Servicio</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				
				<div class="panel panel-bordered panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title">Información General</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo de Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.tipo_documento }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Nº de Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-3 col-xs-6 col-lg-2">{{ form.tipo }}</div>
						    <div class="col-sm-3 col-xs-6 col-lg-2">{{ form.dni }}</div>
						    
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Nombre <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nombre }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.fecha }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Sede <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.sede_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Serie / Número<small class="text-danger">*</small></label>
						    <div class="col-sm-3 col-xs-6 col-lg-2">{{ form.serie }}</div>
						    <div class="col-sm-3 col-xs-6 col-lg-2">{{ form.numero }}</div>
						</div>

						<!-- <div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo de Pago <small class="text-danger">*</small></label>
						    <div class="col-sm-3 col-xs-6 col-lg-2">{{ form.tipo_pago }}</div>
						    <div class="col-sm-3 col-xs-6 col-lg-2 tipo_tarjeta">{{ form.tipo_tarjeta }}</div>
						</div> -->

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Estado Pago <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.estado_pago }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Transferencia Gratuita <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.transferencia_gratuita }}</div>
						</div>

                        <div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Rubro</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.entry }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-bordered panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title">Detalles de Productos</h3>
					</div>
					<div class="panel-body">
						<div class="pad-btm form-inline">
							<div class="row">
								<div class="col-sm-6 table-toolbar-left">
									<a href="javascript:;" onclick="agregarDetalle({})" class="btn btn-success btn-labeled fa fa-plus">Agregar Elemento</a>
								</div>
							</div>
						</div>
						<table class="special">
							<thead>
								<tr>
									<th>Categoría</th>
									<th>Concepto</th>
									<th>Stock</th>
									<th>Cant.</th>
									<th>P.Unit.</th>
									<th>Importe</th>
									<th></th>
								</tr>
							</thead>
							<tbody id="listaDetalle"></tbody>
							<tfoot>
								<tr>
									<th colspan="3"></th>	
									<th colspan="2">TOTAL</th>
									<td style="font-weight: bold" id="montoTotal" class="center">0</td>
								</tr>
								<tr class="xcomision">
									<th colspan="3"></th>
									<th colspan="2">COMISIÓN TARJETA</th>
									<td style="font-weight: bold" id="montoComision" class="center">0</td>
								</tr>
								<tr class="xcomision">
									<th colspan="3"></th>
									<th colspan="2">TOTAL A PAGAR</th>
									<td style="font-weight: bold" id="montoTotalFinal" class="center">0</td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		
			<div class="modal-footer">
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>
</div>
{% endblock %}
