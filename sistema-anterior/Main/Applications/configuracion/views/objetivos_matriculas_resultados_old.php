{% if USUARIO.checkPermissions('OBJETIVOS|ALL') %}
<div class="menu">
	<button class="btn-default btn" onclick="fancybox('/configuracion/objetivos_matriculas')">{{ icon('register') }} Editar Datos</button>
</div>
{% endif %}
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
<div class="block" style="width: 1000px">
	<h2>Objetivos Matrículas {{ anio }}</h2>
	<form id="fobjetivos">
	{% set xtotal_1 = 0 %}
	{% set xtotal_2 = 0 %}
	{% set xtotal_3 = 0 %}
	{% set xtotal_4 = 0 %}
	{% set xtotal_5 = 0 %}
	{% set xtotal_6 = 0 %}
	{% set xtotal_7 = 0 %}

	{% for nivel in COLEGIO.getNiveles() %}
	<table class="special">
		<thead style="font-size: 10px">
			<tr>
				<th style="width: 140px">{{ nivel.nombre|upper }}</th>
				<th>Se esperan antiguos</th>
				<th>Objetivos</th>
				<th>Matriculados<br /> Antiguos</th>
				<th>Matriculados<br /> Nuevos</th>
				<th>Falta Captar<br /> Antiguos</th>
				<th>Falta Captar<br /> Nuevos</th>
				<th>Total<br /> Matriculados</th>
				<th>Estado</th>
			</tr>
		</thead>
		{% set total_1 = 0 %}
		{% set total_2 = 0 %}
		{% set total_3 = 0 %}
		{% set total_4 = 0 %}
		{% set total_5 = 0 %}
		{% set total_6 = 0 %}
		{% set total_7 = 0 %}
		{% for grupo in COLEGIO.getGruposByNivel(nivel.id, anio) %}
		{% set value_1 = objetivo.get('se_esperan_antiguos', grupo.id) %}
		{% set value_2 = objetivo.get('vacantes', grupo.id) %}
		{% set value_3 = objetivo.get('alumnos_antiguos', grupo.id) %}
		{% set value_4 = objetivo.get('alumnos_nuevos', grupo.id) %}
		{% set value_5 = objetivo.get('falta_captar_antiguos', grupo.id) %}
		{% set value_6 = objetivo.get('falta_captar_nuevos', grupo.id) %}
		{% set value_7 = objetivo.getTotalMatriculados(grupo.id) %}
		<tr>
			<th>{{ grupo.getGradoDescribed() }} {{ grupo.seccion|upper }}</th>
			<td class="center">{{ value_1 }}</td>
			<td class="center">{{ value_2 }}</td>
			<td class="center">{{ value_3 }}</td>
			<td class="center">{{ value_4 }}</td>
			<td class="center">{{ value_5 }}</td>
			<td class="center">{{ value_6 }}</td> 
			<td class="center">{{ value_7 }}</td>
			<td class="center"><b>{{ objetivo.getEstado(grupo.id) }}</b></td>
		</tr>
		{% set total_1 = total_1 + value_1 %}
		{% set total_2 = total_2 + value_2 %}
		{% set total_3 = total_3 + value_3 %}
		{% set total_4 = total_4 + value_4 %}
		{% set total_5 = total_5 + value_5 %}
		{% set total_6 = total_6 + value_6 %}
		{% set total_7 = total_7 + value_7 %}

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
	{% endfor %}

	<h3>TOTAL GENERAL</h3>
	<table class="special">
		<thead style="font-size: 10px">
			<tr>
				<th style="width: 140px"></th>
				<th>Se esperan antiguos</th>
				<th>Objetivos</th>
				<th>Matriculados<br /> Antiguos</th>
				<th>Matriculados<br /> Nuevos</th>
				<th>Falta Captar<br /> Antiguos</th>
				<th>Falta Captar<br /> Nuevos</th>
				<th>Total<br /> Matriculados</th>
				<th>Alumnos<br /> Faltantes</th>
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
				<td>{{ (xtotal_2 - xtotal_7) < 0 ? 0 : (xtotal_2 - xtotal_7) }}</td>
			</tr>
		</tbody>
	</table>

	<input type="hidden" name="objetivo_id" value="{{ objetivo.id }}" />
	<div class="formLoading"></div>
	</form>
</div>