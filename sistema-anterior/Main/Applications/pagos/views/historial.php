{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	//$('#listaPagos').dataTable();
	setMenuActive('pagos');
});

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Pagos</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Pagos</a></li>
		<li class="active">Historial de Pagos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel panel-primary panel-bordered">
		<div class="panel-heading">
			<h3 class="panel-title">Historial de Pagos</h3>
		</div>
		<div class="panel-body tab-base">
			{% if matriculas|length > 0 %}
			<ul class="nav nav-tabs" id="tabs">
				{% for matricula in matriculas %}
					<li {{ loop.index == 1 ? 'class="active"': '' }}>
						<a href="#tabs-{{ matricula.id }}" data-toggle="tab">
							{{ matricula.grupo.nivel.nombre|upper }} - {{ matricula.grupo.getGrado() }} {{ matricula.grupo.seccion|upper }} - {{ matricula.grupo.anio }}
						</a>
					</li>
				{% endfor %}
			</ul>
			
			<div class="tab-content">
			{% for matricula in matriculas %}
			<div class="tab-pane fade {{ loop.index == 1 ? 'in active': '' }}" id="tabs-{{ matricula.id }}">
			<div class="alert alert-info center">
		        La información es referencial que se calcula a partir del costo seleccionado al momento de registrar la matrícula.
		    </div>
			<table class="special" style="width: 100%; text-align: center">
				<tr>
					<th>Monto a pagar {{ __config.moneda }}</th><td>  {{ matricula.getTotalPagar()|number_format(2) }}</td>
					<th>Monto pagado {{ __config.moneda }}</th><td> {{ matricula.getTotalPagado()|number_format(2) }}</td>
					<th>Monto que adeuda {{ __config.moneda }}</th><td>{{ matricula.getSaldo()|number_format(2) }}</td>
					<th>Otros Pagos {{ __config.moneda }}</th><td>{{ matricula.getOtrosPagos()|number_format(2) }}</td>
				</tr>
			</table><br />
			{% set pagos = matricula.getPagosAlumnoHistorial() %}
			{% if pagos|length > 0 %}
			<table class="special" style="text-align: center">
				<thead>
					<tr>
						<th>Tipo de Pago</th>
						<th>Monto</th>
				
						<!--<th>Descripción</th>-->
						<!--<th>Observaciones</th>-->
						<th>Fecha</th>
						<th>Nº Operación</th>
						<th>Serie/Número</th>
						<th>Fecha Emisión</th>
						<th>Fecha Cancelado</th>
					</tr>
				</thead>
				<tbody>
					
					{% for pago in pagos %}
					{% set impresion = pago.getActiveImpresion(false) %}
					<tr style="{{ pago.estado_pago == 'PENDIENTE' ? 'color: red' : '' }}">
						<td>{{ pago.getTipoDescription() }}</td>
						<td>S/ {{ (pago.monto + pago.mora)|number_format(2) }}{% if pago.mora > 0 %}<br /><small>Mora: S/. {{ pago.mora|number_format(2) }}</small>{% endif %}</td>
						
						<!--<td>{{ pago.descripcion }}</td>-->
						<!--<td>{{ pago.observaciones }}</td>-->
						<td>{{ pago.getFechaHora() }}</td>
						<td class="text-center">{{ pago.estado_pago == 'CANCELADO' ? pago.nro_movimiento_banco~'|'~pago.nro_movimiento_importado : '-' }}</td>
						<td class="text-center">{{ pago.estado_pago == 'CANCELADO' ? impresion.getSerieNumeroPrefijo() : '-' }}</td>
						<td class="text-center">{{ pago.estado_pago == 'CANCELADO' ? impresion.fecha_impresion|date('d-m-Y') : '-' }}</td>
						<td class="text-center">{{ pago.estado_pago == 'CANCELADO' ? pago.fecha_cancelado|date('d-m-Y') : '-' }}</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>

            {% if link_consulta %}
            <div class="alert alert-info text-center">
                <p>Puede consultar sus Boletas haciendo clic en el siguiente botón</p>
                <div><a class="btn btn-primary mar-top" href="{{ link_consulta }}" target="_blank">Consultar Boletas</a></div>

            </div>
			<!-- <div class="alert alert-warning text-center">
				<p>Puede consultar sus boletas todos los pagos realizados HASTA el 21-05-23
				<a class="btn-link" target="_blank" href="https://escondatagate.page.link/qdpm">https://escondatagate.page.link/qdpm</a></p>

				<p>Puede consultar sus Boletas cuyos pagos fueron realizados DESDE el 22-05-23
				<a class="btn-link" target="_blank" href="https://sfe.bizlinks.com.pe/sfeperu/public/loginAnonimo.jsf">https://sfe.bizlinks.com.pe/sfeperu/public/loginAnonimo.jsf</a></p>
			</div> -->
            {% endif %}
			{% else %}
			<center><b>NO HA REGISTRADO NINGÚN PAGO</b></center><br />
			{% endif %}
			</div>
			{% endfor %}
			</div>
		    {% else %}
		    <p class="text-center"><b>NO SE REGISTRARON MATRICULAS</b></p>
		    {% endif %}
		</div>
	</div>

    {% if sales_items|length > 0 %}
    <div class="panel panel-primary panel-bordered">
        <div class="panel-heading">
            <h3 class="panel-title">Historial de Ventas</h3>
        </div>
        <div class="panel-body">
            <table class="special">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Total</th>
                        
                    </tr>
                </thead>
                <tbody>
                    {% for item in sales_items %}
                    <tr>
                        <td class="text-center">{{ item.boleta.fecha }}</td>
                        <td>{{ item.concepto.descripcion }}</td>
                        <td class="text-center">{{ item.cantidad }}</td>
                        <td class="text-right">{{ item.precio|number_format(2) }}</td>
                        
                        <td class="text-right">{{ (item.cantidad * item.precio)|number_format(2) }}</td>
                    </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>

    {% endif %}
</div>

{% endblock %}
