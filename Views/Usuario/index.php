<?php require_once "Views/Header.php"; ?>

<div class="table-container-header">
    <h3>Directorio de Usuarios</h3>
    <?php if (can('usuarios.crear')): ?>
    <a href="<?= BASE_URL ?>/Usuario/crear" class="btn btn-gold"><i class="fa-solid fa-user-plus"></i> Registrar Usuario</a>
    <?php endif; ?>
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
        <tbody>
            <?php if(!empty($data['usuarios'])): ?>
                <?php foreach($data['usuarios'] as $usu): ?>
                    <tr>
                        <td><?= $usu['id_usuario'] ?></td>
                        <td><span class="badge-neutral"><?= e($usu['rol']) ?></span></td>
                        <td class="font-semibold"><?= e($usu['username']) ?></td>
                        <td><?= e($usu['nombre']) ?></td>
                        <td><?= e($usu['dni']) ?></td>
                        <td><?= !empty($usu['telefono']) ? e($usu['telefono']) : '-' ?></td>
                        <td><?= !empty($usu['email']) ? e($usu['email']) : '-' ?></td>
                        <td><?= !empty($usu['direccion']) ? e($usu['direccion']) : '-' ?></td>
                        <td>
                            <div class="actions-group">
                                <?php if (can('usuarios.editar')): ?>
                                <a href="<?= BASE_URL ?>/Usuario/editar/<?= $usu['id_usuario'] ?>" class="btn btn-edit" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                                <?php endif; ?>
                                <?php if (can('usuarios.eliminar')): ?>
                                <button type="button" class="btn btn-delete" title="Eliminar" onclick="confirmarEliminacion('<?= BASE_URL ?>/Usuario/eliminar/<?= $usu['id_usuario'] ?>', 'usuario')"><i class="fa-solid fa-trash-can"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No hay usuarios registrados en el sistema.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
async function confirmarEliminacion(url, tipo) {
    const result = await Modal.confirm(
        'Confirmar Eliminación',
        `¿Está seguro de dar de baja a este ${tipo}? Esta acción no se puede deshacer.`,
        'danger'
    );
    if (result) {
        window.location.href = url;
    }
}
</script>

<?php require_once "Views/Footer.php"; ?>
