{% extends '__external.php' %}
{% block main_content %}
<!--
{% if USUARIO.checkPermissions('OBJETIVOS|ALL') %}
<div class="menu">
	<button class="btn-default btn" onclick="fancybox('/configuracion/objetivos_matriculas')">{{ icon('register') }} Editar Datos</button>
</div>
{% endif %}
-->
<style>
table.special th{
	font-size: 10px;
	padding: 5px 0;
	text-align: center;
}
table.special td{
	font-size: 10px;
	padding: 5px 0;
	text-align: center;
}
</style>
<div class="block" style="width: 100%">
	<h2>Objetivos Matrículas {{ anio }}</h2>
	<form id="fobjetivos">
	{% set xtotal_1 = 0 %}
	{% set xtotal_2 = 0 %}
	{% set xtotal_3 = 0 %}
	{% set xtotal_4 = 0 %}
	{% set xtotal_5 = 0 %}
	{% set xtotal_6 = 0 %}
	{% set xtotal_7 = 0 %}
	{% set xtotal_8 = 0 %}
	{% set xtotal_9 = 0 %}
	{% set xtotal_10 = 0 %}
	{% set xtotal_11 = 0 %}
	{% set xtotal_12 = 0 %}

	{% for nivel in COLEGIO.getNiveles() %}
	<table class="special">
		<thead style="font-size: 10px">
			<tr>
				<th style="width: 140px">{{ nivel.nombre|upper }}</th>
				<th>Objetivos <br/> Vacantes</th>
				<th>Pasan <br />del {{ anio - 1 }}</th>
				<th>Se <br/>Retiran</th>
				<th>Total<br /> Antiguos</th>
				<th>Ratificación<br /> {{ anio }}</th>
				<th>Faltan<br /> Antiguos</th>
				<th>Total<br /> Nuevos</th>
				<th>Matriculados<br /> Nuevos</th>
				<th>Faltan <br /> Nuevos</th>
				<th>Total <br /> Matriculados</th>
				<th>Total <br />Faltan Pagar</th>
				<th>Total <br />Vacantes</th>
			</tr>
		</thead>
		{% set total_1 = 0 %}
		{% set total_2 = 0 %}
		{% set total_3 = 0 %}
		{% set total_4 = 0 %}
		{% set total_5 = 0 %}
		{% set total_6 = 0 %}
		{% set total_7 = 0 %}
		{% set total_8 = 0 %}
		{% set total_9 = 0 %}
		{% set total_10 = 0 %}
		{% set total_11 = 0 %}
		{% set total_12 = 0 %}

		{% for grupo in COLEGIO.getGruposByNivel(nivel.id, anio) %}
		{% set value_1 = objetivo.get('vacantes', grupo.id) %}
		{% set value_2 = objetivo.get('pasan_anterior', grupo.id) %}
		{% set value_3 = objetivo.get('se_retiran', grupo.id) %}

		{% set value_4 = objetivo.get('total_antiguos', grupo.id) %}

		{% set totalMatriculados = objetivo.getMatriculados(grupo.id) %}
		
		{% set value_5 = totalMatriculados.antiguos %}
		{% set value_6 = (value_4 - value_5) > 0 ? (value_4 - value_5) : '' %}
		{% set value_7 = totalMatriculados.nuevos %}
		{% set value_8 = totalMatriculados.nuevos %}
		{% set value_9 = 0 %}
		{% set value_10 = totalMatriculados.antiguos + totalMatriculados.nuevos %}
		{% set value_11 = value_6 + value_9 %}
		{% set value_12 = value_1 - (value_10 + value_11) %}
		{% set value_12 = value_12 > 0 ? value_12 : 0 %}
	
		<tr>
			<th>{{ grupo.getGradoDescribed() }} {{ grupo.seccion|upper }} - {{ grupo.sede.nombre }}</th>
			<td class="center">{{ value_1 }}</td>
			<td class="center">{{ value_2 }}</td>
			<td class="center">{{ value_3 }}</td>

			<td class="center">{{ value_4 }}</td>
			<td class="center">{{ value_5 }}</td>
			<td class="center">{{ value_6 }}</td> 
			<td class="center">{{ value_7 }}</td>
			<td class="center">{{ value_8 }}</td>
			<td class="center">{{ value_9 }}</td>
			<td class="center">{{ value_10 }}</td>
			<td class="center">{{ value_11 }}</td>
			<td class="center">{{ value_12 }}</td>
		</tr>
		{% set total_1 = total_1 + value_1 %}
		{% set total_2 = total_2 + value_2 %}
		{% set total_3 = total_3 + value_3 %}
		{% set total_4 = total_4 + value_4 %}
		{% set total_5 = total_5 + value_5 %}
		{% set total_6 = total_6 + value_6 %}
		{% set total_7 = total_7 + value_7 %}
		{% set total_8 = total_8 + value_8 %}
		{% set total_9 = total_9 + value_9 %}
		{% set total_10 = total_10 + value_10 %}
		{% set total_11 = total_11 + value_11 %}
		{% set total_12 = total_12 + value_12 %}

		{% endfor %}
		<tfoot>
			<tr>
				<th>TOTAL</th>
				<th>{{ total_1 }}</th>
				<th>{{ total_2 }}</th>
				<th>{{ total_3 }}</th>
				<th>{{ total_4 }}</th>
				<th>{{ total_5 }}</th>
				<th>{{ total_6 }}</th>
				<th>{{ total_7 }}</th>
				<th>{{ total_8 }}</th>
				<th>{{ total_9 }}</th>
				<th>{{ total_10 }}</th>
				<th>{{ total_11 }}</th>
				<th>{{ total_12 }}</th>
			</tr>
		</tfoot>
	</table>
		{% set xtotal_1 = xtotal_1 + total_1 %}
		{% set xtotal_2 = xtotal_2 + total_2 %}
		{% set xtotal_3 = xtotal_3 + total_3 %}
		{% set xtotal_4 = xtotal_4 + total_4 %}
		{% set xtotal_5 = xtotal_5 + total_5 %}
		{% set xtotal_6 = xtotal_6 + total_6 %}
		{% set xtotal_7 = xtotal_7 + total_7 %}
		{% set xtotal_8 = xtotal_8 + total_8 %}
		{% set xtotal_9 = xtotal_9 + total_9 %}
		{% set xtotal_10 = xtotal_10 + total_10 %}
		{% set xtotal_11 = xtotal_11 + total_11 %}
		{% set xtotal_12 = xtotal_12 + total_12 %}

	{% endfor %}

	<h3>TOTAL GENERAL</h3>
	<table class="special">
		<thead style="font-size: 10px">
			<tr>
				<th style="width: 140px"></th>
				<th>Objetivos <br/> Vacantes</th>
				<th>Pasan <br />del {{ anio - 1 }}</th>
				<th>Se <br/>Retiran</th>
				<th>Total<br /> Antiguos</th>
				<th>Ratificación<br /> {{ anio }}</th>
				<th>Faltan<br /> Antiguos</th>
				<th>Total<br /> Nuevos</th>
				<th>Matriculados<br /> Nuevos</th>
				<th>Faltan <br /> Nuevos</th>
				<th>Total <br /> Matriculados</th>
				<th>Total <br />Faltan Pagar</th>
				<th>Total <br />Vacantes</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th>TOTAL GENERAL</th>
				<td>{{ xtotal_1 }}</td>
				<td>{{ xtotal_2 }}</td>
				<td>{{ xtotal_3 }}</td>
				<td>{{ xtotal_4 }}</td>
				<td>{{ xtotal_5 }}</td>
				<td>{{ xtotal_6 }}</td>
				<td>{{ xtotal_7 }}</td>
				<td>{{ xtotal_8 }}</td>
				<td>{{ xtotal_9 }}</td>
				<td>{{ xtotal_10 }}</td>
				<td>{{ xtotal_11 }}</td>
				<td>{{ xtotal_12 }}</td>
			</tr>
		</tbody>
	</table>

	<input type="hidden" name="objetivo_id" value="{{ objetivo.id }}" />
	<div class="formLoading"></div>
	</form>
</div>
{% endblock %}