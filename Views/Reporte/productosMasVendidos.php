<?php require_once "Views/Header.php"; ?>

<div class="table-container-header">
    <h3><i class="fa-solid fa-fire" style="color: var(--accent-gold);"></i> Productos Más Vendidos</h3>
    <div class="actions-group">
        <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button>
        <button type="button" class="btn btn-gold" id="btn_exportar_masVendidos"><i class="fa-solid fa-file-excel"></i> Exportar Excel</button>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="form-card-header">
        <h3><i class="fa-solid fa-filter"></i> Filtros</h3>
    </div>
    <form method="GET" action="<?= BASE_URL ?>/Reporte/productosMasVendidos" class="form-grid" style="padding-bottom: 15px;">
        <div class="form-group col-3">
            <label>Fecha Inicio</label>
            <input type="date" name="desde" value="<?= e($filtros['desde'] ?? date('Y-m-01')) ?>">
        </div>
        <div class="form-group col-3">
            <label>Fecha Fin</label>
            <input type="date" name="hasta" value="<?= e($filtros['hasta'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="form-group col-3">
            <label>Categoría</label>
            <select name="id_categoria">
                <option value="">Todas</option>
                <?php foreach ($categorias ?? [] as $cat): ?>
                <option value="<?= $cat['id_categoria'] ?>" <?= ($filtros['id_categoria'] ?? '') == $cat['id_categoria'] ? 'selected' : '' ?>><?= e($cat['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-2">
            <label>Top N</label>
            <select name="topN">
                <option value="10" <?= ($filtros['topN'] ?? 10) == 10 ? 'selected' : '' ?>>Top 10</option>
                <option value="20" <?= ($filtros['topN'] ?? '') == 20 ? 'selected' : '' ?>>Top 20</option>
                <option value="50" <?= ($filtros['topN'] ?? '') == 50 ? 'selected' : '' ?>>Top 50</option>
            </select>
        </div>
        <div class="form-group col-1" style="justify-content: flex-end;">
            <button type="submit" class="btn btn-gold"><i class="fa-solid fa-magnifying-glass"></i></button>
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
            <div class="stat-icon"><i class="fa-solid fa-trophy"></i></div>
            <div class="stat-details">
                <h3><?= count($productos ?? []) ?></h3>
                <p>Productos en Ranking</p>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Unidades Vendidas</th>
                <th>Ingresos</th>
                <th>% Ingresos</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
            <tr><td colspan="6" style="text-align: center; padding: 20px;">No hay datos en el período seleccionado.</td></tr>
            <?php else: ?>
            <?php foreach ($productos as $i => $p): ?>
            <tr>
                <td><span class="badge-accent"><?= $i + 1 ?></span></td>
                <td style="font-weight: 600;"><?= e($p['nombre']) ?></td>
                <td><span class="badge-neutral"><?= e($p['categoria']) ?></span></td>
                <td><?= intval($p['unidades_vendidas']) ?></td>
                <td class="price-text">S/. <?= number_format(floatval($p['ingresos']), 2) ?></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $p['porcentaje_ingresos'] ?>%; background: var(--accent-gold); border-radius: 4px;"></div>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; min-width: 40px;"><?= $p['porcentaje_ingresos'] ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
const exportParamsMasVendidos = <?= json_encode(array_filter($filtros ?? [], fn($v) => $v !== '')) ?>;
</script>

<?php require_once "Views/Footer.php"; ?>
