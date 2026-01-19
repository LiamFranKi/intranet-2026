
<script>
function ActionResponse(r){
    switch(parseInt(r[0])){
        case 1:
            $.each($('.line-alumno'), function(key_alumno, line_alumno){
				notas = r[1][key_alumno].notas;
				$.each($('.x_c', $(line_alumno)), function(key_field, field){
					console.log(field);
					nota = parseInt(notas[key_field]);
                    if(notas[key_field] != undefined && notas[key_field] != null){
					   $(field).val(nota);
                    }
					if(key_field == (notas.length) - 1) $(field).trigger('blur');
				});
				console.log(line_alumno);
			});
		
            $('#cfupload').hide();
        break;
        
        case 0:
            $.prompt('No se pudo cargar el archivo');
        break;
    }
}
$(function(){
    $('#fupload').submit(function(e){
        e.preventDefault();
        if($('#x_archivo').val() == null || $('#x_archivo').val() == '') return jAlert('Seleccione un archivo');
        $(this).sendGateway('/notas/do_cargar_archivo_full');
    });
});
</script>
<div id="cfupload" class="block" style="display: none">
    <h2>Cargar Notas</h2>
    <div class="alert alert-info center">Seleccione el archivo excel (*.xlsx), si no cuenta con uno puede descargar este <a href="/notas/full_example?asignatura_id={{ asignatura.id }}&ciclo={{ get.ciclo }}&title={{ curso.nombre|upper }} - {{ asignatura.grupo.anio }}" >archivo</a>.</div>
    <p>
    <form id="fupload" class="form-inline center">
        <input type="file" name="archivo" id="x_archivo" style="width: 250px; display: inline-block" />
        <input type="hidden" name="total_criterios" value="{{ criterios|length }}" />
        <button class="btn btn-default">Cargar Archivo</button>
        <button class="btn btn-default" type="button" onclick="$('#cfupload').hide()">Cancelar</button>
    </form>
    </p>
</div>
