
	<table style="width: 100%">
		<tr>
			<td valign="top" class="text-center">
                <img style="width: 200px; border: 1px solid #ccc; padding: 3px" style="border: 1px solid #ccc" src="{{ alumno.getFoto() }}" />
            </td>
			<td valign="top" style="padding-left: 10px; padding-top: 0px">
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
			</td>
		</tr>
	</table>
