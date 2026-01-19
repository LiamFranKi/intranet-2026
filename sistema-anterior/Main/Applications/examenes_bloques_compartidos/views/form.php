{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formExamen_Bloque_Compartido').niftyOverlay();
	$('#formExamen_Bloque_Compartido').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			{% if examen_bloque_compartido.is_new_record() %}
			if($('#grupos').val() == null){
				return zk.pageAlert({message: 'Seleccione al menos un grupo.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
			}
			{% endif  %}

			$(_form).sendForm('/examenes_bloques_compartidos/save', function(r){
				

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

	$('.fecha').datepicker({format: 'yyyy-mm-dd', autoclose: true})
	$('.hora').timepicker()
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Examenes Bloques / Compartidos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Examenes Bloques</a></li>
		<li class="active">Compartir Examen</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formExamen_Bloque_Compartido" data-target="#formExamen_Bloque_Compartido" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Compartir Examen</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.examen_id }}

				{% if examen_bloque_compartido.is_new_record() %}
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Grupo</label>
				    <div class="col-sm-6">
				    	{{ form.grupos }}
				    </div>
				</div>
				{% else %}
				{{ form.grupo_id }}
				{% endif %}

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Bimestre / N° <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-6 col-lg-2">{{ form.ciclo }}</div>
				    <div class="col-sm-6 col-xs-6 col-lg-2">{{ form.nro }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Tiempo Min. <small class="text-danger">*</small></label>
				    <div class="col-sm-4 col-xs-12 col-lg-2">{{ form.tiempo }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Intentos <small class="text-danger">*</small></label>
				    <div class="col-sm-4 col-xs-12 col-lg-2">{{ form.intentos }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Expiracion <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-6 col-lg-2">
				    	<input type="text" class="form-control fecha" name="expiracion_fecha" value="{{ examen_bloque_compartido.expiracion|date('Y-m-d') }}">
				    </div>
				    <div class="col-sm-6 col-xs-6 col-lg-2">
				    	<input type="text" class="form-control hora" name="expiracion_hora" value="{{ examen_bloque_compartido.expiracion|date('h:i A') }}">
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
