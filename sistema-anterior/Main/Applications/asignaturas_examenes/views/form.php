{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formAsignatura_Examen').niftyOverlay();
	$('#formAsignatura_Examen').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/asignaturas_examenes/save', function(r){
				

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

    $('#tipo').on('change', function(){
        if(this.value == "VIRTUAL"){
            $('.typeVirtual').show();
            $('.typePDF').hide();
        }else{
            $('.typePDF').show();
            $('.typeVirtual').hide();
        }
    }).trigger('change')

	$('#tipo_puntaje').bind('change', function(){
		if(this.value == 'GENERAL' && $('#tipo').val() == "VIRTUAL"){
			$('#puntosCalificacionGeneral').show();
		}else{
			$('#puntosCalificacionGeneral').hide();
		}
	});

	$('#penalizar_incorrecta').bind('change', function(){
		if(this.value == 'SI' && $('#tipo').val() == "VIRTUAL"){
			$('#puntosPenalizacionIncorrecta').show();
		}else{
			$('#puntosPenalizacionIncorrecta').hide();
		}
	});

	$('#tipo_puntaje').trigger('change');
	$('#penalizar_incorrecta').trigger('change');

	//$('.tip').tooltip();

	/* $('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true});
	$('.hora').timepicker(); */

    
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Asignaturas / Exámenes</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Asignaturas / Exámenes</a></li>
		<li class="active">Registro de Examen</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formAsignatura_Examen" data-target="#formAsignatura_Examen" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ asignatura_examen.is_new_record() ? "Registrar" : "Editar" }}Examen</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.asignatura_id }}
			

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Titulo <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.titulo }}</div>
				</div>

                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Tipo</label>
				    <div class="col-sm-4 col-lg-2">{{ form.tipo|raw }}</div>
				</div>

                <div class="form-group form-group-sm typePDF">
				    <label class="col-sm-4 control-label" for="">Archivo PDF</label>
				    <div class="col-sm-4 col-lg-2">{{ form.archivo_pdf|raw }}</div>
				</div>


				<div class="form-group form-group-sm typeVirtual">
				    <label class="col-sm-4 control-label" for="">Calificación</label>
				    <div class="col-sm-4 col-lg-2">{{ form.tipo_puntaje|raw }}</div>
				</div>
				<div class="form-group form-group-sm typeVirtual" id="puntosCalificacionGeneral">
				    <label class="col-sm-4 control-label" for="">Puntos</label>
				    <div class="col-sm-4 col-lg-2">{{ form.puntos_correcta|raw }}</div>
				</div>
				<div class="form-group form-group-sm typeVirtual">
				    <label class="col-sm-4 control-label" for="">Penalizar Incorrecta</label>
				    <div class="col-sm-4 col-lg-2">{{ form.penalizar_incorrecta|raw }}</div>
				</div>
			
				<div class="form-group form-group-sm typeVirtual" id="puntosPenalizacionIncorrecta">
				    <label class="col-sm-4 control-label" for=""></label>
				    <div class="col-sm-4 col-lg-2">{{ form.penalizacion_incorrecta|raw }}</div>
				</div>
				<div class="form-group form-group-sm typeVirtual">
				    <label class="col-sm-4 control-label" for="">Tiempo <span class="text-danger">*</span></label>
				    <div class="col-sm-4 col-lg-2">{{ form.tiempo }}</div>
				</div>
				<div class="form-group form-group-sm typeVirtual">
				    <label class="col-sm-4 control-label" for="">Intentos</label>
				    <div class="col-sm-4 col-lg-2">{{ form.intentos }}</div>
				</div>

				<div class="form-group form-group-sm typeVirtual">
				    <label class="col-sm-4 control-label" for="">Bimestre</label>
				    <div class="col-sm-4 col-lg-2">{{ form.ciclo }}</div>
				</div>

				<div class="form-group form-group-sm typeVirtual">
				    <label class="col-sm-4 control-label" for="">Orden de Preguntas</label>
				    <div class="col-sm-4 col-lg-3 col-xs-12">{{ form.orden_preguntas }}</div>
				</div>

				<!-- <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Desde (Fecha / Hora)</label>
				    <div class="col-sm-3 col-lg-2 col-xs-6">{{ form.fecha_desde }}</div>
				    <div class="col-sm-3 col-lg-2 col-xs-6">{{ form.hora_desde }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Hasta (Fecha / Hora) <span class="text-danger">*</span></label>
				    <div class="col-sm-3 col-lg-2 col-xs-6">{{ form.fecha_hasta }}</div>
				    <div class="col-sm-3 col-lg-2 col-xs-6">{{ form.hora_hasta }}</div>
				</div> -->
				

				<div class="form-group form-group-sm typeVirtual">
				    <label class="col-sm-4 control-label" for="">Preguntas Max. <span class="text-danger">*</span></label>
				    <div class="col-sm-4 col-lg-2 col-xs-12">
				    	{{ form.preguntas_max }}
				    </div>
				</div>
                
                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Estado <span class="text-danger">*</span></label>
				    <div class="col-sm-4 col-lg-2 col-xs-12">
				    	{{ form.estado }}
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
