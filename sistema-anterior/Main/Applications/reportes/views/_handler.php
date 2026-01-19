<script>
$(function(){
	$('.reporte, .reporte_label').submit(function(e){
		e.preventDefault();
		application = 'reportes';
		handler = $(this).data('handler');
		if(handler.indexOf(':') > 0){
			handler = handler.split(':');
			application = handler[0];
			handler = handler[1];
		}

		if($(this).data('fancybox')){
			fancybox('/'+ application +'/'+ handler +'?' + $(this).serialize());
		}else{
			zk.printDocument('/'+ application +'/'+ handler +'?' + $(this).serialize());
		}
		
	});

    $.each($('.reporte'), function(i, obj){
        
        $(obj).changeGradoOptions({
            showLabel: false
        });
    })

	
	$('.reporte_label').changeGradoOptions({
		showLabel: true
	});

	$('.reporte, .reporte_label').append('<button class="btn btn-primary"><i class="icon-search"></i> Ver Reporte</button>');
	
	$('.calendar').datepicker({format: 'dd-mm-yyyy', autoclose: true});
	$('.xdatepicker').datepicker({format: 'yyyy-mm-dd', autoclose: true});

	$('.calendar').css('width', '90px')
	$('form').attr('autocomplete', 'off')
});
</script>
<style>
.reporte{
	 
}
.center{
	text-align:  center !important;
}
</style>