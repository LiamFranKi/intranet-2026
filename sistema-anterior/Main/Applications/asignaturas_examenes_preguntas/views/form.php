{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
var indexAlternativa = '{{ asignatura_examen_pregunta.alternativas|length }}';
$(function(){
	$('.check').niftyCheck();
	$('#formAsignatura_Examen_Pregunta').niftyOverlay();
	$('#formAsignatura_Examen_Pregunta').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$('#descripcion').val( window.eDescripcion.getData())

			for(i=0; i < parseInt(indexAlternativa); i++)
				$('#alternativa_' + i).val(window['eAlternativa'+ i].getData())

			$(_form).sendForm('/asignaturas_examenes_preguntas/save', function(r){
				

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

    $('#tipo').on('change', function(){
        if($(this).val() == 'COMPLETAR'){
            $('.alternativas').hide();
            $('.completar').show()
        }else{
            $('.alternativas').show();
            $('.completar').hide()
        }
    }).trigger('change');

	ckEditorVerySimple('#descripcion', 'eDescripcion');
});

function agregar_alternativa(){
	data = '<tr>'+
				'<td class="center">'+
					'<textarea name="alternativa['+ indexAlternativa +'][descripcion]" id="alternativa_'+ indexAlternativa +'" value="" class="input-sm form-control"></textarea>'+
				'</td>'+
				'<td class="center"><label class="form-checkbox form-normal form-primary check"><input type="checkbox" name="alternativa['+ indexAlternativa +'][correcta]"></label></td>'+
				'<td class="center"><button class="btn-danger btn" onclick="$(this).parent().parent().remove()" type="button"><i class="fa fa-remove"></i></button></td>'+
			'</tr>';
	$('#listaAlternativas').append(data);
	//fckeditor('alternativa_'  +indexAlternativa, 60, 'AlternativaExamen');
	ckEditorVerySimple('#alternativa_'  +indexAlternativa, 'eAlternativa' + indexAlternativa);
	++indexAlternativa;
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Exámenes</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Preguntas</a></li>
		<li class="active">Registro de Pregunta</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formAsignatura_Examen_Pregunta" data-target="#formAsignatura_Examen_Pregunta" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">{{ asignatura_examen_pregunta.is_new_record() ? "Registrar" : "Editar" }} Pregunta</h3>
			</div>
			<div class="panel-body">
			
				{{ form.id|raw }}
				{{ form.examen_id }}

                <div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Tipo <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.tipo }}</div>
				</div>

				<div class="form-group form-group-sm">
				    <label class="col-sm-12 control-label text-left" for="">Descripción <small class="text-danger">*</small></label>
				    <div class="col-lg-12">
                        {{ form.descripcion }}
                        <div class="completar">Coloque los campos a completar de la forma: [[respuesta]]. Ej: La capital del Perú es [[Lima]]</div>
                    </div>
				</div>

				{% if asignatura_examen_pregunta.examen.tipo_puntaje == 'INDIVIDUAL' %}
				<div class="form-group form-group-sm">
				    <label class="col-sm-4 control-label" for="">Puntos <small class="text-danger">*</small></label>
				    <div class="col-sm-6 col-xs-12 col-lg-4">{{ form.puntos }}</div>
				</div>
				{% endif %}
				

				<div class="form-group form-group-sm alternativas">
				    <div class="col-lg-12">
				    	<div class="panel panel-primary panel-bordered">
				    		<div class="panel-heading">
				    			<div class="panel-control">
				    				<div class="input-group-wrap">
				    					<div class="input-group text-right">
					    					<span class="input-group-btn">
					    						<a href="javascript:;" onclick="agregar_alternativa()" class="btn btn-success"><i class="fa fa-plus"></i></a>
					    					</span>
				    					</div>
				    				</div>
				    			</div>
				    			<h3 class="panel-title">Alternativas</h3>
				    		</div>
				    		<div class="panel-body">
				    			<table class="special">
									<tr>
										<th>Descripción</th>
										<th style="width: 60px">Correcta</th>
										<th style="width: 50px"></th>
									</tr>
									<tbody id="listaAlternativas">
									{% for alternativa in asignatura_examen_pregunta.getAlternativas() %}
									<tr>
										<td class="center">
											<script>
											$(function(){
												ckEditorVerySimple('#alternativa_{{ loop.index - 1 }}', 'eAlternativa{{ loop.index - 1 }}');
											})
											</script>
											<textarea type="text" name="alternativa[{{ _key }}][descripcion]" id="alternativa_{{ loop.index - 1 }}" class="input-sm form-control">{{ alternativa.descripcion }}</textarea>
											<input type="hidden" name="alternativa[{{ _key }}][edit]" value="{{ alternativa.id }}">
										</td>
										<td class="center">
											<label class="form-checkbox form-normal form-primary check"><input type="checkbox" name="alternativa[{{ _key }}][correcta]" {{ alternativa.correcta() ? 'checked' : '' }}></label>
										</td>
										<td class="center"><button class="btn-danger btn" onclick="$(this).parent().parent().remove()" type="button"><i class="fa fa-remove"></i></button></td>
									</tr>
									{% endfor %}

									</tbody>
								</table>

				    		</div>
				    	</div>
				    	
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
{% endblock %}
