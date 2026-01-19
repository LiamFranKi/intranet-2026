<ul id="mainnav-menu" class="list-group">

    <li class="list-header">Menú Principal</li>
	
    <li id="menu-dashboard">
        <a href="#/home/dashboard">
            {{ icon('house') }}
            <span class="menu-title">Dashboard</span>
        </a>
    </li>

    <li id="menu-matricular">
        <a href="#/alumnos/matricular">
            {{ icon('register') }}
            <span class="menu-title">Matricular Nuevo</span>
        </a>
    </li>

    <li id="menu-alumnos">
        <a href="#">
            {{ icon('user') }}
            <span class="menu-title">Alumnos</span>
            <i class="arrow"></i>
        </a>

        <ul class="collapse">
            <li><a href="#/alumnos">Lista de Alumnos</a></li>
            <li><a href="#/alumnos/form">Registrar Nuevo</a></li>

        </ul>
    </li>

    <li id="menu-matricular">
        <a href="#/apoderados">
            {{ icon('user_red') }}
            <span class="menu-title">Padres de Familia</span>
        </a>
    </li>
    <li id="menu-grupos">
        <a href="#/grupos">
            {{ icon('group') }}
            <span class="menu-title">Grupos</span>
        </a>
    </li>
    <li id="menu-personal">
        <a href="#/personal">
            {{ icon('user_gray') }}
            <span class="menu-title">Personal</span>
        </a>
    </li>

    

    <li class="list-header">Cursos</li>
    <li id="menu-cursos">
        <a href="#/cursos">
            {{ icon('book') }}
            <span class="menu-title">Cursos</span>
        </a>
    </li>

    <li id="menu-asignaturas">
        <a href="#">
            {{ icon('book_open') }}
            <span class="menu-title">Asignaturas</span>
            <i class="arrow"></i>
        </a>

        <ul class="collapse">
            <li><a href="#/asignaturas">Lista de Asignaturas</a></li>
            <li><a href="#/asignaturas/registrar">Registrar Nuevo</a></li>

        </ul>
    </li>
    <li id="menu-areas">
        <a href="#/areas">
            {{ icon('report') }}
            <span class="menu-title">Áreas</span>
        </a>
    </li>
    <!-- <li class="list-header">Bloques</li>
    <li id="menu-bloques">
        <a href="#/bloques">
            {{ icon('bricks') }}
            <span class="menu-title">Bloques</span>
        </a>
    </li>
    <li id="menu-examenes_bloques">
        <a href="#/examenes_bloques">
            {{ icon('list') }}
            <span class="menu-title">Exámenes Bloques</span>
        </a>
    </li> -->

    <li class="list-header">Banco Temas</li>
    <li id="menu-banco_temas">
        <a href="#/banco_temas">
            {{ icon('database') }}
            <span class="menu-title">Banco de Temas</span>
        </a>
    </li>

    <li class="list-header">Pagos</li>
    <li id="menu-pagos">
        <a href="#">
            {{ icon('application_side_tree') }}
            <span class="menu-title">Pagos</span>
            <i class="arrow"></i>
        </a>

        <ul class="collapse">
            <li><a href="#/pagos">Buscar Alumnos</a></li>
            <li><a href="#/importar_exportar">Importar / Exportar BCP</a></li>
            <li><a href="#/pagos/boletear">Boletear Pagos</a></li>
            <li><a href="#/pagos/importaciones">Historial Archivos Imp.</a></li>
        </ul>
    </li>
    <li class="list-header">Servicios / Ventas / Caja</li>
    <li id="menu-pagos">
        <a href="#">
            {{ icon('basket_go') }}
            <span class="menu-title">Facturación</span>
            <i class="arrow"></i>
        </a>

        <ul class="collapse">
            <li><a href="#/boletas">Ventas</a></li>
            <li><a href="#/boletas_ingresos">Compras</a></li>
            <li><a href="#/boletas_categorias">Categorías</a></li>
            <li><a href="#/boletas_subcategorias">Subcategorías</a></li>
            <li><a href="#/boletas_conceptos">Productos</a></li>
            <li><a href="#/reportes/facturacion">Reportes</a></li>
          
        </ul>
    </li>
    <li id="menu-cash_accounts">
        <a href="#/cash_accounts">
            {{ icon('package_go') }}
            <span class="menu-title">Caja</span>
        </a>
    </li>


    {% set publicPersonal = COLEGIO.getPublicSpacePersonal() %}
	{% if publicPersonal %}
    <li class="list-header">Archivos</li>
	<li><a href="javascript:;" onclick="fancybox('/filemanager/index/{{ publicPersonal.id }}?token={{ publicPersonal.getFileManagerToken() }}&p={{ base64_encode('R,C,U,D') }}&base=/')">{{ icon('folder') }} Administrar Archivos</a></li>
	{% endif %}
    <li class="list-header">Atenciones</li>
    <li id="menu-topico_atenciones">
        <a href="#/topico_atenciones">
            {{ icon('shield') }}
            <span class="menu-title">Psicología</span>
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
    
    <li class="list-header">Reportes</li>
    <li id="menu-reportes">
        <a href="#/reportes">
            {{ icon('report') }}
            <span class="menu-title">Reportes</span>
        </a>
    </li>
	
</ul>