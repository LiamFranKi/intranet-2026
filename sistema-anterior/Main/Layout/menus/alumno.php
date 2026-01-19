<ul id="mainnav-menu" class="list-group">

    <li class="list-header">Menú Principal</li>
	
    <li id="menu-dashboard">
        <a href="#/home/dashboard_alumno">
            {{ icon('house') }}
            <span class="menu-title">Dashboard</span>
        </a>
    </li>
    <li id="menu-perfil">
        <a href="#/alumnos/perfil/{{ sha1(USUARIO.alumno_id) }}">
            {{ icon('user') }}
            <span class="menu-title">Mi Perfil</span>
        </a>
    </li>
    <li id="menu-horario">
        {% if MATRICULA %}
        <a href="javascript:;" onclick="zk.printDocument('/grupos/imprimir_horario_horizontal?grupo_id={{ MATRICULA.grupo_id }}')">
        {% else %}
        <a href="javascript:;" onclick="alert('No se encontró una matrícula para el año activo')">
        {% endif %}
            {{ icon('calendar') }}
            <span class="menu-title">Mi Horario</span>
        </a>
    </li>
    <li id="menu-cursos_asignados">
        <a href="#/asignaturas/alumno">
            {{ icon('book_next') }}
            <span class="menu-title">Cursos Asignados</span>
        </a>
    </li>
    <li id="menu-matriculas_alumno">
        <a href="#/matriculas/alumno">
            {{ icon('layout_edit') }}
            <span class="menu-title">Notas y Asistencia</span>
        </a>
    </li>

    <!-- <li id="menu-examenes_bloques">
        <a href="#/examenes_bloques/alumno">
            {{ icon('page_attach') }} 
            <span class="menu-title">Exámenes - Bloques</span>
        </a>
    </li> -->
    <li class="list-header">Comunicados</li>
    <li id="menu-comunicados">
        <a href="#/comunicados/publico">
            {{ icon('bell_go') }}
            <span class="menu-title">Comunicados</span>
        </a>
    </li>
    <li class="list-header">Calendario</li>
    <li id="menu-actividades">
        <a href="#/actividades">
            {{ icon('calendar') }}
            <span class="menu-title">Actividades</span>
        </a>
    </li>
    <li class="list-header">Mensajería</li>
    <li id="menu-mensajes">
        <a href="#/mensajes">
            {{ icon('email') }}
            <span class="menu-title">Mensajes</span>
        </a>
    </li>

    <li class="list-header">Otros</li>
    <li id="menu-avatarShopSales">
        <a href="#/avatar_shop_sales/shop">
            {{ icon('cart_go') }}
            <span class="menu-title">Tienda de Avatares</span>
        </a>
    </li>
</ul>