{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	//$('#listaCashAccountFlows').dataTable();
	setMenuActive('cashAccountFlows');
});

function borrarCashAccountFlow(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/cash_account_flows/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});
			}
		});
	});
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Movimientos - {{ cashAccount.name }}</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/cash_accounts">Caja</a></li>
		<li class="active">Lista de Movimientos</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Movimientos</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/cash_account_flows/form?cashAccountId={{ params.cashAccountId }}" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

            <table class="special">
                <tr>
                    <th>Cuenta</th>
                    <td>{{ cashAccount.name }}</td>
                </tr>
                <tr>
                    <th>Saldo</th>
                    <td class="text-bold">{{ cashAccount.getBalance()|number_format(2) }}</td>
                </tr>
            </table>

            {% if cashAccountFlows|length > 0 %}

			<table id="listaCashAccountFlows" class="table table-striped table-bordered special">
				<thead>
					<tr>
                        <th>Fecha / Hora</th>
                        <th>Tipo</th>
						<th>Descripción</th>
                        <th>Rubro</th>
                        <th>Monto</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for cashAccountFlow in cashAccountFlows %}
					<tr>
                        <td class="text-center">{{ cashAccountFlow.date|date('d-m-Y') }} {{ cashAccountFlow.time|date('H:i') }}</td>
                        <td class="text-center"><span class="label label-{{ cashAccountFlow.type == 1 ? 'success' : 'danger' }} label-table">{{ cashAccountFlow.getType() }}</span></td>
						<td>{{ cashAccountFlow.description }}</td>
                        <td>{{ cashAccountFlow.entry }}</td>
                        <td class="text-center">{{ cashAccountFlow.amount|number_format(2) }}</td>

						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li><a href="#/cash_account_flows/form/{{ sha1(cashAccountFlow.id) }}">{{ icon('register') }} Editar Movimiento</a></li>
									<li><a href="javascript:;" onclick="borrarCashAccountFlow('{{ sha1(cashAccountFlow.id) }}')">{{ icon('delete') }} Borrar Movimiento</a></li>
								</ul>
							</div>
						</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>
            {% else %}
            <p class="text-center text-bold">NO SE ENCONTRARON RESULTADOS</p>
            {% endif %}
		</div>
	</div>
</div>

{% endblock %}
