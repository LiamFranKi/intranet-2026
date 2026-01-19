<script>
$(function(){
    $('#exportBtn').on('click', function(){
        $(this).hide();
        exportarCanvasPDF("#notasDetalladas", function(){
            $('#exportBtn').show();
        })
    })
})


</script>
<div id="page-head">
	<div id="page-title">
		<h1 class="page-header text-overflow">Ver Notas</h1>
	</div>
	<ol class="breadcrumb">
		<li><a href="/"><i class="demo-pli-home"></i></a></li>
		<li><a href="#/grupos">Grupos</a></li>
		<li class="active">Notas Detalladas</li>
	</ol>
</div>
<div id="page-content">

	<div class="panel" id="notasDetalladas">
		<div class="panel-heading">
			<h3 class="panel-title">Notas Registradas</h3>
		</div>
		<div class="panel-body">
            <div class="mar-btm">
                <button class="btn btn-default" id="exportBtn">{{ icon('printer') }} Exportar Notas</button>
            </div>

			<div class="alert alert-danger text-center">La información mostrada puede no estar completa al 100%, para cualquier consulta comuníquese con el administrador.</div>
			{% for asignatura in matricula.getAsignaturas() %}

			{% set criterios = asignatura.getCriterios(get.ciclo) %}

			{% set needNotas = criterios|length %}
			{% set totalNotas = 0 %}

			<div class="panel panel-primary panel-bordered">
				<div class="panel-heading">
					<h3 class="panel-title">{{ asignatura.curso.nombre }}</h3>
				</div>
				<div class="panel-body">
					<table class="special">

						{% for criterio in criterios %}
						{% set indicadores = criterio.getIndicadores() %}
						{% set nota = matricula.getNota(asignatura.id, criterio.id, get.ciclo) %}
							{% if not is_null(nota) %}
								{% set totalNotas = totalNotas + 1 %}
							{% endif %}
						<tr>
							<th style="width: 250px">{{ criterio.descripcion }}
							{% if asignatura.grupo.nivel.calificacionPorcentual() %}( {{ criterio.peso }}% ){% endif %}
							</th>
							<td class="center" style="">
							{% if indicadores|length > 0 %}
							<table style="width: 100%">
								<tr>
									{% for indicador in indicadores %}
					                    {% for i in 0..(indicador.cuadros - 1) %}
					                        {% set nota_indicador = matricula.getNotaDetalle(asignatura.id, get.ciclo, criterio.id, indicador.id, i) %}
											<td>{{ nota_indicador ? nota_indicador : '-' }}</td>
					                    {% endfor %}
					                {% endfor %}
									<td style="background: #FDE9D9">{{ nota ? nota|upper : '-' }}</td>
								</tr>
							</table>
							{% else %}
								{{ nota ? nota|upper : '-' }}
							{% endif %}
							</td>
						</tr>
						{% endfor %}
						{% if asignatura.grupo.nivel.calificacionPorcentual() and asignatura.curso.examenMensual() %}
						{% set notaExamenMensual = matricula.getPromedioExamenMensual(asignatura, get.ciclo, false) %}
							{% if not is_null(notaExamenMensual) %}
								{% set totalNotas = totalNotas + 1 %}
							{% endif %}
						<tr>
							<th style="width: 250px">Examen Mensual ( {{ asignatura.curso.peso_examen_mensual }}% )</th>
							<td class="center" style="">
								<table style="width: 100%">
									<tr>
										{% for i in 1..2 %}
							            	{% set notaExamen = matricula.getNotaExamenMensual(asignatura.id, i, get.ciclo) %}
							                <td>{{ notaExamen ? notaExamen : '-' }}</td>
							    		{% endfor %}
										<td style="background: #FDE9D9">{{ notaExamenMensual ? notaExamenMensual : '-' }}</td>
									</tr>
								</table>

							</td>
						</tr>

						{% set needNotas = needNotas + 1 %}
						{% endif %}

						{% set promedio = matricula.getPromedio(asignatura.id, get.ciclo) %}
						{% if totalNotas != needNotas %}
							{% set promedio = '-' %}
						{% endif %}
						<tr>
							<th>PROMEDIO</th>
							<th style="text-align: center; padding: 0"><b>{{ promedio ? promedio : '-' }}</b></th>
						</tr>
					</table>
				</div>
			</div>
		
			{% endfor %}
		</div>
	</div>
</div>
	