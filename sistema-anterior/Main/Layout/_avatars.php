<style>
.avatar-widget * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.avatar-widget {
    background-color: #f5f5f5;
    color: #333;
    padding: 20px;
}

.avatar-widget-container {
    max-width: 100%;
    margin: 0 auto;
    background-color: white;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.avatar-widget-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    text-align: center;
}

.avatar-widget-title {
    font-size: 1.3em;
    font-weight: bold;
    margin-bottom: 10px;
}

.avatar-widget-saldo-display {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    font-size: 1.8rem;
    font-weight: bold;
}

.avatar-widget-star-icon {
    color: #f1c40f;
    font-size: 2rem;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.avatar-widget-content {
    padding: 20px;
}

.avatar-widget-section-title {
    font-size: 1.1em;
    font-weight: bold;
    margin-bottom: 15px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.avatar-widget-avatares-canjeados {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.avatar-widget-avatar-mini {
    position: relative;
    aspect-ratio: 581/1280;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e0e0e0;
    transition: transform 0.2s;
}

.avatar-widget-avatar-mini:hover {
    transform: scale(1.05);
    border-color: #3498db;
}

.avatar-widget-avatar-mini img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-widget-avatar-mini-name {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    color: white;
    padding: 8px 4px 4px;
    font-size: 0.7em;
    text-align: center;
    font-weight: bold;
}

.avatar-widget-stats-section {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
}

.avatar-widget-stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.avatar-widget-stat-item:last-child {
    margin-bottom: 0;
}

.avatar-widget-stat-label {
    color: #666;
    font-size: 0.9em;
}

.avatar-widget-stat-value {
    font-weight: bold;
    color: #2c3e50;
}

.avatar-widget-empty-state {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 20px;
}

.avatar-widget-action-button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 15px;
    transition: transform 0.2s;
}

.avatar-widget-action-button:hover {
    transform: translateY(-2px);
}

/* Media queries para responsividad */
@media (max-width: 480px) {
    .avatar-widget-container {
        max-width: 100%;
        margin: 0;
    }
    
    .avatar-widget-avatares-canjeados {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .avatar-widget-saldo-display {
        font-size: 1.5rem;
    }
    
    .avatar-widget-star-icon {
        font-size: 1.7rem;
    }
}
</style>

<div class="avatar-widget">
    <div class="avatar-widget-container">
        <div class="avatar-widget-header">
            <div class="avatar-widget-title">Mi Colección de Avatares</div>
            <div class="avatar-widget-saldo-display">
                <span class="avatar-widget-star-icon">★</span>
                <span>{{ USUARIO.alumno.getStarsAmount() }}</span>
            </div>
        </div>
        
        <div class="avatar-widget-content">
            <div class="avatar-widget-section-title">
                🏆 Avatares Canjeados
            </div>
            
            <div class="avatar-widget-avatares-canjeados">
                {% if USUARIO.alumno.getAvatars()|length > 0 %}
                    {% for item in USUARIO.alumno.getAvatars() %}
                    <div class="avatar-widget-avatar-mini">
                        <a href="javascript:;" onclick="$.fancybox('/Static/Image/Avatars/{{ item.image }}')"><img src="/Static/Image/Avatars/{{ item.image }}" alt="{{ item.name }}"></a>
                        <div class="avatar-widget-avatar-mini-name">{{ item.name }} - Lv {{ item.level }}</div>
                    </div>
                    {% endfor %}
                {% else %}
                    <p class="text-center">TODAVÍA NO HA CANJEADO NINGÚN AVATAR</p>
                {% endif %}
            </div>
            
            <!-- <div class="avatar-widget-stats-section">
                <div class="avatar-widget-section-title">
                    📊 Estadísticas
                </div>
                
                <div class="avatar-widget-stat-item">
                    <span class="avatar-widget-stat-label">Avatares canjeados:</span>
                    <span class="avatar-widget-stat-value">{{ USUARIO.alumno.avatar_sales|length }}</span>
                </div>

            </div> -->
            
            <button class="avatar-widget-action-button" onclick="zk.goToUrl('/avatar_shop_sales/shop')">
                Ver Tienda de Avatares
            </button>
        </div>
    </div>
</div>