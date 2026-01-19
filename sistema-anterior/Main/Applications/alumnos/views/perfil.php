<script>
setMenuActive('perfil')
</script>
{# 
<script>
$(function(){
	$('#formPerfil').niftyOverlay();
	$('#formFoto').on('submit', function(e){
		e.preventDefault();
		$('#formPerfil').niftyOverlay('show');
		$(this).sendForm('/alumnos/subir_foto', function(r){
			$('#formPerfil').niftyOverlay('hide');
			if(r[0] == 1){
				zk.reloadPage()
				zk.pageAlert({message: 'Datos guardados correctamente', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
			}else{
				zk.pageAlert({message: 'No se pudo subir la foto.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
			}
		}, 'json');
	});

	$('#foto').on('change', function(){
		if(this.value != null)
			$('#formFoto').trigger('submit')
	})
})

function subirFoto(){
	$('#foto').trigger('click');
}
</script>
#}
<div id="page-content">
	<div class="panel" id="formPerfil" data-toggle="overlay" data-target="#formPerfil">
        <div class="panel-body">
            <div class="fixed-fluid">
                <div class="fixed-md-200 pull-sm-left fixed-right-border">

                    <!-- Simple profile -->
                    <div class="text-center">
                        <div class="pad-ver">
                            <img src="{{ alumno.getFoto() }}" class="img-lg img-circle" alt="Profile Picture">
                        </div>
                        <h4 class="text-lg text-overflow mar-no">{{ alumno.getFullName() }}</h4>
                        <p class="text-sm text-muted">ALUMNO</p>

						
                        <button class="btn btn-block btn-success" onclick="zk.goToUrl('/alumnos/editar_perfil')">Editar Datos</button>
                      <!--   <button class="btn btn-block btn-primary" onclick="subirFoto()">Subir Foto</button>
                        
                        
                        <form id="formFoto" style="display: none">
                        	<input type="file" name="foto" id="foto">
                        </form> -->
						
                    </div>
                    <hr>

                    <!-- Profile Details -->
                    <p class="pad-ver text-main text-sm text-uppercase text-bold">Matrículas</p>
                    {% for matricula in alumno.getMatriculas() %}
                   		<p>{{ matricula.grupo.getNombreShort2() }}</p>
                    {% endfor %}
                </div>
                <div class="fluid">
                    <div class="panel">
                    	<div class="panel-heading">
                    		<h3 class="panel-title">Mi Información</h3>
                    	</div>
                    	<div class="panel-body">
                    		<table class="special info" style="width: 100%">
								<tr>
									<th>APELLIDOS Y NOMBRES</th>
									<td>{{ alumno.getFullName() }}</td>
								</tr>
								<tr>
									<th>FECHA DE NACIMIENTO</th>
									<td>{{ alumno.getFechaNacimiento() }}</td>
								</tr>
			                    <tr>
			                        <th>LUGAR DE NACIMIENTO</th>
			                        <td>{{ alumno.pais_nacimiento.nombre }}</td>
			                    </tr>
								<tr>
									<th>Tipo de Documento</th>
									<td>{{ alumno.getTipoDocumento() }}</td>
								</tr>
								<tr>
									<th>Nº de Documento</th>
									<td>{{ alumno.nro_documento }}</td>
								</tr>
								<tr>
									<th>SEXO</th>
									<td>{{ alumno.getSexo() }}</td>
								</tr>
								
								<tr>
									<th>EMAIL</th>
									<td>{{ alumno.email }}</td>
								</tr>
								<tr>
									<th>ESTADO CIVIL</th>
									<td>{{ alumno.getEstadoCivil() }}</td>
								</tr>
								<tr>
									<th>RELIGIÓN</th>
									<td>{{ alumno.getReligion() }}</td>
								</tr>
			                    
								<tr>
									<th>LENGUA MATERNA</th>
									<td>{{ alumno.getLenguaMaterna() }}</td>
								</tr>
								<tr>
									<th>SEGUNDA LENGUA</th>
									<td>{{ alumno.getSegundaLengua() }}</td>
								</tr>
								<tr>
									<th>Nº DE HERMANOS</th>
									<td>{{ alumno.nro_hermanos }}</td>
								</tr>
								<tr>
									<th>LUGAR QUE OCUPA</th>
									<td>{{ alumno.lugar_hermanos }}º</td>
								</tr>
								<tr>
									<th>FECHA DE INSCRIPCIÓN</th>
									<td>{{ alumno.getFechaInscripcion() }}</td>
								</tr>
							</table>
                    	</div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>