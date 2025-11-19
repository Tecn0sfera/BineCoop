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
                <button class="nav-btn" onclick="location.href='./quienes_somos.php'" data-translate="nav.quienes">Quiénes Somos</button>
                <button class="nav-btn" onclick="location.href='./viviendas.php'" data-translate="nav.viviendas">Viviendas</button>
            </nav>
        </div>
    </header>

   <!-- Origins Section -->
    <section class="origins scroll-reveal">
        <div class="origins-container">
            <div class="origins-content">
                <h2 data-translate="faq.q1">¿Qué es BINECOOP y cuál es su propósito?</h2>
                <p data-translate="faq.a1">BINECOOP (Bienestar de Cooperativa Pública Nacional) es una cooperativa de vivienda fundada en 1938 por trabajadores organizados que creyeron en la vivienda como un derecho colectivo. Desde entonces, promovemos un modelo solidario, democrático y sustentable para que las familias accedan a su primer hogar sin depender de créditos bancarios tradicionales.
</p>

                <h2 data-translate="faq.q2">¿Quién puede ser parte de la cooperativa y cómo se participa?</h2>
                <p data-translate="faq.a2">Cualquier persona que comparta nuestros valores de solidaridad, trabajo colectivo y autogestión puede postularse para ser parte de BINECOOP. Los socios participan activamente en la toma de decisiones, en asambleas y en distintas tareas comunitarias, recibiendo apoyo y formación para hacerlo.
</p>

                <h2 data-translate="faq.q3">¿Cómo son las viviendas y los proyectos que desarrolla BINECOOP?</h2>
                <p data-translate="faq.a3">Las viviendas son construidas bajo un modelo cooperativo, en barrios planificados con criterio de comunidad. Cada proyecto se adapta a las necesidades de los socios, priorizando funcionalidad, calidad y sostenibilidad. Actualmente, contamos con varias unidades en distintos barrios de Montevideo.
</p>

                <h2 data-translate="faq.q4">¿Cómo se financian las viviendas y qué aportes hacen los socios?</h2>
                <p data-translate="faq.a4">Los proyectos se financian colectivamente, sin recurrir a créditos bancarios convencionales. Cada socio realiza aportes mensuales accesibles, y la cooperativa gestiona los recursos de forma transparente y eficiente, con el apoyo técnico del Centro de Investigaciones Económicas (CIE) y otros aliados.
</p>

                <h2 data-translate="faq.q5">¿Cómo funciona la organización interna de la cooperativa?</h2>
                <p data-translate="faq.a5">BINECOOP se basa en un sistema de autogestión: las decisiones se toman de forma democrática en asambleas, y se fomenta la participación activa de todos los socios. Contamos con equipos de trabajo organizados y asesoramiento técnico para garantizar el buen funcionamiento económico y social del modelo.
</p>

                <h2 data-translate="faq.q6">¿Con qué instituciones trabaja BINECOOP y cómo se vincula con el Estado?</h2>
                <p data-translate="faq.a6">Mantenemos una relación activa con centros académicos como el CIE, instituciones públicas y organismos de vivienda. Gracias a estos vínculos, hemos podido participar en el diseño de políticas públicas y mantenernos a la vanguardia en innovación cooperativa.
