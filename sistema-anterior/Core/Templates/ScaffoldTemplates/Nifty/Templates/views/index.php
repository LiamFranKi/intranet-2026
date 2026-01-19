{{ '{% extends main_template %}' }}
{{ '{% block main_content %}' }}
<script>
$(function(){
	$('#lista{{ Name|ucwords }}').dataTable();
	setMenuActive('{{ Name }}');
});

function borrar_{{ Model|lower }}(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/{{ Name }}/borrar', {id: id}, function(r){
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
		<h1 class="page-header text-overflow">{{ Name|ucwords }}</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">{{ Name|ucwords }}</a></li>
		<li class="active">Lista de {{ Name|ucwords }}</li>
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Lista de {{ Name|capitalize }}</h3>
		</div>
		<div class="panel-body">
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/{{ Name }}/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>

			<table id="lista{{ Name|ucwords }}" class="table table-striped table-bordered">
				<thead>
					<tr>{{ thead|raw }}
					</tr>
				</thead>
				<tbody>
					{{ tbody|raw }}
				</tbody>
			</table>
		</div>
	</div>
</div>

{{ '{% endblock %}' }}
