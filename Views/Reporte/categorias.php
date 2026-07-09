<?php require_once "Views/Header.php"; ?>

<div class="table-container-header">
    <h3><i class="fa-solid fa-tags" style="color: var(--accent-gold);"></i> Ventas por Categoría</h3>
    <div class="actions-group">
        <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button>
        <button type="button" class="btn btn-gold" id="btn_exportar_categorias"><i class="fa-solid fa-file-excel"></i> Exportar Excel</button>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="form-card-header">
        <h3><i class="fa-solid fa-filter"></i> Filtros</h3>
    </div>
    <form method="GET" action="<?= BASE_URL ?>/Reporte/categorias" class="form-grid" style="padding-bottom: 15px;">
        <div class="form-group col-4">
            <label>Fecha Inicio</label>
            <input type="date" name="desde" value="<?= e($filtros['desde'] ?? date('Y-m-01')) ?>">
        </div>
        <div class="form-group col-4">
            <label>Fecha Fin</label>
            <input type="date" name="hasta" value="<?= e($filtros['hasta'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="form-group col-4" style="justify-content: flex-end;">
            <button type="submit" class="btn btn-gold"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
        </div>
    </form>
</div>

<div class="dashboard-grid">
    <div class="stat-card card gold-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
            <div class="stat-details">
                <h3>S/. <?= number_format(floatval($totalIngresos ?? 0), 2) ?></h3>
                <p>Ingresos Totales</p>
            </div>
        </div>
    </div>
    <div class="stat-card card blue-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
            <div class="stat-details">
                <h3><?= intval($totalUnidades ?? 0) ?></h3>
                <p>Unidades Totales</p>
            </div>
        </div>
    </div>
    <div class="stat-card card blue-light-card">
        <div class="card-body">
            <div class="stat-icon"><i class="fa-solid fa-tags"></i></div>
            <div class="stat-details">
                <h3><?= count($categorias ?? []) ?></h3>
                <p>Categorías con Ventas</p>
            </div>
        </div>
    </div>
</div>

<div id="chart_categorias_barras" class="chart-container" style="margin-bottom: 20px;"></div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Categoría</th>
                <th>Unidades</th>
                <th>Ingresos</th>
                <th>% del Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categorias)): ?>
            <tr><td colspan="5" style="text-align: center; padding: 20px;">No hay datos en el período.</td></tr>
            <?php else: ?>
            <?php foreach ($categorias as $i => $c): ?>
            <tr>
                <td><span class="badge-accent"><?= $i + 1 ?></span></td>
                <td style="font-weight: 600;"><?= e($c['categoria']) ?></td>
                <td><?= intval($c['unidades']) ?></td>
                <td class="price-text">S/. <?= number_format(floatval($c['ingresos']), 2) ?></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $c['porcentaje'] ?>%; background: var(--accent-gold); border-radius: 4px;"></div>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; min-width: 40px;"><?= $c['porcentaje'] ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
const chartDataCategorias = <?= json_encode($categorias ?? []) ?>;
const exportParamsCategorias = <?= json_encode(array_filter($filtros ?? [], fn($v) => $v !== '')) ?>;
</script>

<?php require_once "Views/Footer.php"; ?>
