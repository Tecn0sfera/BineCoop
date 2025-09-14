document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.overlay');
    const userMenu = document.querySelector('.user-menu');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    const notifications = document.querySelector('.notifications');
    
    // Detectar dispositivo
    const isMobile = window.matchMedia('(max-width: 992px)').matches;
    
    // Configurar animaciones para elementos del sidebar
    const sidebarItems = document.querySelectorAll('.sidebar-nav li');
    sidebarItems.forEach((item, index) => {
        item.style.setProperty('--i', index);
    });
    
    // Menú toggle para móvil
    if (isMobile) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('no-scroll');
        });
        
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            this.classList.remove('active');
            document.body.classList.remove('no-scroll');
        });
    } else {
        menuToggle.style.display = 'none';
        overlay.style.display = 'none';
    }
    
    // Menú de usuario - Mejorado para móvil y desktop
    userMenu.addEventListener('click', function(e) {
        if (isMobile) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('active');
        }
    });
    
    // Cerrar menús al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!userMenu.contains(e.target)) {
            dropdownMenu.classList.remove('active');
        }
    });
    
    // Notificaciones - Efecto moderno
    if (notifications) {
        notifications.addEventListener('click', function() {
            // Aquí iría la lógica para mostrar notificaciones
            this.classList.add('pulse');
            setTimeout(() => {
                this.classList.remove('pulse');
            }, 300);
        });
    }
    
    // Tablas responsivas mejoradas
    const tables = document.querySelectorAll('.data-table');
    tables.forEach(table => {
        // Selección de filas con efecto
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('click', function() {
                rows.forEach(r => r.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
        
        // Hacer tabla responsiva
        if (isMobile) {
            const headers = Array.from(table.querySelectorAll('thead th'));
            headers.forEach((header, index) => {
                if (index > 2) {
                    header.style.display = 'none';
                    table.querySelectorAll(`tbody td:nth-child(${index + 1})`).forEach(cell => {
                        cell.style.display = 'none';
                    });
                }
            });
        }
    });
    
    // Paginación mejorada
    const paginationButtons = document.querySelectorAll('.pagination button');
    paginationButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('active')) {
                paginationButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Efecto de transición
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            }
        });
    });
    
    // Efecto de carga suave
    setTimeout(() => {
        document.body.classList.add('loaded');
    }, 300);
    
    // Manejo del resize con debounce
    let resizeTimer;
    window.addEventListener('resize', function() {
        document.body.classList.add('resizing');
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            document.body.classList.remove('resizing');
            const currentIsMobile = window.matchMedia('(max-width: 992px)').matches;
            if (currentIsMobile !== isMobile) {
                window.location.reload();
            }
        }, 250);
    });
    
    // Tooltips modernos
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            if (!isMobile) {
                const tooltip = document.createElement('div');
                tooltip.className = 'custom-tooltip';
                tooltip.textContent = this.getAttribute('data-tooltip');
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = `${rect.left + rect.width / 2}px`;
                tooltip.style.top = `${rect.top - 10}px`;
                tooltip.style.transform = 'translate(-50%, -100%)';
                
                setTimeout(() => {
                    tooltip.classList.add('show');
                }, 10);
                
                this.tooltip = tooltip;
            }
        });
        
        el.addEventListener('mouseleave', function() {
            if (this.tooltip) {
                this.tooltip.classList.remove('show');
                setTimeout(() => {
                    this.tooltip.remove();
                }, 200);
            }
        });
    });
});
