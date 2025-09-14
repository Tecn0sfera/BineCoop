<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BINECOOP - Bienestar de Cooperativa Pública Nacional</title>
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
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
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
               <div class="logo-icon"><a href="./index.php"><img src="https://tectesting.fwh.is/cdn_images/iii.png" style="width=50px; height:50px; margin-left:-100px;"></img></a></div>
                <div class="logo-text">
            </div>
            <nav class="nav-menu">
                <button class="nav-btn" onclick="location.href='./quienes_somos.php'">Quiénes Somos</button>
                <button class="nav-btn" onclick="location.href='./viviendas.php'">Viviendas</button>
                <button class="nav-btn" onclick="location.href='./faq.php'">Preguntas Frecuentes</button>
                <button class="nav-btn login-btn" onclick="location.href='./login.php'">Login</button>
            </nav>
        </div>
    </header>

    <!-- Hero Section with Slider -->
    <section class="hero">
        <div class="slider-container">
            <div class="slider-wrapper" id="sliderWrapper">
                <div class="slide active">
                    <h3>Oportunidades en Viviendas en Trámite</h3>
                    <p>Descubre las mejores oportunidades de inversión en viviendas que están en proceso de constitución legal.</p>
                </div>
                <div class="slide">
                    <h3>Viviendas Habilitadas</h3>
                    <p>Explora viviendas ya establecidas y funcionando con todas las garantías legales y financieras.</p>
                </div>
                <div class="slide">
                    <h3>Todas las Viviendas</h3>
                    <p>Accede a nuestro catálogo completo de viviendas disponibles para inversión y participación.</p>
                </div>
                <div class="slide">
                    <h3>Asesoramiento Profesional</h3>
                    <p>Obtén consultoría especializada para tomar las mejores decisiones de inversión inmobiliaria.</p>
                </div>
            </div>
            <button class="slider-nav prev" onclick="changeSlide(-1)"></button>
            <button class="slider-nav next" onclick="changeSlide(1)"></button>
        </div>
    </section>

    <!-- Origins Section -->
    <section class="origins scroll-reveal">
        <div class="origins-container">
            <div class="origins-content">
                <h2>Nuestros Orígenes</h2>
                <p>En 1938, en el marco de la creciente demanda de vivienda para los sectores más necesitados de Montevideo, se fundó la Cooperativa BINECOOP — Bienestar de Cooperativa Pública Nacional. Su creación fue impulsada por un grupo de trabajadores organizados, principalmente sindicalistas, obreros portuarios y empleados del Estado, que compartían la visión de que la vivienda debe ser un derecho colectivo y accesible para todos.

Desde sus inicios, la cooperativa se destacó por su fuerte conexión con el Centro de Investigaciones Económicas (CIE), un espacio académico que brindaba apoyo técnico y económico a proyectos de desarrollo social. Esta colaboración con economistas y urbanistas permitió que BINECOOP fuera pionera en implementar modelos de financiación alternativos y sostenibles, lo que la transformó en un referente para la vivienda cooperativa en el país.</p>
                <p>La cooperativa inició sus primeros proyectos en la zona de Villa del Cerro, donde se construyeron los primeros bloques habitacionales, empleando un modelo basado en la autogestión y la cooperación entre los miembros. En sus primeros años, los socios se organizaron para realizar el trabajo comunitario bajo la supervisión de expertos del CIE, quienes también brindaron asesoría sobre cómo optimizar recursos y asegurar la viabilidad económica a largo plazo.

En las décadas siguientes, BINECOOP consolidó su modelo económico autosustentable, permitiendo que más de 500 familias accedieran a su primera vivienda a través de planes accesibles, sin depender de créditos bancarios convencionales. Además, la cooperativa se expandió a diferentes puntos de Montevideo, llevando su filosofía de solidaridad, democracia y sostenibilidad a diversas comunidades.</p>
                <p>A lo largo de los años, BINECOOP continuó fortaleciendo sus lazos con centros académicos y entidades gubernamentales, jugando un rol clave en la creación de políticas públicas de vivienda cooperativa. A día de hoy, la cooperativa mantiene su vínculo activo con el CIE, garantizando que sus proyectos sigan siendo innovadores y adaptados a las necesidades actuales de los trabajadores.</p>
                <div class="origins-buttons">
                    <button class="origins-btn secondary">Contacto</button>
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
            <h2>Consejo de Administración</h2>
            <div class="admin-grid">
                <div class="admin-card">
                    <h3>Presidente</h3>
                    <p>Jorge Omar Taboada</p>
                </div>
                <div class="admin-card">
                    <h3>Secretaria</h3>
                    <p>Cayam Valenzuela</p>
                </div>
                <div class="admin-card">
                    <h3>Tesorera</h3>
                    <p>Norma Beatriz Ferreyra</p>
                </div>
                <div class="admin-card">
                    <h3>1° Vocal</h3>
                    <p>Rodrigo Pizá Fibonacci</p>
                </div>
                <div class="admin-card">
                    <h3>2° Vocal</h3>
                    <p>Emmanuel Gerardo Santillana Sipán</p>
                </div>
                <div class="admin-card">
                    <h3>3° Vocal</h3>
                    <p>Miguel Ernesto del Rio Pereyra</p>
                </div>
                <div class="admin-card">
                    <h3>Síndico Titular</h3>
                    <p>Raúl Esteban Pereyra</p>
                </div>
                <div class="admin-card">
                    <h3>Síndico Suplente</h3>
                    <p>Leandro Joaquín Nicolás Canavesi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Section -->
    <section class="trust scroll-reveal">
        <div class="trust-container">
            <h2>Somos una Cooperativa de confianza</h2>
            <p>Creemos en la importancia y la necesidad de comprender la realidad y el funcionamiento del mundo del cooperativismo, brindando soluciones integrales y sostenibles para nuestros asociados.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <div class="logo">
                    <div class="logo-icon"><img src="https://tectesting.fwh.is/cdn_images/ae.png" style="width=50px; height:50px;"></img></div>
                    <div class="logo-text">
                        <h1 style="font-size: 24px; color: white;">BINECOOP</h1>
                        <p>Bienestar de Cooperativa Pública Nacional</p>
                    </div>
                </div>
            </div>
            <div class="footer-section">
                <h3>Estamos en:</h3>
                <p>Calle de la Solidaridad 1156</p>
                <p>Barrio La Blanqueada, Montevideo, Uruguay</p>
                <br>
                <h3>Contactanos:</h3>
                <p>Mail: contactovivienda@bcpn.com.uy</p>
                <p>Tel: +598 2 507 3894</p>
                <p>De Lu a Vie 10 a 17hs.</p>
            </div>
            <div class="footer-section">
                <h3>Síguenos</h3>
                <div class="social-links">
                    <a href="#" class="social-link">📘</a>
                    <a href="#" class="social-link">📷</a>
                    <a href="#" class="social-link">🐦</a>
                    <a href="#" class="social-link">📺</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 BINECOOP. Todos los derechos reservados. Desarrollado con tecnología moderna.</p>
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
    </script>
</body>
</html>
