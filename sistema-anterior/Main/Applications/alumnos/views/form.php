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

			$(_form).sendForm('/alumnos/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/alumnos');
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

	// Nº Hermanos
	$('#nro_hermanos').on('change', function(){
		total = parseInt(this.value);
		val = '{{ alumno.lugar_hermanos }}';
		$('#lugar_hermanos').find('option').remove();
		for(i=1;i<=total + 1;i++){
			$('#lugar_hermanos').append('<option value="'+i+'" '+ (val == i ? 'selected' : '') +'>'+i+'</option>');
		}
		//$('#lugar_hermanos').val('{{ alumno.lugar_hermanos }}');
	});
	$('#nro_hermanos').trigger('change');

	zk.filterCountryDPD({
		pais: {
			field: '#pais_nacimiento_id'
		},
		departamento: {
			field: '#departamento_nacimiento_id',
			value: '{{ alumno.departamento_nacimiento_id }}'
		},
		provincia: {
			field: '#provincia_nacimiento_id',
			value: '{{ alumno.provincia_nacimiento_id }}'
		},
		distrito: {
			field: '#distrito_nacimiento_id',
			value: '{{ alumno.distrito_nacimiento_id }}'
		}
	});

	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})
});
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
						<h3 class="panel-title">Información General</h3>
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
						    <label class="col-sm-4 control-label" for="">Estado Civil <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.estado_civil }}</div>
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
						    <label class="col-sm-4 control-label" for="">Foto</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">
						    	<p><img src="{{ alumno.getFoto() }}" style="width: 120px" /></p>
						    	{{ form.foto }}
							</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Lengua Materna</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.lengua_materna }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Segunda Lengua</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.segunda_lengua }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">N° Hermanos </label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.nro_hermanos }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Lugar que ocupa</label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.lugar_hermanos }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Religión </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.religion }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Discapacidad </label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.discapacidad }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Fecha / Lugar de Nacimiento</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha de Nacimiento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_nacimiento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Pais </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.pais_nacimiento_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Departamento</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.departamento_nacimiento_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Provincia </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.provincia_nacimiento_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Distrito </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.distrito_nacimiento_id }}</div>
						</div>						
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Información Adicional</h3>
					</div>
					<div class="panel-body">
						
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha de Inscripcion <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.fecha_inscripcion }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Observaciones</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.observaciones }}</div>
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
