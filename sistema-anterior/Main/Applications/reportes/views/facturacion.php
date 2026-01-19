{% include '_handler.php' %}
<script>
    $(function(){
        $('#concepto_id').select2()
    })
</script>
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
			<div class="panel panel-bordered panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Reporte de Ventas / Servicios - EXCEL</h3>
				</div>
				<div class="panel-body">
					<form class="form-inline center reporte" data-handler="boletas:reporte_registros_excel">
						<input type="text" class="form-control calendar" name="from" placeholder="Desde" />
						<input type="text" class="form-control calendar" name="to" placeholder="Hasta" />
						<select name="concepto_id" class="form-control" id="concepto_id">
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
		</div>
	</div>
</div>