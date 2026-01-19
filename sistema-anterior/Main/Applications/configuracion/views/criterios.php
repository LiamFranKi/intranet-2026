<script>
var CRITERIOS_INDEX = parseInt('{{ criterios|length }}');
$(function(){
    $('#fconfiguracion').niftyOverlay();
	$('#fconfiguracion').bootstrapValidator({
		fields: {},
		onSuccess: function(e){
			e.preventDefault();
			_form = e.target;

            if(confirm('¿Está seguro de aplicar estos criterios a todas las asignaturas?'))

			$('button[type="submit"]').attr('disabled', false);

			$(_form).sendForm('/configuracion/save_criterios', function(r){
				

				switch(parseInt(r[0])){
					case 1:
						zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
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

    $('#fconfiguracion').changeGradoOptions({
		showLabel: true,
        label: "Todos",
		value: '{{ get.grado }}'
	});
})


function agregarCriterio(){
    let data = `
        <tr>
            <td class="text-center"><input type="text" class="form-control" name="criterio[${CRITERIOS_INDEX}][descripcion]" /></td>
            <td class="text-center"><input type="text" class="form-control" name="criterio[${CRITERIOS_INDEX}][abreviatura]" /></td>
            <td class="text-center"><input type="text" class="form-control" style="width: 70px" name="criterio[${CRITERIOS_INDEX}][peso]" /></td>
            <td class="text-center"><input type="number" class="form-control" style="width: 70px" name="criterio[${CRITERIOS_INDEX}][ciclo]" min="0" value="0" /></td>
            <td class="text-center"><input type="number" class="form-control" style="width: 70px" name="criterio[${CRITERIOS_INDEX}][cuadros]" min="0" /></td>
            <td class="text-center"><button class="btn btn-danger" type="button" onclick="$(this).parent().parent().remove()"><i class="fa fa-remove" /></button></td>
        </tr>
    `;

    $(data).appendTo($('#criteriosContainer'));
    CRITERIOS_INDEX++;
}
</script>
<div id="page-head">
    <div id="page-title">
        <h1 class="page-header text-overflow">Criterios Globales</h1>
    </div>
    <ol class="breadcrumb">
        <li><a href="/"><i class="demo-pli-home"></i></a></li>

        <li class="active">Configuración de Criterios</li>
    </ol>
</div>

<div id="page-content">
    <form id="fconfiguracion" class="form-horizontal" data-target="#fconfiguracion" data-toggle="overlay">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Criterios Globales</h3>
            </div>
            <div class="panel-body">
                <div class="panel panel-primary panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">Lista de Criterios</h3>
                    </div>
                    <div class="panel-body">
                        <div class="mar-btm"><button class="btn btn-success" type="button" onclick="agregarCriterio()">Agregar Nuevo</button></div>
                        <table class="special">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Abreviatura</th>
                                    <th>Peso %</th>
                                    <th>Bimestre (0 = TODOS)</th>
                                    <th>Cuadros</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="criteriosContainer">
                                {% for criterio in criterios %}
                                <tr>
                                    <td class="text-center"><input type="text" class="form-control" name="criterio[{{ _key }}][descripcion]" value="{{ criterio.descripcion }}" /></td>
                                    <td class="text-center"><input type="text" class="form-control" name="criterio[{{ _key }}][abreviatura]" value="{{ criterio.abreviatura }}" /></td>
                                    <td class="text-center"><input type="text" class="form-control" style="width: 70px" name="criterio[{{ _key }}][peso]" value="{{ criterio.peso }}" /></td>
                                    <td class="text-center"><input type="number" class="form-control" style="width: 70px" name="criterio[{{ _key }}][ciclo]" min="0" value="{{ criterio.ciclo }}" /></td>
                                    <td class="text-center"><input type="number" class="form-control" style="width: 70px" name="criterio[{{ _key }}][cuadros]" min="0" value="{{ criterio.cuadros }}" /></td>
                                    <td class="text-center"><button class="btn btn-danger" type="button" onclick="$(this).parent().parent().remove()"><i class="fa fa-remove" /></button></td>
                                </tr>
                                {% endfor %}
                            </tbody>
                            
                        </table>

                        <div class="alert alert-warning text-center">Esta lista no representa la estructura actual de los criterios, solo es una plantilla para aplicar cambios en el momento.</div>
                    </div>
                </div>


                <div class="panel panel-primary panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">Aplicar a</h3>
                    </div>
                    <div class="panel-body">
                        <table class="special">
                            <tr>
                                <th>Sede</th>
                                <th>Nivel</th>
                                <th>Grado</th>
                                <th>Año</th>
                            </tr>
                            <tr>
                                <td class="text-center">{{ form.sede_id }}</td>
                                <td class="text-center">{{ form.nivel_id }}</td>
                                <td class="text-center">{{ form.grado }}</td>
                                <td class="text-center">{{ form.anio }}</td>
                            </tr>
                        </table>
                        <div class="alert alert-danger text-center">Al aplicar los criterios se borrarán todos los actuales, incluyendo las notas, y se registrarán nuevos.</div>
                    </div>
                    
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" type="button" onclick="history.back(-1)">Cancelar</button>
                <button class="btn btn-primary" type="submit">Guardar y Aplicar Datos</button>
            </div>
        </div>


    </form>
</div>