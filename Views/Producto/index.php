<?php require_once "Views/Header.php"; ?>

<div class="table-container-header">
    <h3>Inventario de Productos</h3>
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="position: relative; display: flex; align-items: center;">
            <i class="fa-solid fa-filter" style="position: absolute; left: 12px; color: var(--text-secondary); pointer-events: none; font-size: 13px;"></i>
            <select id="filtro_categoria" onchange="filtrarProductos()" style="padding: 8px 12px 8px 34px; border: 1.5px solid #cbd5e1; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; background: white; cursor: pointer; outline: none;">
                <option value="">Todas las categorías</option>
                <?php
                $cats = [];
                foreach($data['productos'] as $p) {
                    $cats[$p['categoria']] = $p['categoria'];
                }
                foreach($cats as $cat): ?>
                    <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (can('productos.crear')): ?>
        <a href="<?= BASE_URL ?>/Producto/crear" class="btn btn-gold"><i class="fa-solid fa-circle-plus"></i> Registrar Producto</a>
        <?php endif; ?>
    </div>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código de Barras</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Precio Venta</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tabla_productos">
            <?php if(!empty($data['productos'])): ?>
                <?php foreach($data['productos'] as $prod): ?>
                    <tr data-categoria="<?= e($prod['categoria']) ?>">
                        <td><?= $prod['id_producto'] ?></td>
                        <td><span class="barcode-badge"><?= e($prod['codigo_barra']) ?></span></td>
                        <td class="font-semibold"><?= e($prod['nombre']) ?></td>
                        <td><span class="badge-neutral"><?= e($prod['categoria']) ?></span></td>
                        <td>
                            <?php if($prod['stock_actual'] <= $prod['stock_minimo']): ?>
                                <span class="stock-badge stock-low"><?= number_format($prod['stock_actual'], 2) ?> <?= e($prod['unidad']) ?> (Bajo)</span>
                            <?php else: ?>
                                <span class="stock-badge stock-normal"><?= number_format($prod['stock_actual'], 2) ?> <?= e($prod['unidad']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="price-text">S/. <?= number_format($prod['precio_venta'], 2) ?></td>
                        <td>
                            <div class="actions-group">
                                <?php if (can('productos.editar')): ?>
                                <a href="<?= BASE_URL ?>/Producto/editar/<?= $prod['id_producto'] ?>" class="btn btn-edit" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                                <?php endif; ?>
                                <?php if (can('productos.eliminar')): ?>
                                <button type="button" class="btn btn-delete" title="Eliminar" onclick="confirmarEliminacion('<?= BASE_URL ?>/Producto/eliminar/<?= $prod['id_producto'] ?>', 'producto')"><i class="fa-solid fa-trash-can"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr id="row_vacia">
                    <td colspan="7" class="text-center">No hay productos registrados en el inventario.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function filtrarProductos() {
    const filtro = document.getElementById('filtro_categoria').value.toLowerCase();
    const filas = document.querySelectorAll('#tabla_productos tr[data-categoria]');
    let visibles = 0;

    filas.forEach(fila => {
        const cat = fila.getAttribute('data-categoria').toLowerCase();
        if (!filtro || cat === filtro) {
            fila.style.display = '';
            visibles++;
        } else {
            fila.style.display = 'none';
        }
    });

    let rowVacia = document.getElementById('row_vacia_filter');
    if (visibles === 0 && !document.getElementById('row_vacia')) {
        if (!rowVacia) {
            const tbody = document.getElementById('tabla_productos');
            rowVacia = document.createElement('tr');
            rowVacia.id = 'row_vacia_filter';
            rowVacia.innerHTML = '<td colspan="7" class="text-center">No hay productos en esta categoría.</td>';
            tbody.appendChild(rowVacia);
        }
    } else if (rowVacia) {
        rowVacia.remove();
    }
}

async function confirmarEliminacion(url, tipo) {
    const result = await Modal.confirm(
        'Confirmar Eliminación',
        `¿Está seguro de eliminar este ${tipo} del sistema? Esta acción no se puede deshacer.`,
        'danger'
    );
    if (result) {
        window.location.href = url;
    }
}
</script>

<?php require_once "Views/Footer.php"; ?>
