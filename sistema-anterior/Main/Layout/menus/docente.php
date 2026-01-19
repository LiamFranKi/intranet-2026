<ul id="mainnav-menu" class="list-group">

    <li class="list-header">Menú Principal</li>
	
    <li id="menu-dashboard">
        <a href="#/home/dashboard_docente">
            {{ icon('house') }}
            <span class="menu-title">Dashboard</span>
        </a>
    </li>
    <li id="menu-perfil">
        <a href="#/personal/perfil/{{ sha1(USUARIO.personal_id) }}">
            {{ icon('user') }}
            <span class="menu-title">Mi Perfil</span>
        </a>
    </li>
    <li id="menu-grupos_asignados">
        <a href="#/grupos/docente">
            {{ icon('group') }}
            <span class="menu-title">Grupos Asignados</span>
        </a>
    </li>
    <li id="menu-cursos_asignados">
        <a href="#/asignaturas/docente">
            {{ icon('book_next') }}
            <span class="menu-title">Cursos Asignados</span>
        </a>
    </li>

    <li id="menu-actividades">
        <a href="javascript:;" onclick="zk.printDocument('/grupos/imprimir_horario_docente?personal_id={{ USUARIO.personal_id }}')">
            {{ icon('calendar') }}
            <span class="menu-title">Mi Horario</span>
        </a>
    </li>

    <!-- <li id="menu-examenes_bloques">
        <a href="#/examenes_bloques/docente">
            {{ icon('list') }}
            <span class="menu-title">Exámenes Bloques</span>
        </a>
    </li> -->
    
    <li id="menu-tutoria">
        <a href="#/tutoria">
            {{ icon('group_go') }}
            <span class="menu-title">Tutoría</span>
        </a>
    </li>

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
</ul>