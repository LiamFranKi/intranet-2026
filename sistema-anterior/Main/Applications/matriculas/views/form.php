{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formMatricula').niftyOverlay();
	$('#formMatricula').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/matriculas/save', function(r){
				

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

	$('#nivel_id').trigger('change');

	$('#formMatricula').changeGradoOptions({
		value: '{{ matricula.grupo.grado }}',
	});

	$('#costo_id').prepend('<option value="-1">-- Personalizado --</option>');
	$('#costo_id').bind('change', function(){
		if(this.value == -1){
			$('.costo_personalizado').show();
		}else{
			$('.costo_personalizado').hide();
		}
	});
	{% if matricula.costo.tipo == 'PERSONAL' %}	
	$('#costo_id').val('-1');
	{% endif %}
	$('#costo_id').trigger('change');


});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Matriculas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/matriculas">Matriculas</a></li>
		<li class="active">Registro de Matricula</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formMatricula" data-target="#formMatricula" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ matricula.is_new_record() ? "Registrar" : "Editar" }} Matricula</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.alumno_id|raw }}
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Sede <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.sede_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nivel <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nivel_id }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Grado <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.grado }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Sección <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.seccion }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Turno <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.turno_id }}</div>
				</div>
				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Año Académico <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.anio }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Estado <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.estado }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Costo</label>
				    <div class="col-sm-4 col-lg-4">{{ form.costo_id }}</div>
				</div>

				<div class="form-group form-group-sm costo_personalizado">
				    <label class="col-sm-4 control-label" for="">Costo Personalizado</label>
				    <div class="col-sm-8">
				    	<input type="text" value="{{ matricula.costo.matricula }}" style="width: 100px; display: inline-block" class="form-control tip" title="Matrícula" name="costo_matricula" data-bv-notempty="true" data-bv-numeric="true" placeholder="Matrícula" />
				    	<input type="text" value="{{ matricula.costo.pension }}" style="width: 100px; display: inline-block" class="form-control tip" title="Pensión" name="costo_pension" data-bv-notempty="true" data-bv-numeric="true" placeholder="Pensión" />
				    	<input type="text" value="{{ matricula.costo.agenda }}" style="width: 100px; display: inline-block" class="form-control tip" title="Agenda" name="costo_agenda" data-bv-notempty="true" data-bv-numeric="true" placeholder="Agenda" />
				    </div>
				</div>

			
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Ocultar <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.ocultar }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Descontar <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.descontar }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Modalidad <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.modalidad }}</div>
				</div>

				
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Registrar Pago</label>
				    <div class="col-sm-6 col-lg-2"><input type="checkbox" name="registrarMatricula" checked /></div>
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
