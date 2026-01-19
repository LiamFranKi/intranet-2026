$(function(){
	$('body').append('<div id="ModalLoading"></div>');
});

function overlayLoader(action){
	if(action == 'hide'){
		//$('#ModalLoading').fadeOut();
		
	}else{
		//$('#ModalLoading').fadeIn();
		$('#content-container').html(`
			<div id="page-head">
				<div id="page-title">
					<h1 class="page-header text-overflow">Cargando...</h1>
				</div>
			
			</div>
			<div id="page-content">
				<div class="panel">
					<div class="panel-body text-center">
						<div class="panel-overlay-content pad-all unselectable"><span class="panel-overlay-icon text-main"><i class="fa fa-refresh fa-spin fa-2x"></i></span><h4 class="panel-overlay-title"></h4><p></p></div>
					</div>
				</div>
			</div>
		`)
	}
}
