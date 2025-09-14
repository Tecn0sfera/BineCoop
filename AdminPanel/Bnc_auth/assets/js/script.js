document.addEventListener('DOMContentLoaded', function() {
    // Manejo específico de los submenús EXTRA
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const parent = this.closest('.has-submenu');
            const submenu = this.nextElementSibling;
            const arrow = this.querySelector('.arrow');
            
            // Cerrar otros submenús abiertos
            document.querySelectorAll('.has-submenu').forEach(item => {
                if (item !== parent) {
                    item.classList.remove('active');
                    item.querySelector('.submenu').style.maxHeight = '0';
                    item.querySelector('.arrow').style.transform = 'rotate(0deg)';
                }
            });
            
            // Alternar el submenú actual
            parent.classList.toggle('active');
            
            if (parent.classList.contains('active')) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
                arrow.style.transform = 'rotate(180deg)';
            } else {
                submenu.style.maxHeight = '0';
                arrow.style.transform = 'rotate(0deg)';
            }
        });
    });
    
    // Manejo de los enlaces del submenú
    const submenuLinks = document.querySelectorAll('.submenu-link');
    submenuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Aquí puedes agregar lógica para cargar contenido dinámico
            console.log('Clic en:', this.textContent.trim());
            
            // Ejemplo: Actualizar el área de contenido principal
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.innerHTML = `
                    <h1>${this.querySelector('i').className} ${this.textContent.trim()}</h1>
                    <div class="card-container">
                        <div class="card">
                            <h3>Contenido de ${this.textContent.trim()}</h3>
                            <p>Esta sección está en desarrollo</p>
                        </div>
                    </div>
                `;
            }
        });
    });
    
    // Cerrar submenús al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.has-submenu')) {
            document.querySelectorAll('.has-submenu').forEach(item => {
                item.classList.remove('active');
                const submenu = item.querySelector('.submenu');
                if (submenu) submenu.style.maxHeight = '0';
                const arrow = item.querySelector('.arrow');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            });
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Toggle del menú en móviles
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Cerrar menú al hacer clic fuera en móviles
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

document.addEventListener('DOMContentLoaded', function() {
    // Manejar submenÃºs
    const submenuToggles = document.querySelectorAll('.has-submenu > a');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            
            // Cerrar otros submenÃºs abiertos
            if (!parent.classList.contains('active')) {
                document.querySelectorAll('.has-submenu').forEach(item => {
                    if (item !== parent) item.classList.remove('active');
                });
            }
            
            parent.classList.toggle('active');
        });
    });
    
    // Simular grÃ¡fico en la tarjeta principal
    const chartPlaceholder = document.querySelector('.chart-placeholder');
    if (chartPlaceholder) {
        // Esto serÃ­a reemplazado por una librerÃ­a de grÃ¡ficos real como Chart.js
        chartPlaceholder.innerHTML = '<div style="height:100%; width:100%; background: linear-gradient(90deg, rgba(52,152,219,0.2) 0%, rgba(52,152,219,0.4) 50%, rgba(52,152,219,0.2) 100%);"></div>';
    }
    
    // Manejar el menÃº activo basado en la URL actual
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const menuItems = document.querySelectorAll('.sidebar-nav li a');
    
    menuItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href && href.includes(currentPage)) {
            item.parentElement.classList.add('active');
            
            // Asegurarse de que los padres de los submenÃºs tambiÃ©n estÃ©n activos
            let parent = item.closest('.submenu');
            while (parent) {
                parent.previousElementSibling.classList.add('active');
                parent.style.maxHeight = parent.scrollHeight + 'px';
                parent = parent.closest('.submenu');
            }
        }
    });
});

});