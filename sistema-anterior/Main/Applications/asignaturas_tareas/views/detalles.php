<script>
function quitarArchivoNuevo(id, tarea_id){
	zk.confirm('¿Está seguro de borrar los datos?', function(){
		zk.sendData('/asignaturas_tareas/quitar_archivo_nuevo', {alumno_id: id, tarea_id: tarea_id}, function(r){
			if(parseInt(r[0]) == 1){
				zk.pageAlert({message: 'Datos borrados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else{
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		});
	});
}
</script>
<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Detalles de la Tarea</h3>
		</div>
		<div class="panel-body">
			<table class="special" style="width:100%">
				<tr>
					<th style="width: 200px">Título</th>
					<td>{{ asignatura_tarea.titulo }}</td>
				</tr>
				<tr>
					<th>Descripción</th>
					<td>{{ asignatura_tarea.descripcion|nl2br }}</td>
				</tr>
				<tr>
					<th>Fecha de Registro</th>
					<td>{{ asignatura_tarea.fecha_hora|date('d-m-Y') }}</td>
				</tr>
				<tr>
					<th>Fecha de Entrega</th>
					<td>{{ asignatura_tarea.fecha_entrega|date('d-m-Y') }}</td>
				</tr>
				<tr>
					<th>Enviador por</th>
					<td>{{ asignatura_tarea.asignatura.personal.getFullName() }}</td>
				</tr>

				{% if asignatura_tarea.getArchivos()|length > 0 %}
				<tr>
					<th>Archivos Adjuntos</th>
					<td>
						<table class="special">
						{% for archivo in asignatura_tarea.getArchivos() %}
							<tr>
								<td><a href="/Static/Archivos/{{ archivo.archivo }}" download="{{ archivo.nombre }}"><b>{{ archivo.nombre }}</b></a></td>
							</tr>
						{% endfor %}
						</table>
					</td>
				</tr>
				{% endif %}

                {% if asignatura_tarea.enlace %}
                <tr>
                    <th>URL</th>
                    <td><a href="{{ asignatura_tarea.enlace }}" target="_blank">{{ asignatura_tarea.enlace }}</a></td>
                </tr>
                {% endif %}

				{% set entregas = asignatura_tarea.getEntregasAlumno(USUARIO.alumno_id) %}
				{% if entregas|length > 0 %}
				<tr>
					<th>Archivo(s) Enviado(s)</th>
					<td>
						<table class="special">
						

						{% for entrega in entregas %}
						

						{% if entrega.url %}
						<tr>
							<td style="width: 300px">
								<a href="{{ entrega.url }}" target="_blank">{{ entrega.url|substr(0, 50) }}...</a>
							</td>
							<td class="text-center" style="width: 130px"><small>{{ entrega.fecha_hora|date('d-m-Y h:i A') }}</small></td>
							<td class="text-center" style="width: 70px"><a href="javascript:;" onclick="quitarArchivoNuevo('{{ USUARIO.alumno_id }}', '{{ entrega.id }}')">{{ icon('delete') }}</a></td>
						</tr>
						{% endif %}
							
						{% endfor %}
						</table>
					</td>
				</tr>
				<tr>
					<th>Entregado</th>
					<td>SI</td>
				</tr>
				{% endif %}
				{% if USUARIO.is('ALUMNO') %}
					{% set entregado = asignatura_tarea.getEntrega(matricula.id) %}

					{% set nota = asignatura_tarea.getNota(matricula) %}
					
					<tr>
						<th>Nota</th>
						<td class="text-bold">{{ nota ? nota.nota : '-' }}</td>
					</tr>
				{% endif %}
			</table>
		</div>
	</div>

	{% if strtotime(date()|date('Y-m-d')) <= strtotime(asignatura_tarea.fecha_entrega) %}
	{% if USUARIO.is('ALUMNO') %}

	<script>
	$(function(){
		$('#fsend').niftyOverlay();
		$('#fsend').submit(function(e){
			e.preventDefault();
			$(this).sendForm('/asignaturas_tareas/do_enviar_archivo', function(r){
				if(parseInt(r[0]) == 1){
					zk.pageAlert({message: 'Archivo enviado correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
					zk.reloadPage()
				}else{
					zk.pageAlert({message: 'No se pudo enviar el archivo.', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				}
			});
		});
	});
	</script>
	<div class="panel mar-top" id="ctrabajo">
		<div class="panel-heading">
			<h4 class="panel-title">Enviar Trabajo Realizado</h4>
		</div>
		<div class="panel-body">
			<!--
			<div class="alert alert-info">
				* Asegúrate de seleccionar el archivo correcto, ya que una vez subido no podrás cambiarlo.<br />
				* Si deseas enviar más de un archivo, recomendamos subir un archivo comprimido (*.rar, *.zip).
		 	</div>
		 	-->
		 	<div class="alert alert-info text-center">
		 		Ingresa la URL del archivo (Drive, Dropbox, etc).
		 	</div>
		 	<form autocomplete="off"  id="fsend" data-toggle="overlay" data-target="#ctrabajo">
				<table class="special">
					<!--
					<tr>
						<th>Archivo</th>
						<td class="text-center"><input type="file" name="archivo" class="required" /></td>
						<td class="text-center"><button class="btn btn-primary">Subir Archivo</button></td>
					</tr>
					-->
					<tr>
						<th>Url:</th>
						<td class="text-center">
							<input type="text" name="url" class="form-control" required />
						</td>
						<td class="text-center"><button class="btn btn-primary">Enviar</button></td>
					</tr>
				</table>
				<input type="hidden" name="alumno_id" value="{{ USUARIO.alumno_id }}" />
				<input type="hidden" name="id" value="{{ sha1(asignatura_tarea.id) }}" />
		 	</form>
		</div>
	</div>

	 	
	 {% endif %}
	 {% endif %}
</div>