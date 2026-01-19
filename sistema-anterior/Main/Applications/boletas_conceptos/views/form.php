{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formBoleta_Concepto').niftyOverlay();
	$('#formBoleta_Concepto').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/boletas_conceptos/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/boletas_conceptos');
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

	$('#controlar_stock').on('change', function(){
		if(this.value == 'SI') $('.stock').show();
		if(this.value == 'NO') $('.stock').hide();
	}).trigger('change');

	$('#categoria_id').bind('change', function(){
		$('#subcategoria_id').find('.item').remove();
		var defaultValue = '{{ boleta_concepto.subcategoria_id }}';
		$.post('/boletas_categorias/subcategorias', {id: this.value}, function(r){
			for(i in r){
				subcategoria = r[i];
				data = '<option class="item" value="'+ subcategoria.id +'" '+ (subcategoria.id == defaultValue ? 'selected' : '') +'>'+ subcategoria.nombre +'</option>';
				$('#subcategoria_id').append(data);
			}
		}, 'json');
	});

	$('#categoria_id').trigger('change');
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Conceptos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/boletas_conceptos">Conceptos</a></li>
		<li class="active">Registro de Concepto</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formBoleta_Concepto" data-target="#formBoleta_Concepto" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ boleta_concepto.is_new_record() ? "Registrar" : "Editar" }} Concepto</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Categoria <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.categoria_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Subcategoria</label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.subcategoria_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descripción <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.descripcion }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descripcion Proveedor</label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.descripcion_proveedor }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Controlar Stock <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.controlar_stock }}</div>
				</div>

				<div class="form-group form-group-sm stock">
				    <label class="col-sm-4 control-label" for="">Stock <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.stock }}</div>
				</div>


				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Ocultar <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.ocultar }}</div>
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
