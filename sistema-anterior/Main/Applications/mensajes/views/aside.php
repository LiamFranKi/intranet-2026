<div class="fixed-sm-200 pull-sm-left fixed-right-border">

    <div class="pad-btm bord-btm">
        <a href="#/mensajes/form" class="btn btn-block btn-success">Enviar Nuevo</a>
    </div>

    <p class="pad-hor mar-top text-main text-bold text-sm text-uppercase">Lista de Mensajes</p>
    <div class="list-group bg-trans pad-btm bord-btm">
        <a href="#/mensajes" class="list-group-item">
            {{ icon('email') }}&nbsp;&nbsp;Mensajes Recibidos
        </a>
        <!--
        <a href="#" class="list-group-item">
            <i class="demo-pli-pen-5 icon-lg icon-fw"></i> Draft
        </a>
    	-->
        <a href="#/mensajes/enviados" class="list-group-item">
            {{ icon('email_go') }}&nbsp;&nbsp;Mensajes Enviados
        </a>
        <!--
        <a href="#" class="list-group-item mail-nav-unread">
            <i class="demo-pli-fire-flame-2 icon-lg icon-fw"></i> Spam (5)
        </a>
        <a href="#" class="list-group-item">
            <i class="demo-pli-trash icon-lg icon-fw"></i> Trash
        </a>
    	-->
    </div>
</div>