{% extends main_template %}
{% block main_content %}
<script>
$(function(){

	setMenuActive('pagos');
	$('.calendar').datepicker({format: 'yyyy-mm-dd', autoclose: true})

	$('#fboletear').submit(function(e){
		e.preventDefault();
		if(confirm('Se registrarán "pagos temporales" correspondientes a los datos seleccionados, ¿Desea continuar?'))
			zk.printDocument('/pagos/do_boletear?' + $(this).serialize());
	});

	$('#fboletear_json').submit(function(e){
		e.preventDefault();
		if(confirm('Se registrarán "pagos temporales" correspondientes a los datos seleccionados, ¿Desea continuar?'))
			zk.printDocument('/pagos/do_boletear_json?' + $(this).serialize());
	});

	$('#fboletear_json2').submit(function(e){
		e.preventDefault();
		if(confirm('Se registrarán "pagos temporales" correspondientes a los datos seleccionados, ¿Desea continuar?'))
			zk.printDocument('/pagos/do_boletear_json2?' + $(this).serialize());
	});

	$('#fboletearMoras').submit(function(e){
		e.preventDefault();
		zk.printDocument('/pagos/do_boletear_moras?' + $(this).serialize());
	});

	$('#fboletearMoras_json').submit(function(e){
		e.preventDefault();
		zk.printDocument('/pagos/do_boletear_moras_json?' + $(this).serialize());
	});
});

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Boletear Pagos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/pagos">Pagos</a></li>
		<li class="active">Boletear Pagos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Boletear Pagos</h3>
		</div>
		<div class="panel-body">
			
			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading">
					<h3 class="panel-title">Boletear Pagos - JSON</h3>
				</div>
				<div class="panel-body">
					<div class="alert alert-info text-center">
						Seleccione el grupo y pago a boletear.
					</div>
					<form id="fboletear_json" class="form-inline" role="form">
						<table class="special">
							<thead>
								<tr>
									<th>Pago</th>
									<th>Grupo</th>
									<th></th>

								</tr>
								<tr>
									<td class="center">
										<select name="nro_pago" id="nro_pago" class="input-sm form-control">
											<!--<option value="-1">-- TODOS --</option>-->
											<!--<option value="0">MATRÍCULA</option>-->
											{% for tipo in COLEGIO.getOptionsNroPago() %}
											<option value="{{ _key }}">MENSUALIDAD {{ tipo|upper }}</option>
											{% endfor %}
										</select>
									</td>
									<td class="center">
										<select name="grupo_id" id="export_grupo_id" class="input-sm form-control" style="width: 200px">
											<!--<option value="-1">-- TODOS --</option>-->
											
											{% for sede in COLEGIO.sedes %}
											<optgroup label="{{ sede.nombre }}">
												{% for grupo in COLEGIO.getGrupos(get_anio, sede.id) %}
												<option value="{{ grupo.id }}">{{ grupo.getNombre() }}</option>
												{% endfor %}
											</optgroup>
											{% endfor %}
										</select>
									</td>
							
									<td class="center">
										<button class="btn-primary btn">Boletear Pagos</button>
									</td>
								</tr>
							</thead>
						</table>
					</form>
				</div>
			</div>

			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading">
					<h3 class="panel-title">Boletear Pagos - JSON</h3>
				</div>
				<div class="panel-body">
					<form id="fboletear_json2" class="form-inline" role="form">
						<table class="special">
							<thead>
								<tr>
									<th>Pago</th>
									
									<th></th>

								</tr>
								<tr>
									<td class="center">
										<select name="nro_pago" id="nro_pago" class="input-sm form-control">
											<!--<option value="-1">-- TODOS --</option>-->
											<!--<option value="0">MATRÍCULA</option>-->
											{% for tipo in COLEGIO.getOptionsNroPago() %}
											<option value="{{ _key }}">MENSUALIDAD {{ tipo|upper }}</option>
											{% endfor %}
										</select>
									</td>
									
							
									<td class="text-center">
										<button class="btn-primary btn">Boletear Pagos</button>
									</td>
								</tr>
							</thead>
						</table>
					</form>
				</div>
			</div>

			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading">
					<h3 class="panel-title">Boletear Moras - JSON</h3>
				</div>
				<div class="panel-body">
					<div class="alert alert-info text-center">
						Se imprimirán las moras de los pagos que se hayan realizado en el mes seleccionado.
					</div>
					
				    <form id="fboletearMoras_json" class="form form-inline reporte text-center" data-handler="concar">
						<input type="text" name="from" value="{{ date()|date('Y-m-d') }}" class="form-control input-sm calendar" />
						<input type="text" name="to" value="{{ date()|date('Y-m-d') }}" class="form-control input-sm calendar" />
						<input type="text" name="cantidad" value="1" placeholder="Cantidad" class="form-control input-sm" />
						<button class="btn-primary btn">Boletear Moras</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

{% endblock %}
