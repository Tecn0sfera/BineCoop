<?php
session_start();

// Configuración de imágenes del header
$header_images = [
    [
        'url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
        'title' => 'Desarrollo de Software',
        'subtitle' => 'Soluciones tecnológicas innovadoras'
    ],
    [
        'url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
        'title' => 'Implementación y Mantenimiento',
        'subtitle' => 'Servicios profesionales especializados'
    ],
    [
        'url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
        'title' => 'Consultoría Tecnológica',
        'subtitle' => 'Innovación tecnológica asegurada'
    ]
];

// Manejo del índice actual del carrusel
$current_slide = isset($_GET['slide']) ? (int)$_GET['slide'] : 0;
if ($current_slide >= count($header_images)) $current_slide = 0;
if ($current_slide < 0) $current_slide = count($header_images) - 1;

// Productos y servicios
$products = [
    [
        'name' => 'Creación y Desarrollo de Software',
        'description' => 'Soluciones a petición completamente aseguradas su innovación y calidad',
        'icon' => 'gear',
        'color' => '#17a2b8'
    ],
    [
        'name' => 'Seguridad y Mantenimiento de Sistemas Informáticos',
        'description' => 'Asegurando la integridad de la información para todo el sistema',
        'icon' => 'shield',
        'color' => '#6f42c1'
    ]
];

