{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formSede').niftyOverlay();
	$('#formSede').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/sedes/save_configuracion', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/sedes');
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
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Sedes</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/sedes">Sedes</a></li>
		<li class="active">Configuración de Boletas</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formSede" data-target="#formSede" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Configuración de Boletas</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
			
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Boletas</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Serie / Nº</label>
						    <div class="col-sm-3">
						    	<input type="text" name="serie" class="form-control tip" data-bv-notempty="true" placeholder="Serie" value="{{ config.serie }}" />
						    </div>
						    <div class="col-sm-3" >
						    	<input type="text" name="numero" class="form-control tip" data-bv-notempty="true" placeholder="Nº" value="{{ config.numero }}">
						    </div>

						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Serie / Nº (002)</label>
						    <div class="col-sm-3">
						    	<input type="text" name="serie_2" class="form-control tip" data-bv-notempty="true" placeholder="Serie" value="{{ config.serie_2 }}" />
						    </div>
						    <div class="col-sm-3" >
						    	<input type="text" name="numero_2" class="form-control tip" data-bv-notempty="true" placeholder="Nº" value="{{ config.numero_2 }}">
						    </div>

						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Serie / Nº (003)</label>
						    <div class="col-sm-3">
						    	<input type="text" name="serie_3" class="form-control tip" data-bv-notempty="true" placeholder="Serie" value="{{ config.serie_3 }}" />
						    </div>
						    <div class="col-sm-3" >
						    	<input type="text" name="numero_3" class="form-control tip" data-bv-notempty="true" placeholder="Nº" value="{{ config.numero_3 }}">
						    </div>

						</div>

					</div>
				</div>
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Notas de Débito</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Serie / Nº</label>
						    <div class="col-sm-3">
						    	<input type="text" name="serie_mora" class="form-control tip" data-bv-notempty="true" placeholder="Serie" value="{{ config.serie_mora }}" />
						    </div>
						    <div class="col-sm-3" >
						    	<input type="text" name="numero_mora" class="form-control tip" data-bv-notempty="true" placeholder="Nº" value="{{ config.numero_mora }}">
						    </div>

						</div>

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
