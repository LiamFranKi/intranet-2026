<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ COLEGIO.titulo_intranet }}</title>
    <script src="/info/values"></script>
    <script src="/Static/js/jquery-2.1.1.min.js"></script>
    <script src="/Static/plugins/jquery-ui/jquery-ui.min.js"></script>


    <!--STYLESHEET-->
    <!--=================================================-->

    <!--Open Sans Font [ OPTIONAL ]-->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>


    <!--Bootstrap Stylesheet [ REQUIRED ]-->
    <link href="/Static/css/bootstrap.min.css" rel="stylesheet">


    <!--Nifty Stylesheet [ REQUIRED ]-->
    <link href="/Static/css/nifty.min.css" rel="stylesheet">


    <!--Nifty Premium Icon [ DEMONSTRATION ]-->
    <link href="/Static/css/custom/nifty-icons.min.css" rel="stylesheet">
    <!--<link href="/Static/plugins/line-icons/css/line-icons.css" rel="stylesheet">-->
    <link href="/Static/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
    <!--=================================================-->


    <!--Demo [ DEMONSTRATION ]-->
    <link href="/Static/css/custom/nifty-config.min.css" rel="stylesheet">



    <!--DataTables -->
    <link href="/Static/plugins/datatables/media/css/dataTables.bootstrap.css" rel="stylesheet">
    <link href="/Static/plugins/datatables/extensions/Responsive/css/responsive.dataTables.min.css" rel="stylesheet">
    <!-- Fancybox -->
    <link href="/Static/plugins/fancybox/jquery.fancybox.css" rel="stylesheet">
    <!--Bootstrap Validator-->
    <link href="/Static/plugins/bootstrap-validator/bootstrapValidator.min.css" rel="stylesheet">
    <link href="/Static/plugins/impromptu/jquery-impromptu.css" rel="stylesheet">

    <link href="/Static/plugins/chosen/chosen.min.css" rel="stylesheet">
    <link href="/Static/plugins/bootstrap-datepicker/bootstrap-datepicker.css" rel="stylesheet">
    <link href="/Static/plugins/bootstrap-timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="/Static/plugins/select2/css/select2.min.css" rel="stylesheet">
    <link href="/Static/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet">
    <link href="/Static/plugins/fullcalendar/nifty-skin/fullcalendar-nifty.min.css" rel="stylesheet">

    <link href="/Static/css/custom.css?v=<%=getToken%>" rel="stylesheet" />

    <!-- CUSTOM -->
    <!-- <link href="/Static/css/themes/type-c/theme-navy.min.css" rel="stylesheet"> -->
    <link href="/Static/css/themes/type-c/theme-ocean.min.css" rel="stylesheet">



    <script id="polyfill">
        Number.prototype.round = function(decimals) {
            return Number((Math.round(this + "e" + decimals) + "e-" + decimals));
        }
    </script>

    <script src="/scripts/values"></script>
</head>
<!--TIPS-->
<!--You may remove all ID or Class names which contain "demo-", they are only used for demonstration. -->

