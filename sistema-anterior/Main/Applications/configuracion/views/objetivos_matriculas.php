<script>
$(function(){
	$('#fobjetivos').submit(function(e){
		e.preventDefault();
		//$.post('', $(this).serialize(), function(r){});
		$(this).sendForm('/configuracion/do_objetivos_matriculas', function(r){
			switch(parseInt(r[0])){
				case 1:
					$.prompt('Datos guardados correctamente.');
				break;

				case 0:
					$.prompt('No se pudieron guardar los datos');
				break;
			}
		});
	});
});
</script>
<div class="menu">
	
	<button class="btn-default btn" onclick="zk.printDocument('/configuracion/objetivos_matriculas_resultados')">{{ icon('list') }} Ver Resultados</button>
	<button class="btn-default btn" onclick="zk.printDocument('/reportes/objetivos_matriculados')">{{ icon('printer') }} Ver Matriculados</button>
	<button class="btn-default btn" onclick="$('#fobjetivos').trigger('submit')">{{ icon('disk') }} Guardar Datos</button>
</div>
<style>
table.special th{
	font-size: 11px;
}
</style>
<div class="block" style="width: 800px">
	<h2>Objetivos Matrículas {{ anio }}</h2>
	<form id="fobjetivos">
	{% for nivel in COLEGIO.getNiveles() %}
	<table class="special">
		<thead style="font-size: 11px">
			<tr>
				<th style="width: 140px">{{ nivel.nombre|upper }}</th>
				
				<th>Objetivos Vacantes</th>
				<th>Pasan del {{ anio - 1 }}</th>
				<th>Se retiran</th>
				<!--
				<th>Se esperan antiguos</th>
				<th>Matriculados<br /> Antiguos</th>
				<th>Matriculados<br /> Nuevos</th>
				-->
			</tr>
		</thead>
		{% for grupo in COLEGIO.getGruposByNivel(nivel.id, anio) %}
		<tr>
			<th>{{ grupo.getGradoDescribed() }} {{ grupo.seccion|upper }} <br />{{ grupo.sede.nombre }}</th>
			<td class="center"><input type="text" value="{{ objetivo.get('vacantes', grupo.id) }}" class="input-sm form-control" name="data[vacantes][{{ grupo.id }}]" style="width: 80px" /></td>
			<td class="center"><input type="text" value="{{ objetivo.get('pasan_anterior', grupo.id) }}" class="input-sm form-control" name="data[pasan_anterior][{{ grupo.id }}]" style="width: 80px" /></td>
			<td class="center"><input type="text" value="{{ objetivo.get('se_retiran', grupo.id) }}" class="input-sm form-control" name="data[se_retiran][{{ grupo.id }}]" style="width: 80px" /></td>
			
			<!--
			<td class="center"><input type="text" value="{{ objetivo.get('se_esperan_antiguos', grupo.id) }}" class="input-sm form-control" name="data[se_esperan_antiguos][{{ grupo.id }}]" style="width: 80px" /></td>
			
			<td class="center"><input type="text" value="{{ objetivo.get('alumnos_antiguos', grupo.id) }}" class="input-sm form-control" name="data[alumnos_antiguos][{{ grupo.id }}]" style="width: 80px" /></td>
			<td class="center"><input type="text" value="{{ objetivo.get('alumnos_nuevos', grupo.id) }}" class="input-sm form-control" name="data[alumnos_nuevos][{{ grupo.id }}]" style="width: 80px" /></td>
			-->
		</tr>
		{% endfor %}
	</table>
	{% endfor %}
	<input type="hidden" name="objetivo_id" value="{{ objetivo.id }}" />
	<div class="formLoading"></div>
	</form>
</div>