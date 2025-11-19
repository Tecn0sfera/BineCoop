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

        /* Hero Section - Mejorado con TailwindCSS */
        .hero {
            height: 700px;
            background: linear-gradient(135deg, rgba(74, 124, 89, 0.95) 0%, rgba(45, 90, 61, 0.95) 100%),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 700"><rect fill="%23e8dcc0" width="1200" height="700"/><path fill="%23d4c4a0" d="M0,500 Q300,450 600,500 T1200,500 L1200,700 L0,700 Z"/></svg>');
            background-size: cover;
            background-position: center;
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        .slide-enter {
            animation: slideIn 0.6s ease-out;
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

            .hero {
                height: 600px;
            }
            
            .slide-card {
                width: 100% !important;
            }
            
            .hero .relative.overflow-hidden {
                height: 450px !important;
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
                <button class="nav-btn" onclick="location.href='./faq.php'" data-translate="nav.faq">Preguntas Frecuentes</button>
                <button class="nav-btn login-btn" onclick="location.href='./login.php'" data-translate="nav.login">Login</button>
            </nav>
        </div>
    </header>

    <!-- Hero Section with Slider - Mejorado con TailwindCSS -->
    <section class="hero flex items-center justify-center">
        <div class="relative w-full max-w-7xl mx-auto px-4 md:px-8 lg:px-12">
            <!-- Slider Container -->
            <div class="relative overflow-hidden rounded-2xl" style="height: 500px;">
                <div class="flex transition-transform duration-700 ease-in-out" id="sliderWrapper" style="will-change: transform;">
                    <!-- Slide 1: Oportunidades en Viviendas en Trámite -->
                    <div class="slide-card w-full flex-shrink-0 flex items-center justify-center px-4 md:px-8">
                        <div class="bg-white/95 backdrop-blur-lg rounded-3xl p-8 md:p-10 shadow-2xl border border-white/20 w-full max-w-md mx-auto flex flex-col items-center justify-center transform transition-all duration-500 hover:shadow-3xl hover:scale-105">
                            <div class="w-16 h-16 bg-gradient-to-br from-coop-green-500 to-coop-green-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-coop-green-700 text-xl md:text-2xl font-bold mb-4 text-center uppercase tracking-wide" data-translate="slide1.title">Oportunidades en Viviendas en Trámite</h3>
                            <p class="text-coop-green-600 text-sm md:text-base leading-relaxed text-center" data-translate="slide1.desc">Descubre las mejores oportunidades de inversión en viviendas que están en proceso de constitución legal.</p>
                        </div>
                    </div>

                    <!-- Slide 2: Viviendas Habilitadas -->
                    <div class="slide-card w-full flex-shrink-0 flex items-center justify-center px-4 md:px-8">
                        <div class="bg-white/95 backdrop-blur-lg rounded-3xl p-8 md:p-10 shadow-2xl border border-white/20 w-full max-w-md mx-auto flex flex-col items-center justify-center transform transition-all duration-500 hover:shadow-3xl hover:scale-105">
                            <div class="w-16 h-16 bg-gradient-to-br from-coop-green-500 to-coop-green-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <h3 class="text-coop-green-700 text-xl md:text-2xl font-bold mb-4 text-center uppercase tracking-wide" data-translate="slide2.title">Viviendas Habilitadas</h3>
                            <p class="text-coop-green-600 text-sm md:text-base leading-relaxed text-center" data-translate="slide2.desc">Explora viviendas ya establecidas y funcionando con todas las garantías legales y financieras.</p>
                        </div>
                    </div>

                    <!-- Slide 3: Todas las Viviendas -->
                    <div class="slide-card w-full flex-shrink-0 flex items-center justify-center px-4 md:px-8">
                        <div class="bg-white/95 backdrop-blur-lg rounded-3xl p-8 md:p-10 shadow-2xl border border-white/20 w-full max-w-md mx-auto flex flex-col items-center justify-center transform transition-all duration-500 hover:shadow-3xl hover:scale-105">
                            <div class="w-16 h-16 bg-gradient-to-br from-coop-green-500 to-coop-green-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-coop-green-700 text-xl md:text-2xl font-bold mb-4 text-center uppercase tracking-wide" data-translate="slide3.title">Todas las Viviendas</h3>
                            <p class="text-coop-green-600 text-sm md:text-base leading-relaxed text-center" data-translate="slide3.desc">Accede a nuestro catálogo completo de viviendas disponibles para inversión y participación.</p>
                        </div>
                    </div>

                    <!-- Slide 4: Asesoramiento Profesional -->
                    <div class="slide-card w-full flex-shrink-0 flex items-center justify-center px-4 md:px-8">
                        <div class="bg-white/95 backdrop-blur-lg rounded-3xl p-8 md:p-10 shadow-2xl border border-white/20 w-full max-w-md mx-auto flex flex-col items-center justify-center transform transition-all duration-500 hover:shadow-3xl hover:scale-105">
                            <div class="w-16 h-16 bg-gradient-to-br from-coop-green-500 to-coop-green-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <h3 class="text-coop-green-700 text-xl md:text-2xl font-bold mb-4 text-center uppercase tracking-wide" data-translate="slide4b.title">Asesoramiento Profesional</h3>
                            <p class="text-coop-green-600 text-sm md:text-base leading-relaxed text-center" data-translate="slide4b.desc">Obtén consultoría especializada para tomar las mejores decisiones de inversión inmobiliaria.</p>
                        </div>
                    </div>

                    <!-- Slide 5: NUEVA - Financiamiento Flexible -->
                    <div class="slide-card w-full flex-shrink-0 flex items-center justify-center px-4 md:px-8">
                        <div class="bg-white/95 backdrop-blur-lg rounded-3xl p-8 md:p-10 shadow-2xl border border-white/20 w-full max-w-md mx-auto flex flex-col items-center justify-center transform transition-all duration-500 hover:shadow-3xl hover:scale-105">
                            <div class="w-16 h-16 bg-gradient-to-br from-coop-green-500 to-coop-green-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-coop-green-700 text-xl md:text-2xl font-bold mb-4 text-center uppercase tracking-wide" data-translate="slide5.title">Financiamiento Flexible</h3>
                            <p class="text-coop-green-600 text-sm md:text-base leading-relaxed text-center" data-translate="slide5.desc">Planes de pago adaptados a tus necesidades con opciones accesibles y sin intermediarios bancarios.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <button onclick="changeSlide(-1)" class="absolute left-2 md:left-4 top-1/2 -translate-y-1/2 z-10 bg-white/90 hover:bg-white backdrop-blur-sm w-12 h-12 md:w-14 md:h-14 rounded-full flex items-center justify-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-110 group">
                <svg class="w-6 h-6 text-coop-green-700 group-hover:text-coop-green-800 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button onclick="changeSlide(1)" class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 z-10 bg-white/90 hover:bg-white backdrop-blur-sm w-12 h-12 md:w-14 md:h-14 rounded-full flex items-center justify-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-110 group">
                <svg class="w-6 h-6 text-coop-green-700 group-hover:text-coop-green-800 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Dots Indicator -->
            <div class="flex justify-center gap-2 mt-8" id="sliderDots">
                <!-- Dots se generan dinámicamente con JavaScript -->
            </div>
        </div>
    </section>

    <!-- Origins Section -->
    <section class="origins scroll-reveal">
        <div class="origins-container">
            <div class="origins-content">
                <h2 data-translate="origins.title">Nuestros Orígenes</h2>
                <p data-translate="origins.text1">En 1938, en el marco de la creciente demanda de vivienda para los sectores más necesitados de Montevideo, se fundó la Cooperativa BINECOOP — Bienestar de Cooperativa Pública Nacional. Su creación fue impulsada por un grupo de trabajadores organizados, principalmente sindicalistas, obreros portuarios y empleados del Estado, que compartían la visión de que la vivienda debe ser un derecho colectivo y accesible para todos.

Desde sus inicios, la cooperativa se destacó por su fuerte conexión con el Centro de Investigaciones Económicas (CIE), un espacio académico que brindaba apoyo técnico y económico a proyectos de desarrollo social. Esta colaboración con economistas y urbanistas permitió que BINECOOP fuera pionera en implementar modelos de financiación alternativos y sostenibles, lo que la transformó en un referente para la vivienda cooperativa en el país.</p>
                <p data-translate="origins.text2">La cooperativa inició sus primeros proyectos en la zona de Villa del Cerro, donde se construyeron los primeros bloques habitacionales, empleando un modelo basado en la autogestión y la cooperación entre los miembros. En sus primeros años, los socios se organizaron para realizar el trabajo comunitario bajo la supervisión de expertos del CIE, quienes también brindaron asesoría sobre cómo optimizar recursos y asegurar la viabilidad económica a largo plazo.

En las décadas siguientes, BINECOOP consolidó su modelo económico autosustentable, permitiendo que más de 500 familias accedieran a su primera vivienda a través de planes accesibles, sin depender de créditos bancarios convencionales. Además, la cooperativa se expandió a diferentes puntos de Montevideo, llevando su filosofía de solidaridad, democracia y sostenibilidad a diversas comunidades.</p>
                <p data-translate="origins.text3">A lo largo de los años, BINECOOP continuó fortaleciendo sus lazos con centros académicos y entidades gubernamentales, jugando un rol clave en la creación de políticas públicas de vivienda cooperativa. A día de hoy, la cooperativa mantiene su vínculo activo con el CIE, garantizando que sus proyectos sigan siendo innovadores y adaptados a las necesidades actuales de los trabajadores.</p>
                <div class="origins-buttons">
                    <button class="origins-btn secondary" data-translate="origins.btn">Contacto</button>
                </div>
            </div>
            <div class="origins-image">
                <div class="building-placeholder">
                    <img src="https://tectesting.fwh.is/cdn_images/casa.png" height="400px" width="600px"></img>
                </div>
            </div>
        </div>
    </section>

    <!-- Administration Section -->
    <section class="administration scroll-reveal">
        <div class="administration-container">
            <h2 data-translate="admin.title">Consejo de Administración</h2>
            <div class="admin-grid">
                <div class="admin-card">
                    <h3 data-translate="admin.card1.title">Presidente</h3>
                    <p>Jorge Omar Taboada</p>
                </div>
                <div class="admin-card">
                    <h3 data-translate="admin.card2.title">Secretaria</h3>
                    <p>Cayam Valenzuela</p>
                </div>
                <div class="admin-card">
                    <h3 data-translate="admin.card3.title">Tesorera</h3>
                    <p>Norma Beatriz Ferreyra</p>
                </div>
                <div class="admin-card">
                    <h3 data-translate="admin.card4.title">1° Vocal</h3>
                    <p>Rodrigo Pizá Fibonacci</p>
                </div>
                <div class="admin-card">
                    <h3 data-translate="admin.card5.title">2° Vocal</h3>
                    <p>Emmanuel Gerardo Santillana Sipán</p>
                </div>
                <div class="admin-card">
                    <h3 data-translate="admin.card6.title">3° Vocal</h3>
                    <p>Miguel Ernesto del Rio Pereyra</p>
                </div>
                <div class="admin-card">
                    <h3 data-translate="admin.card7.title">Síndico Titular</h3>
                    <p>Raúl Esteban Pereyra</p>
                </div>
                <div class="admin-card">
                    <h3 data-translate="admin.card8.title">Síndico Suplente</h3>
                    <p>Leandro Joaquín Nicolás Canavesi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Section -->
    <section class="trust scroll-reveal">
        <div class="trust-container">
            <h2 data-translate="trust.title">Somos una Cooperativa de confianza</h2>
            <p data-translate="trust.desc">Creemos en la importancia y la necesidad de comprender la realidad y el funcionamiento del mundo del cooperativismo, brindando soluciones integrales y sostenibles para nuestros asociados.</p>
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
        // Slider functionality mejorado
        let currentSlide = 0;
        const slideCards = document.querySelectorAll('.slide-card');
        const totalSlides = slideCards.length;
        let slideWidth = 0;
        let gap = 24; // gap en px (gap-6 = 24px)

        // Calcular ancho del slide basado en el tamaño de pantalla
        function calculateSlideWidth() {
            const sliderContainer = document.querySelector('.hero .relative.overflow-hidden');
            if (sliderContainer) {
                slideWidth = sliderContainer.offsetWidth;
            } else if (slideCards[0]) {
                slideWidth = slideCards[0].offsetWidth;
            }
        }

        function updateSlider() {
            calculateSlideWidth();
            const wrapper = document.getElementById('sliderWrapper');
            // Centrar el slide activo: mover el wrapper para que el slide actual esté en el centro
            const translateX = -currentSlide * slideWidth;
            wrapper.style.transform = `translateX(${translateX}px)`;
            
            // Actualizar indicadores de puntos
            updateDots();
            
            // Agregar animación de entrada
            slideCards.forEach((card, index) => {
                const slideContent = card.querySelector('div');
                if (index === currentSlide) {
                    slideContent.classList.add('slide-enter');
                    slideContent.classList.remove('opacity-70', 'scale-95');
                    slideContent.classList.add('opacity-100', 'scale-100');
                } else {
                    slideContent.classList.remove('slide-enter', 'opacity-100', 'scale-100');
                    slideContent.classList.add('opacity-70', 'scale-95');
                }
            });
        }

        function changeSlide(direction) {
            currentSlide += direction;
            
            if (currentSlide >= totalSlides) {
                currentSlide = 0;
            } else if (currentSlide < 0) {
                currentSlide = totalSlides - 1;
            }
            
            updateSlider();
        }

        // Crear indicadores de puntos
        function createDots() {
            const dotsContainer = document.getElementById('sliderDots');
            dotsContainer.innerHTML = '';
            
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('button');
                dot.className = `w-2.5 h-2.5 md:w-3 md:h-3 rounded-full transition-all duration-300 ${
                    i === currentSlide 
                        ? 'bg-coop-green-700 scale-125' 
                        : 'bg-white/50 hover:bg-white/70'
                }`;
                dot.setAttribute('aria-label', `Ir a slide ${i + 1}`);
                dot.onclick = () => {
                    currentSlide = i;
                    updateSlider();
                };
                dotsContainer.appendChild(dot);
            }
        }

        function updateDots() {
            const dots = document.querySelectorAll('#sliderDots button');
            dots.forEach((dot, index) => {
                if (index === currentSlide) {
                    dot.classList.remove('bg-white/50', 'bg-white/70');
                    dot.classList.add('bg-coop-green-700', 'scale-125');
                } else {
                    dot.classList.remove('bg-coop-green-700', 'scale-125');
                    dot.classList.add('bg-white/50');
                }
            });
        }

        // Auto-play slider con pausa al hover
        let autoPlayInterval;
        function startAutoPlay() {
            autoPlayInterval = setInterval(() => {
                changeSlide(1);
            }, 5000);
        }

        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }

        // Pausar autoplay al hacer hover
        const sliderContainer = document.querySelector('.hero');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopAutoPlay);
            sliderContainer.addEventListener('mouseleave', startAutoPlay);
        }

        // Inicializar slider
        window.addEventListener('resize', () => {
            calculateSlideWidth();
            updateSlider();
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
            calculateSlideWidth();
            createDots();
            updateSlider();
            startAutoPlay();
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
                'slide1.title': 'Oportunidades en Viviendas en Trámite',
                'slide1.desc': 'Descubre las mejores oportunidades de inversión en viviendas que están en proceso de constitución legal.',
                'slide2.title': 'Viviendas Habilitadas',
                'slide2.desc': 'Explora viviendas ya establecidas y funcionando con todas las garantías legales y financieras.',
                'slide3.title': 'Todas las Viviendas',
                'slide3.desc': 'Accede a nuestro catálogo completo de viviendas disponibles para inversión y participación.',
                'slide4.title': 'Asesoramiento Personalizado',
                'slide4.desc': 'Nuestro equipo de expertos te guía en cada paso del proceso de adquisición de tu vivienda.',
                'slide4b.title': 'Asesoramiento Profesional',
                'slide4b.desc': 'Obtén consultoría especializada para tomar las mejores decisiones de inversión inmobiliaria.',
                'slide5.title': 'Financiamiento Flexible',
                'slide5.desc': 'Planes de pago adaptados a tus necesidades con opciones accesibles y sin intermediarios bancarios.',
                'origins.title': 'Nuestros Orígenes',
                'origins.text1': 'En 1938, en el marco de la creciente demanda de vivienda para los sectores más necesitados de Montevideo, se fundó la Cooperativa BINECOOP — Bienestar de Cooperativa Pública Nacional. Su creación fue impulsada por un grupo de trabajadores organizados, principalmente sindicalistas, obreros portuarios y empleados del Estado, que compartían la visión de que la vivienda debe ser un derecho colectivo y accesible para todos.\n\nDesde sus inicios, la cooperativa se destacó por su fuerte conexión con el Centro de Investigaciones Económicas (CIE), un espacio académico que brindaba apoyo técnico y económico a proyectos de desarrollo social. Esta colaboración con economistas y urbanistas permitió que BINECOOP fuera pionera en implementar modelos de financiación alternativos y sostenibles, lo que la transformó en un referente para la vivienda cooperativa en el país.',
                'origins.text2': 'La cooperativa inició sus primeros proyectos en la zona de Villa del Cerro, donde se construyeron los primeros bloques habitacionales, empleando un modelo basado en la autogestión y la cooperación entre los miembros. En sus primeros años, los socios se organizaron para realizar el trabajo comunitario bajo la supervisión de expertos del CIE, quienes también brindaron asesoría sobre cómo optimizar recursos y asegurar la viabilidad económica a largo plazo.\n\nEn las décadas siguientes, BINECOOP consolidó su modelo económico autosustentable, permitiendo que más de 500 familias accedieran a su primera vivienda a través de planes accesibles, sin depender de créditos bancarios convencionales. Además, la cooperativa se expandió a diferentes puntos de Montevideo, llevando su filosofía de solidaridad, democracia y sostenibilidad a diversas comunidades.',
                'origins.text3': 'A lo largo de los años, BINECOOP continuó fortaleciendo sus lazos con centros académicos y entidades gubernamentales, jugando un rol clave en la creación de políticas públicas de vivienda cooperativa. A día de hoy, la cooperativa mantiene su vínculo activo con el CIE, garantizando que sus proyectos sigan siendo innovadores y adaptados a las necesidades actuales de los trabajadores.',
                'origins.btn': 'Contacto',
                'admin.title': 'Consejo de Administración',
                'admin.card1.title': 'Presidente',
                'admin.card2.title': 'Secretaria',
                'admin.card3.title': 'Tesorera',
                'admin.card4.title': '1° Vocal',
                'admin.card5.title': '2° Vocal',
                'admin.card6.title': '3° Vocal',
                'admin.card7.title': 'Síndico Titular',
                'admin.card8.title': 'Síndico Suplente',
                'trust.title': 'Somos una Cooperativa de confianza',
                'trust.desc': 'Creemos en la importancia y la necesidad de comprender la realidad y el funcionamiento del mundo del cooperativismo, brindando soluciones integrales y sostenibles para nuestros asociados.',
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
                'slide1.title': 'Housing Opportunities in Process',
                'slide1.desc': 'Discover the best investment opportunities in housing that are in the process of legal constitution.',
                'slide2.title': 'Enabled Housing',
                'slide2.desc': 'Explore already established and functioning housing with all legal and financial guarantees.',
                'slide3.title': 'All Housing',
                'slide3.desc': 'Access our complete catalog of available housing for investment and participation.',
                'slide4.title': 'Personalized Advice',
                'slide4.desc': 'Our team of experts guides you through every step of the housing acquisition process.',
                'slide4b.title': 'Professional Advice',
                'slide4b.desc': 'Get specialized consulting to make the best real estate investment decisions.',
                'slide5.title': 'Flexible Financing',
                'slide5.desc': 'Payment plans adapted to your needs with accessible options and without banking intermediaries.',
                'origins.title': 'Our Origins',
                'origins.text1': 'In 1938, in the context of the growing demand for housing for the most needy sectors of Montevideo, the BINECOOP Cooperative — National Public Cooperative Welfare — was founded. Its creation was driven by a group of organized workers, mainly trade unionists, port workers and state employees, who shared the vision that housing should be a collective and accessible right for all.\n\nFrom its beginnings, the cooperative stood out for its strong connection with the Center for Economic Research (CIE), an academic space that provided technical and economic support to social development projects. This collaboration with economists and urban planners allowed BINECOOP to be a pioneer in implementing alternative and sustainable financing models, which transformed it into a reference for cooperative housing in the country.',
                'origins.text2': 'The cooperative began its first projects in the Villa del Cerro area, where the first housing blocks were built, using a model based on self-management and cooperation among members. In its early years, members organized to carry out community work under the supervision of CIE experts, who also provided advice on how to optimize resources and ensure long-term economic viability.\n\nIn the following decades, BINECOOP consolidated its self-sustaining economic model, allowing more than 500 families to access their first home through accessible plans, without depending on conventional bank loans. In addition, the cooperative expanded to different points in Montevideo, bringing its philosophy of solidarity, democracy and sustainability to various communities.',
                'origins.text3': 'Over the years, BINECOOP continued to strengthen its ties with academic centers and government entities, playing a key role in creating public policies for cooperative housing. To this day, the cooperative maintains its active link with the CIE, ensuring that its projects continue to be innovative and adapted to the current needs of workers.',
                'origins.btn': 'Contact',
                'admin.title': 'Board of Directors',
                'admin.card1.title': 'President',
                'admin.card2.title': 'Secretary',
                'admin.card3.title': 'Treasurer',
                'admin.card4.title': '1st Member',
                'admin.card5.title': '2nd Member',
                'admin.card6.title': '3rd Member',
                'admin.card7.title': 'Principal Auditor',
                'admin.card8.title': 'Substitute Auditor',
                'trust.title': 'We are a Trustworthy Cooperative',
                'trust.desc': 'We believe in the importance and need to understand the reality and functioning of the cooperative world, providing comprehensive and sustainable solutions for our members.',
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
                'slide1.title': 'Oportunidades em Habitações em Tramitação',
                'slide1.desc': 'Descubra as melhores oportunidades de investimento em habitações que estão em processo de constituição legal.',
                'slide2.title': 'Habitações Habilitadas',
                'slide2.desc': 'Explore habitações já estabelecidas e funcionando com todas as garantias legais e financeiras.',
                'slide3.title': 'Todas as Habitações',
                'slide3.desc': 'Acesse nosso catálogo completo de habitações disponíveis para investimento e participação.',
                'slide4.title': 'Assessoria Personalizada',
                'slide4.desc': 'Nossa equipe de especialistas orienta você em cada etapa do processo de aquisição da sua habitação.',
                'slide4b.title': 'Assessoria Profissional',
                'slide4b.desc': 'Obtenha consultoria especializada para tomar as melhores decisões de investimento imobiliário.',
                'slide5.title': 'Financiamento Flexível',
                'slide5.desc': 'Planos de pagamento adaptados às suas necessidades com opções acessíveis e sem intermediários bancários.',
                'origins.title': 'Nossas Origens',
                'origins.text1': 'Em 1938, no contexto da crescente demanda de habitação para os setores mais necessitados de Montevidéu, foi fundada a Cooperativa BINECOOP — Bem-estar de Cooperativa Pública Nacional. Sua criação foi impulsionada por um grupo de trabalhadores organizados, principalmente sindicalistas, trabalhadores portuários e funcionários do Estado, que compartilhavam a visão de que a habitação deve ser um direito coletivo e acessível para todos.\n\nDesde seus inícios, a cooperativa se destacou por sua forte conexão com o Centro de Pesquisas Econômicas (CIE), um espaço acadêmico que fornecia apoio técnico e econômico a projetos de desenvolvimento social. Esta colaboração com economistas e urbanistas permitiu que a BINECOOP fosse pioneira na implementação de modelos de financiamento alternativos e sustentáveis, o que a transformou em referência para a habitação cooperativa no país.',
                'origins.text2': 'A cooperativa iniciou seus primeiros projetos na área de Villa del Cerro, onde foram construídos os primeiros blocos habitacionais, empregando um modelo baseado na autogestão e na cooperação entre os membros. Em seus primeiros anos, os membros se organizaram para realizar o trabalho comunitário sob a supervisão de especialistas do CIE, que também forneceram assessoria sobre como otimizar recursos e garantir a viabilidade econômica a longo prazo.\n\nNas décadas seguintes, a BINECOOP consolidou seu modelo econômico autossustentável, permitindo que mais de 500 famílias acessassem sua primeira casa através de planos acessíveis, sem depender de empréstimos bancários convencionais. Além disso, a cooperativa se expandiu para diferentes pontos de Montevidéu, levando sua filosofia de solidariedade, democracia e sustentabilidade a várias comunidades.',
                'origins.text3': 'Ao longo dos anos, a BINECOOP continuou fortalecendo seus laços com centros acadêmicos e entidades governamentais, desempenhando um papel fundamental na criação de políticas públicas de habitação cooperativa. Até hoje, a cooperativa mantém seu vínculo ativo com o CIE, garantindo que seus projetos continuem sendo inovadores e adaptados às necessidades atuais dos trabalhadores.',
                'origins.btn': 'Contato',
                'admin.title': 'Conselho de Administração',
                'admin.card1.title': 'Presidente',
                'admin.card2.title': 'Secretária',
                'admin.card3.title': 'Tesoureira',
                'admin.card4.title': '1° Membro',
                'admin.card5.title': '2° Membro',
                'admin.card6.title': '3° Membro',
                'admin.card7.title': 'Síndico Titular',
                'admin.card8.title': 'Síndico Suplente',
                'trust.title': 'Somos uma Cooperativa de confiança',
                'trust.desc': 'Acreditamos na importância e na necessidade de compreender a realidade e o funcionamento do mundo cooperativista, oferecendo soluções integrais e sustentáveis para nossos associados.',
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
                'slide1.title': '正在办理的住房机会',
                'slide1.desc': '发现在法律组建过程中的最佳住房投资机会。',
                'slide2.title': '已启用的住房',
                'slide2.desc': '探索已建立并运行的所有法律和财务保障的住房。',
                'slide3.title': '所有住房',
                'slide3.desc': '访问我们完整的可用住房目录，用于投资和参与。',
                'slide4.title': '个性化建议',
                'slide4.desc': '我们的专家团队指导您完成住房收购过程的每一步。',
                'slide4b.title': '专业建议',
                'slide4b.desc': '获得专业咨询，做出最佳的房地产投资决策。',
                'slide5.title': '灵活融资',
                'slide5.desc': '根据您的需求量身定制的付款计划，提供便捷的选择，无需银行中介。',
                'origins.title': '我们的起源',
                'origins.text1': '1938年，在蒙得维的亚最需要住房的部门需求不断增长的背景下，BINECOOP合作社——国家公共合作福利——成立了。它的创建是由一群有组织的工人推动的，主要是工会会员、港口工人和国家雇员，他们共同认为住房应该是一个集体且所有人都可以享有的权利。\n\n从一开始，合作社就因其与经济研究中心（CIE）的紧密联系而脱颖而出，这是一个为社会发展项目提供技术和经济支持的学术空间。与经济学家和城市规划者的这种合作使BINECOOP成为实施替代性和可持续融资模式的先驱，这使其成为该国合作住房的参考。',
                'origins.text2': '合作社在Villa del Cerro地区开始了其首批项目，在那里建造了第一批住房街区，采用了基于成员之间自主管理和合作的模式。在最初的几年里，成员们组织起来在CIE专家的监督下进行社区工作，他们还就如何优化资源和确保长期经济可行性提供了建议。\n\n在接下来的几十年里，BINECOOP巩固了其自我维持的经济模式，使500多个家庭通过便捷的计划获得了他们的第一套住房，而不依赖于传统的银行贷款。此外，合作社扩展到蒙得维的亚的不同地点，将其团结、民主和可持续性的理念带到各个社区。',
                'origins.text3': '多年来，BINECOOP继续加强与学术中心和政府实体的联系，在制定合作住房公共政策方面发挥了关键作用。直到今天，合作社仍与CIE保持积极联系，确保其项目继续创新并适应当前工人的需求。',
                'origins.btn': '联系方式',
                'admin.title': '管理委员会',
                'admin.card1.title': '主席',
                'admin.card2.title': '秘书',
                'admin.card3.title': '财务主管',
                'admin.card4.title': '第一成员',
                'admin.card5.title': '第二成员',
                'admin.card6.title': '第三成员',
                'admin.card7.title': '主审计员',
                'admin.card8.title': '副审计员',
                'trust.title': '我们是一个值得信赖的合作社',
                'trust.desc': '我们相信理解和认识合作社世界的现实和运作的重要性和必要性，为我们的成员提供全面和可持续的解决方案。',
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
