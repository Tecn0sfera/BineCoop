<?php
// Incluir manejador de idiomas
require_once __DIR__ . '/lang_handler.php';
$current_lang = $GLOBALS['current_lang'];
?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($current_lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BINECOOP - Bienestar de Cooperativa Pública Nacional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'coop-green': {
                            50: '#f0f7f4',
                            100: '#d4e8dd',
                            500: '#4a7c59',
                            600: '#3d6a4d',
                            700: '#2d5a3d',
                            800: '#1a3426',
                        },
                        'coop-beige': {
                            50: '#f8f6f0',
                            100: '#f4f1e8',
                            200: '#e8dcc0',
                            300: '#d4c4a0',
                        }
                    },
                    fontFamily: {
                        'sans': ['Segoe UI', 'Tahoma', 'Geneva', 'Verdana', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #f4f1e8 0%, #e8dcc0 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1000;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            /* Permitir que el logo tenga el tamaño deseado sin restricciones */
            min-width: auto;
            width: auto;
            height: auto;
        }
        
        .logo-icon img {
            max-width: none !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain;
        }

        .logo-icon-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            flex-shrink: 0;
        }
        
        .logo-icon-footer img {
            width: 50px !important;
            height: 50px !important;
            max-width: 50px !important;
            max-height: 50px !important;
            object-fit: contain;
        }

        .logo-text h1 {
            color: #2d5a3d;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .logo-text p {
            color: #4a7c59;
            font-size: 14px;
            margin-top: -5px;
        }

        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-btn {
            background: linear-gradient(135deg, #4a7c59 0%, #2d5a3d 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(74, 124, 89, 0.3);
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 124, 89, 0.4);
            background: linear-gradient(135deg, #5a8c69 0%, #3d6a4d 100%);
        }

        .login-btn {
            background: linear-gradient(135deg, #6a9ab0 0%, #4a7a90 100%);
            box-shadow: 0 4px 15px rgba(106, 154, 176, 0.3);
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #7aaac0 0%, #5a8aa0 100%);
            box-shadow: 0 6px 20px rgba(106, 154, 176, 0.4);
        }

        /* Origins Section */
        .origins {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8f6f0 0%, #e8dcc0 100%);
        }

        .origins-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .origins-content h2 {
            color: #2d5a3d;
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 30px;
            position: relative;
        }

        .origins-content h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, #4a7c59 0%, #2d5a3d 100%);
            border-radius: 2px;
        }

        .origins-content p {
            color: #555;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        .origins-buttons {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }

        .origins-btn {
            background: linear-gradient(135deg, #4a7c59 0%, #2d5a3d 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(74, 124, 89, 0.3);
        }

        .origins-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(74, 124, 89, 0.4);
        }

        .origins-btn.secondary {
            background: transparent;
            color: #4a7c59;
            border: 2px solid #4a7c59;
            box-shadow: none;
        }

        .origins-btn.secondary:hover {
            background: #4a7c59;
            color: white;
        }

        .origins-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .origins-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(74, 124, 89, 0.1) 0%, rgba(45, 90, 61, 0.1) 100%);
            z-index: 1;
        }

        .building-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #d4c4a0 0%, #b8a88a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8a7a6a;
            font-size: 18px;
            font-weight: 600;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s ease;
        }

        .modal-header {
            background: linear-gradient(135deg, #4a7c59 0%, #2d5a3d 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 20px 20px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .close {
            color: white;
            float: right;
            font-size: 32px;
            font-weight: bold;
            line-height: 1;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close:hover {
            transform: scale(1.1);
            opacity: 0.8;
        }

        .modal-body {
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .details-section {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .detail-group {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 15px;
            border-left: 4px solid #4a7c59;
        }

        .detail-group h3 {
            color: #2d5a3d;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-group ul {
            list-style: none;
            padding: 0;
        }

        .detail-group li {
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
            color: #555;
            line-height: 1.5;
        }

        .detail-group li::before {
            content: '▪';
            color: #4a7c59;
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .detail-group .highlight {
            font-weight: 600;
            color: #2d5a3d;
        }

        .plan-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .plan-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #e8dcc0 0%, #d4c4a0 100%);
            border: 2px dashed #4a7c59;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #4a7c59;
            font-weight: 600;
            text-align: center;
            padding: 20px;
        }

        .plan-placeholder::before {
            content: '';
            font-size: 48px;
            margin-bottom: 10px;
        }

        .plan-info {
            background: #f0f8f4;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #4a7c59;
        }

        .plan-info h4 {
            color: #2d5a3d;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .plan-info p {
            color: #555;
            font-size: 14px;
            margin: 0;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #2d5a3d 0%, #1a3426 100%);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 60px;
        }

        .footer-section h3 {
            color: #f4f1e8;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .footer-section p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-link svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .social-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .footer-bottom {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Language Selector */
        .language-selector {
            position: relative;
            display: inline-block;
        }

        .language-selector-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .language-selector-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .language-selector-btn svg {
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }

        .language-selector.active .language-selector-btn svg {
            transform: rotate(180deg);
        }

        .language-dropdown {
            position: absolute;
            bottom: 100%;
            right: 0;
            margin-bottom: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 150px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .language-selector.active .language-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .language-option {
            padding: 12px 16px;
            cursor: pointer;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s ease;
            border-radius: 8px;
            margin: 4px;
        }

        .language-option:first-child {
            margin-top: 4px;
        }

        .language-option:last-child {
            margin-bottom: 4px;
        }

        .language-option:hover {
            background: #f4f1e8;
        }

        .language-option.active {
            background: #2d5a3d;
            color: white;
        }

        .language-option svg {
            width: 20px;
            height: 20px;
        }

        @media (max-width: 768px) {
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .language-dropdown {
                right: auto;
                left: 50%;
                transform: translateX(-50%) translateY(10px);
            }

            .language-selector.active .language-dropdown {
                transform: translateX(-50%) translateY(0);
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .scroll-reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s ease;
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 20px;
            }

            .nav-menu {
                flex-wrap: wrap;
                gap: 15px;
            }

            .origins-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .modal-body {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .footer-container {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
               <div class="logo-icon"><a href="./index.php"><img src="https://tectesting.fwh.is/cdn_images/iii.png" style="width: 150px; height: 50px; margin-left: -100px;"></img></a></div>
                <div class="logo-text">
            </div>
            <nav class="nav-menu">
                <button class="nav-btn" onclick="location.href='./quienes_somos.php'" data-translate="nav.quienes">Quiénes somos</button>
                <button class="nav-btn" onclick="location.href='./faq.php'" data-translate="nav.faq">Preguntas Frecuentes</button>
            </nav>
        </div>
    </header>

   <!-- Origins Section -->
    <section class="origins scroll-reveal">
        <div class="origins-container">
            <div class="origins-content">
                <h2 data-translate="housing.apartment.title">Vivienda de Apartamento</h2>
                <p data-translate="housing.apartment.desc">Este apartamento es una unidad funcional y luminosa de aproximadamente 55 m², ideal para familias pequeñas o parejas. Cuenta con un amplio living-comedor al frente con gran entrada de luz natural, una cocina independiente, un dormitorio principal, un baño completo y, en algunos casos, un segundo dormitorio adaptable como escritorio o cuarto infantil. Su diseño sencillo, típico de las viviendas cooperativas de los años 80 en Las Piedras, prioriza la comodidad, la ventilación y la practicidad en un entorno barrial tranquilo y bien conectado.</p>
                <div class="origins-buttons">
                    <button class="origins-btn secondary" onclick="openModal('apartmentModal')" data-translate="housing.consult">Consultar</button>
                    <button class="origins-btn secondary" onclick="location.href='./login.php'" data-translate="housing.acquire">Adquirir</button>
                </div>
            </div>
            <div class="origins-image">
                <div class="building-placeholder">
                    <img src="https://tectesting.fwh.is/cdn_images/ed_vivienda.png" height="400px" width="600px"></img>
                </div>
            </div>
             <div class="origins-content">
                <h2 data-translate="housing.house.title">Casa de Vivienda</h2>
                <p data-translate="housing.house.desc">La vivienda individual en Binecoop está pensada para brindar confort, eficiencia y conexión comunitaria. Con diseño moderno, cuenta con dos plantas que distribuyen de forma práctica sus espacios: amplio living-comedor integrado a la cocina, tres dormitorios, dos baños completos, espacio flexible para oficina o estudio y salida a un patio verde con pérgola. Construida con materiales sustentables y equipada con grandes ventanales, la casa prioriza la luz natural y la armonía con el entorno, adaptándose a las necesidades de cada familia dentro del barrio cooperativo.</p>
                <div class="origins-buttons">
                    <button class="origins-btn secondary" onclick="openModal('houseModal')" data-translate="housing.consult">Consultar</button>
                    <button class="origins-btn secondary" onclick="location.href='./login.php'" data-translate="housing.acquire">Adquirir</button>
                </div>
            </div>
            <div class="origins-image">
                <div class="building-placeholder">
                    <img src="https://tectesting.fwh.is/cdn_images/casa_out.png" height="400px" width="600px"></img>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal for Apartment -->
    <div id="apartmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('apartmentModal')">&times;</span>
                <h2 data-translate="housing.apartment.modal.title">Apartamento BINECOOP - Características Detalladas</h2>
            </div>
            <div class="modal-body">
                <div class="details-section">
                    <div class="detail-group">
                        <h3 data-translate="apartment.gen.title">Descripción General</h3>
                        <ul>
                            <li data-translate="apartment.gen.item1"><span class="highlight">Superficie estimada:</span> 50 a 60 m²</li>
                            <li data-translate="apartment.gen.item2"><span class="highlight">Planta:</span> Rectangular y lineal</li>
                            <li data-translate="apartment.gen.item3"><span class="highlight">Estilo:</span> Funcional, con buena iluminación natural frontal</li>
                            <li data-translate="apartment.gen.item4"><span class="highlight">Ventanal principal:</span> Gran entrada de luz natural</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3 data-translate="apartment.dist.title">Distribución de Ambientes</h3>
                        <ul>
                            <li data-translate="apartment.dist.item1"><span class="highlight">Living-Comedor:</span> Al frente (4.5m x 3.5m), gran ventanal</li>
                            <li data-translate="apartment.dist.item2"><span class="highlight">Cocina:</span> Contigua al living (2.5m x 2m), forma rectangular</li>
                            <li data-translate="apartment.dist.item3"><span class="highlight">Dormitorio principal:</span> Al fondo (3.5m x 3m), placard incluido</li>
                            <li data-translate="apartment.dist.item4"><span class="highlight">Dormitorio secundario:</span> Opcional (3m x 2.2m), ideal para oficina</li>
                            <li data-translate="apartment.dist.item5"><span class="highlight">Baño:</span> Completo (2.2m x 1.6m), ducha y ventilación</li>
                            <li data-translate="apartment.dist.item6"><span class="highlight">Pasillo:</span> Distribución (1.2m ancho) conecta todos los ambientes</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3 data-translate="apartment.feat.title">Características Constructivas</h3>
                        <ul>
                            <li data-translate="apartment.feat.item1">Construcción típica años 80</li>
                            <li data-translate="apartment.feat.item2">Ventilación cruzada optimizada</li>
                            <li data-translate="apartment.feat.item3">Orientación para máximo aprovechamiento de luz</li>
                            <li data-translate="apartment.feat.item4">Acabados funcionales y duraderos</li>
                        </ul>
                    </div>
                </div>

                <div class="plan-section">
                    <div class="plan-placeholder">
                        <div><img src="https://tectesting.fwh.is/cdn_images/ap_plan.png" height="348px" width="300px"></img></div>
                    </div>
                    <div class="plan-info">
                        <h4 data-translate="housing.plan.info">Información del Plano</h4>
                        <p data-translate="housing.apartment.plan.desc">El plano muestra la distribución lineal típica de los apartamentos cooperativos, con optimización del espacio y circulación eficiente entre ambientes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for House -->
    <div id="houseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('houseModal')">&times;</span>
                <h2 data-translate="housing.house.modal.title">Casa de Vivienda BINECOOP - Características Detalladas</h2>
            </div>
            <div class="modal-body">
                <div class="details-section">
                    <div class="detail-group">
                        <h3 data-translate="house.gen.title">Características Generales</h3>
                        <ul>
                            <li data-translate="house.gen.item1"><span class="highlight">Estilo arquitectónico:</span> Moderno con detalles en madera y grandes ventanales</li>
                            <li data-translate="house.gen.item2"><span class="highlight">Integración comunitaria:</span> Orientación pensada para aprovechar la luz natural y cercanía al espacio verde común</li>
                            <li data-translate="house.gen.item3"><span class="highlight">Materiales predominantes:</span> Bloques térmicos, madera reciclada y paneles solares</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3 data-translate="house.dist.title">Distribución de Habitaciones</h3>
                        <ul>
                            <li data-translate="house.dist.item1"><span class="highlight">Planta Baja:</span></li>
                            <li data-translate="house.dist.item2">• Sala de estar amplia conectada con comedor y cocina estilo americano</li>
                            <li data-translate="house.dist.item3">• Baño completo</li>
                            <li data-translate="house.dist.item4">• Oficina o habitación flexible</li>
                            <li data-translate="house.dist.item5">• Lavadero independiente con salida al patio trasero</li>
                            <li data-translate="house.dist.item6"><span class="highlight">Planta Alta:</span></li>
                            <li data-translate="house.dist.item7">• Dormitorio principal con baño en suite y vestidor</li>
                            <li data-translate="house.dist.item8">• 2 dormitorios secundarios</li>
                            <li data-translate="house.dist.item9">• Baño compartido</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3 data-translate="house.dims.title">Dimensiones Aproximadas</h3>
                        <ul>
                            <li data-translate="house.dims.item1"><span class="highlight">Superficie total construida:</span> 120 m²</li>
                            <li data-translate="house.dims.item2"><span class="highlight">Terreno asignado:</span> 250 m²</li>
                            <li data-translate="house.dims.item3"><span class="highlight">Altura máxima:</span> 6,5 metros</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3 data-translate="house.sust.title">Características Sustentables</h3>
                        <ul>
                            <li data-translate="house.sust.item1">Paneles solares para agua caliente</li>
                            <li data-translate="house.sust.item2">Sistema de recolección de agua de lluvia</li>
                            <li data-translate="house.sust.item3">Aislación térmica optimizada</li>
                            <li data-translate="house.sust.item4">Pérgola con plantas trepadoras</li>
                        </ul>
                    </div>
                </div>

                <div class="plan-section">
                    <div class="plan-placeholder">
                        <div><img src="https://tectesting.fwh.is/cdn_images/cs_plan.png" height="348px" width="300px"></img></div>
                    </div>
                    <div class="plan-info">
                        <h4 data-translate="housing.plan.info">Información de Planos</h4>
                        <p data-translate="housing.house.plan.desc">Los planos incluyen distribución en dos plantas, con especificaciones técnicas, orientación solar y detalles constructivos sustentables.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <div class="logo">
                    <div class="logo-icon-footer"><img src="https://tectesting.fwh.is/cdn_images/ae.png" style="width: 50px; height:50px;"></img></div>
                    <div class="logo-text">
                        <h1 style="font-size: 24px; color: white;">BINECOOP</h1>
                        <p>Bienestar de Cooperativa Pública Nacional</p>
                    </div>
                </div>
            </div>
            <div class="footer-section">
                <h3 data-translate="footer.location">Estamos en:</h3>
                <p>Calle de la Solidaridad 1156</p>
                <p>Barrio La Blanqueada, Montevideo, Uruguay</p>
                <br>
                <h3 data-translate="footer.contact">Contactanos:</h3>
                <p>Mail: contactovivienda@bcpn.com.uy</p>
                <p>Tel: +598 2 507 3894</p>
                <p>De Lu a Vie 10 a 17hs.</p>
            </div>
            <div class="footer-section">
                <h3 data-translate="footer.follow">Síguenos</h3>
                <div class="social-links">
                    <a href="#" class="social-link" aria-label="Facebook" title="Facebook">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram" title="Instagram">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="X (Twitter)" title="X (Twitter)">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="YouTube" title="YouTube">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p data-translate="footer.copyright">&copy; 2025 BINECOOP. Todos los derechos reservados. Desarrollado por Tecnósfera</p>
            <div class="language-selector" id="languageSelector">
                <button class="language-selector-btn" id="languageBtn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    <span id="currentLanguage">Español</span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="language-dropdown">
                    <div class="language-option active" data-lang="es">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span>Español</span>
                    </div>
                    <div class="language-option" data-lang="en">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span>English</span>
                    </div>
                    <div class="language-option" data-lang="pt">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span>Português</span>
                    </div>
                    <div class="language-option" data-lang="zh">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span>中文</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Modal functionality
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Add click animation to button
            event.target.style.transform = 'scale(0.95)';
            setTimeout(() => {
                event.target.style.transform = '';
            }, 150);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (modal.style.display === 'block') {
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });

        // Scroll reveal animation
        function revealOnScroll() {
            const reveals = document.querySelectorAll('.scroll-reveal');
            
            reveals.forEach(element => {
                const windowHeight = window.innerHeight;
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('revealed');
                }
            });
        }

        window.addEventListener('scroll', revealOnScroll);

        // Smooth scrolling for navigation
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!this.getAttribute('onclick')) {
                    e.preventDefault();
                }
                
                // Add click animation
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Initial reveal check
            revealOnScroll();
            
            // Add hover effects to buttons
            document.querySelectorAll('.origins-btn').forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(-3px)';
                });
            });
        });

        // Add parallax effect to sections
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const origins = document.querySelector('.origins');
            const rate = scrolled * -0.1;
            
            if (origins) {
                origins.style.transform = `translateY(${rate}px)`;
            }
        });

        // Language Translation System
        const translations = {
            es: {
                'nav.quienes': 'Quiénes Somos',
                'nav.viviendas': 'Viviendas',
                'nav.faq': 'Preguntas Frecuentes',
                'nav.login': 'Login',
                'housing.apartment.title': 'Vivienda de Apartamento',
                'housing.apartment.desc': 'Este apartamento es una unidad funcional y luminosa de aproximadamente 55 m², ideal para familias pequeñas o parejas. Cuenta con un amplio living-comedor al frente con gran entrada de luz natural, una cocina independiente, un dormitorio principal, un baño completo y, en algunos casos, un segundo dormitorio adaptable como escritorio o cuarto infantil. Su diseño sencillo, típico de las viviendas cooperativas de los años 80 en Las Piedras, prioriza la comodidad, la ventilación y la practicidad en un entorno barrial tranquilo y bien conectado.',
                'housing.apartment.modal.title': 'Apartamento BINECOOP - Características Detalladas',
                'housing.apartment.plan.desc': 'El plano muestra la distribución lineal típica de los apartamentos cooperativos, con optimización del espacio y circulación eficiente entre ambientes.',
                'housing.house.title': 'Casa de Vivienda',
                'housing.house.desc': 'La vivienda individual en Binecoop está pensada para brindar confort, eficiencia y conexión comunitaria. Con diseño moderno, cuenta con dos plantas que distribuyen de forma práctica sus espacios: amplio living-comedor integrado a la cocina, tres dormitorios, dos baños completos, espacio flexible para oficina o estudio y salida a un patio verde con pérgola. Construida con materiales sustentables y equipada con grandes ventanales, la casa prioriza la luz natural y la armonía con el entorno, adaptándose a las necesidades de cada familia dentro del barrio cooperativo.',
                'housing.house.modal.title': 'Casa de Vivienda BINECOOP - Características Detalladas',
                'housing.house.plan.desc': 'Los planos incluyen distribución en dos plantas, con especificaciones técnicas, orientación solar y detalles constructivos sustentables.',
                'housing.consult': 'Consultar',
                'housing.acquire': 'Adquirir',
                'housing.plan.info': 'Información del Plano',
                'apartment.gen.title': 'Descripción General',
                'apartment.gen.item1': '<span class="highlight">Superficie estimada:</span> 50 a 60 m²',
                'apartment.gen.item2': '<span class="highlight">Planta:</span> Rectangular y lineal',
                'apartment.gen.item3': '<span class="highlight">Estilo:</span> Funcional, con buena iluminación natural frontal',
                'apartment.gen.item4': '<span class="highlight">Ventanal principal:</span> Gran entrada de luz natural',
                'apartment.dist.title': 'Distribución de Ambientes',
                'apartment.dist.item1': '<span class="highlight">Living-Comedor:</span> Al frente (4.5m x 3.5m), gran ventanal',
                'apartment.dist.item2': '<span class="highlight">Cocina:</span> Contigua al living (2.5m x 2m), forma rectangular',
                'apartment.dist.item3': '<span class="highlight">Dormitorio principal:</span> Al fondo (3.5m x 3m), placard incluido',
                'apartment.dist.item4': '<span class="highlight">Dormitorio secundario:</span> Opcional (3m x 2.2m), ideal para oficina',
                'apartment.dist.item5': '<span class="highlight">Baño:</span> Completo (2.2m x 1.6m), ducha y ventilación',
                'apartment.dist.item6': '<span class="highlight">Pasillo:</span> Distribución (1.2m ancho) conecta todos los ambientes',
                'apartment.feat.title': 'Características Constructivas',
                'apartment.feat.item1': 'Construcción típica años 80',
                'apartment.feat.item2': 'Ventilación cruzada optimizada',
                'apartment.feat.item3': 'Orientación para máximo aprovechamiento de luz',
                'apartment.feat.item4': 'Acabados funcionales y duraderos',
                'house.gen.title': 'Características Generales',
                'house.gen.item1': '<span class="highlight">Estilo arquitectónico:</span> Moderno con detalles en madera y grandes ventanales',
                'house.gen.item2': '<span class="highlight">Integración comunitaria:</span> Orientación pensada para aprovechar la luz natural y cercanía al espacio verde común',
                'house.gen.item3': '<span class="highlight">Materiales predominantes:</span> Bloques térmicos, madera reciclada y paneles solares',
                'house.dist.title': 'Distribución de Habitaciones',
                'house.dist.item1': '<span class="highlight">Planta Baja:</span>',
                'house.dist.item2': '• Sala de estar amplia conectada con comedor y cocina estilo americano',
                'house.dist.item3': '• Baño completo',
                'house.dist.item4': '• Oficina o habitación flexible',
                'house.dist.item5': '• Lavadero independiente con salida al patio trasero',
                'house.dist.item6': '<span class="highlight">Planta Alta:</span>',
                'house.dist.item7': '• Dormitorio principal con baño en suite y vestidor',
                'house.dist.item8': '• 2 dormitorios secundarios',
                'house.dist.item9': '• Baño compartido',
                'house.dims.title': 'Dimensiones Aproximadas',
                'house.dims.item1': '<span class="highlight">Superficie total construida:</span> 120 m²',
                'house.dims.item2': '<span class="highlight">Terreno asignado:</span> 250 m²',
                'house.dims.item3': '<span class="highlight">Altura máxima:</span> 6,5 metros',
                'house.sust.title': 'Características Sustentables',
                'house.sust.item1': 'Paneles solares para agua caliente',
                'house.sust.item2': 'Sistema de recolección de agua de lluvia',
                'house.sust.item3': 'Aislación térmica optimizada',
                'house.sust.item4': 'Pérgola con plantas trepadoras',
                'footer.location': 'Estamos en:',
                'footer.contact': 'Contactanos:',
                'footer.follow': 'Síguenos',
                'footer.copyright': '© 2025 BINECOOP. Todos los derechos reservados. Desarrollado por Tecnósfera'
            },
            en: {
                'nav.quienes': 'About Us',
                'nav.viviendas': 'Housing',
                'nav.faq': 'FAQ',
                'nav.login': 'Login',
                'housing.apartment.title': 'Apartment Housing',
                'housing.apartment.desc': 'This apartment is a functional and bright unit of approximately 55 m², ideal for small families or couples. It features a spacious living-dining room at the front with large natural light entry, an independent kitchen, a main bedroom, a complete bathroom and, in some cases, a second adaptable bedroom as a study or children\'s room. Its simple design, typical of cooperative housing from the 80s in Las Piedras, prioritizes comfort, ventilation and practicality in a quiet, well-connected neighborhood environment.',
                'housing.apartment.modal.title': 'BINECOOP Apartment - Detailed Features',
                'housing.apartment.plan.desc': 'The plan shows the typical linear distribution of cooperative apartments, with space optimization and efficient circulation between rooms.',
                'housing.house.title': 'House Housing',
                'housing.house.desc': 'The individual housing in Binecoop is designed to provide comfort, efficiency and community connection. With modern design, it has two floors that distribute its spaces practically: large living-dining room integrated with the kitchen, three bedrooms, two complete bathrooms, flexible space for office or study and exit to a green patio with pergola. Built with sustainable materials and equipped with large windows, the house prioritizes natural light and harmony with the environment, adapting to the needs of each family within the cooperative neighborhood.',
                'housing.house.modal.title': 'BINECOOP House Housing - Detailed Features',
                'housing.house.plan.desc': 'The plans include distribution on two floors, with technical specifications, solar orientation and sustainable construction details.',
                'housing.consult': 'Consult',
                'housing.acquire': 'Acquire',
                'housing.plan.info': 'Plan Information',
                'apartment.gen.title': 'General Description',
                'apartment.gen.item1': '<span class="highlight">Estimated area:</span> 50 to 60 m²',
                'apartment.gen.item2': '<span class="highlight">Layout:</span> Rectangular and linear',
                'apartment.gen.item3': '<span class="highlight">Style:</span> Functional, with good front natural lighting',
                'apartment.gen.item4': '<span class="highlight">Main window:</span> Large natural light entry',
                'apartment.dist.title': 'Room Distribution',
                'apartment.dist.item1': '<span class="highlight">Living-Dining Room:</span> At the front (4.5m x 3.5m), large window',
                'apartment.dist.item2': '<span class="highlight">Kitchen:</span> Adjacent to living room (2.5m x 2m), rectangular shape',
                'apartment.dist.item3': '<span class="highlight">Main bedroom:</span> At the back (3.5m x 3m), wardrobe included',
                'apartment.dist.item4': '<span class="highlight">Secondary bedroom:</span> Optional (3m x 2.2m), ideal for office',
                'apartment.dist.item5': '<span class="highlight">Bathroom:</span> Complete (2.2m x 1.6m), shower and ventilation',
                'apartment.dist.item6': '<span class="highlight">Hallway:</span> Distribution (1.2m wide) connects all rooms',
                'apartment.feat.title': 'Construction Features',
                'apartment.feat.item1': 'Typical 80s construction',
                'apartment.feat.item2': 'Optimized cross ventilation',
                'apartment.feat.item3': 'Orientation for maximum light utilization',
                'apartment.feat.item4': 'Functional and durable finishes',
                'house.gen.title': 'General Features',
                'house.gen.item1': '<span class="highlight">Architectural style:</span> Modern with wood details and large windows',
                'house.gen.item2': '<span class="highlight">Community integration:</span> Orientation designed to take advantage of natural light and proximity to common green space',
                'house.gen.item3': '<span class="highlight">Predominant materials:</span> Thermal blocks, recycled wood and solar panels',
                'house.dist.title': 'Room Distribution',
                'house.dist.item1': '<span class="highlight">Ground Floor:</span>',
                'house.dist.item2': '• Large living room connected with dining room and American-style kitchen',
                'house.dist.item3': '• Complete bathroom',
                'house.dist.item4': '• Office or flexible room',
                'house.dist.item5': '• Independent laundry room with exit to backyard',
                'house.dist.item6': '<span class="highlight">Upper Floor:</span>',
                'house.dist.item7': '• Main bedroom with ensuite bathroom and walk-in closet',
                'house.dist.item8': '• 2 secondary bedrooms',
                'house.dist.item9': '• Shared bathroom',
                'house.dims.title': 'Approximate Dimensions',
                'house.dims.item1': '<span class="highlight">Total built area:</span> 120 m²',
                'house.dims.item2': '<span class="highlight">Assigned land:</span> 250 m²',
                'house.dims.item3': '<span class="highlight">Maximum height:</span> 6.5 meters',
                'house.sust.title': 'Sustainable Features',
                'house.sust.item1': 'Solar panels for hot water',
                'house.sust.item2': 'Rainwater collection system',
                'house.sust.item3': 'Optimized thermal insulation',
                'house.sust.item4': 'Pergola with climbing plants',
                'footer.location': 'We are at:',
                'footer.contact': 'Contact us:',
                'footer.follow': 'Follow us',
                'footer.copyright': '© 2025 BINECOOP. All rights reserved. Developed by Tecnósfera'
            },
            pt: {
                'nav.quienes': 'Quem Somos',
                'nav.viviendas': 'Habitações',
                'nav.faq': 'Perguntas Frequentes',
                'nav.login': 'Login',
                'housing.apartment.title': 'Habitação de Apartamento',
                'housing.apartment.desc': 'Este apartamento é uma unidade funcional e luminosa de aproximadamente 55 m², ideal para famílias pequenas ou casais. Possui uma ampla sala de estar-jantar na frente com grande entrada de luz natural, uma cozinha independente, um quarto principal, um banheiro completo e, em alguns casos, um segundo quarto adaptável como escritório ou quarto infantil. Seu design simples, típico das habitações cooperativas dos anos 80 em Las Piedras, prioriza o conforto, a ventilação e a praticidade em um ambiente de bairro tranquilo e bem conectado.',
                'housing.apartment.modal.title': 'Apartamento BINECOOP - Características Detalhadas',
                'housing.apartment.plan.desc': 'O plano mostra a distribuição linear típica dos apartamentos cooperativos, com otimização do espaço e circulação eficiente entre ambientes.',
                'housing.house.title': 'Casa Habitacional',
                'housing.house.desc': 'A habitação individual na Binecoop é pensada para oferecer conforto, eficiência e conexão comunitária. Com design moderno, possui dois andares que distribuem de forma prática seus espaços: ampla sala de estar-jantar integrada à cozinha, três quartos, dois banheiros completos, espaço flexível para escritório ou estudo e saída para um pátio verde com pérgola. Construída com materiais sustentáveis e equipada com grandes janelas, a casa prioriza a luz natural e a harmonia com o entorno, adaptando-se às necessidades de cada família dentro do bairro cooperativo.',
                'housing.house.modal.title': 'Casa Habitacional BINECOOP - Características Detalhadas',
                'housing.house.plan.desc': 'Os planos incluem distribuição em dois andares, com especificações técnicas, orientação solar e detalhes construtivos sustentáveis.',
                'housing.consult': 'Consultar',
                'housing.acquire': 'Adquirir',
                'housing.plan.info': 'Informação do Plano',
                'apartment.gen.title': 'Descrição Geral',
                'apartment.gen.item1': '<span class="highlight">Área estimada:</span> 50 a 60 m²',
                'apartment.gen.item2': '<span class="highlight">Planta:</span> Retangular e linear',
                'apartment.gen.item3': '<span class="highlight">Estilo:</span> Funcional, com boa iluminação natural frontal',
                'apartment.gen.item4': '<span class="highlight">Janela principal:</span> Grande entrada de luz natural',
                'apartment.dist.title': 'Distribuição de Ambientes',
                'apartment.dist.item1': '<span class="highlight">Sala de Estar-Jantar:</span> Na frente (4.5m x 3.5m), grande janela',
                'apartment.dist.item2': '<span class="highlight">Cozinha:</span> Contígua à sala (2.5m x 2m), forma retangular',
                'apartment.dist.item3': '<span class="highlight">Quarto principal:</span> No fundo (3.5m x 3m), guarda-roupa incluído',
                'apartment.dist.item4': '<span class="highlight">Quarto secundário:</span> Opcional (3m x 2.2m), ideal para escritório',
                'apartment.dist.item5': '<span class="highlight">Banheiro:</span> Completo (2.2m x 1.6m), chuveiro e ventilação',
                'apartment.dist.item6': '<span class="highlight">Corredor:</span> Distribuição (1.2m de largura) conecta todos os ambientes',
                'apartment.feat.title': 'Características Construtivas',
                'apartment.feat.item1': 'Construção típica dos anos 80',
                'apartment.feat.item2': 'Ventilação cruzada otimizada',
                'apartment.feat.item3': 'Orientação para máximo aproveitamento de luz',
                'apartment.feat.item4': 'Acabamentos funcionais e duráveis',
                'house.gen.title': 'Características Gerais',
                'house.gen.item1': '<span class="highlight">Estilo arquitetônico:</span> Moderno com detalhes em madeira e grandes janelas',
                'house.gen.item2': '<span class="highlight">Integração comunitária:</span> Orientação pensada para aproveitar a luz natural e proximidade ao espaço verde comum',
                'house.gen.item3': '<span class="highlight">Materiais predominantes:</span> Blocos térmicos, madeira reciclada e painéis solares',
                'house.dist.title': 'Distribuição de Quartos',
                'house.dist.item1': '<span class="highlight">Térreo:</span>',
                'house.dist.item2': '• Sala de estar ampla conectada com sala de jantar e cozinha estilo americano',
                'house.dist.item3': '• Banheiro completo',
                'house.dist.item4': '• Escritório ou quarto flexível',
                'house.dist.item5': '• Lavanderia independente com saída para o quintal',
                'house.dist.item6': '<span class="highlight">Andar Superior:</span>',
                'house.dist.item7': '• Quarto principal com banheiro em suite e vestiário',
                'house.dist.item8': '• 2 quartos secundários',
                'house.dist.item9': '• Banheiro compartilhado',
                'house.dims.title': 'Dimensões Aproximadas',
                'house.dims.item1': '<span class="highlight">Área total construída:</span> 120 m²',
                'house.dims.item2': '<span class="highlight">Terreno designado:</span> 250 m²',
                'house.dims.item3': '<span class="highlight">Altura máxima:</span> 6,5 metros',
                'house.sust.title': 'Características Sustentáveis',
                'house.sust.item1': 'Painéis solares para água quente',
                'house.sust.item2': 'Sistema de coleta de água da chuva',
                'house.sust.item3': 'Isolamento térmico otimizado',
                'house.sust.item4': 'Pérgola com plantas trepadeiras',
                'footer.location': 'Estamos em:',
                'footer.contact': 'Contate-nos:',
                'footer.follow': 'Siga-nos',
                'footer.copyright': '© 2025 BINECOOP. Todos os direitos reservados. Desenvolvido por Tecnósfera'
            },
            zh: {
                'nav.quienes': '关于我们',
                'nav.viviendas': '住房',
                'nav.faq': '常见问题',
                'nav.login': '登录',
                'housing.apartment.title': '公寓住房',
                'housing.apartment.desc': '这套公寓是一个功能齐全、明亮的大约55平方米的单位，非常适合小家庭或夫妇。它拥有一个宽敞的前厅用餐区，自然采光充足，一个独立厨房，一个主卧室，一个完整的浴室，在某些情况下，还有一个可适应的第二卧室作为书房或儿童房。其简洁的设计，典型的80年代Las Piedras合作住房，在安静且交通便利的社区环境中优先考虑舒适性、通风性和实用性。',
                'housing.apartment.modal.title': 'BINECOOP公寓 - 详细特性',
                'housing.apartment.plan.desc': '平面图显示了合作公寓的典型线性分布，空间优化，房间之间流通高效。',
                'housing.house.title': '住宅房屋',
                'housing.house.desc': 'Binecoop的独立住房旨在提供舒适、效率和社区连接。采用现代设计，拥有两层，实用地分配其空间：与厨房集成的宽敞起居用餐区，三间卧室，两个完整浴室，灵活的书房或学习空间，以及通往带凉棚的绿色庭院的出口。使用可持续材料建造并配备大窗户，房屋优先考虑自然光和与环境的和谐，适应合作社社区内每个家庭的需求。',
                'housing.house.modal.title': 'BINECOOP住宅房屋 - 详细特性',
                'housing.house.plan.desc': '平面图包括两层楼的分布，技术规格，太阳能朝向和可持续建筑细节。',
                'housing.consult': '咨询',
                'housing.acquire': '购买',
                'housing.plan.info': '平面图信息',
                'apartment.gen.title': '一般描述',
                'apartment.gen.item1': '<span class="highlight">估计面积：</span> 50至60平方米',
                'apartment.gen.item2': '<span class="highlight">布局：</span> 矩形和线性',
                'apartment.gen.item3': '<span class="highlight">风格：</span> 功能性，前方自然采光良好',
                'apartment.gen.item4': '<span class="highlight">主窗：</span> 大型自然光入口',
                'apartment.dist.title': '房间分布',
                'apartment.dist.item1': '<span class="highlight">客厅-餐厅：</span> 前方（4.5米 x 3.5米），大窗户',
                'apartment.dist.item2': '<span class="highlight">厨房：</span> 与客厅相邻（2.5米 x 2米），矩形形状',
                'apartment.dist.item3': '<span class="highlight">主卧室：</span> 后部（3.5米 x 3米），包含衣柜',
                'apartment.dist.item4': '<span class="highlight">次卧室：</span> 可选（3米 x 2.2米），适合用作书房',
                'apartment.dist.item5': '<span class="highlight">浴室：</span> 完整（2.2米 x 1.6米），淋浴和通风',
                'apartment.dist.item6': '<span class="highlight">走廊：</span> 分布（1.2米宽）连接所有房间',
                'apartment.feat.title': '建筑特性',
                'apartment.feat.item1': '典型的80年代建筑',
                'apartment.feat.item2': '优化的交叉通风',
                'apartment.feat.item3': '朝向以充分利用光线',
                'apartment.feat.item4': '功能性和耐用的装修',
                'house.gen.title': '一般特性',
                'house.gen.item1': '<span class="highlight">建筑风格：</span> 现代风格，带木制细节和大窗户',
                'house.gen.item2': '<span class="highlight">社区整合：</span> 设计朝向以利用自然光并靠近公共绿地',
                'house.gen.item3': '<span class="highlight">主要材料：</span> 保温块、回收木材和太阳能电池板',
                'house.dist.title': '房间分布',
                'house.dist.item1': '<span class="highlight">一楼：</span>',
                'house.dist.item2': '• 宽敞的客厅，与餐厅和美式厨房相连',
                'house.dist.item3': '• 完整浴室',
                'house.dist.item4': '• 书房或灵活房间',
                'house.dist.item5': '• 独立洗衣房，通往后院',
                'house.dist.item6': '<span class="highlight">二楼：</span>',
                'house.dist.item7': '• 主卧室，带套间浴室和衣帽间',
                'house.dist.item8': '• 2间次卧室',
                'house.dist.item9': '• 共用浴室',
                'house.dims.title': '大致尺寸',
                'house.dims.item1': '<span class="highlight">总建筑面积：</span> 120平方米',
                'house.dims.item2': '<span class="highlight">分配土地：</span> 250平方米',
                'house.dims.item3': '<span class="highlight">最大高度：</span> 6.5米',
                'house.sust.title': '可持续特性',
                'house.sust.item1': '用于热水的太阳能电池板',
                'house.sust.item2': '雨水收集系统',
                'house.sust.item3': '优化的保温隔热',
                'house.sust.item4': '带攀爬植物的凉棚',
                'footer.location': '我们在：',
                'footer.contact': '联系我们：',
                'footer.follow': '关注我们',
                'footer.copyright': '© 2025 BINECOOP。保留所有权利。由Tecnósfera开发'
            }
        };

        const languageNames = {
            es: 'Español',
            en: 'English',
            pt: 'Português',
            zh: '中文'
        };

        // Función para obtener cookie
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        // Función para establecer cookie
        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = `expires=${date.toUTCString()}`;
            document.cookie = `${name}=${value};${expires};path=/`;
        }

        // Get current language from URL parameter, cookie, or default to Spanish
        function getCurrentLanguage() {
            const urlParams = new URLSearchParams(window.location.search);
            const langFromUrl = urlParams.get('lang');
            
            if (langFromUrl && ['es', 'en', 'pt', 'zh'].includes(langFromUrl)) {
                // Guardar en cookie si viene de URL
                setCookie('site_lang', langFromUrl, 30);
                return langFromUrl;
            }
            
            // Intentar leer de cookie
            const langFromCookie = getCookie('site_lang');
            if (langFromCookie && ['es', 'en', 'pt', 'zh'].includes(langFromCookie)) {
                return langFromCookie;
            }
            
            return 'es';
        }

        // Change language and update URL
        function changeLanguage(lang) {
            // Guardar en cookie
            setCookie('site_lang', lang, 30);
            
            const url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }

        // Función para actualizar enlaces con el idioma actual (rutas relativas recursivas)
        function updateLinksWithLanguage() {
            const currentLang = getCurrentLanguage();
            const currentPath = window.location.pathname;
            const currentDir = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
            
            const links = document.querySelectorAll('a[href], button[onclick*="location.href"]');
            
            links.forEach(link => {
                if (link.tagName === 'A') {
                    const href = link.getAttribute('href');
                    if (href && !href.startsWith('http') && !href.startsWith('#') && !href.startsWith('javascript:') && !href.startsWith('mailto:')) {
                        // Construir ruta relativa al directorio actual
                        let relativePath = href;
                        
                        // Remover parámetros existentes para reconstruir la URL
                        const urlParts = relativePath.split('?');
                        relativePath = urlParts[0];
                        const existingParams = urlParts[1] || '';
                        
                        // Si empieza con ./, mantenerlo relativo al directorio actual
                        if (relativePath.startsWith('./')) {
                            relativePath = relativePath.substring(2);
                        }
                        
                        // Si no empieza con /, es relativo al directorio actual
                        if (!relativePath.startsWith('/')) {
                            relativePath = currentDir + relativePath;
                        }
                        
                        // Agregar parámetro lang (preservar parámetros existentes si los hay)
                        let newHref = relativePath;
                        if (existingParams) {
                            // Si ya hay parámetros, agregar lang
                            newHref += '?' + existingParams + '&lang=' + currentLang;
                        } else {
                            newHref += '?lang=' + currentLang;
                        }
                        
                        link.setAttribute('href', newHref);
                    }
                } else if (link.tagName === 'BUTTON' && link.getAttribute('onclick')) {
                    const onclick = link.getAttribute('onclick');
                    if (onclick.includes('location.href')) {
                        const match = onclick.match(/location\.href=['"]([^'"]+)['"]/);
                        if (match) {
                            const href = match[1];
                            if (href && !href.startsWith('http') && !href.startsWith('#') && !href.startsWith('javascript:')) {
                                // Construir ruta relativa al directorio actual
                                let relativePath = href;
                                
                                // Remover parámetros existentes para reconstruir la URL
                                const urlParts = relativePath.split('?');
                                relativePath = urlParts[0];
                                const existingParams = urlParts[1] || '';
                                
                                // Si empieza con ./, mantenerlo relativo al directorio actual
                                if (relativePath.startsWith('./')) {
                                    relativePath = relativePath.substring(2);
                                }
                                
                                // Si no empieza con /, es relativo al directorio actual
                                if (!relativePath.startsWith('/')) {
                                    relativePath = currentDir + relativePath;
                                }
                                
                                // Agregar parámetro lang (preservar parámetros existentes si los hay)
                                let newHref = relativePath;
                                if (existingParams) {
                                    // Si ya hay parámetros, agregar lang
                                    newHref += '?' + existingParams + '&lang=' + currentLang;
                                } else {
                                    newHref += '?lang=' + currentLang;
                                }
                                
                                link.setAttribute('onclick', onclick.replace(match[0], `location.href='${newHref}'`));
                            }
                        }
                    }
                }
            });
        }

        // Apply translations to elements
        function applyTranslations(lang) {
            const elements = document.querySelectorAll('[data-translate]');
            elements.forEach(element => {
                const key = element.getAttribute('data-translate');
                if (translations[lang] && translations[lang][key]) {
                    // Use innerHTML for paragraphs and list items (they may contain HTML), textContent for other elements
                    if (element.tagName === 'P' || element.tagName === 'LI') {
                        element.innerHTML = translations[lang][key].replace(/\n/g, '<br>');
                    } else {
                        element.textContent = translations[lang][key];
                    }
                }
            });

            // Update current language display
            const currentLanguageSpan = document.getElementById('currentLanguage');
            if (currentLanguageSpan) {
                currentLanguageSpan.textContent = languageNames[lang];
            }

            // Update active language option
            document.querySelectorAll('.language-option').forEach(option => {
                option.classList.remove('active');
                if (option.getAttribute('data-lang') === lang) {
                    option.classList.add('active');
                }
            });

            // Update HTML lang attribute
            document.documentElement.lang = lang;
        }

        // Language selector functionality
        document.addEventListener('DOMContentLoaded', function() {
            const languageSelector = document.getElementById('languageSelector');
            const languageBtn = document.getElementById('languageBtn');
            const languageOptions = document.querySelectorAll('.language-option');

            // Toggle dropdown
            if (languageBtn) {
                languageBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    languageSelector.classList.toggle('active');
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!languageSelector.contains(e.target)) {
                    languageSelector.classList.remove('active');
                }
            });

            // Handle language selection
            languageOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const lang = this.getAttribute('data-lang');
                    changeLanguage(lang);
                });
            });

            // Apply translations on page load
            const currentLang = getCurrentLanguage();
            applyTranslations(currentLang);
            
            // Actualizar enlaces con el idioma actual
            updateLinksWithLanguage();
        });
    </script>
</body>
</html>
