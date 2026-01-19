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
				{% if concepto.controlarStock() and concepto.ocultar == 'NO' %}
				
					"{{ concepto.id }}": {{ concepto.attributes()|json_encode }},
				{% endif %}
			{% endfor %}
		}
	},
	{% endfor %}
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
		}else{
			$(sender).parent().next().html('-');
		}
	}
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

function agregarDetalle(defaults){
	defaults = $.extend({categoria_id: -1, detalle_id: -1, cantidad: 1, precio: 0}, defaults);
	categoriaSelect = '<select id="categoria_'+ detalleIndex +'" onchange="setConceptos(this, \''+ (defaults['concepto_id']) +'\', '+ detalleIndex +')" name="categoria_id[]" class="input-sm form-control">'
	categoriaSelect += '<option value="-">-</option>';
	for(i in boletasCategorias){
		categoria = boletasCategorias[i];
		categoriaSelect += '<option value="'+ categoria.id +'" '+ (categoria.id == defaults.categoria_id ? 'selected' : '') +'>'+ categoria.nombre +'</option>';
	}
	categoriaSelect += '</select>';
	data = '<tr class="detalle" id="detalle_'+ detalleIndex +'" data-index="'+ detalleIndex +'">'+
		'<td class="text-center">'+ categoriaSelect +'</td>' +
		'<td class="text-center"></td>'+
		'<td class="text-center" style="font-weight: bold"></td>'+
		'<td class="text-center"><input id="cantidad_'+ detalleIndex +'" min="0" type="number" name="cantidad[]" value="'+ defaults.cantidad +'" class="input-sm form-control" style="width: 50px" /></td>'+
		'<td class="text-center"><input id="precio_'+ detalleIndex +'" type="text" name="precio[]" value="'+ defaults.precio +'" class="input-sm form-control" style="width: 70px" /></td>'+
		'<td class="text-center"><button class="btn-default btn" type="button" onclick="$(this).parent().parent().remove(); ">{{ icon('delete') }}</button></td>'

	'</tr>';

	$('#listaDetalle').append(data);
	$('#categoria_' + detalleIndex).trigger('change');

	++detalleIndex;
	console.log(detalleIndex);
}
$(function(){
	$('#formBoleta_Ingreso').niftyOverlay();
	$('#formBoleta_Ingreso').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/boletas_ingresos/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						history.back(-1)
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

	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true});
	{% for detalle in boleta_ingreso.detalles %}
		agregarDetalle({categoria_id: '{{ detalle.categoria_id }}', concepto_id: '{{ detalle.concepto_id }}', cantidad: '{{ detalle.cantidad }}', precio: '{{ detalle.precio }}'})
	{% endfor %}

});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Ingresos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/boletas_ingresos">Ingresos</a></li>
		<li class="active">Registro de Ingreso</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formBoleta_Ingreso" data-target="#formBoleta_Ingreso" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ boleta_ingreso.is_new_record() ? "Registrar" : "Editar" }} Ingreso</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				
				<div class="panel panel-bordered panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title">Información General</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Descripcion <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.descripcion }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.tipo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Serie / Número <small class="text-danger">*</small></label>
						    <div class="col-sm-3 col-xs-6 col-lg-2">{{ form.serie }}</div>
						    <div class="col-sm-3 col-xs-6 col-lg-2">{{ form.numero }}</div>
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
									<th>Precio</th>
									<th></th>
								</tr>
							</thead>
							<tbody id="listaDetalle"></tbody>
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
