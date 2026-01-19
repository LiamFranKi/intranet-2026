<script>
$(function(){
	ckEditorVerySimple('#mensaje', 'eMensaje');
	
	$('#para').ajaxChosen({
        type: 'GET',
        url: '/info/usuario',
        dataType: 'json',
        keepTypingMsg: 'Continue Escribiendo...',
        lookingForMsg: 'Buscando '
    }, function (data) {
        var results = [];

        $.each(data, function (i, val) {
            results.push({ value: val.value, text: val.text });
        });

        return results;
    });


	$('#formMensaje').niftyOverlay();
	$('#formMensaje').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);
			


			$('#mensaje').val(eMensaje.getData());

			if($('#para').val() == null){
				return zk.pageAlert({message: 'Selecciona al menos un destinatario', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
			}

			$(_form).sendForm('/mensajes/save', function(r){
				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						zk.goToUrl('/mensajes');
					break;
					
					case 0:
						zk.pageAlert({message: 'No se pudieron guardar los datos', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
						
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
})
</script>
<style>
.ck-editor__editable{
	height:  300px;
}
</style>
<!--Page content-->
<!--===================================================-->
<div id="page-content">
    <div class="panel">
        <div class="panel-body">
            <div class="fixed-fluid">
                {% include 'aside.php' %}

                <div class="fluid">
                    <!-- COMPOSE EMAIL -->
                    <!--===================================================-->

                    <div class="pad-btm clearfix">
                        <!--Cc & bcc toggle buttons-->
                        <div class="pull-right pad-btm">
                           
                        </div>
                    </div>



                    <!--Input form-->
                    <form role="form" id="formMensaje" class="form-horizontal" data-target="#formMensaje" data-toggle="overlay">
                        <div class="form-group">
                            <label class="col-lg-2 control-label text-left" for="inputEmail">Para</label>
                            <div class="col-lg-10">
                                <select name="para[]" multiple="true" style="width: 100%" data-placeholder="Seleccione" id="para">
                                	<option value=""></option>
                                	{% for usuario in usuarios %}
						            <option value="{{ usuario.id }}" selected>{{ usuario.getFullName() }}</option>
						            {% endfor %}
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-lg-2 control-label text-left" for="inputSubject">Asunto</label>
                            <div class="col-lg-10">
                                <input type="text" id="asunto" name="asunto" class="form-control" data-bv-notempty="true">
                            </div>
                        </div>

                        <textarea name="mensaje" id="mensaje"></textarea>

                        <div class="pad-ver">

	                        <!--Send button-->
	                        <button id="mail-send-btn" class="btn btn-primary btn-block">
	                            <i class="demo-psi-mail-send icon-lg icon-fw"></i> Enviar Mensaje
	                        </button>

	                    </div>
                    </form>


                    


                    <!--===================================================-->
                    <!-- END COMPOSE EMAIL -->
                </div>
            </div>
        </div>
    </div>
			
			    
</div>
<!--===================================================-->
<!--End page content-->
