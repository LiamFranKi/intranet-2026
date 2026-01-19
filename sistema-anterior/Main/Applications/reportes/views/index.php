{% include '_handler.php' %}

<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Reportes</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/reportes">Reportes</a></li>
		
	</ol>
</div>
<div id="page-content">

	<div class="panel">
		<div class="panel-body">
			<!--
			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Lista de Alumnos - Por Costo</h3>
				</div>
				<div class="panel-body">
					<div class="alert alert-info"><b>Alumnos Becados, Semibecados: </b> Registre un costo que describa los montos que pagan los alumnos, luego seleccione el costo y podrá ver el reporte.</div>
				    <form class="form-inline center reporte" data-handler="alumnos_costo">
						
						<select name="costo_id" class="form-control" style="width: 300px">
							{% for costo in costos %}
							<option value="{{ costo.id }}">{{ costo.descripcion|upper }} - Matrícula: {{ costo.matricula|number_format(2) }} - Pensión: {{ costo.pension|number_format(2) }}</option>
							{% endfor %}
						</select>
						{{ FORM.nivel_id }}
				        {{ FORM.anio }}
					</form>
				</div>
			</div>
			-->
			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Lista de Alumnos Retirados</h3>
				</div>
				<div class="panel-body">
					<div class="alert alert-info">* Seleccione el año y el nivel del que desea ver los alumnos retirados.</div>
					<form class="form form-inline center reporte" data-handler="alumnos_retirados">
						{{ FORM.nivel_id }}
						{{ FORM.anio }}

					</form>
				</div>
			</div>
			
			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Lista de Alumnos Matriculados</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="imprimir_lista_alumnos">
						{{ FORM.sede_id }}
						{{ FORM.nivel_id }}
						{{ FORM.grado }}
						{{ FORM.seccion }}
						{{ FORM.turno_id }}
						{{ FORM.anio }}
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Resumen Alumnos Matriculados</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="resumen_lista_alumnos">
						{{ FORM.anio }}
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Resumen de Ingresos</h3>
				</div>
				<div class="panel-body">
					<div class="alert alert-info">
				        <b>Resumen Diario:</b> En ambos cuadros seleccione la fecha de la que desea ver el resumen.<br />
				        <b>Resumen Mensual:</b> En el primer cuadro seleccione el primer día del mes y en el segundo el último día del mes.<br />
				        <b>Resumen Anual:</b> En el primer cuadro seleccione el primer día del año y en el segundo el último día del año.
				    </div>
				    <form class="form form-inline reporte center" data-handler="resumen_ingresos">
						<label>Mostrar resumen desde</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="text" name="fecha1" id="x_fecha1" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>Hasta</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="text" name="fecha2" id="x_fecha2" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Alumnos Deudores</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline reporte center" data-handler="alumnos_deudores">
						<label for="">Mostrar deudores de</label>
						{{ FORM.anio }}
						
						<select name="nro_pago" id="nro_pago" class="form-control">
							<option value="">-- TODOS --</option>
							<option value="0">MATRÍCULA</option>
							{% for tipo in COLEGIO.getOptionsNroPago() %}
							<option value="{{ _key }}">MENSUALIDAD {{ tipo|upper }}</option>
							{% endfor %}
						</select>
					
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Alumnos Pagadores</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline reporte center" data-handler="alumnos_pagadores">
						<label for="">Mostrar pagadores de</label>
						{{ FORM.anio }}
						<select name="nro_pago" id="nro_pago" class="form-control">
							<option value="-1">-- TODOS --</option>
							<option value="0">MATRÍCULA</option>
							{% for tipo in COLEGIO.getOptionsNroPago() %}
							<option value="{{ _key }}">MENSUALIDAD {{ tipo|upper }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Lista de Profesores por Aula</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="lista_profesores_aula">
						{{ FORM.sede_id }}
						{{ FORM.nivel_id }}
						{{ FORM.grado }}
						{{ FORM.seccion }}
						{{ FORM.turno_id }}
						{{ FORM.anio }}

					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Personal Administrativo</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="personal_administrativo">
						<label>Ordernar por </label>
						<select name="order" class="form-control input-sm">
							<option value="nombres">NOMBRES</option>
							<option value="apellidos">APELLIDOS</option>
							<option value="cargo">CARGO</option>
						</select>
						<select name="direction"  class="form-control input-sm">
							<option value="ASC">ASCENDENTE</option>
							<option value="DESC">DESCENDENTE</option>
						</select>

					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte Mensual de Asistencia</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="asistencia_mensual">
						{{ FORM.sede_id }}
						{{ FORM.nivel_id }}
						{{ FORM.grado }}
						{{ FORM.seccion }}
						{{ FORM.turno_id }}
						{{ FORM.anio }}
						<select name="mes" style="width: 150px" class="form-control input-sm">
							{% for i in 3..12 %}
							<option value="{{ i }}">{{ COLEGIO.MESES[i-1] }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Estadísticas de Notas</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="estadisticas_finales">
						{{ FORM.sede_id }}
						{{ FORM.nivel_id }}
						{{ FORM.grado }}
						{{ FORM.seccion }}
						{{ FORM.turno_id }}
						{{ FORM.anio }}
						<select name="ciclo" id="ciclo" class="form-control">
							{% for tipo in COLEGIO.getOptionsCicloNotas() %}
							<option value="{{ _key }}">{{ tipo|upper }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">NOMINA DE MATRICULA</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="nomina_matricula">
						{{ FORM.sede_id }}
						{{ FORM.nivel_id }}
						{{ FORM.grado }}
						{{ FORM.seccion }}
						{{ FORM.turno_id }}
						{{ FORM.anio }}
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte de Ventas / Servicios - EXCEL</h3>
				</div>
				<div class="panel-body">
					<form class="form-inline center reporte" data-handler="boletas:reporte_registros_excel">
						<input type="text" class="form-control calendar" name="from" placeholder="Desde" />
						<input type="text" class="form-control calendar" name="to" placeholder="Hasta" />
						<select name="concepto_id" class="form-control" style="width: 150px">
							<option value="">-- PRODUCTO --</option>
							{% for concepto in COLEGIO.getBoletasConceptos() %}
							<option value="{{ concepto.id }}">{{ concepto.descripcion }}</option>
							{% endfor %}
						</select>
						<select name="categoria_id" class="form-control" style="width: 150px">
							<option value="">-- CATEGORIA --</option>
							{% for categoria in COLEGIO.getBoletasCategorias() %}
							<option value="{{ categoria.id }}">{{ categoria.nombre }}</option>
							{% endfor %}
						</select>
						<select name="estado" class="form-control">
							<option value="">-- ESTADO --</option>
							<option value="ACTIVO">ACTIVO</option>
							<option value="ANULADO">ANULADO</option>
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte - Stock de Productos</h3>
				</div>
				<div class="panel-body">
					<form class="form-inline center reporte" data-handler="boletas:stock_productos">
						<select name="concepto_id" class="form-control" style="width: 250px">
							<option value="">-- CONCEPTO --</option>
							{% for concepto in COLEGIO.getBoletasConceptosEstandar() %}
								{% if concepto.controlarStock() %}
								<option value="{{ concepto.id }}">{{ concepto.descripcion }}</option>
								{% endif %}
							{% endfor %}
						</select>
						<select name="comparer" class="form-control">
							<option value="<">MENOR A</option>
							<option value=">">MAYOR A</option>
							<option value="=">IGUAL A</option>
						</select>
						<input type="text" name="cantidad" class="form-control" style="width: 100px" />
					</form>
				</div>
			</div>


			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte de Moras</h3>
				</div>
				<div class="panel-body">
					<div class="alert alert-info">
						* Los registros se filtran y ordenan por "fecha de pago".
					</div>
				    <form class="form form-inline reporte center" data-handler="moras">
						<input type="text" name="from" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
						<input type="text" name="to" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
						<select name="estado_impresion" class="form-control input-sm">
							<option value="IMPRESO">Impreso</option>
							<option value="NO_IMPRESO">No Impreso</option>
						</select>
						<select name="tipo_mora" class="form-control input-sm">
							<option value="">-- Tipo --</option>
							<option value="NOTA">Nota de débito</option>
							<option value="BOLETA">Boleta de Venta</option>
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte Concar</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline reporte center" data-handler="concar">
						<input type="text" name="from" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
						<input type="text" name="to" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte Concar - Banco</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline reporte center" data-handler="concar_banco">
						<input type="text" name="from" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
						<input type="text" name="to" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte Historial de Pagos</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline reporte center" data-handler="historial_pagos">
						<input type="text" name="from" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
						<input type="text" name="to" value="{{ date()|date('d-m-Y') }}" class="form-control calendar" />
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Ranking de Notas</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="ranking_notas">
						{{ FORM.sede_id }}
						{{ FORM.nivel_id }}
						{{ FORM.grado }}
						{{ FORM.seccion }}
						{{ FORM.turno_id }}
						{{ FORM.anio }}
						<select name="ciclo" id="ciclo" class="form-control">
							{% for tipo in COLEGIO.getOptionsCicloNotas() %}
							<option value="{{ _key }}">{{ tipo|upper }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Monto a pagar - Real</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline reporte center" data-handler="montos_alumnos_real">
						<label for="">Mostrar alumnos del</label>
						{{ FORM.sede_id }}
						{{ FORM.anio }}
						<select name="mostrar" class="form-control" style="width: 150px">
							<option value="TODOS">TODOS</option>
							<option value="PAGADOS">PAGADOS</option>
							<option value="DEUDORES">DEUDORES</option>
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Alumnos no matriculados</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline reporte center" data-handler="alumnos_no_matriculados">
						<label for="">Mostrar alumnos no matriculados el</label>
						{{ FORM.anio }}
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Transferencias Gratuitas</h3>
				</div>
				<div class="panel-body">
					<form class="form-inline center reporte" data-handler="boletas:transferencias_gratuitas">
						<input type="text" style="width: 120px" class="form-control calendar" name="from" placeholder="Desde" />
						<input type="text" style="width: 120px" class="form-control calendar" name="to" placeholder="Hasta" />
						<select name="concepto_id" class="form-control" style="width: 150px">
							<option value="">-- CONCEPTO --</option>
							{% for concepto in COLEGIO.getBoletasConceptos() %}
							<option value="{{ concepto.id }}">{{ concepto.descripcion }}</option>
							{% endfor %}
						</select>
						<select name="categoria_id" class="form-control" style="width: 150px">
							<option value="">-- CATEGORIA --</option>
							{% for categoria in COLEGIO.getBoletasCategorias() %}
							<option value="{{ categoria.id }}">{{ categoria.nombre }}</option>
							{% endfor %}
						</select>
						<select name="estado" class="form-control">
							<option value="">-- ESTADO --</option>
							<option value="ACTIVO">ACTIVO</option>
							<option value="ANULADO">ANULADO</option>
						</select>
					</form>
				</div>
			</div>
		
			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Imprimir Boletas</h3>
				</div>
				<div class="panel-body">
					<form class="form-inline center reporte" data-handler="reportes:impresiones">
						<label for="">Serie</label>
						<input type="text" style="width: 70px" class="form-control input-sm" name="serie" placeholder="Serie" />
						<label for="">Desde</label>
						<input type="text" style="width: 70px" class="form-control input-sm" name="numero_desde" placeholder="Número" />
						<label for="">Hasta</label>
						<input type="text" style="width: 70px" class="form-control input-sm" name="numero_hasta" placeholder="Número" />
						<label for="">Tipo</label>
						<select name="tipo_documento" class="form-control" style="width: 150px">
							<option value="BOLETA">Boleta de Venta</option>
							<option value="NOTA">Nota de Débito</option>
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Consolidado de Promedios</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="consolidado_promedios">
						
						{{ FORM.anio }}
						<select name="ciclo" id="ciclo" class="form-control">
							{% for tipo in COLEGIO.getOptionsCicloNotas() %}
							<option value="{{ _key }}">{{ tipo|upper }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Consolidado de Promedios - Detalles</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="consolidado_promedios_detalles">
						{{ FORM.nivel_id }}
						{{ FORM.anio }}
						<select name="ciclo" id="ciclo" class="form-control">
							{% for tipo in COLEGIO.getOptionsCicloNotas() %}
							<option value="{{ _key }}">{{ tipo|upper }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Estadísticas de Notas - Grupos</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="estadisticas_notas_bloques">
						{{ FORM.bloque_id }}

						{{ FORM.anio }}
						<select name="ciclo" id="ciclo" class="input-sm form-control">
							{% for tipo in COLEGIO.getOptionsCicloNotas() %}
							<option value="{{ _key }}">{{ tipo|upper }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Estadísticas de Notas - Grupos PDF</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="estadisticas_notas_bloques_pdf">
						{{ FORM.bloque_id }}

						{{ FORM.anio }}
						<select name="ciclo" id="ciclo" class="input-sm form-control">
							{% for tipo in COLEGIO.getOptionsCicloNotas() %}
							<option value="{{ _key }}">{{ tipo|upper }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Estadísticas de Notas - Balance General</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte" data-handler="estadisticas_balance_general">
						<select name="ciclo" id="ciclo" class="input-sm form-control">
							{% for tipo in COLEGIO.getOptionsCicloNotas() %}
							<option value="{{ _key }}">{{ tipo|upper }}</option>
							{% endfor %}
						</select>
						{{ FORM.anio }}

					</form>
				</div>
			</div>


			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte de Alumnos</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline reporte center" data-handler="reporte_alumnos">
						
						{{ FORM.anio }}
						{{ FORM.modalidad }}
						<select name="antiguedad" class="form-control">
							<option value="">-- Antiguedad --</option>
							<option value="NUEVO">NUEVO</option>
							<option value="ANTIGUO">ANTIGUO</option>
						</select>
					</form>
				</div>
			</div>


			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Asistencia Personal</h3>
				</div>
				<div class="panel-body">
					<form class="form-inline center reporte" data-handler="asistencia_personal">
						<input type="date" style="width: 120px" class="form-control" value="{{ date()|date('Y-m-d') }}" name="from" placeholder="Desde" />
						<input type="date" style="width: 120px" class="form-control" value="{{ date()|date('Y-m-d') }}" name="to" placeholder="Hasta" />
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">REPORTE STARSOFT - REGISTROS DE VENTAS</h3>
				</div>
				<div class="panel-body">
					<form class="form-inline center reporte" data-handler="reporte_starsoft">
						<input type="number" name="anio" class="form-control" value="{{ date()|date('Y') }}" />
						<select name="mes" class="form-control">
							{% for mes in COLEGIO.MESES %}
							<option value="{{ _key + 1 }}">{{ mes }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>

			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">REPORTE STARSOFT - LIBRO BANCOS INGRESOS</h3>
				</div>
				<div class="panel-body">
					<form class="form-inline center reporte" data-handler="reporte_starsoft_ingresos">
						<input type="number" name="anio" class="form-control" value="{{ date()|date('Y') }}" />
						<select name="mes" class="form-control">
							{% for mes in COLEGIO.MESES %}
							<option value="{{ _key + 1 }}">{{ mes }}</option>
							{% endfor %}
						</select>
					</form>
				</div>
			</div>


			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Lista de Alumnos - Registro Académico</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte_label" data-handler="imprimir_lista_alumnos_registro_auxiliar">
						{{ FORM.sede_id }}
						{{ FORM.nivel_id }}
						{{ FORM.grado }}

						{{ FORM.anio }}
					</form>
				</div>
			</div>

            <div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte de Deuda x Año</h3>
				</div>
				<div class="panel-body">
					<form class="form form-inline center reporte_label" data-handler="reporte_deuda_x_anio">
						{{ FORM.anio }}
					</form>
				</div>
			</div>
		</div>

	</div>
</div>
