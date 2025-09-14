document.addEventListener('DOMContentLoaded', function() {
    // Manejo espec�fico de los submen�s EXTRA
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const parent = this.closest('.has-submenu');
            const submenu = this.nextElementSibling;
            const arrow = this.querySelector('.arrow');
            
            // Cerrar otros submen�s abiertos
            document.querySelectorAll('.has-submenu').forEach(item => {
                if (item !== parent) {
                    item.classList.remove('active');
                    item.querySelector('.submenu').style.maxHeight = '0';
                    item.querySelector('.arrow').style.transform = 'rotate(0deg)';
                }
            });
            
            // Alternar el submen� actual
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
    
    // Manejo de los enlaces del submen�
    const submenuLinks = document.querySelectorAll('.submenu-link');
    submenuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Aqu� puedes agregar l�gica para cargar contenido din�mico
            console.log('Clic en:', this.textContent.trim());
            
            // Ejemplo: Actualizar el �rea de contenido principal
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.innerHTML = `
                    <h1>${this.querySelector('i').className} ${this.textContent.trim()}</h1>
                    <div class="card-container">
                        <div class="card">
                            <h3>Contenido de ${this.textContent.trim()}</h3>
                            <p>Esta secci�n est� en desarrollo</p>
                        </div>
                    </div>
                `;
            }
        });
    });
    
    // Cerrar submen�s al hacer clic fuera
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
    // Toggle del men� en m�viles
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Cerrar men� al hacer clic fuera en m�viles
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

document.addEventListener('DOMContentLoaded', function() {
    // Manejar submenús
    const submenuToggles = document.querySelectorAll('.has-submenu > a');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            
            // Cerrar otros submenús abiertos
            if (!parent.classList.contains('active')) {
                document.querySelectorAll('.has-submenu').forEach(item => {
                    if (item !== parent) item.classList.remove('active');
                });
            }
            
            parent.classList.toggle('active');
        });
    });
    
    // Simular gráfico en la tarjeta principal
    const chartPlaceholder = document.querySelector('.chart-placeholder');
    if (chartPlaceholder) {
        // Esto sería reemplazado por una librería de gráficos real como Chart.js
        chartPlaceholder.innerHTML = '<div style="height:100%; width:100%; background: linear-gradient(90deg, rgba(52,152,219,0.2) 0%, rgba(52,152,219,0.4) 50%, rgba(52,152,219,0.2) 100%);"></div>';
    }
    
    // Manejar el menú activo basado en la URL actual
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const menuItems = document.querySelectorAll('.sidebar-nav li a');
    
    menuItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href && href.includes(currentPage)) {
            item.parentElement.classList.add('active');
            
            // Asegurarse de que los padres de los submenús también estén activos
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