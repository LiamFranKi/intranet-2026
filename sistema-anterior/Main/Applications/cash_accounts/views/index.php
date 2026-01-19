{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaCashAccounts').dataTable();
	setMenuActive('cash_accounts');
});

function borrarCashAccount(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/cash_accounts/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">Caja</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/cash_accounts">Caja</a></li>
		<li class="active">Lista de Cuentas</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de Cuentas</h3>
		</div>
		<div class="panel-body">
            {% if USUARIO.is(['ADMINISTRADOR']) %}
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/cash_accounts/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>
            {% endif %}

			<table id="listaCashAccounts" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Nombre</th>
                        <th>Moneda</th>
                        <th>Tipo</th>
                        <th>Saldo</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{% for cashAccount in cashAccounts %}
					<tr>
						<td>{{ cashAccount.name }}</td>
                        <td class="text-center">{{ cashAccount.currency.name }}</td>
                        <td class="text-center">{{ cashAccount.type.name }}</td>
                        <td class="text-center">{{ cashAccount.getBalance()|number_format(2) }}</td>

						<td class="text-center" style="width: 120px">
							<div class="btn-group dropup">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
                                    <li><a href="#/cash_account_flows/?cashAccountId={{ sha1(cashAccount.id) }}">{{ icon('application_view_list') }} Movimientos</a></li>
                                    {% if USUARIO.is('ADMINISTRADOR') %}
                                    <li class="divider"></li>
                                    <li><a href="#/cash_accounts/form/{{ sha1(cashAccount.id) }}">{{ icon('register') }} Editar Cuenta</a></li>
									<li><a href="javascript:;" onclick="borrarCashAccount('{{ sha1(cashAccount.id) }}')">{{ icon('delete') }} Borrar Cuenta</a></li>
                                    {% endif %}
								</ul>
							</div>
						</td>
					</tr>
					{% endfor %}
				</tbody>
			</table>

		</div>
	</div>
</div>

{% endblock %}
