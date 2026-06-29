<?php
use Libraries\Middleware\RBACMiddleware;
use Libraries\Middleware\CSRFMiddleware;

$rol = $_SESSION['rol'] ?? 0;
$permisos = RBACMiddleware::getPermisosRol($rol);
$slugs = array_column($permisos, 'slug');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= CSRFMiddleware::getTokenMeta() ?>
    <title><?= $data['page_title'] ?? 'Amazon Market - Sistema de Ventas' ?></title>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Hojas de Estilos Corporativas (Azul y Dorado) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/Assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <!-- Barra Lateral Izquierda (Sidebar) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><span class="gold-text">Amazon</span> <span class="white-text">Market</span></h2>
                <span class="role-badge"><i class="fa-solid fa-store"></i> Minimarket</span>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
                <div class="user-info">
                    <span class="user-name"><?= e($_SESSION['nombre'] ?? 'Operador') ?></span>
                    <span class="user-role"><?= ($_SESSION['rol'] == 1) ? 'Administrador' : 'Vendedor' ?></span>
                </div>
            </div>

            <nav class="menu">
                <?php if (in_array('dashboard.ver', $slugs)): ?>
                <a href="<?= BASE_URL ?>/Home" class="menu-link <?= (strpos($_GET['url'] ?? '', 'Home') !== false) ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
                <?php endif; ?>

                <?php if (in_array('productos.listar', $slugs)): ?>
                <a href="<?= BASE_URL ?>/Producto" class="menu-link <?= (strpos($_GET['url'] ?? '', 'Producto') !== false) ? 'active' : '' ?>">
                    <i class="fa-solid fa-boxes-stacked"></i> Productos
                </a>
                <?php endif; ?>

                <?php if (in_array('clientes.listar', $slugs)): ?>
                <a href="<?= BASE_URL ?>/Cliente" class="menu-link <?= (strpos($_GET['url'] ?? '', 'Cliente') !== false) ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i> Clientes
                </a>
                <?php endif; ?>

                <?php if (in_array('usuarios.listar', $slugs)): ?>
                <a href="<?= BASE_URL ?>/Usuario" class="menu-link <?= (strpos($_GET['url'] ?? '', 'Usuario') !== false) ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-gear"></i> Usuarios
                </a>
                <?php endif; ?>

                <?php if (in_array('ventas.acceder', $slugs)): ?>
                <a href="<?= BASE_URL ?>/Venta" class="menu-link <?= (strpos($_GET['url'] ?? '', 'Venta') !== false) ? 'active' : '' ?>">
                    <i class="fa-solid fa-cash-register"></i> Punto de Venta
                </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/Auth/logout" class="menu-link logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
                </a>
            </nav>
        </aside>

        <!-- Contenedor Principal (Workspace) -->
        <main class="main-content">
            <!-- Barra Superior del Workspace -->
            <header class="topbar">
                <div class="topbar-left">
                    <h1 class="page-title"><i class="fa-solid fa-folder-open"></i> <?= str_replace(" - Amazon Market", "", $data['page_title'] ?? '') ?></h1>
                </div>
                <div class="topbar-right">
                    <span class="date-badge"><i class="fa-solid fa-calendar-day"></i> <?= date('d/m/Y') ?></span>
                </div>
            </header>
            
            <div class="content-wrapper">
                <!-- Mensajes Flash de Éxito o Error -->
                <?php if(!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success" id="flash_success">
                        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger" id="flash_error">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
