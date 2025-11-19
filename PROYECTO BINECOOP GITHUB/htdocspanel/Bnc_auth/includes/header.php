<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<header>
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle" style="display: none;">
            <i class="fas fa-bars"></i>
        </button>
        <h3>Panel de Administraci&oacute;n</h3>
    </div>
    
    <div class="header-right">
        <div class="notifications">
            <i class="fas fa-bell"></i>
            <span class="badge">3</span>
        </div>
        
        <div class="user-info dropdown">
            <div class="user-avatar hover-scale">AD</div>
            <span>Admin User</span>
            
            <div class="dropdown-menu">
                <a href="#"><i class="fas fa-user-cog"></i> Perfil</a>
                <a href="#"><i class="fas fa-cog"></i> Configuraci&oacute;n</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesi&oacute;n</a>
            </div>
        </div>
    </div>
</header>