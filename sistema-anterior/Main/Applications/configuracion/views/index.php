<script>


$(function(){
	$('#fconfiguracion').niftyOverlay();
	$('#fconfiguracion').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/configuracion/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
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

	$('.tip').tooltip({
		placement: 'top',
		trigger: 'focus',
		html: true
	});

	$('#ciclo_pensiones').on('change', function(){
		if(this.value == 0){
			$('#inicio_pensiones').parent().parent().show();
		}else{
			$('#inicio_pensiones').parent().parent().hide();
		}
	});

	$('#ciclo_notas').on('change', function(){
		if(this.value == 0){
			$('#inicio_notas').parent().parent().show();
			$('#rangos_ciclos_notas').hide();
		}else{
			$('#inicio_notas').parent().parent().hide();
			$('#rangos_ciclos_notas').show();
		}

		createRangeCicloNotas();
	});

	$('#total_notas').on('blur', function(){
		createRangeCicloNotas();
	});

	$('#ciclo_pensiones').trigger('change');
	$('#ciclo_notas').trigger('change');

	//$('.datepicker').datetimepicker({pickTime: false, language: 'es', format: 'YYYY-MM-DD'});
	$('.datepicker').datepicker({format: 'yyyy-mm-dd', autoclose: true})
});


function createRangeCicloNotas(){
	var rangosCicloNotas = {{ COLEGIO.getRangosCiclosNotas()|json_encode }};
	total = parseInt($('#total_notas').val());
	label = $('#ciclo_notas').find('option:selected').html();
	$('#lista_rangos_ciclos_notas').find('tr').remove();
	data = '';
	for(i=1;i<=total;i++){
		if(rangosCicloNotas[i] == undefined){
			rangosCicloNotas[i] = {inicio: '', final: ''}
		}
		data += '<tr>';
		data += '<th style="width: 150px">'+ label +' '+ i +'</th>';
		data += '<td class="center"><input type="text" name="rangos_ciclos_notas['+ i +'][inicio]" class="form-control input-sm" placeholder="dd-mm" style="width: 90px" value="'+ rangosCicloNotas[i].inicio +'"  /></td>';
		data += '<td class="center"><input type="text" name="rangos_ciclos_notas['+ i +'][final]" class="form-control input-sm" placeholder="dd-mm" style="width: 90px" value="'+ rangosCicloNotas[i].final +'"  /></td>';
		data += '</tr>';
	}

	$('#lista_rangos_ciclos_notas').append(data);
}

var indexRangoMensaje = {{ COLEGIO.getRangosMensajes()|length }};
function agregarRangoMensaje(){
		data = '';
		data += '<tr>';
		data += '<td class="center"><input type="text" name="rangos_mensajes['+ indexRangoMensaje +'][rango]" class="form-control input-sm" placeholder="" style="width: 90px" value=""  /></td>';

		data += '<td class="center"><input type="text" name="rangos_mensajes['+ indexRangoMensaje +'][mensaje]" class="form-control input-sm" placeholder="Mensaje" style="width: 300px" value=""  /></td>';
		data += '<td class="center"><button class="btn btn-default" type="button" onclick="$(this).parent().parent().remove()">{{ icon('delete') }}</button></td>';
		data += '</tr>';

	$('#lista_rangos_mensajes').append(data);
	++indexRangoMensaje;
}

var indexRangoNotaPrimaria = {{ COLEGIO.getRangosNotasPrimaria()|length }};
function agregarRangoNotaPrimaria(){
		data = '';
		data += '<tr>';
		data += '<td class="center"><input type="text" name="rangos_notas_primaria['+ indexRangoMensaje +'][rango]" class="form-control input-sm" placeholder="" style="width: 90px" value=""  /></td>';

		data += '<td class="center"><input type="text" name="rangos_notas_primaria['+ indexRangoMensaje +'][letra]" class="form-control input-sm" placeholder="Letra" style="width: 300px" value=""  /></td>';
		data += '<td class="center"><button class="btn btn-default" type="button" onclick="$(this).parent().parent().remove()">{{ icon('delete') }}</button></td>';
		data += '</tr>';

	$('#lista_rangos_notas_primaria').append(data);
	++indexRangoMensaje;
}

