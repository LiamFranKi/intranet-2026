{% extends main_template %}
{% block main_content %}
<!-- JAVASCRIPT -->
<script>
$(function(){
	$('.check').niftyCheck();
	$('#formAsignatura_Tarea').niftyOverlay();
	$('#formAsignatura_Tarea').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/asignaturas_tareas/save_entrega', function(r){
				

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

});

</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Tareas Virtuales</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		
		<li class="active">Registro de Entregas</li>
	</ol>
</div>
<div id="page-content">

	<form class="form-horizontal" autocomplete="off" id="formAsignatura_Tarea" data-target="#formAsignatura_Tarea" data-toggle="overlay">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">Registrar Entregas</h3>
			</div>
			<div class="panel-body">
				<table class="special">
		            <tr>
		                <th>Nº</th>
		                <th>Apellidos y Nombres</th>
		                <th style="cursor: pointer" id="ehead">Entregado</th>
		                <th>Nota</th>
		                <th style="width: 70px">Visto</th>

		                <th>Archivo(s)</th>
		                <!--<th>Respuestas</th>-->
		            </tr>
		            {% for matricula in matriculas %}
		            {% set alumno = matricula.alumno %}
		            {% set entregado = asignatura_tarea.getEntrega(matricula.id) %}
		                <tr>
		                    <td class="center">{{ loop.index }}</td>
		                    <td>{{ alumno.apellido_paterno|upper }} {{ alumno.apellido_materno|upper }}, {{ alumno.nombres|upper }}</td>
		                    <td class="center">
		                        <label class="form-checkbox form-normal form-primary check"><input type="checkbox" name="entregado[]" value="{{ matricula.id }}" {{ entregado ? 'checked' : '' }} /></label>
		                    </td>
		                    <td class="text-center">
		                        {% set nota = asignatura_tarea.getNota(matricula) %}
		                        <input type="text" class="form-control text-center" name="notas[{{ matricula.id }}]" style="width: 70px" value="{{ nota ? nota.nota : '' }}" />
		                        
		                    </td>
		                    <td class="center">{{ asignatura_tarea.getView(alumno.id) ? 'SI' : 'NO' }}</td>
		                    
		                    {% set entregas = asignatura_tarea.getEntregasAlumno(alumno.id) %}
		                    {% if asignatura_tarea.haveUploadedFile(alumno.id) or entregas|length > 0  %}
		                    {% set archivo = asignatura_tarea.getFile(alumno.id) %}
		                        <!--
		                        <a href="/Static/archivos/{{ archivo }}" download="{{ archivo }}" class="btn">Descargar Archivo</a>
		                        -->
		                        <td class="text-center" style="width: 120px">
		                            <div class="btn-group dropdown">
		                                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">Archivos <i class="caret"></i></button>
		                                <ul class="dropdown-menu dropdown-menu-right" role="menu"> 
		                                    

		                                    {% for entrega in entregas %}
		                                        {% if entrega.archivo %}
		                                        <li><a href="/Static/archivos/{{ entrega.archivo }}" download="{{ entrega.nombre }}"><b>{{ entrega.nombre }}</b><br /><small>Fecha: {{ entrega.fecha_hora|date('d-m-Y h:i A') }}</small></a></li>
		                                        {% endif %}


		                                        {% if entrega.url %}
		                                        <li><a href="{{ entrega.url }}" target="_blank"><b>{{ entrega.url }}</b><br /><small>Fecha: {{ entrega.fecha_hora|date('d-m-Y h:i A') }}</small></a></li>
		                                        {% endif %}
		                                    {% endfor %}
		                                    
		                                    {% if archivo %}
		                                    <li><a href="/Static/archivos/{{ asignatura_tarea.getFileReal(alumno.id) }}" download="{{ asignatura_tarea.getFileName(alumno.id) }}"><b>{{ asignatura_tarea.getFileName(alumno.id) }}</b><br /><small>Fecha: -</small></a></li>
		                                    {% endif %}
		                                </ul>
		                            </div>
		                        </td>
		                    {% else %}
		                        <td class="center"><b>NINGUNO</b></td>
		                    {% endif %}
		                    
		                    
		                    <!--
		                    <td class="text-center">
		                        <button class="btn btn-default" onclick="fancybox('/asignaturas_tareas/respuestas?tarea_id={{ asignatura_tarea.id }}&alumno_id={{ alumno.id }}')">{{ icon('report_go') }} Responder</button>
		                    </td>
		                	-->
		                </tr>
		            {% endfor %}
		        </table>
		        <input type="hidden" name="id" value="{{ sha1(asignatura_tarea.id) }}" />
			
			</div>
		
			<div class="modal-footer">
	            <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
	            <button class="btn btn-primary" type="submit">Guardar Datos</button>
	        </div>
		</div>
	</form>
</div>
{% endblock %}
