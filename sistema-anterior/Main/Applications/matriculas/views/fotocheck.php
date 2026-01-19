<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotocheck Estudiantil</title>
    <script src="/Static/js/jquery-2.1.1.min.js"></script>
    <script src="/Static/js/qrcode.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Comic Sans MS', 'Chalkboard SE', sans-serif;
            box-sizing: border-box;
            /*background-color: #f0f0f0;*/
        }
        .fotocheck {
            width: 8cm;
            height: 9cm;
            border: 3px solid #000;
            border-radius: 20px;
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
            /*background-color: ##03a9f4;*/
            position: relative;
            overflow: hidden;
        }
        .inner-container {
            background-color: white;
            border-radius: 15px;
            height: 100%;
            padding: 10px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            position: relative;
            border: 2px solid #000;
        }
        .header {
            text-align: center;
            padding-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .school-name {
            font-size: 14px;
            font-weight: bold;
            flex-grow: 1;
            text-align: center;
        }
        .logo-container {
            width: 60px;
            height: 60px;
            /* border: 1px solid #000; */
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            text-align: center;
        }


        .content-area {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin: 10px 0;
        }
        .qr-code {
            width: 100px;
            height: 100px;
            /* border: 2px solid #000; */
            background-color: white;
        }
        .photo-space {
            width: 80px;
            height: 100px;
            border: 2px solid #000;
            background-color: white;
        }
        .student-info {
            font-size: 12px;
            margin-top: 5px;
            width: 100%;
        }
        .info-line {
            display: flex;
            margin-bottom: 4px;
        }
        .info-label {
            font-weight: bold;
            margin-right: 5px;
        }
        .info-value {
            flex-grow: 1;
            border-bottom: 1px dotted #999;
        }
        .year {
            text-align: center;
            font-weight: bold;
            margin-top: auto;
            font-size: 12px;
        }
        .pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.1;
        }
        .pattern:before {
            content: "";
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            z-index: -1;
            background: repeating-linear-gradient(
                45deg,
                #000,
                #000 5px,
                transparent 5px,
                transparent 20px
            );
        }
    </style>

    <script>
    $(function(){
        var qrcode = new QRCode("qr-code", {
            text: "{{ sha1(matricula.id) }}",
            width: 100,
            height: 100,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    })
    </script>
</head>
<body>
    <div class="fotocheck">
        <div class="pattern"></div>
        <div class="inner-container">
            <div class="header">
                <div class="school-name">{{ Config_get('remitente_emails') }}</div>

                <img src="/Static/Image/Fondos/{{ Config_get('libreta_logo') }}" class="logo-container">
                <!-- <div class="logo-container">
                    <div>Insignia<br>Colegio</div>
                </div> -->
            </div>
            
            <div class="content-area">
                <div class="qr-code" id="qr-code">
                    <!-- <img src="/api/placeholder/80/80" alt="Código QR"> -->
                </div>
                <div class="photo-space">
                    <!-- <img src="/api/placeholder/80/100" alt="Foto Estudiante"> -->
                </div>
            </div>
            
            <div class="student-info">
                <div class="info-line">
                    <div class="info-label">Nombres:</div>
                    <div class="info-value">{{ matricula.alumno.nombres }}</div>
                </div>
                <div class="info-line">
                    <div class="info-label">Apellidos:</div>
                    <div class="info-value">{{ matricula.alumno.apellido_paterno }} {{ matricula.alumno.apellido_materno }}</div>
                </div>
                <div class="info-line">
                    <div class="info-label">Sección:</div>
                    <div class="info-value">{{ nivel }}</div>
                </div>
                
            </div>
            
            <div class="year">{{ matricula.grupo.anio }}</div>
        </div>
    </div>
</body>
</html>