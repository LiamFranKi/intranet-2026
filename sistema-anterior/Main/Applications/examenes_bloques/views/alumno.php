<script>
$(function(){
	setMenuActive('examenes_bloques');
	$('.tip').tooltip({html: true});
})

function iniciarPrueba(id){
	_functions = {
		clearAndRetry: function(e, v){
			e.preventDefault();
			if(v == 1){
				$('#pw1').val('');
				return $.prompt.goToState('state1');
			}
			$.prompt.close();
		}
	};
	
	_data = {
		state1:{
			html: '<label>Ingrese la contraseña para iniciar:</label> <input type="password" name="password" id="pw1" class="form-control" />',
			buttons: {
				
				"Cancelar": 0,
				
				"Aceptar": 1
			},
			submit: function(e, v){
				e.preventDefault();
				if(v == 1){
					pw1 = $('#pw1').val();
					if(pw1 == ''){
						return $.prompt.goToState('blank');
					}
					
					return $.post('/examenes_bloques/iniciar_prueba', {password: pw1, compartido_id: id, matricula_id: '{{ matricula.id }}'}, function(r){
						if(r[0] == -5){
							return $.prompt.goToState('incorrect');
						}
						if(r[0] == -2){
							return $.prompt.goToState('cantDoTest');
						}

						$.prompt.close()
						zk.goToUrl('/examenes_bloques/prueba/' + r.prueba_id + '&token=' + r.token + '&time=' + r.time);
					}, 'json');
				}
				$.prompt.close();
			}
		},
		
		blank: {
			html: 'La contraseña no puede quedar vacía',
			buttons: {
				
				"Cancelar": 0,
				
				"Intentar Nuevamente": 1
			},
			submit: _functions.clearAndRetry
		},
		cantDoTest: {
			html: 'Ya superó la máxima cantidad de intentos.'
		},
		incorrect: {
			html: 'La contraseña es incorrecta',
			buttons: {
				
				"Cancelar": 0,
				
				"Intentar Nuevamente": 1
			},
			submit: _functions.clearAndRetry
		},
	}
	
	$.prompt(_data);
}
</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Examenes de Bloques</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/examenes_bloques/alumno">Examenes Bloques</a></li>
		<li class="active">Lista de Exámenes</li>
	</ol>
</div>

<div id="page-content">
	<div class="panel">
					
        <!--Panel heading-->
        <div class="panel-heading">
            <div class="panel-control">

                <!--Nav tabs-->
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#tabs-box-1" aria-expanded="true">Activos</a></li>
                    <li class=""><a data-toggle="tab" href="#tabs-box-2" aria-expanded="false">Archivados</a></li>
                </ul>

            </div>
            <h3 class="panel-title">Exámenes Bloques</h3>
        </div>

        <!--Panel body-->
        <div class="panel-body">

            <!--Tabs content-->
            <div class="tab-content">
                <div id="tabs-box-1" class="tab-pane fade active in">
                	{% if compartidos|length > 0 %}
					<table id="lexamenes" class="dataTable table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>Titulo - Curso</th>
								<th>Bimestre</th>
								<th>Tiempo - Preguntas</th>
						
								<th>Activo Hasta</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							{% for compartido in compartidos %}
							<tr>			
								<td>{{ compartido.examen.titulo }}</td>
								<td class="text-center">Bimestre {{ compartido.ciclo }} - {{ COLEGIO.roman(compartido.nro) }}</td>
								<td class="text-center">{{ compartido.tiempo == 0 ? 'ILIMITADO' : compartido.tiempo~" min" }} - {{ compartido.examen.getCursos()|length }} cursos</td>		
								
								<td class="text-center">{{ COLEGIO.getFechaHora(compartido.expiracion) }}</td>
								<td class="text-center">

									{% set pruebaActiva = matricula.getPruebaActivaBloque(compartido) %}
									{% if pruebaActiva and pruebaActiva.activa() %}
										<button class="btn-success btn tip" onclick="zk.goToUrl('/examenes_bloques/prueba/{{ sha1(pruebaActiva.id) }}?token={{ pruebaActiva.token }}&time={{ pruebaActiva.fecha_hora|strtotime }}')" {{ pruebaActiva.compartido.hasTiempoLimite() ? 'title="Esta prueba terminará el: <br />'~COLEGIO.getFechaHora(pruebaActiva.expiracion)~'"' : '' }}>Continuar Prueba</button>
									{% else %}
										{% if matricula.canDoTestBloque(compartido) %}
										<button class="btn-primary btn" onclick="iniciarPrueba('{{ compartido.id }}')">Iniciar Prueba</button>
										{% else %}

										{% if compartido.getEstado() == 'Inactivo' %}
											{% set prueba = matricula.getBestTestBloque(compartido) %}
											{% if prueba %}
											<a class="btn-primary btn" href="#/examenes_bloques/resultados_respuestas/{{ sha1(prueba.id) }}">{{ icon('application_view_list') }} Ver Resultados</a>
											{% else %}
											<button class="btn-danger btn">No Disponible</button>
											{% endif %}
										{% else %}
											<button class="btn-danger btn">Finalizado</button>
										{% endif %}
										<!--<button class="btn-primary btn tip" title="Finalizado" onclick="">PRUEBA FINALIZADA</button>-->
										{% endif %}
									{% endif %}
									
								</td>
							</tr>
							{% endfor %}
						</tbody>
					</table>
					{% else %}
					<p class="text-center"><b>NO SE ENCONTRARON RESULTADOS</b></p>
					{% endif %}
                </div>
                <div id="tabs-box-2" class="tab-pane fade">
                    {% if archivados|length > 0 %}
					<table id="lexamenes" class="dataTable table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>Titulo - Curso</th>
								<th>Bimestre</th>
								<th>Tiempo - Preguntas</th>
						
								<th>Activo Hasta</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							{% for compartido in archivados %}
							<tr>			
								<td>{{ compartido.examen.titulo }}</td>
								<td class="text-center">Bimestre {{ compartido.ciclo }} - {{ COLEGIO.roman(compartido.nro) }}</td>
								<td class="text-center">{{ compartido.tiempo == 0 ? 'ILIMITADO' : compartido.tiempo~" min" }} - {{ compartido.examen.getCursos()|length }} cursos</td>		
								
								<td class="text-center">{{ COLEGIO.getFechaHora(compartido.expiracion) }}</td>
								<td class="text-center">
									{% set prueba = matricula.getBestTestBloque(compartido) %}
									{% if prueba %}

									<button class="btn-default btn" onclick="fancybox('/examenes_bloques/resultados_respuestas/{{ prueba.id }}')">{{ icon('application_view_list') }} Ver Resultados</button>
									{% endif %}
								</td>
							</tr>
							{% endfor %}
						</tbody>
					</table>
					{% else %}
					<p class="text-center"><b>NO SE ENCONTRARON RESULTADOS</b></p>
					{% endif %}
                </div>
            </div>
        </div>
    </div>
</div>