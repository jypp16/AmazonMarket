<?php require_once "Views/Header.php"; ?>

<div class="table-container-header">
    <h3>Inventario de Productos</h3>
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="position: relative; display: flex; align-items: center;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; color: var(--text-secondary); pointer-events: none; font-size: 13px;"></i>
            <input type="text" id="busqueda_producto" placeholder="Buscar producto..." style="padding: 8px 12px 8px 34px; border: 1.5px solid #cbd5e1; border-radius: var(--radius-md); font-size: 13px; font-weight: 500; width: 220px; outline: none;">
        </div>
        <div style="position: relative; display: flex; align-items: center;">
            <i class="fa-solid fa-filter" style="position: absolute; left: 12px; color: var(--text-secondary); pointer-events: none; font-size: 13px;"></i>
            <select id="filtro_categoria" style="padding: 8px 12px 8px 34px; border: 1.5px solid #cbd5e1; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; background: white; cursor: pointer; outline: none;">
                <option value="">Todas las categorías</option>
            </select>
        </div>
        <?php if (can('productos.crear')): ?>
        <button type="button" id="btn_crear_producto" class="btn btn-gold"><i class="fa-solid fa-circle-plus"></i> Registrar Producto</button>
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
            <tr>
                <td colspan="7" class="text-center">Cargando productos...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
const canEdit = <?= can('productos.editar') ? 'true' : 'false' ?>;
const canDelete = <?= can('productos.eliminar') ? 'true' : 'false' ?>;
</script>

<?php require_once "Views/Footer.php"; ?>
