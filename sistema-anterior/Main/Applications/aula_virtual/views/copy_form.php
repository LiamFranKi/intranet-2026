<script>
$(function(){
    $('#copyForm').niftyOverlay();
    $('#copyForm').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/aula_virtual/do_copy_form', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Contenido copiado correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});

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

    $('#files_id').select2({placeholder: '-- Seleccione --'})
    $('#homeworks_id').select2({placeholder: '-- Seleccione --'})
    $('#tests_id').select2({placeholder: '-- Seleccione --'})
    $('#videos_id').select2({placeholder: '-- Seleccione --'})
    $('#links_id').select2({placeholder: '-- Seleccione --'})
})
</script>
<form class="form" id="copyForm" data-target="#copyForm" data-toggle="overlay">
    <div class="modal-content" style="min-width: 800px">
        <div class="modal-header">
            <h3 class="modal-title">Copiar Contenido</h3>
        </div>
        <div class="modal-body">
           
            <div class="form-group">
                <label class="control-label">Copiar A <small class="text-danger">*</small></label>
                <select name="assignment_id" id="assignment_id" class="form-control">
                    {% for assignment in otherAssignments %}
                    <option value="{{ assignment.id }}">{{ assignment.curso.nombre }} - {{ assignment.grupo.getNombreShort() }}</option>
                    {% endfor %}
                </select>
            </div>

            <div class="form-group">
                <label class="control-label" style="display: block">Temas Interactivos</label>
                <select name="files_id[]" id="files_id" class="form-control" multiple>
                    {% for file in files %}
                    <option value="{{ file.id }}">B{{ file.ciclo }} - {{ file.nombre }}</option>
                    {% endfor %}
                </select>
            </div>

            <div class="form-group">
                <label class="control-label" style="display: block">Tareas Virtuales</label>
                <select name="homeworks_id[]" id="homeworks_id" class="form-control" multiple>
                    {% for homework in homeWorks %}
                    <option value="{{ homework.id }}" >B{{ homework.ciclo }} - {{ homework.titulo }}</option>
                    {% endfor %}
                </select>
            </div>

            <div class="form-group">
                <label class="control-label" style="display: block">Exámenes </label>
                <select name="tests_id[]" id="tests_id" class="form-control" multiple>
                    {% for test in tests %}
                    <option value="{{ test.id }}" >B{{ test.ciclo }} - {{ test.titulo }}</option>
                    {% endfor %}
                </select>
            </div>

            <div class="form-group">
                <label class="control-label" style="display: block">Videos </label>
                <select name="videos_id[]" id="videos_id" class="form-control" multiple>
                    {% for video in videos %}
                    <option value="{{ video.id }}" >B{{ video.ciclo }} - {{ video.descripcion }}</option>
                    {% endfor %}
                </select>
            </div>

            <div class="form-group">
                <label class="control-label" style="display: block">Enlaces </label>
                <select name="links_id[]" id="links_id" class="form-control" multiple>
                    {% for link in links %}
                    <option value="{{ link.id }}" >B{{ link.ciclo }} - {{ link.descripcion }}</option>
                    {% endfor %}
                </select>
            </div>

        </div>

        <div class="modal-footer">
            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="$.fancybox.close()">Cancelar</button>
            <button class="btn btn-primary" type="submit">Guardar Datos</button>
        </div>
    </div>
</form>