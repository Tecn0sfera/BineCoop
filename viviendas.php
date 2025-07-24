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
               <div class="logo-icon"><a href="./index.php"><img src="https://tectesting.fwh.is/cdn_images/iii.png" style="width=50px; height:50px; margin-left:-100px;"></img></a></div>
                <div class="logo-text">
            </div>
            <nav class="nav-menu">
                <button class="nav-btn" onclick="location.href='./quienes_somos.php'">Quiénes somos</button>
                <button class="nav-btn" onclick="location.href='./faq.php'">Preguntas Frecuentes</button>
            </nav>
        </div>
    </header>

   <!-- Origins Section -->
    <section class="origins scroll-reveal">
        <div class="origins-container">
            <div class="origins-content">
                <h2>Vivienda de Apartamento</h2>
                <p>Este apartamento es una unidad funcional y luminosa de aproximadamente 55 m², ideal para familias pequeñas o parejas. Cuenta con un amplio living-comedor al frente con gran entrada de luz natural, una cocina independiente, un dormitorio principal, un baño completo y, en algunos casos, un segundo dormitorio adaptable como escritorio o cuarto infantil. Su diseño sencillo, típico de las viviendas cooperativas de los años 80 en Las Piedras, prioriza la comodidad, la ventilación y la practicidad en un entorno barrial tranquilo y bien conectado.</p>
                <div class="origins-buttons">
                    <button class="origins-btn secondary" onclick="openModal('apartmentModal')">Consultar</button>
                    <button class="origins-btn secondary">Adquirir</button>
                </div>
            </div>
            <div class="origins-image">
                <div class="building-placeholder">
                    <img src="https://tectesting.fwh.is/cdn_images/ed_vivienda.png" height="400px" width="600px"></img>
                </div>
            </div>
             <div class="origins-content">
                <h2>Casa de Vivienda</h2>
                <p>La vivienda individual en Binecoop está pensada para brindar confort, eficiencia y conexión comunitaria. Con diseño moderno, cuenta con dos plantas que distribuyen de forma práctica sus espacios: amplio living-comedor integrado a la cocina, tres dormitorios, dos baños completos, espacio flexible para oficina o estudio y salida a un patio verde con pérgola. Construida con materiales sustentables y equipada con grandes ventanales, la casa prioriza la luz natural y la armonía con el entorno, adaptándose a las necesidades de cada familia dentro del barrio cooperativo.</p>
                <div class="origins-buttons">
                    <button class="origins-btn secondary" onclick="openModal('houseModal')">Consultar</button>
                    <button class="origins-btn secondary">Adquirir</button>
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
                <h2>Apartamento BINECOOP - Características Detalladas</h2>
            </div>
            <div class="modal-body">
                <div class="details-section">
                    <div class="detail-group">
                        <h3>Descripción General</h3>
                        <ul>
                            <li><span class="highlight">Superficie estimada:</span> 50 a 60 m²</li>
                            <li><span class="highlight">Planta:</span> Rectangular y lineal</li>
                            <li><span class="highlight">Estilo:</span> Funcional, con buena iluminación natural frontal</li>
                            <li><span class="highlight">Ventanal principal:</span> Gran entrada de luz natural</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3>Distribución de Ambientes</h3>
                        <ul>
                            <li><span class="highlight">Living-Comedor:</span> Al frente (4.5m x 3.5m), gran ventanal</li>
                            <li><span class="highlight">Cocina:</span> Contigua al living (2.5m x 2m), forma rectangular</li>
                            <li><span class="highlight">Dormitorio principal:</span> Al fondo (3.5m x 3m), placard incluido</li>
                            <li><span class="highlight">Dormitorio secundario:</span> Opcional (3m x 2.2m), ideal para oficina</li>
                            <li><span class="highlight">Baño:</span> Completo (2.2m x 1.6m), ducha y ventilación</li>
                            <li><span class="highlight">Pasillo:</span> Distribución (1.2m ancho) conecta todos los ambientes</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3>Características Constructivas</h3>
                        <ul>
                            <li>Construcción típica años 80</li>
                            <li>Ventilación cruzada optimizada</li>
                            <li>Orientación para máximo aprovechamiento de luz</li>
                            <li>Acabados funcionales y duraderos</li>
                        </ul>
                    </div>
                </div>

                <div class="plan-section">
                    <div class="plan-placeholder">
                        <div><img src="https://tectesting.fwh.is/cdn_images/ap_plan.png" height="348px" width="300px"></img></div>
                    </div>
                    <div class="plan-info">
                        <h4>Información del Plano</h4>
                        <p>El plano muestra la distribución lineal típica de los apartamentos cooperativos, con optimización del espacio y circulación eficiente entre ambientes.</p>
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
                <h2>Casa de Vivienda BINECOOP - Características Detalladas</h2>
            </div>
            <div class="modal-body">
                <div class="details-section">
                    <div class="detail-group">
                        <h3>Características Generales</h3>
                        <ul>
                            <li><span class="highlight">Estilo arquitectónico:</span> Moderno con detalles en madera y grandes ventanales</li>
                            <li><span class="highlight">Integración comunitaria:</span> Orientación pensada para aprovechar la luz natural y cercanía al espacio verde común</li>
                            <li><span class="highlight">Materiales predominantes:</span> Bloques térmicos, madera reciclada y paneles solares</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3>Distribución de Habitaciones</h3>
                        <ul>
                            <li><span class="highlight">Planta Baja:</span></li>
                            <li>• Sala de estar amplia conectada con comedor y cocina estilo americano</li>
                            <li>• Baño completo</li>
                            <li>• Oficina o habitación flexible</li>
                            <li>• Lavadero independiente con salida al patio trasero</li>
                            <li><span class="highlight">Planta Alta:</span></li>
                            <li>• Dormitorio principal con baño en suite y vestidor</li>
                            <li>• 2 dormitorios secundarios</li>
                            <li>• Baño compartido</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3>Dimensiones Aproximadas</h3>
                        <ul>
                            <li><span class="highlight">Superficie total construida:</span> 120 m²</li>
                            <li><span class="highlight">Terreno asignado:</span> 250 m²</li>
                            <li><span class="highlight">Altura máxima:</span> 6,5 metros</li>
                        </ul>
                    </div>

                    <div class="detail-group">
                        <h3>Características Sustentables</h3>
                        <ul>
                            <li>Paneles solares para agua caliente</li>
                            <li>Sistema de recolección de agua de lluvia</li>
                            <li>Aislación térmica optimizada</li>
                            <li>Pérgola con plantas trepadoras</li>
                        </ul>
                    </div>
                </div>

                <div class="plan-section">
                    <div class="plan-placeholder">
                        <div><img src="https://tectesting.fwh.is/cdn_images/cs_plan.png" height="348px" width="300px"></img></div>
                    </div>
                    <div class="plan-info">
                        <h4>Información de Planos</h4>
                        <p>Los planos incluyen distribución en dos plantas, con especificaciones técnicas, orientación solar y detalles constructivos sustentables.</p>
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
    </script>
</body>
</html>
