<script>
var postsApp;
$(function(){
	postsApp = new PublicacionesApp('#containerPublicaciones');
	postsApp.loadPage(1)


	$('#publicar').niftyOverlay();
	$('#linkSubirFoto').on('click', function(){
		$('#x_imagen').trigger('click')
	})
	$('#linkSubirArchivo').on('click', function(){
		$('#x_archivo').trigger('click')
	})

	$('#x_imagen').on('change', function(){
		if(this.value != null){
			$('#formImagen').trigger('submit');
		}
	})

	$('#x_archivo').on('change', function(){
		if(this.value != null){
			$('#formArchivo').trigger('submit');
		}
	})

	$('#formImagen').on('submit', function(e){
		e.preventDefault();
		$('#publicar').niftyOverlay('show');
		$('#formImagen').sendForm('/publicaciones/subir_foto', function(r){
			$('#publicar').niftyOverlay('hide');
			if(r[0] == 0){
				return zk.pageAlert({message: 'No se pudo subir la foto', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});;
			}

			$('#publicacion_images').show();
			$('#publicacion_images').append(`<div onclick="$(this).remove()" class="col-lg-4 col-sm-4 col-xs-4 pad-top">
							<input type="hidden" name="x_images[]" value="`+ r.imagen +`">
							<img src="`+ r.imagen +`" style="max-width: 100px">
						</div>`)
		});
	})

	$('#formArchivo').on('submit', function(e){
		e.preventDefault();
		$('#publicar').niftyOverlay('show');
		$('#formArchivo').sendForm('/publicaciones/subir_archivo', function(r){
			$('#publicar').niftyOverlay('hide');
			if(r[0] == 0){
				return zk.pageAlert({message: 'No se pudo subir el archivo', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});;
			}
			$('#publicacion_archivos').show();
			$('#publicacion_archivos>table').append(`<tr>
								<td>
								<a href="/Static/Archivos/`+ r.archivo +` target="_blank" download="`+ r.nombre +`">`+ r.nombre +`</a>
								<input type="hidden" name="x_archivos[`+ r.nombre +`]" value="`+ r.archivo +`">
								</td>
								<td class="text-center" style="width: 50px"><button type="button" onclick="$(this).parent().parent().remove()" class="btn btn-danger"><i class="fa fa-remove"></i></button></td>
							</tr>`)
		});
	})

	$('#privacy').chosen();

	$('#formPublicacion').on('submit', function(e){
		
		e.preventDefault();
		if($('#privacy').val() == null){
			return zk.pageAlert({message: 'Elija con quien compartir la publicación', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});;
		}
		$('#publicar').niftyOverlay('show');
		$(this).sendForm('/publicaciones/save', function(r){
			$('#publicar').niftyOverlay('hide');
			if(r[0] == 1){
				postsApp.loadPage(1)
				clearFormPublicaciones()
				zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
			}else{
				zk.pageAlert({message: 'No se pudo guardar la publicación', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});;
			}
		});
	})
})

function clearFormPublicaciones(){
	$('[name="publicacion_contenido"]').val('')
	$('#privacy').val('')
	$('#publicacion_images').html('')
	$('#publicacion_archivos>table').find('tr').remove();
}
</script>
<div class="panel">
	{% if not USUARIO.is('ALUMNO') and not USUARIO.is('APODERADO') %}
	<div class="panel-body" id="publicar" style="padding-bottom: 0px" data-toggle="overlay" data-target="#publicar">
		<div class="pad-btm">
			<form id="formPublicacion">
				<div class="mar-btm clearfix">
				    <a class="btn btn-icon demo-pli-camera-2 icon-lg add-tooltip" href="javascript:;" id="linkSubirFoto" data-original-title="Agregar Foto" data-toggle="tooltip"></a>
				    <a class="btn btn-icon demo-pli-file icon-lg add-tooltip" href="javascript:;" id="linkSubirArchivo" data-original-title="Agregar Archivo" data-toggle="tooltip"></a>
				</div>

				<textarea class="form-control" rows="4" name="publicacion_contenido" placeholder="¿Tienes algo que compartir?"></textarea>
				<div class="mar-top" >
					<div class="row" id="publicacion_images" style="display: none">
					</div>
				</div>
				<div class="mar-top">
					<div class="row" id="publicacion_archivos" style="display: none">
						<table class="special">
							
						</table>
					</div>
				</div>

				<div class="mar-top clearfix">
					<label for="">Compartir Con: </label>
					<select name="privacy[]" id="privacy" data-placeholder="Compartir con" multiple class="form-control">
						<option value="-1">TODOS</option>
						<option value="-2">PERSONAL ADMINISTRATIVO</option>
						{% for grupo in USUARIO.personal.getGruposAsignados(COLEGIO.anio_activo) %}
						<option value="{{ grupo.id }}">{{ grupo.tutor_id == USUARIO.personal_id ? 'TUTOR - ' : '' }}{{ grupo.getNombre() }}</option>
						{% endfor %}
					</select>

				    <button class="btn mar-top btn-sm btn-primary pull-right" type="submit"><i class="demo-psi-right-4 icon-fw"></i> Compartir</button>
				</div>
			</form>

			<form id="formImagen" style="display: none">
				<input type="file" name="x_imagen" id="x_imagen" />
			</form>
			<form id="formArchivo" style="display: none">
				<input type="file" name="x_archivo" id="x_archivo" />
			</form>
		</div>
		<hr>
	</div>
	{% endif %}
	<div class="panel-body" id="containerPublicaciones" data-target="#containerPublicaciones" data-toggle="overlay">
	</div>
</div>