</p>


                <div class="origins-buttons">
                    <button class="origins-btn secondary" data-translate="faq.contact">Contacto</button>
                </div>
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
                'faq.q1': '¿Qué es BINECOOP y cuál es su propósito?',
                'faq.a1': 'BINECOOP (Bienestar de Cooperativa Pública Nacional) es una cooperativa de vivienda fundada en 1938 por trabajadores organizados que creyeron en la vivienda como un derecho colectivo. Desde entonces, promovemos un modelo solidario, democrático y sustentable para que las familias accedan a su primer hogar sin depender de créditos bancarios tradicionales.',
                'faq.q2': '¿Quién puede ser parte de la cooperativa y cómo se participa?',
                'faq.a2': 'Cualquier persona que comparta nuestros valores de solidaridad, trabajo colectivo y autogestión puede postularse para ser parte de BINECOOP. Los socios participan activamente en la toma de decisiones, en asambleas y en distintas tareas comunitarias, recibiendo apoyo y formación para hacerlo.',
                'faq.q3': '¿Cómo son las viviendas y los proyectos que desarrolla BINECOOP?',
                'faq.a3': 'Las viviendas son construidas bajo un modelo cooperativo, en barrios planificados con criterio de comunidad. Cada proyecto se adapta a las necesidades de los socios, priorizando funcionalidad, calidad y sostenibilidad. Actualmente, contamos con varias unidades en distintos barrios de Montevideo.',
                'faq.q4': '¿Cómo se financian las viviendas y qué aportes hacen los socios?',
                'faq.a4': 'Los proyectos se financian colectivamente, sin recurrir a créditos bancarios convencionales. Cada socio realiza aportes mensuales accesibles, y la cooperativa gestiona los recursos de forma transparente y eficiente, con el apoyo técnico del Centro de Investigaciones Económicas (CIE) y otros aliados.',
                'faq.q5': '¿Cómo funciona la organización interna de la cooperativa?',
                'faq.a5': 'BINECOOP se basa en un sistema de autogestión: las decisiones se toman de forma democrática en asambleas, y se fomenta la participación activa de todos los socios. Contamos con equipos de trabajo organizados y asesoramiento técnico para garantizar el buen funcionamiento económico y social del modelo.',
                'faq.q6': '¿Con qué instituciones trabaja BINECOOP y cómo se vincula con el Estado?',
                'faq.a6': 'Mantenemos una relación activa con centros académicos como el CIE, instituciones públicas y organismos de vivienda. Gracias a estos vínculos, hemos podido participar en el diseño de políticas públicas y mantenernos a la vanguardia en innovación cooperativa.',
                'faq.contact': 'Contacto',
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
                'faq.q1': 'What is BINECOOP and what is its purpose?',
                'faq.a1': 'BINECOOP (National Public Cooperative Welfare) is a housing cooperative founded in 1938 by organized workers who believed in housing as a collective right. Since then, we have promoted a solidary, democratic and sustainable model so that families can access their first home without depending on traditional bank loans.',
                'faq.q2': 'Who can be part of the cooperative and how do you participate?',
                'faq.a2': 'Anyone who shares our values of solidarity, collective work and self-management can apply to be part of BINECOOP. Members actively participate in decision-making, in assemblies and in various community tasks, receiving support and training to do so.',
                'faq.q3': 'What are the homes and projects that BINECOOP develops?',
                'faq.a3': 'The homes are built under a cooperative model, in neighborhoods planned with community criteria. Each project adapts to the needs of members, prioritizing functionality, quality and sustainability. Currently, we have several units in different neighborhoods of Montevideo.',
                'faq.q4': 'How are homes financed and what contributions do members make?',
                'faq.a4': 'Projects are financed collectively, without resorting to conventional bank loans. Each member makes affordable monthly contributions, and the cooperative manages resources transparently and efficiently, with technical support from the Center for Economic Research (CIE) and other allies.',
                'faq.q5': 'How does the internal organization of the cooperative work?',
                'faq.a5': 'BINECOOP is based on a self-management system: decisions are made democratically in assemblies, and active participation of all members is encouraged. We have organized work teams and technical advice to ensure the good economic and social functioning of the model.',
                'faq.q6': 'What institutions does BINECOOP work with and how does it relate to the State?',
                'faq.a6': 'We maintain an active relationship with academic centers such as CIE, public institutions and housing organizations. Thanks to these links, we have been able to participate in the design of public policies and remain at the forefront of cooperative innovation.',
                'faq.contact': 'Contact',
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
                'faq.q1': 'O que é BINECOOP e qual é o seu propósito?',
                'faq.a1': 'BINECOOP (Bem-estar de Cooperativa Pública Nacional) é uma cooperativa habitacional fundada em 1938 por trabalhadores organizados que acreditavam na habitação como um direito coletivo. Desde então, promovemos um modelo solidário, democrático e sustentável para que as famílias acessem sua primeira casa sem depender de empréstimos bancários tradicionais.',
                'faq.q2': 'Quem pode fazer parte da cooperativa e como se participa?',
                'faq.a2': 'Qualquer pessoa que compartilhe nossos valores de solidariedade, trabalho coletivo e autogestão pode se candidatar para fazer parte da BINECOOP. Os membros participam ativamente na tomada de decisões, em assembleias e em várias tarefas comunitárias, recebendo apoio e formação para fazê-lo.',
                'faq.q3': 'Como são as habitações e os projetos que a BINECOOP desenvolve?',
                'faq.a3': 'As habitações são construídas sob um modelo cooperativo, em bairros planejados com critérios de comunidade. Cada projeto se adapta às necessidades dos membros, priorizando funcionalidade, qualidade e sustentabilidade. Atualmente, temos várias unidades em diferentes bairros de Montevidéu.',
                'faq.q4': 'Como as habitações são financiadas e que contribuições os membros fazem?',
                'faq.a4': 'Os projetos são financiados coletivamente, sem recorrer a empréstimos bancários convencionais. Cada membro faz contribuições mensais acessíveis, e a cooperativa gerencia os recursos de forma transparente e eficiente, com apoio técnico do Centro de Pesquisas Econômicas (CIE) e outros aliados.',
                'faq.q5': 'Como funciona a organização interna da cooperativa?',
                'faq.a5': 'A BINECOOP é baseada em um sistema de autogestão: as decisões são tomadas democraticamente em assembleias, e a participação ativa de todos os membros é incentivada. Temos equipes de trabalho organizadas e assessoria técnica para garantir o bom funcionamento econômico e social do modelo.',
                'faq.q6': 'Com quais instituições a BINECOOP trabalha e como se relaciona com o Estado?',
                'faq.a6': 'Mantemos um relacionamento ativo com centros acadêmicos como o CIE, instituições públicas e organizações de habitação. Graças a esses vínculos, pudemos participar no design de políticas públicas e permanecer na vanguarda da inovação cooperativa.',
                'faq.contact': 'Contato',
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
                'faq.q1': '什么是BINECOOP及其目的？',
                'faq.a1': 'BINECOOP（国家公共合作福利）是由有组织的工人在1938年成立的住房合作社，他们相信住房是一项集体权利。从那时起，我们推广了一个团结、民主和可持续的模式，使家庭无需依赖传统银行贷款即可获得第一套住房。',
                'faq.q2': '谁可以成为合作社成员以及如何参与？',
                'faq.a2': '任何认同我们的团结、集体工作和自主管理价值观的人都可以申请加入BINECOOP。成员积极参与决策、大会和各种社区任务，并获得支持和培训。',
                'faq.q3': 'BINECOOP开发的住房和项目是什么样的？',
                'faq.a3': '住房是在合作模式下建造的，位于按社区标准规划的社区中。每个项目都适应成员的需求，优先考虑功能性、质量和可持续性。目前，我们在蒙得维的亚的不同社区拥有多个单位。',
                'faq.q4': '住房如何融资以及成员做出什么贡献？',
                'faq.a4': '项目是集体融资的，不依赖传统的银行贷款。每个成员每月提供可负担的捐款，合作社以透明和高效的方式管理资源，并得到经济研究中心（CIE）和其他合作伙伴的技术支持。',
                'faq.q5': '合作社的内部组织如何运作？',
                'faq.a5': 'BINECOOP基于自主管理系统：决策在大会上民主做出，鼓励所有成员积极参与。我们拥有有组织的工作团队和技术咨询，以确保模式的良好经济和社会运作。',
                'faq.q6': 'BINECOOP与哪些机构合作以及如何与国家联系？',
                'faq.a6': '我们与学术中心（如CIE）、公共机构和住房组织保持积极关系。由于这些联系，我们能够参与公共政策的设计，并保持在合作创新前沿。',
                'faq.contact': '联系方式',
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
