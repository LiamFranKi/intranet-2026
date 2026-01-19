<script>
$(function(){
	$('#demo-calendar').fullCalendar({
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
            fancybox('/actividades/detalles/' + event.id)
        }
    });
});
</script>
<div class="panel">
	<div class="panel-body" style="">
		<div id='demo-calendar'></div>
	</div>
</div>
