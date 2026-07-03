<?php require_once "Views/Header.php"; ?>

<div class="table-container-header">
    <h3>Directorio de Clientes</h3>
    <?php if (can('clientes.crear')): ?>
    <a href="<?= BASE_URL ?>/Cliente/crear" class="btn btn-gold"><i class="fa-solid fa-user-plus"></i> Registrar Cliente</a>
    <?php endif; ?>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo Doc.</th>
                <th>Nro Documento</th>
                <th>Nombre del Cliente</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($data['clientes'])): ?>
                <?php foreach($data['clientes'] as $cli): ?>
                    <tr>
                        <td><?= $cli['id_cliente'] ?></td>
                        <td><span class="document-badge"><?= e($cli['tipo_documento']) ?></span></td>
                        <td class="font-semibold"><?= e($cli['nro_documento']) ?></td>
                        <td class="font-semibold"><?= e($cli['nombre']) ?></td>
                        <td><?= !empty($cli['telefono']) ? e($cli['telefono']) : '-' ?></td>
                        <td><?= !empty($cli['email']) ? e($cli['email']) : '-' ?></td>
                        <td><?= !empty($cli['direccion']) ? e($cli['direccion']) : '-' ?></td>
                        <td>
                            <div class="actions-group">
                                <?php if (can('clientes.editar')): ?>
                                <a href="<?= BASE_URL ?>/Cliente/editar/<?= $cli['id_cliente'] ?>" class="btn btn-edit" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                                <?php endif; ?>
                                <?php if (can('clientes.eliminar')): ?>
                                <button type="button" class="btn btn-delete" title="Eliminar" onclick="confirmarEliminacion('<?= BASE_URL ?>/Cliente/eliminar/<?= $cli['id_cliente'] ?>', 'cliente')"><i class="fa-solid fa-trash-can"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No hay clientes registrados en el directorio.</td>
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