// Enlaces del footer
$footer_links = [
    'Política de calidad' => '#',
    'Política de privacidad' => '#',
    'Nuestros clientes' => 'https://tectesting.fwh.is/cdn_images/Nuestros_Clientes.pdf',
    'Atención al cliente' => '#',
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tecnosfera - Soluciones Informáticas & Desarrollo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        /* Header Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            padding: 0.5rem 0;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            height: 150px;
            width: 350px;
            font-size: 2rem;
            font-weight: 700;
            background: blue;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }


        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #667eea;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        /* Hero Section with Carousel */
        .hero {
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            overflow: hidden;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: all 0.8s ease-in-out;
            z-index: -2;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(2, 17, 255, 0.5), rgba(132, 17, 250, 0.2));
            z-index: -1;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 2rem;
            z-index: 10;
            animation: fadeInUp 1s ease-out;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: none;
            color: white;
            font-size: 2rem;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 50%;
            z-index: 100;
        }

        .carousel-nav:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-prev {
            left: 2rem;
        }

        .carousel-next {
            right: 2rem;
        }

        .carousel-indicators {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 1rem;
            z-index: 100;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .indicator.active {
            background: white;
            transform: scale(1.2);
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(238, 90, 36, 0.4);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(238, 90, 36, 0.6);
        }

        /* About Section */
        .about {
            padding: 6rem 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .about-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #666;
            margin-bottom: 2rem;
        }

        .about-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .feature-item {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        /* Solutions Section */
        .solutions {
            padding: 6rem 0;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 4rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .product-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            border-color: var(--product-color);
        }

        .product-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 1.5rem;
            background: var(--product-color);
        }

        .product-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }

        .product-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Services Section */
        .services {
            padding: 6rem 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }

        .services h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .services p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: #ffffff;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .footer-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(125px, 1fr));
            gap: 1rem;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: #667eea;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #667eea;
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid #333;
            padding-top: 2rem;
            text-align: center;
            color: #999;
            font-size: 0.9rem;
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
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .about-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .about-features {
                grid-template-columns: 1fr;
            }

            .carousel-nav {
                display: none;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .social-links {
                justify-content: center;
            }
        }

        /* Loading Animation */
        .loading {
            opacity: 0.7;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="#" class="logo"><img src="https://tectesting.fwh.is/cdn_images/logo_main.png" width= "400px"; height= "230px";></img></a>
            <ul class="nav-menu">
                <li><a href="#inicio" class="nav-link active">Inicio</a></li>
                <li><a href="#servicios" class="nav-link">Servicios</a></li>
                <li><a href="#productos" class="nav-link">Productos</a></li>
                <li><a href="https://tectesting.fwh.is/cdn_images/Nuestros_Clientes.pdf" class="nav-link">Clientes</a></li>
                <li><a href="#empresa" class="nav-link">Empresa</a></li>
                <li><a href="https://forms.gle/RLLkJpHpzpwMzFPU9" class="nav-link">Contacto</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section with Carousel -->
    <section class="hero" id="inicio">
        <div class="hero-background" style="background-image: url('<?php echo $header_images[$current_slide]['url']; ?>')"></div>
        <div class="hero-overlay"></div>
        
        <div class="hero-content">
            <h1 class="fade-in"><?php echo $header_images[$current_slide]['title']; ?></h1>
            <p class="fade-in"><?php echo $header_images[$current_slide]['subtitle']; ?></p>
            <a href="https://forms.gle/RLLkJpHpzpwMzFPU9" class="cta-button fade-in">Contáctanos ahora</a>
        </div>

        <!-- Carousel Navigation -->
        <button class="carousel-nav carousel-prev" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="carousel-nav carousel-next" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Carousel Indicators -->
        <div class="carousel-indicators">
            <?php for ($i = 0; $i < count($header_images); $i++): ?>
                <div class="indicator <?php echo $i === $current_slide ? 'active' : ''; ?>" 
                     onclick="goToSlide(<?php echo $i; ?>)"></div>
            <?php endfor; ?>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="empresa">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2 class="fade-in">Somos una empresa dedicada al desarrollo</h2>
                    <p class="fade-in">
                        Nos especializamos en el desarrollo, implementación y mantenimiento de 
                        software para empresas de mediano y gran porte, contando con un grupo 
                        humano con una innovación muy destacable en el sector tecnológico.
                    </p>
                    <a href="https://forms.gle/RLLkJpHpzpwMzFPU9" class="cta-button fade-in">Contáctarse ahora</a>
                </div>
                <div class="about-features">
                    <div class="feature-item fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h3>Desarrollo</h3>
                        <p>Soluciones personalizadas</p>
                    </div>
                    <div class="feature-item fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3>Implementación</h3>
                        <p>Procesos optimizados</p>
                    </div>
                    <div class="feature-item fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3>Mantenimiento</h3>
                        <p>Soporte continuo</p>
                    </div>
                    <div class="feature-item fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Innovación</h3>
                        <p>+Innovación destacada en el ámbito informático</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Solutions Section -->
    <section class="solutions" id="productos">
        <div class="container">
            <h2 class="section-title fade-in">Nuestras soluciones</h2>
            <p class="section-subtitle fade-in">
                Todos nuestros productos están desarrollados con mucho amor. Esto nos permite brindar mayores 
                posibilidades de integración a nuevas tecnologías, menores tiempos de desarrollo y la flexibilidad de 
                crear soluciones adaptadas a la realidad de cada cliente.
            </p>

            <div class="products-grid">
                <?php foreach ($products as $index => $product): ?>
                    <div class="product-card fade-in" style="--product-color: <?php echo $product['color']; ?>">
                        <div class="product-icon">
                            <i class="fas fa-<?php echo $product['icon']; ?>"></i>
                        </div>
                        <h3><?php echo $product['name']; ?></h3>
                        <p><?php echo $product['description']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="servicios">
        <div class="container">
            <h2 class="fade-in">Todas nuestras Soluciones a tu Alcance</h2>
            <p class="fade-in">
                Te ofrecemos una amplia variedad de servicios pensados para 
                ayudarte a que tu vida y tu trabajo no se corten por nada.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contacto">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo"><img src="https://tectesting.fwh.is/cdn_images/white.png" height="150px" width="300px"></img></div>
                </div>
                <div class="footer-links">
                    <?php foreach ($footer_links as $text => $url): ?>
                        <a href="<?php echo $url; ?>"><?php echo $text; ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-at"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Tecnosfera. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Carousel functionality
        let currentSlide = <?php echo $current_slide; ?>;
        const totalSlides = <?php echo count($header_images); ?>;
        const images = <?php echo json_encode($header_images); ?>;

        function changeSlide(direction) {
            document.body.classList.add('loading');
            
            currentSlide += direction;
            if (currentSlide >= totalSlides) currentSlide = 0;
            if (currentSlide < 0) currentSlide = totalSlides - 1;
            
            updateCarousel();
        }

        function goToSlide(index) {
            document.body.classList.add('loading');
            currentSlide = index;
            updateCarousel();
        }

        function updateCarousel() {
            // Update URL with PHP redirect
            const url = new URL(window.location);
            url.searchParams.set('slide', currentSlide);
            
            // Smooth transition effect
            const heroBackground = document.querySelector('.hero-background');
            const heroContent = document.querySelector('.hero-content');
            
            heroBackground.style.opacity = '0';
            heroContent.style.opacity = '0';
            
            setTimeout(() => {
                window.location.href = url.toString();
            }, 300);
        }


        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Active navigation link highlighting
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                if (scrollY >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });

        // Remove loading state after page loads
        window.addEventListener('load', () => {
            document.body.classList.remove('loading');
        });

        // Keyboard navigation for carousel
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                changeSlide(-1);
            } else if (e.key === 'ArrowRight') {
                changeSlide(1);
            }
        });

        // Touch/swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        document.querySelector('.hero').addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        });

        document.querySelector('.hero').addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            if (touchEndX < touchStartX - swipeThreshold) {
                changeSlide(1); // Swipe left, next slide
            }
            if (touchEndX > touchStartX + swipeThreshold) {
                changeSlide(-1); // Swipe right, previous slide
            }
        }
        function preloadImages() {
            images.forEach((image, index) => {
                if (index !== currentSlide) {
                    const img = new Image();
                    img.src = image.url;
                }
            });
        }

        // Initialize preloading
        preloadImages();

        // Form validation and submission (if contact form exists)
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Lazy loading for images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Search functionality (if needed)
        function searchProducts(query) {
            const products = document.querySelectorAll('.product-card');
            products.forEach(product => {
                const title = product.querySelector('h3').textContent.toLowerCase();
                const description = product.querySelector('p').textContent.toLowerCase();
                
                if (title.includes(query.toLowerCase()) || description.includes(query.toLowerCase())) {
                    product.style.display = 'block';
                    product.style.animation = 'fadeInUp 0.5s ease';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        // Dynamic content loading
        async function loadContent(endpoint) {
            try {
                const response = await fetch(endpoint);
                if (!response.ok) throw new Error('Network response was not ok');
                return await response.json();
            } catch (error) {
                console.error('Error loading content:', error);
                return null;
            }
        }

        // Cookie consent (GDPR compliance)
        function checkCookieConsent() {
            if (!localStorage.getItem('cookieConsent')) {
                showCookieNotice();
            }
        }

        function showCookieNotice() {
            const notice = document.createElement('div');
            notice.innerHTML = `
                <div style="position: fixed; bottom: 0; left: 0; right: 0; background: #333; color: white; padding: 1rem; z-index: 10000; text-align: center;">
                    <p style="margin: 0 0 1rem 0;">Este sitio utiliza cookies para mejorar su experiencia. 
                    <button onclick="acceptCookies()" style="background: #667eea; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; margin-left: 1rem; cursor: pointer;">Aceptar</button>
                    <button onclick="declineCookies()" style="background: transparent; color: white; border: 1px solid white; padding: 0.5rem 1rem; border-radius: 5px; margin-left: 0.5rem; cursor: pointer;">Rechazar</button>
                    </p>
                </div>
            `;
            document.body.appendChild(notice);
        }

        function acceptCookies() {
            localStorage.setItem('cookieConsent', 'accepted');
            document.querySelector('[style*="position: fixed; bottom: 0"]').remove();
        }

        function declineCookies() {
            localStorage.setItem('cookieConsent', 'declined');
            document.querySelector('[style*="position: fixed; bottom: 0"]').remove();
        }

        // Analytics tracking (placeholder)
        function trackEvent(category, action, label) {
            // Google Analytics or other tracking service integration
            console.log(`Tracking: ${category} - ${action} - ${label}`);
        }

        // Error handling
        window.addEventListener('error', (e) => {
            console.error('JavaScript error:', e.error);
            // Send error to logging service
        });

        // Service Worker registration for PWA capabilities
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('SW registered: ', registration);
                    })
                    .catch(registrationError => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        // Initialize everything when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize animations
            document.querySelectorAll('.fade-in').forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });

            // Initialize tooltips
            const tooltips = document.querySelectorAll('[data-tooltip]');
            tooltips.forEach(tooltip => {
                tooltip.addEventListener('mouseenter', showTooltip);
                tooltip.addEventListener('mouseleave', hideTooltip);
            });

            // Check cookie consent
            checkCookieConsent();

            // Track page view
            trackEvent('Page', 'View', 'Homepage');
        });

        // Utility functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        }

        // Optimized scroll handler
        const optimizedScroll = throttle(() => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }, 16);

        window.addEventListener('scroll', optimizedScroll);

        // Print styles
        window.addEventListener('beforeprint', () => {
            document.body.classList.add('printing');
        });

        window.addEventListener('afterprint', () => {
            document.body.classList.remove('printing');
        });

        // Accessibility improvements
        document.addEventListener('keydown', (e) => {
            // Escape key closes modals
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal.active');
                modals.forEach(modal => modal.classList.remove('active'));
            }
            
            // Tab navigation improvements
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-nav');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-nav');
        });

        // Progressive enhancement
        if (CSS.supports('backdrop-filter', 'blur(10px)')) {
            document.body.classList.add('supports-backdrop-filter');
        }

        if (CSS.supports('display', 'grid')) {
            document.body.classList.add('supports-grid');
        }

        // Final initialization
        console.log('Tecnosfera website initialized successfully');
        console.log(`Current slide: ${currentSlide + 1}/${totalSlides}`);
    </script>
</body>
</html>
