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

        /* Hero Section */
        .hero {
            height: 600px;
            background: linear-gradient(135deg, rgba(74, 124, 89, 0.9) 0%, rgba(45, 90, 61, 0.9) 100%),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect fill="%23e8dcc0" width="1200" height="600"/><path fill="%23d4c4a0" d="M0,400 Q300,350 600,400 T1200,400 L1200,600 L0,600 Z"/></svg>');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="%23ffffff" opacity="0.1"/><circle cx="20" cy="20" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="80" cy="30" r="1.5" fill="%23ffffff" opacity="0.1"/></svg>');
            animation: float 20s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .slider-container {
            position: relative;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 60px;
        }

        .slider-wrapper {
            display: flex;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            gap: 60px;
        }

        .slide {
            min-width: 350px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            opacity: 0.7;
            transform: scale(0.9);
            transition: all 0.5s ease;
        }

        .slide.active {
            opacity: 1;
            transform: scale(1);
        }

        .slide h3 {
            color: #2d5a3d;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .slide p {
            color: #4a7c59;
            font-size: 16px;
            line-height: 1.6;
        }

        .slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .slider-nav:hover {
            background: white;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .slider-nav.prev {
            left: 10px;
        }

        .slider-nav.next {
            right: 10px;
        }

        .slider-nav::before {
            content: '';
            width: 12px;
            height: 12px;
            border: 2px solid #2d5a3d;
            border-right: none;
            border-bottom: none;
            transform: rotate(-45deg);
        }

        .slider-nav.next::before {
            transform: rotate(135deg);
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

        /* Administration Section */
        .administration {
            padding: 100px 0;
            background: linear-gradient(135deg, #2d5a3d 0%, #4a7c59 100%);
            color: white;
        }

        .administration-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }

        .administration h2 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 60px;
            position: relative;
        }

        .administration h2::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(135deg, #f4f1e8 0%, #e8dcc0 100%);
            border-radius: 2px;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }

        .admin-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .admin-card h3 {
            color: #f4f1e8;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .admin-card p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }

        /* Trust Section */
        .trust {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8f6f0 0%, #e8dcc0 100%);
            text-align: center;
        }

        .trust-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .trust h2 {
            color: #2d5a3d;
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 30px;
        }

        .trust p {
            color: #555;
            font-size: 18px;
            line-height: 1.7;
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

            .slider-container {
                padding: 0 20px;
            }

            .slide {
                min-width: 280px;
            }

            .footer-container {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.8s ease-out;
        }

        /* Scroll Animations */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s ease;
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
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
                <button class="nav-btn" onclick="location.href='./viviendas.php'" data-translate="nav.viviendas">Viviendas</button>
                <button class="nav-btn" onclick="location.href='./faq.php'" data-translate="nav.faq">Preguntas Frecuentes</button>
            </nav>
        </div>
    </header>

   <!-- Origins Section -->
    <section class="origins scroll-reveal">
        <div class="origins-container">
            <div class="origins-content">
                <h2 data-translate="who.title">¿Quiénes somos?</h2>
                <p data-translate="who.text">Somos BINECOOP (Bienestar de Cooperativa Pública Nacional), una cooperativa de vivienda fundada en 1938 por trabajadores organizados —sindicalistas, obreros portuarios y empleados del Estado— con el objetivo de garantizar el acceso a una vivienda digna como un derecho colectivo.

Desde nuestros inicios, creemos en la autogestión, la solidaridad y la cooperación como herramientas para transformar la realidad de los sectores más postergados. Apostamos a un modelo de desarrollo autosustentable, democrático y sin fines de lucro, que ha permitido que más de 500 familias accedan a su primer hogar sin recurrir a créditos bancarios tradicionales.

Contamos con una larga trayectoria de trabajo conjunto con el Centro de Investigaciones Económicas (CIE) y otras instituciones académicas y públicas, lo que nos ha permitido innovar en modelos de financiamiento y planificación urbana, manteniéndonos a la vanguardia de las políticas de vivienda cooperativa en Uruguay.

Hoy, más de ocho décadas después de nuestro nacimiento en Villa del Cerro, seguimos comprometidos con la construcción de comunidades inclusivas, sostenibles y participativas en distintos barrios de Montevideo.

Somos BINECOOP. Cooperación con propósito, vivienda con dignidad.

</p>
                <div class="origins-buttons">
                    <button class="origins-btn secondary" data-translate="who.contact">Contacto</button>
                </div>
            </div>
            <div class="origins-image">
                <div class="building-placeholder">
                    <img src="https://tectesting.fwh.is/cdn_images/who.png" height="400px" width="600px"></img>
                </div>
            </div>
        </div>
    </section>

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
        // Slider functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const totalSlides = slides.length;

        function updateSlider() {
            const wrapper = document.getElementById('sliderWrapper');
            const translateX = -currentSlide * (350 + 60); // slide width + gap
            wrapper.style.transform = `translateX(${translateX}px)`;
            
            // Update active slide
            slides.forEach((slide, index) => {
                slide.classList.remove('active');
                if (index === currentSlide || index === currentSlide + 1) {
                    slide.classList.add('active');
                }
            });
        }

        function changeSlide(direction) {
            currentSlide += direction;
            
            if (currentSlide >= totalSlides - 1) {
                currentSlide = 0;
            } else if (currentSlide < 0) {
                currentSlide = totalSlides - 2;
            }
            
            updateSlider();
        }

        // Auto-play slider
        setInterval(() => {
            changeSlide(1);
        }, 5000);

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
                e.preventDefault();
                
                // Add click animation
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
                
                // Simulate navigation (in real implementation, this would navigate)
                console.log('Navigating to:', this.textContent);
            });
        });

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Fade in header
            document.querySelector('.header').classList.add('fade-in');
            
            // Initial reveal check
            revealOnScroll();
            
            // Initialize slider
            updateSlider();
        });

        // Add parallax effect to hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            const rate = scrolled * -0.5;
            
            hero.style.transform = `translateY(${rate}px)`;
        });

        // Add hover effects to admin cards
        document.querySelectorAll('.admin-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Language Translation System
        const translations = {
            es: {
                'nav.quienes': 'Quiénes Somos',
                'nav.viviendas': 'Viviendas',
                'nav.faq': 'Preguntas Frecuentes',
                'nav.login': 'Login',
                'who.title': '¿Quiénes somos?',
                'who.text': 'Somos BINECOOP (Bienestar de Cooperativa Pública Nacional), una cooperativa de vivienda fundada en 1938 por trabajadores organizados —sindicalistas, obreros portuarios y empleados del Estado— con el objetivo de garantizar el acceso a una vivienda digna como un derecho colectivo.\n\nDesde nuestros inicios, creemos en la autogestión, la solidaridad y la cooperación como herramientas para transformar la realidad de los sectores más postergados. Apostamos a un modelo de desarrollo autosustentable, democrático y sin fines de lucro, que ha permitido que más de 500 familias accedan a su primer hogar sin recurrir a créditos bancarios tradicionales.\n\nContamos con una larga trayectoria de trabajo conjunto con el Centro de Investigaciones Económicas (CIE) y otras instituciones académicas y públicas, lo que nos ha permitido innovar en modelos de financiamiento y planificación urbana, manteniéndonos a la vanguardia de las políticas de vivienda cooperativa en Uruguay.\n\nHoy, más de ocho décadas después de nuestro nacimiento en Villa del Cerro, seguimos comprometidos con la construcción de comunidades inclusivas, sostenibles y participativas en distintos barrios de Montevideo.\n\nSomos BINECOOP. Cooperación con propósito, vivienda con dignidad.',
                'who.contact': 'Contacto',
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
                'who.title': 'Who are we?',
                'who.text': 'We are BINECOOP (National Public Cooperative Welfare), a housing cooperative founded in 1938 by organized workers — trade unionists, port workers and state employees — with the goal of guaranteeing access to decent housing as a collective right.\n\nSince our beginnings, we believe in self-management, solidarity and cooperation as tools to transform the reality of the most marginalized sectors. We bet on a self-sustaining, democratic and non-profit development model, which has allowed more than 500 families to access their first home without resorting to traditional bank loans.\n\nWe have a long history of joint work with the Center for Economic Research (CIE) and other academic and public institutions, which has allowed us to innovate in financing and urban planning models, keeping us at the forefront of cooperative housing policies in Uruguay.\n\nToday, more than eight decades after our birth in Villa del Cerro, we remain committed to building inclusive, sustainable and participatory communities in different neighborhoods of Montevideo.\n\nWe are BINECOOP. Cooperation with purpose, housing with dignity.',
                'who.contact': 'Contact',
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
                'who.title': 'Quem somos?',
                'who.text': 'Somos BINECOOP (Bem-estar de Cooperativa Pública Nacional), uma cooperativa habitacional fundada em 1938 por trabalhadores organizados — sindicalistas, trabalhadores portuários e funcionários do Estado — com o objetivo de garantir o acesso a uma habitação digna como um direito coletivo.\n\nDesde nossos inícios, acreditamos na autogestão, solidariedade e cooperação como ferramentas para transformar a realidade dos setores mais postergados. Apostamos em um modelo de desenvolvimento autossustentável, democrático e sem fins lucrativos, que permitiu que mais de 500 famílias acessem seu primeiro lar sem recorrer a empréstimos bancários tradicionais.\n\nTemos uma longa trajetória de trabalho conjunto com o Centro de Pesquisas Econômicas (CIE) e outras instituições acadêmicas e públicas, o que nos permitiu inovar em modelos de financiamento e planejamento urbano, mantendo-nos na vanguarda das políticas de habitação cooperativa no Uruguai.\n\nHoje, mais de oito décadas após nosso nascimento em Villa del Cerro, continuamos comprometidos com a construção de comunidades inclusivas, sustentáveis e participativas em diferentes bairros de Montevidéu.\n\nSomos BINECOOP. Cooperação com propósito, habitação com dignidade.',
                'who.contact': 'Contato',
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
                'who.title': '我们是谁？',
                'who.text': '我们是BINECOOP（国家公共合作福利），一个由有组织的工人——工会会员、港口工人和国家雇员——于1938年成立的住房合作社，目标是保障获得体面住房作为集体权利。\n\n从一开始，我们相信自主管理、团结和合作是改变最边缘化部门现实的工具。我们押注于一个自我维持、民主和非营利的发展模式，这使得500多个家庭无需依赖传统银行贷款即可获得第一套住房。\n\n我们与经济研究中心（CIE）和其他学术和公共机构有着长期的合作历史，这使我们能够在融资和城市规划模式方面进行创新，保持在乌拉圭合作住房政策的前沿。\n\n今天，在我们在Villa del Cerro诞生八十多年后，我们仍然致力于在蒙得维的亚的不同社区建设包容、可持续和参与性的社区。\n\n我们是BINECOOP。有目的的合作，有尊严的住房。',
                'who.contact': '联系方式',
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
                    // Use innerHTML for paragraphs to support line breaks, textContent for other elements
                    if (element.tagName === 'P') {
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
