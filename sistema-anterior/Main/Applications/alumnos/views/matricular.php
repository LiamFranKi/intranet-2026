{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formAlumno').niftyOverlay();
	$('#formAlumno').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/alumnos/save_matricular', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/alumnos');
					break;
					
					case 0:
						zk.pageAlert({message: 'No se pudieron guardar los datos', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
					break;

					case 0:
						zk.pageAlert({message: 'El grupo elegido no existe', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
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


	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})
	agregarApoderado();

	$('#formAlumno').changeGradoOptions({
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
	$('#costo_id').trigger('change');
});

function agregarApoderado(){
	var data = $('#templateApoderado').html();
	$('#allApoderados').append(data);

	$('#formAlumno')
                .bootstrapValidator('addField', $('#formAlumno').find('[name^="apoderado_"]'))
}

function getApoderado(sender){
	var tipo = $(sender).parent().parent().parent().find('[name="apoderado_tipo_documento[]"]').val();
	var nro = $(sender).parent().parent().parent().find('[name="apoderado_nro_documento[]"]').val();
	console.log(tipo)
	$.post('/info/apoderado', {tipo_documento: tipo, nro_documento: nro}, function(r){
			if(r){
				for(i in r){
					if(i != 'id'){
						$(sender).parent().parent().parent().find('[name="apoderado_'+i+'[]"]').val(r[i]);
					}
				}
			}
		} , 'json');
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Alumnos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/alumnos">Alumnos</a></li>
		<li class="active">Registro de Alumno</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formAlumno" data-target="#formAlumno" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ alumno.is_new_record() ? "Registrar" : "Editar" }} Alumno</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Datos del Alumno</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Codigo <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.codigo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo de Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.tipo_documento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">N° de Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.nro_documento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Apellido Paterno <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.apellido_paterno }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Apellido Materno <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.apellido_materno }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Nombres <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nombres }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Sexo <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.sexo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha de Nacimiento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.fecha_nacimiento }}</div>
						</div>
						

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Email</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.email }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Domicilio</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.domicilio }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Telefonos</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.telefonos }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha de Inscripcion <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.fecha_inscripcion }}</div>
						</div>

						
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">PADRE DE FAMILIA, APODERADO</h3>
						<div class="pull-right"></div>
					</div>
					<div class="panel-body">
						<button class="btn btn-default" onclick="agregarApoderado()" type="button">{{ icon('add') }} Agregar</button>
						<div id="allApoderados">
							
						</div>
					</div>
				</div>
				
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Datos de la Matrícula</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Sede</label>
						    <div class="col-sm-4 col-lg-3">{{ fm.sede_id }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Nivel</label>
						    <div class="col-sm-4 col-lg-3">{{ fm.nivel_id }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Grado</label>
						    <div class="col-sm-4 col-lg-3">{{ fm.grado }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Sección</label>
						    <div class="col-sm-4 col-lg-3">{{ fm.seccion }}</div>
						</div>
						
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Turno</label>
						    <div class="col-sm-4 col-lg-3">{{ fm.turno_id }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Año Académico</label>
						    <div class="col-sm-4 col-lg-3">{{ fm.anio }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Estado</label>
						    <div class="col-sm-4 col-lg-3">{{ fm.estado }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Costo</label>
						    <div class="col-sm-4 col-lg-4">{{ fm.costo_id }}</div>
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
						    <label class="col-sm-4 control-label" for="">Modalidad</label>
						    <div class="col-sm-4 col-lg-3">{{ fm.modalidad }}</div>
						</div>

						{% if matricula.is_new_record() %}
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Registrar Pago</label>
						    <div class="col-sm-6"><input type="checkbox" name="registrarMatricula" checked /></div>
						</div>
						{% endif %}
					</div>
				</div>
			</div>
		
			<div class="modal-footer">
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>

	<div id="templateApoderado" style="display: none">
		<div style="border-bottom: 1px solid #ccc; margin-bottom: 5px">
			<div class="form-group form-group-sm">
			    <label class="col-sm-4 control-label" for="">Tipo de Documento</label>
			    <div class="col-sm-4 col-lg-2">{{ fa.tipo_documento }}</div>
			</div>
			<div class="form-group form-group-sm">
			    <label class="col-sm-4 control-label" for="">N° de Documento</label>
			    <div class="col-sm-4 col-lg-2">{{ fa.nro_documento }}</div>
			</div>
			<div class="form-group form-group-sm">
			    <label class="col-sm-4 control-label" for="">Apellido Paterno</label>
			    <div class="col-sm-6 col-lg-4">{{ fa.apellido_paterno }}</div>
			</div>
			<div class="form-group form-group-sm">
			    <label class="col-sm-4 control-label" for="">Apellido Materno</label>
			    <div class="col-sm-6 col-lg-4">{{ fa.apellido_materno }}</div>
			</div>
			<div class="form-group form-group-sm">
			    <label class="col-sm-4 control-label" for="">Nombres</label>
			    <div class="col-sm-6 col-lg-4">{{ fa.nombres }}</div>
			</div>
		
			<div class="form-group form-group-sm">
			    <label class="col-sm-4 control-label" for="">Estado Civil</label>
			    <div class="col-sm-6 col-lg-2">{{ fa.estado_civil }}</div>
			</div>
			<div class="form-group form-group-sm">
			    <label class="col-sm-4 control-label" for="">Email</label>
			    <div class="col-sm-6 col-lg-4">{{ fa.email }}</div>
			</div>
			<div class="form-group form-group-sm">
			    <label class="col-sm-4 control-label" for="">Parentesco</label>
			    <div class="col-sm-6 col-lg-2">{{ fa.parentesco }}</div>
			</div>
		<div>
	</div>
</div>
{% endblock %}
