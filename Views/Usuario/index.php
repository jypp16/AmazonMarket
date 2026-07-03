<?php require_once "Views/Header.php"; ?>

<div class="table-container-header">
    <h3>Directorio de Usuarios</h3>
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="position: relative; display: flex; align-items: center;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; color: var(--text-secondary); pointer-events: none; font-size: 13px;"></i>
            <input type="text" id="busqueda_usuario" placeholder="Buscar usuario..." style="padding: 8px 12px 8px 34px; border: 1.5px solid #cbd5e1; border-radius: var(--radius-md); font-size: 13px; font-weight: 500; width: 220px; outline: none;">
        </div>
        <?php if (can('usuarios.crear')): ?>
        <button type="button" id="btn_crear_usuario" class="btn btn-gold"><i class="fa-solid fa-user-plus"></i> Registrar Usuario</button>
        <?php endif; ?>
    </div>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Rol</th>
                <th>Username</th>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tabla_usuarios">
            <tr>
                <td colspan="9" class="text-center">Cargando usuarios...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
const canEdit = <?= can('usuarios.editar') ? 'true' : 'false' ?>;
const canDelete = <?= can('usuarios.eliminar') ? 'true' : 'false' ?>;
</script>

<?php require_once "Views/Footer.php"; ?>
