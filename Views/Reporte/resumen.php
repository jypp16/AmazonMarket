<?php require_once "Views/Header.php"; ?>

<div class="table-container-header">
    <h3><i class="fa-solid fa-file-pdf" style="color: var(--accent-gold);"></i> Resumen Ejecutivo</h3>
    <div class="actions-group">
        <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir / PDF</button>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="form-card-header">
        <h3><i class="fa-solid fa-calendar"></i> Período: <?= e($filtros['desde'] ?? date('Y-m-01')) ?> al <?= e($filtros['hasta'] ?? date('Y-m-d')) ?></h3>
    </div>
    <form method="GET" action="<?= BASE_URL ?>/Reporte/resumen" class="form-grid" style="padding-bottom: 15px;">
        <div class="form-group col-4">
            <label>Fecha Inicio</label>
            <input type="date" name="desde" value="<?= e($filtros['desde'] ?? date('Y-m-01')) ?>">
        </div>
        <div class="form-group col-4">
            <label>Fecha Fin</label>
            <input type="date" name="hasta" value="<?= e($filtros['hasta'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="form-group col-4" style="justify-content: flex-end;">
            <button type="submit" class="btn btn-gold"><i class="fa-solid fa-magnifying-glass"></i> Actualizar</button>
        </div>
    </form>
</div>

<div class="dashboard-grid">
    <div class="stat-card card gold-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
            <div class="stat-details">
                <h3>S/. <?= number_format(floatval($ventas['resumen']['total_ingresos'] ?? 0), 2) ?></h3>
                <p>Ingresos Totales</p>
            </div>
        </div>
    </div>
    <div class="stat-card card blue-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
            <div class="stat-details">
                <h3><?= intval($ventas['resumen']['total_ventas'] ?? 0) ?></h3>
                <p>Total Ventas</p>
            </div>
        </div>
    </div>
    <div class="stat-card card blue-light-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
            <div class="stat-details">
                <h3>S/. <?= number_format(floatval($ventas['resumen']['ticket_promedio'] ?? 0), 2) ?></h3>
                <p>Ticket Promedio</p>
            </div>
        </div>
    </div>
    <div class="stat-card card gold-light-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-warehouse"></i></div>
            <div class="stat-details">
                <h3>S/. <?= number_format(floatval($inventario['resumen']['valor_total'] ?? 0), 2) ?></h3>
                <p>Valor Inventario</p>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    <div class="card" style="padding: 20px;">
        <h4 style="font-size: 14px; color: var(--primary-color); margin-bottom: 14px; font-weight: 700;">
            <i class="fa-solid fa-fire" style="color: var(--accent-gold);"></i> Top 5 Productos
        </h4>
        <table class="table" style="margin: 0;">
            <thead>
                <tr><th>#</th><th>Producto</th><th>Ingresos</th></tr>
            </thead>
            <tbody>
                <?php foreach (($masVendidos['productos'] ?? []) as $i => $p): ?>
                <tr>
                    <td><span class="badge-accent"><?= $i + 1 ?></span></td>
                    <td style="font-weight: 600;"><?= e($p['nombre']) ?></td>
                    <td class="price-text">S/. <?= number_format(floatval($p['ingresos']), 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="padding: 20px;">
        <h4 style="font-size: 14px; color: var(--primary-color); margin-bottom: 14px; font-weight: 700;">
            <i class="fa-solid fa-users" style="color: var(--accent-gold);"></i> Top 3 Clientes
        </h4>
        <table class="table" style="margin: 0;">
            <thead>
                <tr><th>#</th><th>Cliente</th><th>Total Gastado</th></tr>
            </thead>
            <tbody>
                <?php foreach (($topClientes['clientes'] ?? []) as $i => $c): ?>
                <tr>
                    <td><span class="badge-accent"><?= $i + 1 ?></span></td>
                    <td style="font-weight: 600;"><?= e($c['nombre']) ?></td>
                    <td class="price-text">S/. <?= number_format(floatval($c['total_gastado']), 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    <div class="card" style="padding: 20px;">
        <h4 style="font-size: 14px; color: var(--primary-color); margin-bottom: 14px; font-weight: 700;">
            <i class="fa-solid fa-user-tie" style="color: var(--accent-gold);"></i> Rendimiento Vendedores
        </h4>
        <table class="table" style="margin: 0;">
            <thead>
                <tr><th>Vendedor</th><th># Ventas</th><th>Ingresos</th><th>%</th></tr>
            </thead>
            <tbody>
                <?php foreach (($vendedores['vendedores'] ?? []) as $v): ?>
                <tr>
                    <td style="font-weight: 600;"><?= e($v['vendedor']) ?></td>
                    <td><?= intval($v['num_ventas']) ?></td>
                    <td class="price-text">S/. <?= number_format(floatval($v['ingreso_total']), 2) ?></td>
                    <td><?= $v['porcentaje'] ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="padding: 20px;">
        <h4 style="font-size: 14px; color: var(--primary-color); margin-bottom: 14px; font-weight: 700;">
            <i class="fa-solid fa-credit-card" style="color: var(--accent-gold);"></i> Métodos de Pago
        </h4>
        <table class="table" style="margin: 0;">
            <thead>
                <tr><th>Método</th><th># Transacciones</th><th>Monto</th><th>%</th></tr>
            </thead>
            <tbody>
                <?php foreach (($metodos['metodos'] ?? []) as $m): ?>
                <tr>
                    <td style="font-weight: 600;"><?= e($m['metodo_pago']) ?></td>
                    <td><?= intval($m['num_transacciones']) ?></td>
                    <td class="price-text">S/. <?= number_format(floatval($m['monto_total']), 2) ?></td>
                    <td><?= $m['porcentaje'] ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (intval($inventario['resumen']['productos_stock_bajo'] ?? 0) > 0): ?>
<div class="card" style="padding: 20px; border-left: 4px solid var(--color-danger); margin-bottom: 20px;">
    <h4 style="font-size: 14px; color: var(--color-danger); margin-bottom: 10px; font-weight: 700;">
        <i class="fa-solid fa-triangle-exclamation"></i> Alertas de Stock Bajo (<?= intval($inventario['resumen']['productos_stock_bajo']) ?> productos)
    </h4>
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        <?php foreach (($inventario['productos'] ?? []) as $p): ?>
        <?php if ($p['stock_bajo']): ?>
        <span class="stock-badge stock-low"><?= e($p['nombre']) ?> (<?= number_format(floatval($p['stock_actual']), 2) ?>)</span>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once "Views/Footer.php"; ?>
