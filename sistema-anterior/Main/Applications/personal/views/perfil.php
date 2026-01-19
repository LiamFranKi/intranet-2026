<script>
setMenuActive('perfil')
</script>
<script>
$(function(){
	$('#formPerfil').niftyOverlay();
	$('#formFoto').on('submit', function(e){
		e.preventDefault();
		$('#formPerfil').niftyOverlay('show');
		$(this).sendForm('/personal/subir_foto', function(r){
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
<div id="page-content">
	<div class="panel">
        <div class="panel-body">
            <div class="fixed-fluid">
                <div class="fixed-md-200 pull-sm-left fixed-right-border">

                    <!-- Simple profile -->
                    <div class="text-center">
                        <div class="pad-ver">
                            <img src="{{ personal.getFoto() }}" class="img-lg img-circle" alt="Profile Picture">
                        </div>
                        <h4 class="text-lg mar-no">{{ personal.getFullName() }}</h4>
                        <p class="text-sm text-muted">{{ personal.usuario.tipo }}</p>

                        <button class="btn btn-block btn-primary btn-lg" onclick="subirFoto()">Subir Foto</button>
                        <form id="formFoto" style="display: none">
                        	<input type="file" name="foto" id="foto">
                        </form>
                    </div>
                    <hr>

                    <!-- Profile Details -->
                    <p class="pad-ver text-main text-sm text-uppercase text-bold">Grupos Asignados</p>
                    {% for grupo in personal.getGruposAsignados(COLEGIO.anio_activo) %}
                   		<p>{{  grupo.getNombreShort2() }}</p>
                    {% endfor %}
                </div>
                <div class="fluid">
                    <div class="panel">
                    	<div class="panel-heading">
                    		<h3 class="panel-title">Mi Información</h3>
                    	</div>
                    	<div class="panel-body">
                    		<table class="special">
								<tr>
									<th style="width: 200px">APELLIDOS Y NOMBRES</th>
									<td>{{ personal.apellidos|upper }}, {{ personal.nombres|upper }}</td>
								</tr>
								<tr>
									<th>Tipo de Documento</th>
									<td>{{ personal.getTipoDocumento() }} - {{ personal.nro_documento }}</td>
								</tr>
			                    <tr>
									<th>SEXO</th>
									<td>{{ personal.getSexo() }}</td>
								</tr>

			                    <tr>
			                        <th>Estado Civil</th>
			                        <td>{{ personal.getEstadoCivil() }}</td>
			                    </tr>
								<tr>
									<th>DIRECCIÓN</th>
									<td>{{ personal.direccion }}</td>
								</tr>
								<tr>
									<th>TELÉFONO FIJO</th>
									<td>{{ personal.telefono_fijo }}</td>
								</tr>
			                    <tr>
			                        <th>Teléfono Celular</th>
			                        <td>{{ personal.telefono_celular }} - {{ personal.getLineaCelular() }}</td>
			                    </tr>
								<tr>
									<th>EMAIL</th>
									<td>{{ personal.email }}</td>
								</tr>
								<tr>
									<th>CARGO</th>
									<td>{{ personal.cargo }}</td>
								</tr>
								<tr>
									<th>FECHA DE NACIMIENTO</th>
									<td>{{ personal.getFechaNacimiento() }}</td>
								</tr>
								<tr>
									<th>FECHA DE INGRESO</th>
									<td>{{ personal.getFechaIngreso() }}</td>
								</tr>
							</table>
                    	</div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>