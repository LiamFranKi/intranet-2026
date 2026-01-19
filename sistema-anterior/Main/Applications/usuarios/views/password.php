<script>
$(function(){
	$('#formUsuario').niftyOverlay();
	$('#formUsuario').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/usuarios/save_password', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						
					break;
					
					case 0:
						zk.pageAlert({message: 'No se pudieron guardar los datos', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
					break;

					case -1:
						zk.pageAlert({message: 'Las contraseñas no coinciden', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
					break;
					
					case -5:
						zk.formErrors(_form, r.errors);
					break;
					
					default:
						
					break;
				}
				
			});
		}
	});
});
</script>

<div id="page-content">

	<form class="form-horizontal" id="formUsuario" data-target="#formUsuario" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Cambio de Contraseña</h3>
			</div>
			<div class="panel-body">
			

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Nueva Contraseña <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">
				    	<input type="password" class="form-control" name="password" data-bv-notempty="true" />
				    </div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Confirme Contraseña <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">
				    	<input type="password" class="form-control" name="password2" data-bv-notempty="true" />
				    </div>
				</div>

			
			</div>
		
			<div class="modal-footer">
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>
</div>