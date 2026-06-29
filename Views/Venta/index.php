<?php require_once "Views/Header.php"; ?>

<div class="pos-layout">
    <!-- Panel Izquierdo: Selección de Cliente, Comprobante y Selección de Producto -->
    <div class="pos-left-panel">
        <!-- Tarjeta de Cliente y Documento -->
        <div class="form-container-card">
            <div class="form-card-header">
                <h3><i class="fa-solid fa-file-invoice"></i> Datos del Comprobante</h3>
            </div>
            <div class="form-grid pad-15">
                <div class="form-group col-12">
                    <label for="id_cliente">Cliente <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-user"></i>
                        <select id="id_cliente" required>
                            <option value="">-- Seleccionar Cliente --</option>
                            <?php foreach($data['clientes'] as $cli): ?>
                                <option value="<?= $cli['id_cliente'] ?>" data-tipo-doc="<?= $cli['id_tipo_documento'] ?>">
                                    <?= htmlspecialchars($cli['nombre']) ?> (<?= $cli['nro_documento'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <small id="cliente-hint" style="color: var(--text-secondary); font-size: 12px; margin-top: 4px; display: none;">
                        <i class="fa-solid fa-info-circle"></i> Para factura, seleccione un cliente con RUC.
                    </small>
                </div>

                <div class="form-group col-6">
                    <label for="id_tipo_comprobante">Comprobante <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-receipt"></i>
                        <select id="id_tipo_comprobante" required>
                            <?php foreach($data['comprobantes'] as $comp): ?>
                                <option value="<?= $comp['id_tipo_comprobante'] ?>"><?= $comp['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group col-6">
                    <label for="id_metodo_pago">Método de Pago <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-credit-card"></i>
                        <select id="id_metodo_pago" required>
                            <?php foreach($data['pagos'] as $pago): ?>
                                <option value="<?= $pago['id_metodo_pago'] ?>"><?= $pago['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Agregar Productos -->
        <div class="form-container-card margin-top-20">
            <div class="form-card-header">
                <h3><i class="fa-solid fa-cart-plus"></i> Añadir Producto</h3>
            </div>
            <div class="form-grid pad-15">
                <div class="form-group col-12">
                    <label for="filtro_categoria_venta">Categoría</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-filter"></i>
                        <select id="filtro_categoria_venta" onchange="filtrarProductosVenta()">
                            <option value="">Todas</option>
                            <?php foreach($data['categorias'] as $cat): ?>
                                <option value="<?= e($cat['nombre']) ?>"><?= e($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group col-12">
                    <label for="select_producto">Producto <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-box-open"></i>
                        <select id="select_producto" onchange="actualizarInfoProducto()">
                            <option value="">-- Seleccionar Producto --</option>
                            <?php foreach($data['productos'] as $prod): ?>
                                <option value="<?= $prod['id_producto'] ?>" data-categoria="<?= e($prod['categoria']) ?>">
                                    <?= htmlspecialchars($prod['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group col-6">
                    <label>Stock Disponible</label>
                    <div class="info-badge-container">
                        <span id="stock_info" class="badge-neutral"><i class="fa-solid fa-cubes"></i> --</span>
                    </div>
                </div>

                <div class="form-group col-6">
                    <label>Precio Unitario</label>
                    <div class="info-badge-container">
                        <span id="precio_info" class="badge-neutral"><i class="fa-solid fa-tags"></i> --</span>
                    </div>
                </div>

                <div class="form-group col-8">
                    <label for="cantidad_producto">Cantidad <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-calculator"></i>
                        <input type="number" id="cantidad_producto" min="0.01" step="0.01" placeholder="0.00">
                    </div>
                </div>

                <div class="form-group col-4" style="display: flex; align-items: flex-end;">
                    <button type="button" class="btn btn-gold btn-full-width" onclick="agregarAlCarrito()">
                        <i class="fa-solid fa-plus"></i> Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Derecho: Carrito de Compras y Checkout -->
    <div class="pos-right-panel">
        <div class="form-container-card" style="height: 100%; display: flex; flex-direction: column;">
            <div class="form-card-header">
                <h3><i class="fa-solid fa-shopping-cart"></i> Detalle de la Venta</h3>
                <span class="badge-accent" id="cart_count_badge">0 items</span>
            </div>

            <!-- Tabla de items del carrito -->
            <div class="cart-items-wrapper flex-grow-1">
                <table class="table table-pos">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-right">Precio</th>
                            <th class="text-center" style="width: 100px;">Cant.</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-center" style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="cart_body">
                        <tr>
                            <td colspan="5" class="text-center empty-cart-msg">
                                <i class="fa-solid fa-basket-shopping"></i> El carrito está vacío. Agregue productos.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Panel de Totales y Botón de Cobro -->
            <div class="checkout-panel">
                <div class="totals-summary">
                    <div class="summary-row">
                        <span>Subtotal (Sin IGV):</span>
                        <span id="lbl_subtotal">S/. 0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>IGV (18% Incluido):</span>
                        <span id="lbl_igv">S/. 0.00</span>
                    </div>
                    <div class="summary-row total-highlight">
                        <span>TOTAL A PAGAR:</span>
                        <span id="lbl_total">S/. 0.00</span>
                    </div>
                </div>

                <button type="button" class="btn btn-checkout btn-full-width margin-top-15" onclick="procesarVenta()">
                    <i class="fa-solid fa-cash-register"></i> REGISTRAR Y PROCESAR VENTA
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal / Overlay de Procesamiento de Carga -->
<div id="loader_overlay" class="loader-overlay" style="display: none;">
    <div class="loader-content">
        <i class="fa-solid fa-circle-notch fa-spin fa-3x gold-text"></i>
        <p class="margin-top-15">Procesando, espere por favor...</p>
    </div>
</div>

<!-- Inyección de URL Base para JS -->
<script>
    var BaseUrl = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/Assets/js/venta.js"></script>

<?php require_once "Views/Footer.php"; ?>
