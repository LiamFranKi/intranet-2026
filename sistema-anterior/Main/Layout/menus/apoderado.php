<ul id="mainnav-menu" class="list-group">

    <li class="list-header">Menú Principal</li>
	<!--
    <li id="menu-dashboard">
        <a href="#/home/dashboard_apoderado">
            {{ icon('house') }}
            <span class="menu-title">Dashboard</span>
        </a>
    </li>
    -->
    <!--
    <li id="menu-perfil">
        <a href="javascript:;">
            {{ icon('user') }}
            <span class="menu-title">Mi Perfil</span>
        </a>
    </li>
    -->
    
    <li id="menu-hijos_NORMAL">
        <a href="#/apoderados/hijos?tipo=NORMAL">
            {{ icon('user_red') }}
            <span class="menu-title">Alumnos a Cargo</span>
        </a>
    </li>
    <li id="menu-hijos_PAGOS">
        <a href="#/apoderados/hijos?tipo=PAGOS">
            {{ icon('application_side_tree') }}
            <span class="menu-title">Mis Pagos</span>
        </a>
    </li>
    <li id="menu-hijos_PSICOLOGIA">
        <a href="#/apoderados/hijos?tipo=PSICOLOGIA">
            {{ icon('shield') }}
            <span class="menu-title">Psicología</span>
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