function reiniciarAccesos(){
	if(confirm('¿Está seguro de reiniciar los datos de acceso para ALUMNOS y APODERADOS?')){
		$('#fconfiguracion').niftyOverlay('show');
		$.post('/configuracion/reiniciar_accesos', {}, function(r){
			$('#fconfiguracion').niftyOverlay('hide');
			zk.pageAlert({message: 'Datos reiniciados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
		}, 'json')
	}
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Configuración</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		
		<li class="active">Configuración General</li>
	</ol>
</div>

<div id="page-content">
<form id="fconfiguracion" class="form-horizontal" data-target="#fconfiguracion" data-toggle="overlay">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Configuración General</h3>
		</div>
		<div class="panel-body">
			
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">INFORMACIÓN GENERAL</h3></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Título Intranet</label>
						    <div class="col-sm-6 col-lg-4">{{ form.titulo_intranet }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Código Modular</label>
						    <div class="col-sm-6 col-lg-2">{{ form.codigo_modular }}</div>
						</div>
						
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Resolución de Creación</label>
						    <div class="col-sm-6 col-lg-2">	{{ form.resolucion_creacion }}</div>
						</div>
						

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fondo - Login</label>
						    <div class="col-sm-6 col-lg-4">{{ form.login_fondo }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Texto Intranet</label>
						    <div class="col-sm-6 col-lg-4">
						    	<input type="text" name="texto_intranet" value="{{ Config_get('texto_intranet') }}" class="form-control">
						    </div>
						</div>

                        <div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Título Formulario Matrícula</label>
						    <div class="col-sm-6 col-lg-4">
						    	<input type="text" name="titulo_formulario_matricula" value="{{ Config_get('titulo_formulario_matricula') }}" class="form-control">
						    </div>
						</div>
					</div>
				</div>
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">INFORMACIÓN RECAUDO</h3></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">N° de Sucursal</label>
						    <div class="col-sm-6 col-lg-4">
						    	<input type="text" name="recaudo_nro_sucursal" value="{{ Config_get('recaudo_nro_sucursal') }}" class="form-control" />
						    </div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">N° de Cuenta</label>
						    <div class="col-sm-6 col-lg-4">
						    	<input type="text" name="recaudo_nro_cuenta" value="{{ Config_get('recaudo_nro_cuenta') }}" class="form-control" />
						    </div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Razón Social</label>
						    <div class="col-sm-6 col-lg-4">
						    	<input type="text" name="recaudo_razon_social" value="{{ Config_get('recaudo_razon_social') }}" class="form-control" />
						    </div>
						</div>
					</div>
				</div>
				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">DATOS - UGEL / DRE</h3></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Código (UGEL - DRE)</label>
						    <div class="col-sm-6 col-lg-2">{{ form.ugel_codigo }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Nombre (UGEL - DRE)</label>
						    <div class="col-sm-6 col-lg-4">{{ form.ugel_nombre }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">DISEÑO LIBRETA DE NOTAS</h3></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Logo</label>
						    <div class="col-sm-6 col-lg-4">
						    	{% if Config_get('libreta_logo') %}
						    	<p><img src="/Static/Image/Fondos/{{ Config_get('libreta_logo') }}" style="max-height: 70px"  alt=""></p>
						    	{% endif %}
						    	<input type="file" name="libreta_logo" class="form-control" accept=".jpg" />
                                <small class="text-bold">Solo archivos .jpg</small>
						    	
						    </div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Fondo</label>
						    <div class="col-sm-6 col-lg-4">
						    	{% if Config_get('libreta_fondo') %}
						    	<p><img src="/Static/Image/Fondos/{{ Config_get('libreta_fondo') }}" style="max-height: 70px"  alt=""></p>
						    	{% endif %}
						    	<input type="file" name="libreta_fondo" class="form-control" accept=".jpg" />
                                <small class="text-bold">Solo archivos .jpg</small>
						    </div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">AÑO ACADÉMICO</h3></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Año Activo</label>
						    <div class="col-sm-4 col-lg-2">
						    	{{ form.anio_activo }}
						    </div>
						</div>

                        <div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Año Matrículas</label>
						    <div class="col-sm-4 col-lg-2">
						    	{{ form.anio_matriculas }}
						    </div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">PAGOS</h3></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Ciclo de Pensiones</label>
						    <div class="col-sm-6 col-lg-2">{{ form.ciclo_pensiones }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Inicio de Cobros</label>
						    <div class="col-sm-6 col-lg-2">{{ form.inicio_pensiones }}</div>
						</div>
						
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Total Pensiones</label>
						    <div class="col-sm-6 col-lg-2">{{ form.total_pensiones }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Moneda</label>
						    <div class="col-sm-6 col-lg-2">{{ form.moneda }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Monto Adicional S/</label>
						    <div class="col-sm-6 col-lg-2">{{ form.monto_adicional }}</div>
						</div>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Bloquear Deudores</label>
						    <div class="col-sm-6 col-lg-2">{{ form.bloquear_deudores }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">NOTAS</h3></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Ciclo de Notas</label>
						    <div class="col-sm-6 col-lg-2">{{ form.ciclo_notas }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Inicio de Registro</label>
						    <div class="col-sm-6 col-lg-2">{{ form.inicio_notas }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Total Ciclos</label>
						    <div class="col-sm-6 col-lg-2">{{ form.total_notas }}</div>
						</div>
						
						
						
						<div class="form-group form-group-sm" id="rangos_ciclos_notas">
							<label for="" class="col-sm-4 control-label"></label>
							<div class="col-sm-8">
								<div >

									<table class="special" >
										<thead>
											<tr>
												<td></td>
												<th>Inicio</th>
												<th>Final</th>
											</tr>
										</thead>
										<tbody id="lista_rangos_ciclos_notas"></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">APRECIACIONES</h3></div>
					<div class="panel-body">
						<p><button class="btn btn-default" type="button" onclick="agregarRangoMensaje()">{{ icon('add') }} Agregar Nuevo</button></p>
						<div class="form-group form-group-sm" id="rangos_mensajes">

							<div class="col-sm-12">
								<div>

									<table class="special" >
										<thead>
											<tr>

												<th>Rango / Letra</th>
												<th>Apreciación</th>
											</tr>
										</thead>
										<tbody id="lista_rangos_mensajes">
											{% for i in COLEGIO.getRangosMensajes() %}
											<tr>
												<td class="center"><input type="text" name="rangos_mensajes[{{ _key }}][rango]" class="form-control input-sm" placeholder="" style="width: 90px" value="{{ i.rango }}"  /></td>
												<td class="center"><input type="text" name="rangos_mensajes[{{ _key }}][mensaje]" class="form-control input-sm" placeholder="Mensaje" style="width: 300px" value="{{ i.mensaje }}"  /></td>
												<td class="center"><button class="btn btn-default" type="button" onclick="$(this).parent().parent().remove()">{{ icon('delete') }}</button></td>
											</tr>
											{% endfor %}
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">CONVERSIÓN A NOTAS A LETRAS - PRIMARIA</h3></div>
					<div class="panel-body">
						<p><button class="btn btn-default" type="button" onclick="agregarRangoNotaPrimaria()">{{ icon('add') }} Agregar Nuevo</button></p>
						<div class="form-group form-group-sm" id="rangos_mensajes">

							<div class="col-sm-12">
								<div>

									<table class="special" >
										<thead>
											<tr>

												<th>Rango</th>
												<th>Letra</th>
											</tr>
										</thead>
										<tbody id="lista_rangos_notas_primaria">
											{% for i in COLEGIO.getRangosNotasPrimaria() %}
											<tr>
												<td class="center"><input type="text" name="rangos_notas_primaria[{{ _key }}][rango]" class="form-control input-sm" placeholder="" style="width: 90px" value="{{ i.rango }}"  /></td>
												<td class="center"><input type="text" name="rangos_notas_primaria[{{ _key }}][letra]" class="form-control input-sm" placeholder="Letra" style="width: 300px" value="{{ i.letra }}"  /></td>
												<td class="center"><button class="btn btn-default" type="button" onclick="$(this).parent().parent().remove()">{{ icon('delete') }}</button></td>
											</tr>
											{% endfor %}
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>


				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">VENCIMIENTO DE PENSIONES</h3></div>
					<div class="panel-body">
						<table class="special">
							<tr>
								<th>Matrícula</th>
								<td class="center"><input type="text" class="form-control input-sm" style="width: 70px" name="pensiones_vencimiento[-1]" value="{{ COLEGIO.getVencimientoPension(-1) }}" placeholder="dd-mm" /></td>
							</tr>
							{% for pago in COLEGIO.getOptionsNroPago() %}
							<tr>
								<th>{{ pago }}</th>
								<td class="center"><input type="text" class="form-control input-sm" style="width: 70px" name="pensiones_vencimiento[{{ _key }}]" value="{{ COLEGIO.getVencimientoPension(_key) }}" /></td>
							</tr>
							{% endfor %}
						</table>

						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Días de Tolerancia</label>
						    <div class="col-sm-4 col-lg-2">{{ form.dias_tolerancia }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">COMISIÓN PAGO CON TARJETA</h3 class="panel-title"></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tarjeta de Débito %</label>
						    <div class="col-sm-4 col-lg-2">{{ form.comision_tarjeta_debito }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Tarjeta de Crébito %</label>
						    <div class="col-sm-4 col-lg-2">{{ form.comision_tarjeta_credito }}</div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">EXÁMENES BLOQUES</h3 class="panel-title"></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Contraseña</label>
						    <div class="col-sm-4 col-lg-3">{{ form.clave_bloques }}</div>
						</div>
						
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">FACTURACIÓN</h3></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">RUC</label>
						    <div class="col-sm-4 col-lg-2">{{ form.ruc }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Razón Social</label>
						    <div class="col-sm-6 col-lg-4">{{ form.razon_social }}</div>
						</div>
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Dirección</label>
						    <div class="col-sm-6 col-lg-6">{{ form.direccion }}</div>
						</div>

                        <div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Link Consulta Boletas</label>
						    <div class="col-sm-6 col-lg-6">{{ form.link_consulta_facturas }}</div>
						</div>


                        <div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Logo Boleta</label>
						    <div class="col-sm-6 col-lg-4">
						    	{% if Config_get('boleta_logo') %}
						    	<p><img src="/Static/Archivos/{{ Config_get('boleta_logo') }}" style="max-height: 70px"  alt=""></p>
						    	{% endif %}
						    	<input type="file" name="boleta_logo" class="form-control" accept=".jpg" />
                                <small class="text-bold">Solo archivos .jpg</small>
						    	
						    </div>
						</div>
					</div>
				</div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">MATRÍCULA ONLINE</h3></div>
					<div class="panel-body">
						<div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Correos Notificación</label>
						    <div class="col-sm-6 col-lg-6">
								<textarea name="email_notificacion_matricula_online" class="form-control">{{ Config_get('email_notificacion_matricula_online') }}</textarea>
							</div>
						</div>

                        <div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Remitente Emails</label>
						    <div class="col-sm-6 col-lg-4">
						    	<input type="text" name="remitente_emails" value="{{ Config_get('remitente_emails') }}" class="form-control" />
						    </div>
						</div>

                        <div class="form-group form-group-sm">
						    <label class="col-sm-4 control-label" for="">Mensaje Matrícula Apoderado</label>
						    <div class="col-sm-6 col-lg-6">
								<textarea name="email_matricula_apoderado" class="form-control" rows="10">{{ Config_get('email_matricula_apoderado') }}</textarea>
							</div>
						</div>
						<div class="form-group form-group-sm">
                            <label class="col-sm-4 control-label" for="">Habilitar Formulario</label>
                            <div class="col-sm-6 col-lg-6">
                                <select name="enable_enrollment_form" class="form-control">
                                    <option value="SI" {{ Config_get('enable_enrollment_form') == 'SI' ? 'selected' : '' }}>SI</option>
                                    <option value="NO" {{ Config_get('enable_enrollment_form') == 'NO' ? 'selected' : '' }}>NO</option>
                                </select>
                            </div>
                        </div>
					</div>
				</div>

                <div class="panel panel-primary panel-bordered">
                    <div class="panel-heading"><h3 class="panel-title">OTROS</h3></div>
                    <div class="panel-body">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-4 control-label" for="">Mostrar Ventana Cumpleaños</label>
                            <div class="col-sm-6 col-lg-6">
                                <select name="show_birthday_window" class="form-control">
                                    <option value="SI" {{ Config_get('show_birthday_window') == 'SI' ? 'selected' : '' }}>SI</option>
                                    <option value="NO" {{ Config_get('show_birthday_window') == 'NO' ? 'selected' : '' }}>NO</option>
                                </select>
                            </div>
                        </div>
    
    
                    </div>
                </div>

				<div class="panel panel-primary panel-bordered">
					<div class="panel-heading"><h3 class="panel-title">REINICIAR ACCESOS</h3></div>
					<div class="panel-body">
						<div class="text-center">
							<button class="btn btn-warning" type="button" onclick="reiniciarAccesos()">Reiniciar Datos de Acceso</button>
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
