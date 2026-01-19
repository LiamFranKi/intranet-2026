<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Examenes de Bloques</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/examenes_bloques/alumno">Examenes Bloques</a></li>
		<li class="active">{{ examen.titulo }} - Bimestre {{ compartido.ciclo }} - {{ COLEGIO.roman(compartido.nro) }}</li>
	</ol>
</div>
<style>
figure img{
	max-width: 100%;
}
.nav-tabs a{
	font-size:  12px;
}
.nav-tabs>.active>a {
    background-color: transparent;
    box-shadow: inset 0 -2px 0 0 #1e3a57 !important;
    color: inherit;
}

.image-style-align-center{
	text-align:  center;
}
</style>
{% set timerContainer = getToken() %}
<script>
function finalizarPruebaConfirm(){
	if(confirm('¿Está seguro(a) de finalizar la prueba?'))
		finalizarPrueba();
}
function finalizarPrueba(){
	$.post('/examenes_bloques/finalizar_prueba', $('#formPrueba').serialize(), function(r){
		if(r[0] == 0){
			zk.pageAlert({message: 'No se pudo finalizar la prueba, intenta nuevamente.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
		}else{
			zk.pageAlert({message: 'La prueba ha finalizado', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
			zk.goToUrl('/examenes_bloques/alumno');
		}
	}, 'json');
}

$(function(){
	$('.check').niftyCheck();

	$('.respuesta').click(function(){
		$.post('/examenes_bloques/save_respuestas', $('#formPrueba').serialize(), function(r){
			if(r[0] == 0){
				zk.pageAlert({message: 'No se pudo guardar la respuesta, intenta nuevamente.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			}
		}, 'json').fail(function(){
			zk.pageAlert({message: 'No se pudo guardar la respuesta, intenta nuevamente.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			
		});
	});

	{% if compartido.hasTiempoLimite() %}
	var horas, minutos, segundos;
	var remainingTime = parseInt({{ remainingTime }});
	Number.prototype.padLeft = function (n,str){
	    return Array(n-String(this).length+1).join(str||'0')+this;
	}
	function updateTimer(){
		horas = Math.floor(remainingTime / 3600);
		minutos = Math.floor((parseInt(remainingTime) % 3600) / 60);
		segundos = ((parseInt(remainingTime) % 3600) % 60);
		$('#mainTimer-{{ timerContainer }}').html(horas.padLeft(2) + ':' + minutos.padLeft(2) + ':' + segundos.padLeft(2));
		setTimeout(function(){
			if(remainingTime > 0){
				--remainingTime;
				updateTimer();
			}else{
				finalizarPrueba();
			}
		}, 1000);
	}

	updateTimer();
	{% endif %}
})
</script>
{% set cursos = examen.getCursos() %}
<div id="page-content">
	<div class="panel">
		<div class="panel-body">
			{% if compartido.hasTiempoLimite() %}
			<p class="text-center"><b style="color: black; font-size: 25px" id="mainTimer-{{ timerContainer }}"></b></p>
			{% endif %}
			<div class="text-center"><button class="btn btn-primary btn-block" type="button" onclick="finalizarPruebaConfirm()">Finalizar Prueba</button></div>
		</div>
	</div>

	<div class="panel">
		
		<div class="panel-body">
			<form id="formPrueba">
				<input type="hidden" name="id" value="{{ prueba.id }}" />
				<input type="hidden" name="token" value="{{ prueba.token }}" />
				<input type="hidden" name="time" value="{{ prueba.fecha_hora|strtotime }}" />
				{% if examen.getCursosID()|length > 0 %}
					
					<ul class="nav nav-tabs" role="tablist">
					  	{% for curso in cursos %}
					    <li class="{{ loop.index == 1 ? 'active' : '' }}"><a href="#curso_{{ loop.index }}" aria-controls="home" role="tab" data-toggle="tab"><b>{{ curso.nombre|upper }}</b></a></li>
					    {% endfor %}
					</ul>
				

					<div>
					  <div class="tab-content">
					  	{% for curso in cursos %}
					    <div role="tabpanel" class="tab-pane fade {{ loop.index == 1 ? 'in active' : '' }}" id="curso_{{ loop.index }}" style="padding-top: 20px">
					    	{% set preguntas = prueba.getPreguntas(curso.id, true) %}
							{% if preguntas|length > 0 %}
							<table class="special">
								{% for pregunta in preguntas %}
								<tr>
									<th style="width: 40px">{{ loop.index }}</th>
									<td style="padding: 10px 10px 5px 10px">{{ pregunta.descripcion }}</td>
									<td style="vertical-align: top">
										<table class="special">
											<!--
											<tr>
												<th colspan="2">ALTERNATIVAS</th>
											</tr>
											-->
											{% set alternativas = pregunta.getAlternativas(true) %}
											{% for alternativa in alternativas %}
											<tr>
												<td class="center" style="width: 50px">
													<label class="form-radio form-normal check">
														<input type="radio" class="respuesta" name="respuestas[{{ pregunta.id }}]" value="{{ alternativa.id }}" {{ respuestas[pregunta.id] == alternativa.id ? 'checked' : '' }}>
													</label>
												</td>
												<td class="descripcionPregunta">{{ alternativa.descripcion }}</td>
											</tr>
											{% endfor %}
										</table>
									</td>
								</tr>
								{% endfor %}
							</table>
							{% else %}
							<p class="text-center"><b>NO SE REGISTRARON PREGUNTAS PARA ESTE CURSO</b></p>
					    	{% endif %}
					    </div>
					    {% endfor %}
					    
					  </div>

					</div>
				{% else %}
				<p class="text-center"><b>NO SE ENCONTRARON CURSOS</b></p>
				{% endif %}
			</form>
		</div>
	</div>
</div>