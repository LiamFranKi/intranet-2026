<script>
Number.prototype.round = function(places) {
    return +(Math.round(this + "e+" + places)  + "e-" + places);
}
</script>
<script>
var CRITERIOS = parseInt('{{ criterios|length }}');
var ALLOWED = ['AD','A', 'B', 'C'];
var VALUES = {'AD': 4, 'A': 3, 'B': 2, 'C': 1};
var MAP = {1: 'C', 2: 'B', 3: 'A', 4: 'AD'};

$(function(){
    $('.criterio').bind('blur', function(e){
        current_value = this.value.toUpperCase().trim();
    });
    
    $('#formConducta').bootstrapValidator({
		//container: 'tooltip',
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;
			$(_form).sendForm('/tutoria/save_conducta', function(r){
				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
					break;
					case 0:
						zk.pageAlert({message: 'No se pudieron guardar los notas', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
					break;
				}
			});
		}
	});
});

</script>

<div id="page-content">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">CONDUCTA - {{ COLEGIO.getCicloNotasSingle(get.ciclo) }} - {{ grupo.anio }}</h3>
		</div>
		<div class="panel-body">
			<div class="menu mar-btm ">
				<button class="btn btn-default" type="button" onclick="$('#formConducta').trigger('submit')">{{ icon('table_save') }} Guardar Datos</button>
			</div>
			
			<form id="formConducta" data-target="#formConducta" data-toggle="overlay">
				<table id="lista_alumnos" class="special" style="width: 100%">
					<thead>
					<tr>
						<th style="width: 20px">Nº</th>
						<th style="">Apellidos y Nombres</th>
						<th style="padding: 3px 5px;text-align: center; width: 100px">NOTA</th>
					</tr>
					
					</thead>
					<tbody>
					{% for matricula in matriculas %}
					{% set alumno = matricula.alumno %}
					<tr class="line-alumno">
						<td style="min-width: 20px" class="text-center">{{ loop.index }}</td>
						<td style="text-align: left; padding-left: 10px; min-width: 300px">{{ alumno.apellido_paterno|upper }} {{ alumno.apellido_materno|upper }}, {{ alumno.nombres|upper }} </a> </td>
						{% set promedio = matricula.getPromedio(-101, get.ciclo) %}
						<td class="center form-group">
							<input style="text-transform: uppercase; width: 35px; text-align: center" data-x="{{ _key }}" data-matricula="{{ matricula.id }}" type="text" class="x_c center input-small criterio tip" name="notas[{{ matricula.id }}]" value="{{ promedio }}" maxlength="2" />
						</td>
					</tr>
					{% endfor %}
					</tbody>
				</table>
				<input type="hidden" name="ciclo" value="{{ get.ciclo }}" />
				<input type="hidden" name="asignatura_id" value="-101" />
			</div>
		</div>
	</div>
</div>
