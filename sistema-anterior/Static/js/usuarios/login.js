$(function(){
	//$('#loginFormContainer').niftyOverlay();

	$('#loginForm').on('submit', function(e){
		e.preventDefault();
		zk.sendData('/usuarios/do_login', $(this).serialize(), function(r){
			if(r.code == 1){
				window.location = '/'
			}
			if(r.code == 0){
				zk.pageAlert({message: "Los datos ingresados son incorrectos", container: 'floating', icon: 'remove', type: 'danger', floating_position: 'top-center'})
			}
			if(r.code == -1){
				zk.pageAlert({message: "El usuario no está activo", container: 'floating', icon: 'remove', type: 'danger', floating_position: 'top-center'})
			}
			if(r.code == -2)
				selectRoleWindow(r);

		}, '#loginFormContainer');
	})
});

function selectRoleWindow(r){
	var message = '<div class="form-block">';
	for(i in r.roles){
		message += '<button class="btn btn-block btn-primary" onclick="doLoginToken(\''+ r.roles[i] +'\', \''+ r.token +'\')">'+ r.roles[i] +'</button>';
	}
	message += '</div>';

	bootbox.dialog({
		title: "Seleccione un rol",
		message: message,
		size: 'small'
	});
}

function doLoginToken(role, token){
	$.post('/login_token', {role: role, token: token}, function(r){
		switch(parseInt(r.code)){
			case 1:
				window.location = '/';
			break;

			case 0:
				bootbox.hideAll();
				zk.pageAlert({message: 'Los datos ingresados son incorrectos', type: 'danger', icon: 'remove', container: 'floating', floating_position: 'top-center'})
			break;
		}
	}, 'json');
	
}