<body>
    <div id="container" class="effect aside-float aside-bright mainnav-lg">

        <!--NAVBAR-->
        <!--===================================================-->
        <header id="navbar">
            <div id="navbar-container" class="boxed">

                <!--Brand logo & name-->
                <!--================================-->
                <div class="navbar-header">
                    <a href="/" class="navbar-brand">
                        <img src="/Static/img/logo.png" alt="Sistema Intranet" class="brand-icon">
                        <div class="brand-title">
                            <span class="brand-text">{{ Config_get('texto_intranet') }}</span>
                        </div>
                    </a>
                </div>
                <div class="navbar-content">
                    <ul class="nav navbar-top-links">
                        <li class="tgl-menu-btn">
                            <a class="mainnav-toggle" href="#">
                                <i class="demo-pli-list-view"></i>
                            </a>
                        </li>
                        <li>
                        </li>
                    </ul>
                    <ul class="nav navbar-top-links">
                        <li id="dropdown-user" class="dropdown">
                            <a href="#" data-toggle="dropdown" class="dropdown-toggle text-right">
                                <span class="ic-user pull-right">
                                    <i class="demo-pli-male"></i>
                                </span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right panel-default">
                                <ul class="head-list">
                                    <li>
                                        <a href="#/usuarios/password">{{ icon('lock') }} Cambiar Contraseña</a>
                                    </li>
                                    <li>
                                        <a href="/usuarios/logout">{{ icon('error') }} Cerrar Sesion</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </header>
        <!--===================================================-->
        <!--END NAVBAR-->

        <div class="boxed">
            
            <!--CONTENT CONTAINER-->
            <!--===================================================-->
            <div id="content-container" data-toggle="overlay" data-target="#content-container">
                <div class="alert alert-danger text-center text-bold" id="no-connection-message" style="display: none">NO TIENES CONEXIÓN A INTERNET</div>

                <div class="row">
                    <div style="padding-right: 0px" class="col-xs-12 col-sm-8 col-lg-8" id="content-container-left" data-toggle="overlay" data-target="#content-container-left">{{ block('main_content') }}</div>
                    <div style="padding-left: 0px" class="col-xs-12 col-sm-4 col-lg-4">

                        {% include '_right.php' %}
                    </div>
                </div>

            </div>
            <!--===================================================-->
            <!--END CONTENT CONTAINER-->






            <!--MAIN NAVIGATION-->
            <!--===================================================-->
            <nav id="mainnav-container">
                <div id="mainnav">

                    <!--Menu-->
                    <!--================================-->
                    <div id="mainnav-menu-wrap">
                        <div class="nano">
                            <div class="nano-content">

                                <!--Profile Widget-->
                                <!--================================-->
                                <div id="mainnav-profile" class="mainnav-profile">
                                    <div class="profile-wrap text-center">
                                        <div class="pad-btm">
                                            <img class="img-circle img-md" src="{{ USUARIO.getFoto() }}" alt="Foto de perfil">
                                        </div>
                                        <a href="#profile-nav" class="box-block" data-toggle="collapse" aria-expanded="false">
                                            <span class="pull-right dropdown-toggle">
                                                <i class="dropdown-caret"></i>
                                            </span>
                                            <p class="mnp-name">{{ USUARIO.getShortName() }}</p>
                                            <span class="mnp-desc">{{ USUARIO.tipo }}</span>
                                        </a>
                                    </div>

                                    {% include '_userSidebarOptions.php' %}
                                </div>


                                <!--Shortcut buttons-->
                                <!--================================-->
                                <div id="mainnav-shortcut" class="hidden">
                                    <ul class="list-unstyled shortcut-wrap">
                                        <li class="col-xs-3" data-content="My Profile">
                                            <a class="shortcut-grid" href="#">
                                                <div class="icon-wrap icon-wrap-sm icon-circle bg-mint">
                                                    <i class="demo-pli-male"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="col-xs-3" data-content="Messages">
                                            <a class="shortcut-grid" href="#">
                                                <div class="icon-wrap icon-wrap-sm icon-circle bg-warning">
                                                    <i class="demo-pli-speech-bubble-3"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="col-xs-3" data-content="Activity">
                                            <a class="shortcut-grid" href="#">
                                                <div class="icon-wrap icon-wrap-sm icon-circle bg-success">
                                                    <i class="demo-pli-thunder"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="col-xs-3" data-content="Lock Screen">
                                            <a class="shortcut-grid" href="#">
                                                <div class="icon-wrap icon-wrap-sm icon-circle bg-purple">
                                                    <i class="demo-pli-lock-2"></i>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                {% include '_menus.php' %}
                            </div>
                        </div>
                    </div>
                    <!--================================-->
                    <!--End menu-->

                </div>
            </nav>
            <!--===================================================-->
            <!--END MAIN NAVIGATION-->

        </div>



        <!-- FOOTER -->
        <!--===================================================-->
        <footer id="footer">

            <!-- Visible when footer positions are fixed -->
            <!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
            <div class="show-fixed pad-rgt pull-right">
                You have <a href="#" class="text-main"><span class="badge badge-danger">3</span> pending action.</a>
            </div>

            <p class="pad-lft">&#0169; Copyright {{ date()|date('Y') }}</p>



        </footer>
        <!--===================================================-->
        <!-- END FOOTER -->


        <!-- SCROLL PAGE BUTTON -->
        <!--===================================================-->
        <button class="scroll-top btn">
            <i class="pci-chevron chevron-up"></i>
        </button>
        <!--===================================================-->
    </div>
    <!--===================================================-->
    <!-- END OF CONTAINER -->





    <!--JAVASCRIPT-->
    <!--=================================================-->



    <!--jQuery [ REQUIRED ]-->



    <!--BootstrapJS [ RECOMMENDED ]-->
    <script src="/Static/js/bootstrap.min.js"></script>


    <!--NiftyJS [ RECOMMENDED ]-->
    <script src="/Static/js/nifty.min.js"></script>

    <!--=================================================-->

    <!--Demo script [ DEMONSTRATION ]-->
    <script src="/Static/js/custom/nifty-config.min.js"></script>


    <!--DataTables -->
    <script src="/Static/plugins/datatables/media/js/jquery.dataTables.js"></script>
    <script src="/Static/plugins/datatables/media/js/dataTables.bootstrap.js"></script>
    <!--<script src="/Static/plugins/datatables/extensions/Responsive/js/dataTables.responsive.min.js"></script>-->
    <!-- Fancybox -->
    <script src="/Static/plugins/fancybox/jquery.fancybox.js"></script>
    <!--Bootstrap Validator-->
    <script src="/Static/plugins/bootstrap-validator/bootstrapValidator.min.js"></script>
    <script src="/Static/plugins/bootstrap-validator/es_CL.js"></script>

    <script src="/Static/plugins/bootbox/bootbox.min.js"></script>
    <script src="/Static/plugins/impromptu/jquery-impromptu.min.js"></script>
    <script src="/Static/plugins/chosen/chosen.jquery.min.js"></script>
    <script src="/Static/plugins/chosen/jquery.ajaxchosen.js"></script>
    <script src="/Static/plugins/bootstrap-datepicker/bootstrap-datepicker.js"></script>
    <script src="/Static/plugins/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>

    <script src="/Static/plugins/flot-charts/jquery.flot.min.js"></script>
    <script src="/Static/plugins/flot-charts/jquery.flot.categories.min.js"></script>
    <script src="/Static/plugins/flot-charts/jquery.flot.orderBars.min.js"></script>
    <script src="/Static/plugins/flot-charts/jquery.flot.tooltip.min.js"></script>
    <script src="/Static/plugins/flot-charts/jquery.flot.resize.min.js"></script>

    <script src="/Static/plugins/fullcalendar/lib/moment.min.js"></script>
    <!--<script src="/Static/plugins/fullcalendar/lib/jquery-ui.custom.min.js"></script>-->
    <script src="/Static/plugins/fullcalendar/fullcalendar.min.js"></script>
    <script src="/Static/plugins/ckeditor5/ckeditor.js"></script>
    <script src="/Static/plugins/select2/js/select2.min.js"></script>

    <script type="text/javascript" src="/Static/plugins/uploader/jquery.iframe-transport.js"></script>
    <script type="text/javascript" src="/Static/plugins/uploader/jquery.fileupload.js"></script>
    <script type="text/javascript" src="/Static/plugins/uploader/jquery.fileupload-process.js"></script>
    <script type="text/javascript" src="/Static/plugins/uploader/jquery.fileupload-image.js"></script>
    <script src="https://unpkg.com/rxjs@%5E7/dist/bundles/rxjs.umd.min.js"></script>
    <script type="module" src="/Static/js/check-internet.js"></script>
    <script type="module" src="/Static/js/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="/Static/js/zk.js?v={{ getToken() }}"></script>
    <script src="/Static/js/main.js?v={{ getToken() }}"></script>

    <script>
        var token = $("meta[name='csrf-token']").attr("content");
        $(document).ajaxSend(function(e, xhr, options) {
            xhr.setRequestHeader("X-CSRF-Token", token);
        });



        $(function() {
            if (location.hash == '') {
                zk.goToUrl('{{ url }}');
            } else {
                zk.reloadPage();
            }
        })

        
    </script>


    {% set birthDays = COLEGIO.getMonthBirthdays(get.monthBirth) %}
    <link href="/Static/plugins/jgrowl/jquery.jgrowl.min.css" rel="stylesheet" />
    <script src="/Static/plugins/jgrowl/jquery.jgrowl.min.js"></script>
    {% if Config_get('show_birthday_window') == 'SI' %}
    <div id="monthBirthdays" style="display: none">
        <div style="width: 400px; background: white; padding: 5px">

            <p>
                <b>Cumpleaños del mes:</b>
                <select name="monthBirth" onchange="window.location = '/?monthBirth=' + this.value">
                    {% for mes in COLEGIO.MESES %}
                    <option value="{{ loop.index }}" {{ loop.index == get.monthBirth ? 'selected' : (not get.monthBirth and date()|date('m') == loop.index ? 'selected' : '') }}>{{ mes }}</option>
                    {% endfor %}
                </select>
            </p>
            {% if birthDays|length > 0 %}
            <table class="special" style="">
                <tr>
                    <th>Fecha</th>
                    <th>Nombre</th>
                </tr>
                {% for birthday in birthDays %}
                <tr>
                    <td class="center">{{ COLEGIO.parseFechaNoYear(birthday.fecha) }}</td>
                    <td class="center">{{ birthday.nombre }}</td>
                </tr>
                {% endfor %}
            </table>
            {% else %}
            <p class="center">No hay cumpleaños este mes.</p>
            {% endif %}
        </div>
    </div>

    <script>
        $(function() {
            $.jGrowl($('#monthBirthdays').html(), {
                sticky: true
            });
        });
    </script>

    {% endif %}

    {% if totalNotices > 0 %}
    <script>
    $(function(){
        fancybox('/comunicados/home_notices')
    })
    </script>
    {% endif %}
    <!-- CONFIG -->
    <!--<div id="demo-nifty-settings" class="demo-nifty-settings"><button id="demo-set-btn" class="btn"><i class="demo-psi-gear"></i></button><div id="demo-set-content" class="demo-set-content"></div></div>-->
</body>

</html>