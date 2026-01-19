<style>
figure img{
	max-width: 100%;
}
</style>
<div id="page-content">
                    
					    <div class="panel">
					        <div class="panel-body">
					            <div class="fixed-fluid">
					                {% include 'aside.php' %}
					                <div class="fluid">
					
					                    <!-- VIEW MESSAGE -->
					                    <!--===================================================-->
					
					                    <div class="mar-btm pad-btm bord-btm">
					                        <h1 class="page-header text-overflow">
					                           	{{ mensaje.asunto }}
					                        </h1>
					                    </div>
					
					                    <div class="row">
					                        <div class="col-sm-7 toolbar-left">
					
					                            <!--Sender Information-->
					                            <div class="media">
					                                <span class="media-left">
					                                <img src="{{ mensaje.destinatario.getFoto() }}" class="img-circle img-sm" alt="Foto">
					                            </span>
					                                <div class="media-body text-left">
					                                    <div class="text-bold">{{ mensaje.destinatario.getFullName() }}</div>
					                                    <small class="text-muted">{{ mensaje.destinatario.tipo }}</small>
					                                </div>
					                            </div>
					                        </div>
					                        <div class="col-sm-5 toolbar-right">
					
					                            <!--Details Information-->
					                            <p class="mar-no"><small class="text-muted">{{ mensaje.fecha_hora|date('d-m-Y h:i A') }}</small></p>
					                            
					                        </div>
					                    </div>

					
					                    <!--Message-->
					                    <!--===================================================-->
					                    <div class="mail-message">
					                        {{ mensaje.mensaje }}
					                    </div>
					                    <!--===================================================-->
					                    <!--End Message-->
					
					                    <!-- Attach Files-->
					                    <!--===================================================-->
					                    {% if mensaje.archivos|length > 0 %}
					                    <div class="pad-ver">
					                        <p class="text-main text-bold box-inline"><i class="demo-psi-paperclip icon-fw"></i> Archivos Adjuntos</p>
					                      
					                        <ul class="mail-attach-list list-ov">
					                            {% for archivo in mensaje.archivos %}
					                            <li>
					                                <a href="/Static/Archivos/{{ archivo.archivo }}" target="_blank" class="thumbnail">
					                                   
					                                    <div class="caption">
					                                        <p class="text-main mar-no">{{ archivo.nombre_archivo }}</p>
					                                       
					                                    </div>
					                                </a>
					                            </li>
					                            {% endfor %}
					                        </ul>
					                    </div>
					                    {% endif %}
					                    <!--===================================================-->
					                    <!-- End Attach Files-->
					
					
					                    <!--===================================================-->
					                    <!-- END VIEW MESSAGE -->
					
					                </div>
					            </div>
					        </div>
					    </div>
					    
                </div>