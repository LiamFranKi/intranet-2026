{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formPersonal').niftyOverlay();
	$('#formPersonal').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/personal/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/personal');
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

	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})

	zk.filterCountryDPD({
		pais: {
			field: '#pais_nacimiento_id'
		},
		departamento: {
			field: '#departamento_nacimiento_id',
			value: '{{ personal.departamento_nacimiento_id }}'
		},
		provincia: {
			field: '#provincia_nacimiento_id',
			value: '{{ personal.provincia_nacimiento_id }}'
		},
		distrito: {
			field: '#distrito_nacimiento_id',
			value: '{{ personal.distrito_nacimiento_id }}'
		}
	});

	zk.filterCountryDPD({
		pais: {
			field: '#domicilio_pais_id'
		},
		departamento: {
			field: '#domicilio_departamento_id',
			value: '{{ personal.domicilio_departamento_id }}'
		},
		provincia: {
			field: '#domicilio_provincia_id',
			value: '{{ personal.domicilio_provincia_id }}'
		},
		distrito: {
			field: '#domicilio_distrito_id',
			value: '{{ personal.domicilio_distrito_id }}'
		}
	});
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Personal</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/personal">Personal</a></li>
		<li class="active">Registro de Personal</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" id="formPersonal" data-target="#formPersonal" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ personal.is_new_record() ? "Registrar" : "Editar" }} Personal</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Datos Personales</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Nombres <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.nombres }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Apellidos <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.apellidos }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo de Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.tipo_documento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">N° Documento <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.nro_documento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Sexo <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.sexo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Estado Civil <small class="text-danger">*</small></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.estado_civil }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Foto </label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">
						    	<p><img src="{{ personal.getFoto() }}" style="width: 120px" /></p>
						    	{{ form.foto }}
							</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Datos de Nacimiento</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha de Nacimiento</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_nacimiento }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Pais</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.pais_nacimiento_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Departamento</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.departamento_nacimiento_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Provincia></label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.provincia_nacimiento_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Distrito</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.distrito_nacimiento_id }}</div>
						</div>
					</div>
				</div>
				
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Domicilio - Información de Contacto</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">País</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.domicilio_pais_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Departamento</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.domicilio_departamento_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Provincia</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.domicilio_provincia_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Distrito</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.domicilio_distrito_id }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Direccion</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.direccion }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Teléfono Fijo</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.telefono_fijo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Celular</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.telefono_celular }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Linea Celular</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.linea_celular }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Email</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.email }}</div>
						</div>
					</div>
				</div>

				
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Información Laboral</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fecha de ingreso</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.fecha_ingreso }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Cargo</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.cargo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tipo de Contrato</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.tipo_contrato }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Observaciones</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.observaciones }}</div>
						</div>

					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Formación Profesional</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Grado de Instruccion</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.grado_instruccion }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Profesión</label>
						    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.profesion }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading">
						<h3 class="panel-title">Asistencia</h3>
					</div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Entrada</label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.hora_entrada }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Salida</label>
						    <div class="col-sm-6 col-xs-12 col-lg-2">{{ form.hora_salida }}</div>
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
