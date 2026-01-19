{% extends main_template %}
{% block main_content %}
<script>
$(function(){
	$('#listaActividades').dataTable();
	setMenuActive('actividades');

	$('#calendario').fullCalendar({
		locale: "es",
        header: {
			right: 'prev,next today',
			//right: 'agendaWeek'
		},
		allDaySlot: false,
		slotEventOverlap: false,
		firstDay: 1,
		selectable: true,
        events: '/actividades/actividades_json',
        eventClick: function( event, jsEvent, view ) {
			{% if USUARIO.is(['ADMINISTRADOR', 'SECRETARIA', 'DOCENTE']) %}
			zk.goToUrl('/actividades/form/' + event.id);
			{% else %}
			fancybox('/actividades/detalles/' + event.id)
			{% endif %}
		},
    });
});
{% if USUARIO.is(['ADMINISTRADOR', 'SECRETARIA', 'DOCENTE']) %}
function borrar_actividad(id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/actividades/borrar', {id: id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}
{% endif %}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Actividades</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/actividades">Actividades</a></li>
	
	</ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Actividades</h3>
		</div>
		<div class="panel-body">
			{% if USUARIO.is(['ADMINISTRADOR', 'SECRETARIA', 'DOCENTE']) %}
			<div class="pad-btm form-inline">
				<div class="row">
					<div class="col-sm-6 table-toolbar-left">
						<a href="#/actividades/form" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
					</div>
				</div>
			</div>
			{% endif %}
			<div id="calendario"></div>
		</div>
	</div>
</div>

{% endblock %}
