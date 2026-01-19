<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Venta</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            /* background-color: #f5f7fa; */
            display: flex;
            justify-content: center;
            align-items: center;
            /* min-height: 100vh; */
        }
        
        .receipt {
            width: 650px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .receipt::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, #3a86ff, #8338ec, #ff006e);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            align-items: center;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
        }
        
        .logo {
            width: 65px;
            height: 65px;
            /* background: linear-gradient(135deg, #ffd500, #ffa200); */
            /* border-radius: 50%; */
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            /* border: 3px solid #444;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); */
            position: relative;
            /* overflow: hidden; */
        }

        .logo img{
            width: 65px;
            height: 65px;
        }
        
        .logo-inner {
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .logo-text {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }
        
        .school-info {
            display: flex;
            flex-direction: column;
        }
        
        .school-name {
            font-weight: bold;
            font-size: 20px;
            color: #2d3748;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .motto {
            font-size: 13px;
            color: #4a5568;
            letter-spacing: 1px;
            font-style: italic;
        }
        
        .document-info {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            width: 220px;
            background-color: #fdfdfd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .ruc {
            font-size: 14px;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
        }
        
        .doc-title {
            background: linear-gradient(90deg, #e2e8f0, #cbd5e0);
            padding: 8px;
            margin: 8px 0;
            font-weight: bold;
            border-radius: 4px;
            color: #2d3748;
            letter-spacing: 1px;
        }
        
        .doc-number {
            
            justify-content: space-between;
            margin-top: 8px;
            font-size: 14px;
            padding: 0 10px;
        }
        
        .client-section {
            display: flex;
            margin-bottom: 20px;
            gap: 20px;
        }
        
        .client-info {
            flex: 1;
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3a86ff;
        }
        
        .date-section {
            width: 180px;
            align-self: flex-start;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }
        
        .info-label {
            width: 110px;
            font-weight: 600;
            color: #4a5568;
        }
        
        .info-value {
            color: #2d3748;
        }
        
        .date-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            font-size: 13px;
        }
        
        .date-table th, .date-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            text-align: center;
        }
        
        .date-table th {
            background: linear-gradient(90deg, #e2e8f0, #cbd5e0);
            color: #2d3748;
            font-weight: 600;
            font-size: 12px;
        }
        
        .date-table td {
            background-color: white;
        }
        
        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 25px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .items-table th, .items-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 12px;
            height: 15px;
        }
        
        .items-table th {
            background: linear-gradient(90deg, #e2e8f0, #cbd5e0);
            text-align: left;
            color: #2d3748;
            font-weight: 600;
        }
        
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        
        .items-table tr:hover td {
            background-color: #edf2f7;
        }
        
        .amount-in-text {
            font-size: 14px;
            margin-bottom: 15px;
            color: #4a5568;
            font-style: italic;
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 6px;
            border-left: 4px solid #8338ec;
        }
        
        .total-section {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background-color: #f8fafc;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .total-label {
            font-weight: bold;
            margin-right: 15px;
            font-size: 16px;
            color: #2d3748;
        }
        
        .total-value {
            font-size: 18px;
            font-weight: bold;
            color: #3a86ff;
        }
        
        .account-info {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding: 15px 0;
            border-top: 2px dashed #e2e8f0;
            border-bottom: 2px dashed #e2e8f0;
        }
        
        .account {
            font-size: 14px;
            color: #4a5568;
            font-weight: 500;
        }
        
        .status {
            text-align: center;
        }
        
        .status-title {
            font-size: 14px;
            color: #4a5568;
            margin-bottom: 5px;
        }
        
        .status-label {
            font-weight: bold;
            background-color: #10b981;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            letter-spacing: 1px;
            box-shadow: 0 2px 5px rgba(16, 185, 129, 0.3);
        }
        
        .footer {
            font-size: 11px;
            color: #718096;
            margin-top: 25px;
            text-align: center;
            line-height: 1.6;
        }
        
        .copyright {
            margin-top: 15px;
            font-size: 10px;
            color: #a0aec0;
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        
        .red-text {
            color: #e53e3e;
            font-weight: bold;
        }

        .text-center{
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    
                    <img src="/Static/Archivos/{{ Config_get('boleta_logo') }}" alt="">
                </div>
                <div class="school-info">
                    <div class="school-name">{{ COLEGIO.razon_social }}</div>
                    <div class="motto"></div>
                </div>
            </div>
            <div class="document-info">
                <div class="ruc">R.U.C. {{ COLEGIO.ruc }}</div>
                <div class="doc-title">BOLETA DE VENTA</div>
                <div class="doc-number">
                    <span class="red-text">N°. {{ boleta.getNroBoleta() }}</span>
                </div>
            </div>
        </div>
        
        <div class="client-section">
            <div class="client-info">
                <div class="info-row">
                    <div class="info-label">SEÑOR (A):</div>
                    <div class="info-value">{{ boleta.nombre }}</div>
                </div>
              
                <div class="info-row">
                    <div class="info-label">DNI:</div>
                    <div class="info-value">{{ boleta.dni }}</div>
                </div>
            </div>
            
            <div class="date-section">
                <table class="date-table">
                    <thead>
                        <tr>
                            <th>DÍA</th>
                            <th>MES</th>
                            <th>AÑO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ boleta.fecha|date('d') }}</td>
                            <td>{{ boleta.fecha|date('m') }}</td>
                            <td>{{ boleta.fecha|date('Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th width="10%">N°.</th>
                    <th width="65%">DESCRIPCIÓN</th>
                    <th width="25%">IMPORTE S/</th>
                </tr>
            </thead>
            <tbody>
                {% for detalle in boleta.detalles %}
                <tr>
                    <td class="text-center">{{ detalle.cantidad }}</td>
                    <td>{{ detalle.concepto.descripcion }}</td>
                    <td class="text-center">{{ detalle.getImporte()|number_format(2) }}</td>
                </tr>
                {% endfor %}

                {% for i in 1..(5 - boleta.detalles|length) %}
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                {% endfor %}
                
            </tbody>
        </table>
        
        <div class="amount-in-text">
        {{ letras }}
        </div>
        
        <div class="total-section">
            <div class="total-label">TOTAL S/.</div>
            <div class="total-value">{{ boleta.getMontoTotal()|number_format(2) }}</div>
        </div>
        
        <div class="account-info">
            <div class="account">{{ boleta.tipo_pago }}</div>
            <div class="status">
                <div class="status-title">ESTADO:</div>
                <div class="status-label">{{ boleta.estado_pago }}</div>
            </div>
        </div>
        
        <div class="footer">
            NO ES VÁLIDO PARA CRÉDITO FISCAL. PUEDE SER CANJELADO POR BOLETA DE VENTA SI ES REQUERIDO.
            <br>
            {{ COLEGIO.razon_social }}
        </div>
        
        <div class="copyright">
            © {{ date|date('Y') }} / {{ COLEGIO.razon_social }}
        </div>
    </div>
    <script>
    window.onload = () => {
        window.print();
    }
    </script>
</body>
</html>