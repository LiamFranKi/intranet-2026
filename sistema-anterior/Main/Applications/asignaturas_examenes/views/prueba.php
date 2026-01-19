<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Exámenes</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="javascript:;" onclick="history.back(-1)">Examenes</a></li>
		<li class="active">{{ examen.titulo }} - Bimestre {{ examen.ciclo }}</li>
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
function getCheckedSummary(){
    let content = "<table class='special'>";
    const items = document.querySelectorAll('.pregunta.pregunta-description,.pregunta:has(input[type=radio]:checked), .pregunta:has(input[type=text])');

    for(i in items){
        if($.isNumeric(i)){
            console.log(items[i])
            content += items[i].outerHTML;
        }
        
    }

    content += '</table>';

    let result = $(content)
    $('td:has(input[type=radio])', result).remove()
    $('.pregunta', result).show()
    $('.descripcionPregunta', result).attr('colspan', 2)
    
    return result[0].outerHTML;
}
function finalizarPruebaConfirm(){
    if(!navigator.onLine){
        return zk.pageAlert({message: 'No es posible finalizar la prueba por error en la conexión.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
    }
    let summary = getCheckedSummary();
    

    zk.confirm('<b>¿Está seguro(a) de finalizar la prueba? <br />Por favor verifique las respuestas marcadas. </b><br /><br />' + summary, function(){
        finalizarPrueba();
    })
}
function finalizarPrueba(){
	$.post('/asignaturas_examenes/finalizar_prueba', $('#formPrueba').serialize(), function(r){
		if(r[0] == 0){
			zk.pageAlert({message: 'No se pudo finalizar la prueba, intenta nuevamente.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
		}else{
			zk.pageAlert({message: 'La prueba ha finalizado', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
			zk.goToUrl('/aula_virtual/index/{{ sha1(examen.asignatura_id) }}')
		}
	}, 'json');
}

var current = 1;
$(function(){
	$('.check').niftyCheck();
    $('.respuesta-completar').on('blur', function(){
        //let _this = $(this);
		$.post('/asignaturas_examenes/save_respuestas', $('#formPrueba').serialize(), function(r){
			if(r[0] == 0){
				zk.pageAlert({message: 'No se pudo guardar la respuesta, intenta nuevamente.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
                //_this.prop('checked', false)
                
			}else{
                zk.pageAlert({message: 'Respuesta guardada correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
            }
		}, 'json').fail(function(){
            //_this.prop('checked', false)
            e.preventDefault();
			zk.pageAlert({message: 'No se pudo guardar la respuesta, intenta nuevamente.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			
		});
    })

	$('.respuesta').click(function(e){
        let _this = $(this);
		$.post('/asignaturas_examenes/save_respuestas', $('#formPrueba').serialize(), function(r){
			if(r[0] == 0){
				zk.pageAlert({message: 'No se pudo guardar la respuesta, intenta nuevamente.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
                _this.prop('checked', false)
                
			}else{
                zk.pageAlert({message: 'Respuesta guardada correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
            }
		}, 'json').fail(function(){
            _this.prop('checked', false)
            e.preventDefault();
			zk.pageAlert({message: 'No se pudo guardar la respuesta, intenta nuevamente.', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});	
			
		});
	});

	{% if examen.hasTiempoLimite() %}
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

	mostrar()
})

function mostrar(){
	$('.pregunta').hide();
	$('.pregunta-' + current).fadeIn();
}

function mover(tipo){
    //console.log(current, tipo)
	if(current == 1 && tipo == "back"){
		return false;
	}

	if(current == {{ preguntas|length }} && tipo == "next"){
		return false;
	}

	if(tipo == "back")
		current--;

	if(tipo == "next")
		current++;

	mostrar();
}
</script>
<div id="page-content">
	<div class="panel">
		<div class="panel-body">
			{% if examen.hasTiempoLimite() %}
			<p class="text-center"><b style="color: black; font-size: 25px" id="mainTimer-{{ timerContainer }}"></b></p>
			{% endif %}
			<div class="text-center"><button class="btn btn-primary btn-block" type="button" onclick="finalizarPruebaConfirm()">Finalizar Prueba</button></div>
		</div>
	</div>

	<div class="panel">
					
        <!--Panel heading-->
        <div class="panel-heading">
            <div class="panel-control">
                <ul class="pager pager-rounded">
                    <li><a href="javascript:;" onclick="mover('back')">Anterior</a></li>
                    <li><a href="javascript:;" onclick="mover('next')">Siguiente</a></li>
                </ul>
            </div>
            <h3 class="panel-title">{{ examen.titulo }}</h3>
        </div>

        <!--Panel body-->
        <div class="panel-body">
        	<form id="formPrueba">
				<input type="hidden" name="id" value="{{ prueba.id }}" />
				<input type="hidden" name="token" value="{{ prueba.token }}" />
				<input type="hidden" name="time" value="{{ prueba.fecha_hora|strtotime }}" />
	        	<!--
	        	{% for pregunta in preguntas %}
	        	<div id="pregunta-{{ loop.index }}" class="pregunta">{{ pregunta.descripcion }}</div>
	        	{% endfor %}
	        	-->

	        	{% if preguntas|length > 0 %}
				<table class="special">
					{% for pregunta in preguntas %}
						{% set preguntaKey = loop.index %}

                        {% if pregunta.tipo == "ALTERNATIVAS" %}
                            <tr class="pregunta pregunta-{{ preguntaKey }} pregunta-description">
                                <th style="width: 40px">{{ loop.index }}</th>
                                <td style="padding: 10px 10px 5px 10px">{{ pregunta.descripcion }}</td>
                            </tr>
                        
                            {% set alternativas = pregunta.getAlternativas(true) %}
                            {% for alternativa in alternativas %}
                            <tr class="pregunta pregunta-{{ preguntaKey }}">
                                <td class="center" style="width: 50px">
                                    <!-- <label class="form-radio form-normal check">
                                        
                                    </label> -->
                                    <input type="radio" class="respuesta" name="respuestas[{{ pregunta.id }}]" value="{{ alternativa.id }}" {{ respuestas[pregunta.id] == alternativa.id ? 'checked' : '' }}>
                                </td>
                                <td class="descripcionPregunta">{{ alternativa.descripcion }}</td>
                            </tr>
                            {% endfor %}
                        {% else %}
                        <tr class="pregunta pregunta-{{ preguntaKey }} pregunta-description">
                            <th style="width: 40px">{{ loop.index }}</th>
                            <th style="padding: 10px 10px 5px 10px">Completa el texto</th>
                        </tr>
                        <tr class="pregunta pregunta-{{ preguntaKey }}">
                            <td></td>
                            <td>
                                {{ pregunta.getFormCompletar(respuestas[pregunta.id]) }}
                            </td>
                        </tr>
                        {% endif %}

					{% endfor %}
				</table>
				{% else %}
				<p class="text-center"><b>NO SE REGISTRARON PREGUNTAS PARA ESTE CURSO</b></p>
		    	{% endif %}
	        </form>
        </div>
    </div>
</div>