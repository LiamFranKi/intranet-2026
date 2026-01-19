{% extends main_template %}
{% block main_content %}
<script>
    $(function() {
        setMenuActive('avatarShopSales');
    });

    function buyItem(id){
	zk.confirm('¿Está seguro de canjear este avatar?', function(){
		zk.sendData('/avatar_shop_sales/save', {item_id: id}, function(r){
			if(parseInt(r.status) == 1){
				zk.pageAlert({message: 'Avatar canjeado con éxito', title: 'Operación Exitosa', icon: 'check', type: 'success', container: 'floating'});
				zk.reloadPage()
			}else if(r.status == -1){
                zk.pageAlert({message: 'No cuenta con saldo suficiente', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});
            } else {
				zk.pageAlert({message: 'No se pudieron borrar los datos', title: 'Operación Fallida', icon: 'remove', type: 'danger', container: 'floating'});
			}
		});
	});
}
</script>
<div id="page-head">
    <div id="page-title">
        <h1 class="page-header text-overflow">Tienda de Avatares</h1>
    </div>
    <ol class="breadcrumb">
        <li><a href="/"><i class="demo-pli-home"></i></a></li>
        <li><a href="#/avatar_shop_sales">Tienda</a></li>
        <li class="active">Lista de Avatares</li>
    </ol>
</div>

<!--Page content-->
<!--===================================================-->
<div id="page-content">

    <div class="panel">

        <div class="panel-body">
            <div class="saldo-container">
                <div class="saldo-titulo">Tu saldo disponible:</div>
                <div class="saldo-cantidad">
                    <span class="saldo-icono">★</span>{{ balance }}
                </div>
            </div>
            <div class="shop-container">
                <div class="avatar-grid">
                    {% for item in items %}
                    <div class="avatar-card {{ in_array(item.id, redeemed) ? 'canjeado' : '' }}">
                        <div class="avatar-image">
                            <img src="/Static/Image/Avatars/{{ item.image }}" alt="Avatar Fantasia">
                        </div>
                        <div class="avatar-info">
                            <div class="avatar-name">{{ item.name }} - Lv {{ item.level }}</div>
                            <div class="avatar-description">{{ item.description }}</div>
                            <div class="avatar-price">{{ item.price }} Estrellas</div>
                            
                            <button class="{{ in_array(item.id, redeemed) ? 'btn-canjeado' : 'btn-canjear' }}" onclick="buyItem('{{ sha1(item.id) }}')" {{ in_array(item.id, redeemed) ? 'disabled' : '' }}>Canjear</button>
                        </div>
                    </div>
                    {% endfor %}
                </div>
            </div>

        </div>
    </div>
</div>

{% endblock %}