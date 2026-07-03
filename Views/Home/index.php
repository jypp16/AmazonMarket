<?php require_once "Views/Header.php"; ?>

<div class="dashboard-grid">
    <!-- Card Productos -->
    <div class="card stat-card blue-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
            <div class="stat-details">
                <h3><?= $data['productos'] ?></h3>
                <p>Productos en Stock</p>
            </div>
        </div>
        <div class="card-footer-action">
            <a href="<?= BASE_URL ?>/Producto">Ver Inventario <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <!-- Card Clientes -->
    <div class="card stat-card gold-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="stat-details">
                <h3><?= $data['clientes'] ?></h3>
                <p>Clientes Registrados</p>
            </div>
        </div>
        <div class="card-footer-action">
            <a href="<?= BASE_URL ?>/Cliente">Ver Clientes <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <!-- Card Ventas -->
    <div class="card stat-card blue-light-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-cash-register"></i></div>
            <div class="stat-details">
                <h3><?= $data['ventas'] ?></h3>
                <p>Ventas Realizadas</p>
            </div>
        </div>
        <div class="card-footer-action">
            <a href="<?= BASE_URL ?>/Venta">Registrar Venta <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <!-- Card Ingresos -->
    <div class="card stat-card gold-light-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <div class="stat-details">
                <h3>S/. <?= number_format($data['ingresos'], 2) ?></h3>
                <p>Ingresos Totales</p>
            </div>
        </div>
        <div class="card-footer-action">
            <span>Métricas del Negocio</span>
        </div>
    </div>
</div>

<div class="welcome-box">
    <h2><i class="fa-solid fa-mug-hot"></i> ¡Bienvenido, <?= e($_SESSION['nombre'] ?? '') ?>!</h2>
    <p>Amazon Market está listo para operar. Utiliza el menú lateral para gestionar productos, registrar clientes o iniciar una transacción de venta en el terminal de facturación.</p>
</div>

<?php require_once "Views/Footer.php"; ?>