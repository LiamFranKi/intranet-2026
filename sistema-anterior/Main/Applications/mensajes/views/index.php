<script>
$(function(){
    setMenuActive('mensajes');
})
function goNext(){
	zk.goToUrl('{{ path }}?page={{ page + 1 }}')
}
function goPrev(){
	{% if page > 1 %}
	zk.goToUrl('{{ path }}?page={{ page - 1 }}')
	{% endif %}
}
</script>
<div id="page-head"></div>
<!--Page content-->
<!--===================================================-->
<div id="page-content">
    
    <!-- MAIL INBOX -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-body">
            <div class="fixed-fluid">
                {% include 'aside.php' %}
                <div class="fluid">
                    <div id="demo-email-list">
                        <div class="row">
                            <div class="col-sm-7 toolbar-left">

                                <!-- Mail toolbar -->
                                <!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->

                               <!--
                                <div class="btn-group">
                                    <label id="demo-checked-all-mail" for="select-all-mail" class="btn btn-default">
                                	<input id="select-all-mail" class="magic-checkbox" type="checkbox">
                                	

                                	<label for="select-all-mail"></label>
                                    </label>
                                    
                                    <button data-toggle="dropdown" class="btn btn-default dropdown-toggle"><i class="dropdown-caret"></i></button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#" id="demo-select-all-list">All</a></li>
                                        <li><a href="#" id="demo-select-none-list">None</a></li>
                                        <li><a href="#" id="demo-select-toggle-list">Toggle</a></li>
                                        <li class="divider"></li>
                                        <li><a href="#" id="demo-select-read-list">Read</a></li>
                                        <li><a href="#" id="demo-select-unread-list">Unread</a></li>
                                        <li><a href="#" id="demo-select-starred-list">Starred</a></li>
                                    </ul>
                                </div>


                                
                                <button id="demo-mail-ref-btn" data-toggle="panel-overlay" data-target="#demo-email-list" class="btn btn-default" type="button">
                            		<i class="demo-psi-repeat-2"></i>
                        		</button>
                        		
                                <div class="btn-group dropdown">
                                    <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
                                More <i class="dropdown-caret"></i>
                            </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#">Mark as read</a></li>
                                        <li><a href="#">Mark as unread</a></li>
                                        <li class="divider"></li>
                                        <li><a href="#">Star</a></li>
                                        <li><a href="#">Clear Star</a></li>
                                    </ul>
                                </div>
                            	-->
                            </div>
                            <div class="col-sm-5 toolbar-right">
                                
                                <div class="btn-group btn-group">
	                                <button class="btn btn-default" type="button" onclick="goPrev()">
	                                    <i class="demo-psi-arrow-left"></i>
	                                </button>
	                                <button class="btn btn-default" type="button" onclick="goNext()">
	                                    <i class="demo-psi-arrow-right"></i>
	                                </button>
                                </div>
                            </div>
                        </div>

                        {% if mensajes|length > 0 %}
                        <ul id="demo-mail-list" class="mail-list pad-top bord-top">

                            {% for mensaje in mensajes %}
                            <li class="{{ mensaje.estado == "NO_LEIDO" ? "mail-list-unread" : '' }} {{ mensaje.favorito == "SI" ? 'mail-starred' : '' }} {{ mensaje.archivos|length > 0 ? 'mail-attach' : '' }}">
                                <div class="mail-control">
                                    <input id="email-list-1" class="magic-checkbox" type="checkbox">
                                    <label for="email-list-1"></label>
                                </div>
                                <div class="mail-star"><a href="#/mensajes/ver/{{ sha1(mensaje.id) }}"><i class="demo-psi-star"></i></a></div>
                                <div class="mail-from"><a href="#/mensajes/ver/{{ sha1(mensaje.id) }}">{{ mensaje.tipo == "RECIBIDO" ? mensaje.remitente.getShortName() : mensaje.destinatario.getShortName() }}</a></div>
                                <div class="mail-time">{{ mensaje.fecha_hora|date('d-m-Y') }}</div>
                                <div class="mail-attach-icon">
                                	{% if mensaje.archivos|length > 0 %}
                                	<i class="demo-psi-paperclip"></i>
                                	{% endif %}
                                </div>
                                <div class="mail-subject">
                                    <a href="#/mensajes/ver/{{ sha1(mensaje.id) }}">{{ mensaje.asunto }}</a>
                                </div>
                            </li>
                            {% endfor %}

                        </ul>
                        {% else %}
                        <p class="text-center text-bold">NO SE ENCONTRARON RESULTADOS</p>
                        {% endif %}
                    </div>


                    <!--Mail footer-->
                    <div class="panel-footer clearfix">
                        <div class="pull-right">
                            
                            <div class="btn-group btn-group">
                                <button class="btn btn-default" type="button" onclick="goPrev()">
                                    <i class="demo-psi-arrow-left"></i>
                                </button>
                                <button class="btn btn-default" type="button" onclick="goNext()">
                                    <i class="demo-psi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===================================================-->
    <!-- END OF MAIL INBOX -->

    
</div>
<!--===================================================-->
<!--End page content-->