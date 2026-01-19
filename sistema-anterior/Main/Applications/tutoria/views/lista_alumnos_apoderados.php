<div class="menu">
    <button class="btn btn-default" onclick="zk.printDocument('/tutoria/imprimir_lista_alumnos?grupo_id={{ grupo.id }}')">{{ icon('printer') }} Lista Alumnos / Apoderados</button>
</div>
<div class="block" style="width: 950px">
<h2>Lista de Alumnos</h2>

<style>
#lista_alumnos th{
	padding-left: 0;
	text-align: center;
}

#lista_alumnos td input{
	text-align: left;
}
</style>
    {% if matriculas|length > 0 %}
    <table id="lista_alumnos" class="special" style="width: 100%">
        <thead>
        <tr>
            <th style="width: 20px">Nº</th>
            <th>Apellidos y Nombres</th>

            <th>Tipo / Nº de Documento</th>
            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {% for matricula in matriculas %}
        {% set alumno = matricula.alumno %}
        {% set apoderados = alumno.getApoderados() %}
        {% set rows = apoderados|length + 1 %}

        <tr class="line-alumno">
            <td style="width: 50px; text-align: center" rowspan="{{ rows }}">{{ loop.index }}</td>
            <td style="text-align: left; padding-left: 10px; font-weight: bold">{{ alumno.getFullName() }} </a> </td>
            <td class="center">{{ alumno.getTipoDocumento() }} - {{ alumno.nro_documento }}</td>
            <td class="center">ALUMNO</td>
            <td class="center" style="width: 120px">
                <button class="btn btn-default" onclick="fancybox('/alumnos/editar_basico/{{ alumno.id }}')">{{ icon('register') }} Editar</button>
            </td>
        </tr>

        {% for apoderado in apoderados %}
        <tr>
           
            <td style="text-align: left; padding-left: 10px;">{{ apoderado.getFullName() }}</td>
            <td class="center">{{ apoderado.getTipoDocumento() }} - {{ apoderado.nro_documento }}</td>
            <td class="center">{{ apoderado.getParentesco()|upper }}</td>
            <td class="center">
                <button class="btn btn-default" onclick="fancybox('/apoderados/editar_basico/{{ apoderado.id }}')">{{ icon('register') }} Editar</button>
            </td>
        </tr>
        {% endfor %}
        {% endfor %}
        </tbody>
    </table>
    {% else %}
    <p class="center"><b>NO SE ENCONTRARON RESULTADOS</b></p>
    {% endif %}
</div>
