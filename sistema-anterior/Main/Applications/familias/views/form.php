{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('#formFamilia').niftyOverlay();
	$('#formFamilia').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			if($('#alumno_id').val() == ""){
				return zk.pageAlert({message: 'Seleccione un alumno', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
			}

			$(_form).sendForm('/familias/save', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
						history.back(-1)
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

	$('#alumno_id').ajaxChosen({
        type: 'GET',
        url: '/info/alumno',
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
});
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Alumnos a Cargo</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/apoderados">Apoderados</a></li>
		<li class="active">Registro de Alumnos a Cargo</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formFamilia" data-target="#formFamilia" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ familia.is_new_record() ? "Registrar" : "Editar" }} Alumno a Cargo</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.apoderado_id }}
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Alumno <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-6">{{ form.alumno_id }}</div>
				</div>

			

			
			</div>
		
			<div class="modal-footer">
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>
</div>
{% endblock %}
