<div class="maintenance-container">
    <div class="maintenance-content">
        <svg xmlns="http://www.w3.org/2000/svg" class="maintenance-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
        </svg>
        <h1>En Mantenimiento</h1>
        <p>Estamos realizando mejoras en nuestra plataforma de matrícula online.</p>
        <p class="sub-text">Por favor, vuelve a intentarlo más tarde.</p>
    </div>
</div>

<style>
    body, html {
        height: 100%;
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        background-color: #f8f9fa;
        color: #333;
    }
    .maintenance-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        text-align: center;
        padding: 20px;
    }
    .maintenance-content {
        max-width: 500px;
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }
    .maintenance-icon {
        width: 64px;
        height: 64px;
        color: #6c757d;
        margin-bottom: 20px;
        animation: float 3s ease-in-out infinite;
    }
    h1 {
        font-weight: 700;
        margin-bottom: 15px;
        font-size: 24px;
        color: #2d3436;
    }
    p {
        font-size: 16px;
        line-height: 1.6;
        color: #636e72;
        margin: 0 0 10px 0;
    }
    .sub-text {
        font-size: 14px;
        color: #b2bec3;
    }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
</style>