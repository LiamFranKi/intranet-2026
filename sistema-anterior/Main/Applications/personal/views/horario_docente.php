<script>
function updateEvent(event){
	$.post('/personal/save_horario', {update: 'true', id: event.id, inicio: event.start.format(), fin: event.end.format()}, function(r){
		if(parseInt(r[0]) == 0){	
			zk.pageAlert({message: 'No se pudieron guardar los datos', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
		}
	}, 'json');
}
var xCalendar;
$(function(){
	xCalendar = $('#calendar').fullCalendar({
		header: false,
		hiddenDays: [ 0, 6 ],
		allDaySlot: false,
		defaultDate: '2015-12-28',
		slotEventOverlap: false,
		selectable: true,
		editable: true,
		selectHelper: true,
		columnFormat: 'dddd',
		axisFormat: 'h(:mm) A',
		defaultView: 'agendaWeek',
		minTime: '07:30:00',
		maxTime: '20:00:00',
		select: function(start, end) {
			zk.goToUrl('/personal/horario?personal_id={{ params.id }}&inicio=' + start.format() + '&fin=' + end.format());
		},
		eventDrop: function( event, jsEvent, ui, view ) {
			updateEvent(event);
		},
		eventResize: function( event, jsEvent, ui, view ) {
			updateEvent(event);
		},
		eventClick: function( event, jsEvent, view ) {
			//fancybox('/personal/horario?id=' + event.id);
			zk.goToUrl('/personal/horario/' + event.id);
		},
		events: '/personal/horario_json/{{ personal.id }}',

		slotDuration: '00:05:00'
    });

    $('#calendar .fc-right').append('<button type="button" onclick="printCalendar()" class="fc-button fc-state-default fc-corner-left fc-corner-right fc-state-default">Imprimir</button>');
});
</script>
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Horario - {{ personal.getFullName() }}</h3>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-lg-12" style="margin-bottom: 10px">
					<button class="btn btn-default" onclick="zk.printDocument('/personal/horario_pdf/{{ sha1(personal.id) }}')">{{ icon('page_white_acrobat') }} Imprimir Horario - PDF</button>

					<button class="btn btn-default" onclick="zk.printDocument('/grupos/imprimir_horario_docente?personal_id={{ personal.id }}')">{{ icon('printer') }} Imprimir Vertical - Anterior</button>
				<button class="btn btn-default" onclick="zk.printDocument('/grupos/imprimir_horario_docente_horizontal?personal_id={{ personal.id }}')">{{ icon('printer') }} Imprimir Horizontal - Anterior</button>
				</div>
			</div>
			<div id="calendar"></div>
		</div>
	</div>
</div>