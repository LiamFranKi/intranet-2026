{% extends main_template %}
{% block main_content %}
<script>
$(function(){

	setMenuActive('pagos');
	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})

	var currentAnio = '{{ anio }}';
	$('.currentAnio').val('{{ anio }}');
    $('.currentAnio').bind('blur', function(){
    	if(this.value != currentAnio){
    		zk.goToUrl('/importar_exportar?anio=' + this.value);
    	}
    });


    $('#fexport, #fexport_single').submit(function(e){
		e.preventDefault();
		zk.printDocument('/importar_exportar/do_bcp?' + $(this).serialize());
	});

	$('#fimport').submit(function(e){
		e.preventDefault();
		
		$(this).sendForm('/importar_exportar/importar_bcp', function(r){
			switch(parseInt(r[0])){
				case 1:
					zk.pageAlert({message: 'Datos importados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				break;

				case -1:
					zk.pageAlert({message: 'No se pudo subir el archivo.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
				break;

				case -2:
					zk.pageAlert({message: 'Se cargó el archivo, pero con algunos errores: <br /> - No se encontraron las matrículas para los siguientes DNI: <br />' + r.errores.join('<br />') + '<br /><br />Tenga en cuenta que el historial solo se guardará cuando solucione todos los errores.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating', timer: 0});
				break;
			}
		})

	});

	$('#fimport_operacion').submit(function(e){
		e.preventDefault();
		$(this).sendForm('/importar_exportar/importar_operacion', function(r){
			switch(parseInt(r[0])){
				case 1:
					zk.pageAlert({message: 'Datos importados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				break;

				case -1:
					zk.pageAlert({message: 'No se pudo subir el archivo.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
				break;

				case -2:
					zk.pageAlert({message: 'Se cargó el archivo, pero con algunos errores: <br /> - No se encontraron las matrículas para los siguientes DNI: <br />' + r.errores.join('<br />') + '<br /><br />Tenga en cuenta que el historial solo se guardará cuando solucione todos los errores.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating', timer: 0});
				break;
			}
		});

	});


	$('#matricula_id').ajaxChosen({
        type: 'GET',
        url: '/info/matricula?anio={{ anio }}',
        dataType: 'json',
        keepTypingMsg: 'Continue Escribiendo...',
        lookingForMsg: 'Buscando ',
		placeholder: "Seleccione una matrícula"
    }, function (data) {
        var results = [];

        $.each(data, function (i, val) {
            results.push({ value: val.value, text: val.text });
        });

        return results;
    });
});

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Importar / Exportar</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/pagos">Pagos</a></li>
		<li class="active">Importar / Exportar</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Importar / Exportar</h3>
		</div>
		<div class="panel-body">
			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading">
					<h3 class="panel-title">Exportar Registro</h3>
				</div>
				<div class="panel-body">
					<form id="fexport" class="form-inline" role="form">
						<table class="special" style="width: 100%">
							<thead>
								<tr>
									<th>Año</th>
									<th>Fecha</th>
									<th>Pago</th>
									<th>Grupo</th>
									<th></th>
									<th>Alumnos</th>
									<th></th>
							
								</tr>
								<tr>
									<td class="center">
										<input type="text" style="width: 50px" class="input-sm form-control currentAnio" name="anio" />
									</td>
									<td class="center">
										<input type="text" style="width: 100px" class="calendar input-sm form-control" value="{{ date()|date('Y-m-d') }}" name="fecha" />
									</td>
									<td class="center">
										<select style="width: 100px" name="nro_pago" id="nro_pago" class="input-sm form-control">
											<option value="-1">-- TODOS --</option>
											<option value="0">MATRÍCULA</option>
											<option value="20">MATRÍCULA Y AGENDA</option>
											<option value="21">RESERVA DE VACANTE</option>
											<option value="22">CANCELACION DE MATRICULA</option>

											{% for tipo in COLEGIO.getOptionsNroPago() %}
											<option value="{{ _key }}">MENSUALIDAD {{ tipo|upper }}</option>
											{% endfor %}

											{% for tipo in COLEGIO.getOptionsNroPago() %}
											<option value="{{ _key + 50 }}">COMEDOR {{ tipo|upper }}</option>
											{% endfor %}
										</select>
									</td>
									<td class="center">
										<select name="grupo_id" style="width: 100px" id="export_grupo_id" class="input-sm form-control" style="width: 200px">
											<option value="-1">-- TODOS --</option>
											{% for sede in COLEGIO.sedes %}
											<optgroup label="{{ sede.nombre }}">
												{% for grupo in COLEGIO.getGrupos(get.anio, sede.id) %}
												<option value="{{ grupo.id }}">{{ grupo.getNombre() }}</option>
												{% endfor %}
											</optgroup>
											{% endfor %}
											

										</select>
									</td>
									<td class="center">
										<select name="tipo" id="tipo" class="input-sm form-control">
											<option value="A">AGREGAR</option>
											<option value="M">MODIFICAR</option>
											
											<option value="E">ELIMINAR</option>
										</select>
									</td>
									
									<td class="center">
										<select name="tipo_alumno" id="tipo_alumno" class="input-sm form-control">
											<option value="TODOS">TODOS</option>
											<option value="NUEVOS">NUEVOS</option>
											<option value="ANTIGUOS">ANTIGUOS</option>
										</select>
									</td>
									<td>
										<select name="tipo_documento" id="tipo_documento" class="input-sm form-control">
											<option value="A">A</option>
											<option value="R">R</option>
										</select>
									</td>
								
								</tr>
							</thead>
						</table>

						<button class="btn btn-primary btn-block">Exportar Registro</button>
					</form>
				</div>
			</div>

			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading">
					<h3 class="panel-title">Exportar Registro - Individual</h3>
				</div>
				<div class="panel-body" style="">
					<form id="fexport_single" class="form-inline" role="form">
						<table class="special" style="width: 100%">
							<thead>
								<tr>
									<th>Año</th>
									<th>Fecha</th>
									<th>Pago</th>
									<th>Alumnos</th>
									<th></th>
									<th></th>
							
								</tr>
								<tr>
									<td class="center">
										<input type="text" style="width: 50px" class="input-sm form-control currentAnio" name="anio" />
									</td>
									<td class="center">
										<input type="text" style="width: 100px" class="calendar input-sm form-control" value="{{ date()|date('Y-m-d') }}" name="fecha" />
									</td>
									<td class="center">
										<select style="width: 100px" name="nro_pago" id="nro_pago" class="input-sm form-control">
											<option value="-1">-- TODOS --</option>
											<option value="0">MATRÍCULA</option>
											<option value="20">MATRÍCULA Y AGENDA</option>
											<option value="21">RESERVA DE VACANTE</option>
											<option value="22">CANCELACION DE MATRICULA</option>

											{% for tipo in COLEGIO.getOptionsNroPago() %}
											<option value="{{ _key }}">MENSUALIDAD {{ tipo|upper }}</option>
											{% endfor %}

											{% for tipo in COLEGIO.getOptionsNroPago() %}
											<option value="{{ _key + 50 }}">COMEDOR {{ tipo|upper }}</option>
											{% endfor %}
										</select>
									</td>
									<td class="center">
									<select class="required" name="matricula_id[]" id="matricula_id" multiple data-placeholder="Seleccione alumnos" style="width: 300px"></select>
									</td>
									<td class="center">
										<select name="tipo" id="tipo" class="input-sm form-control">
											<option value="A">AGREGAR</option>
											<option value="M">MODIFICAR</option>
											
											<option value="E">ELIMINAR</option>
										</select>
									</td>
									
									<td>
										<select name="tipo_documento" id="tipo_documento" class="input-sm form-control">
											<option value="A">A</option>
											<option value="R">R</option>
										</select>
									</td>
								
								</tr>
							</thead>
						</table>

						<button class="btn btn-primary btn-block">Exportar Registro</button>
					</form>
				</div>
			</div>

			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading">
					<h3 class="panel-title">Importar Registro</h3>
				</div>
				<div class="panel-body">
					<form id="fimport" class="form-inline" role="form">
						<table class="special">
							<tr>
								<th>Archivo</th>

								<th></th>
							</tr>
							<tr>
								<td class="text-center">
									<input type="file" name="archivo" />
								</td>
                                
								<td class="text-center">
									<button class="btn-primary btn">Importar Archivo</button>
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>

			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading">
					<h3 class="panel-title">Importar N° de Operación</h3>
				</div>
				<div class="panel-body">
					<form id="fimport_operacion" class="form-inline" role="form">
						<table class="special">
							<tr>
								<th>Archivo</th>
                                <th>Fila Inicio</th>
								<th></th>
							</tr>
							<tr>
								<td>
									<input type="file" name="archivo" />
								</td>
                                <td class="text-center">
                                    <input type="number" min="1" class="form-control" name="start_row" value="5">
                                </td>
								<td class="text-center">
									<button class="btn-primary btn">Importar Archivo</button>
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

{% endblock %}
