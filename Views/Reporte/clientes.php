<?php require_once "Views/Header.php"; ?>

<div class="table-container-header">
    <h3><i class="fa-solid fa-users" style="color: var(--accent-gold);"></i> Reporte de Clientes</h3>
    <div class="actions-group">
        <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button>
        <button type="button" class="btn btn-gold" id="btn_exportar_clientes"><i class="fa-solid fa-file-excel"></i> Exportar Excel</button>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="form-card-header">
        <h3><i class="fa-solid fa-filter"></i> Filtros</h3>
    </div>
    <form method="GET" action="<?= BASE_URL ?>/Reporte/clientes" class="form-grid" style="padding-bottom: 15px;">
        <div class="form-group col-3">
            <label>Fecha Inicio</label>
            <input type="date" name="desde" value="<?= e($filtros['desde'] ?? date('Y-m-01')) ?>">
        </div>
        <div class="form-group col-3">
            <label>Fecha Fin</label>
            <input type="date" name="hasta" value="<?= e($filtros['hasta'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="form-group col-2">
            <label>Top N</label>
            <select name="topN">
                <option value="10" <?= ($filtros['topN'] ?? 20) == 10 ? 'selected' : '' ?>>Top 10</option>
                <option value="20" <?= ($filtros['topN'] ?? 20) == 20 ? 'selected' : '' ?>>Top 20</option>
                <option value="50" <?= ($filtros['topN'] ?? '') == 50 ? 'selected' : '' ?>>Top 50</option>
            </select>
        </div>
        <div class="form-group col-2" style="justify-content: flex-end;">
            <button type="submit" class="btn btn-gold"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
        </div>
    </form>
</div>

<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
    <div class="card" style="padding: 20px;">
        <h4 style="font-size: 14px; color: var(--text-secondary); margin-bottom: 12px;"><i class="fa-solid fa-chart-pie"></i> Distribución de Clientes</h4>
        <div style="display: flex; gap: 12px;">
            <?php foreach ($distribucion ?? [] as $d): ?>
            <div style="flex: 1; text-align: center; padding: 12px; background: <?= $d['tipo'] === 'Nuevos' ? 'var(--accent-gold-light)' : '#dbeafe' ?>; border-radius: var(--radius-md);">
                <div style="font-size: 22px; font-weight: 800; color: <?= $d['tipo'] === 'Nuevos' ? '#a18115' : '#2563eb' ?>;"><?= intval($d['cantidad']) ?></div>
                <div style="font-size: 12px; font-weight: 600; color: var(--text-secondary);"><?= e($d['tipo']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="table-responsive" style="margin-top: 20px;">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Documento</th>
                <th># Compras</th>
                <th>Total Gastado</th>
                <th>Ticket Promedio</th>
                <th>Última Compra</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clientes)): ?>
            <tr><td colspan="7" style="text-align: center; padding: 20px;">No hay datos de clientes en el período.</td></tr>
            <?php else: ?>
            <?php foreach ($clientes as $i => $c): ?>
            <tr>
                <td><span class="badge-accent"><?= $i + 1 ?></span></td>
                <td style="font-weight: 600;"><?= e($c['nombre']) ?></td>
                <td><span class="barcode-badge"><?= e($c['tipo_doc'] . ' ' . $c['nro_documento']) ?></span></td>
                <td><?= intval($c['num_compras']) ?></td>
                <td class="price-text">S/. <?= number_format(floatval($c['total_gastado']), 2) ?></td>
                <td>S/. <?= number_format(floatval($c['ticket_promedio']), 2) ?></td>
                <td><?= e(date('d/m/Y', strtotime($c['ultima_compra']))) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
const exportParamsClientes = <?= json_encode(array_filter($filtros ?? [], fn($v) => $v !== '')) ?>;
</script>

<?php require_once "Views/Footer.php"; ?>
