let carrito = [];
let productosCache = {};

// =====================================================
// Filtrado de clientes según tipo de comprobante
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    const selectComprobante = document.getElementById('id_tipo_comprobante');
    const selectCliente = document.getElementById('id_cliente');
    const hint = document.getElementById('cliente-hint');

    if (selectComprobante && selectCliente) {
        selectComprobante.addEventListener('change', function() {
            filtrarClientes();
        });
        filtrarClientes();
    }

    function filtrarClientes() {
        const idComprobante = selectComprobante.value;
        const esFactura = idComprobante == '2';
        const options = selectCliente.querySelectorAll('option[value]');
        let primerVisible = null;

        options.forEach(opt => {
            if (opt.value === '') return;
            const tipoDoc = opt.getAttribute('data-tipo-doc');
            if (esFactura) {
                const visible = tipoDoc == '2';
                opt.style.display = visible ? '' : 'none';
                if (visible && !primerVisible) primerVisible = opt;
            } else {
                opt.style.display = '';
                if (!primerVisible) primerVisible = opt;
            }
        });

        const currentVal = selectCliente.value;
        const currentOption = selectCliente.querySelector(`option[value="${currentVal}"]`);
        if (currentOption && currentOption.style.display === 'none') {
            selectCliente.value = '';
        }

        if (esFactura) {
            hint.style.display = 'block';
        } else {
            hint.style.display = 'none';
        }
    }
});

// =====================================================
// Filtrado de productos por categoría en el POS
// =====================================================
function filtrarProductosVenta() {
    const filtro = document.getElementById('filtro_categoria_venta').value;
    const selectProducto = document.getElementById('select_producto');
    const options = selectProducto.querySelectorAll('option[value]');
    let primerVisible = null;

    options.forEach(opt => {
        if (opt.value === '') return;
        const cat = opt.getAttribute('data-categoria');
        if (!filtro || cat === filtro) {
            opt.style.display = '';
            if (!primerVisible) primerVisible = opt;
        } else {
            opt.style.display = 'none';
        }
    });

    const currentVal = selectProducto.value;
    const currentOption = selectProducto.querySelector(`option[value="${currentVal}"]`);
    if (currentOption && currentOption.style.display === 'none') {
        selectProducto.value = '';
    }
}

async function obtenerProducto(id_producto) {
    if (!id_producto) return null;
    if (productosCache[id_producto]) {
        return productosCache[id_producto];
    }

    try {
        const response = await fetch(`${BaseUrl}/Producto/detalle/${id_producto}`, {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            return null;
        }
        
        const result = await response.json();
        
        if (result.status && result.data) {
            productosCache[id_producto] = result.data;
            return result.data;
        } else {
            return null;
        }
    } catch (error) {
        console.error("Error al obtener producto:", error);
        return null;
    }
}

async function actualizarInfoProducto() {
    const id_producto = document.getElementById("select_producto").value;
    const stock_info = document.getElementById("stock_info");
    const precio_info = document.getElementById("precio_info");
    const cantidad_input = document.getElementById("cantidad_producto");

    stock_info.innerHTML = '<i class="fa-solid fa-cubes"></i> --';
    stock_info.className = "badge-neutral";
    precio_info.innerHTML = '<i class="fa-solid fa-tags"></i> --';
    precio_info.className = "badge-neutral";
    cantidad_input.value = "";
    cantidad_input.removeAttribute('step');
    cantidad_input.removeAttribute('min');

    if (!id_producto) {
        return;
    }

    stock_info.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cargando...';

    const producto = await obtenerProducto(id_producto);

    if (producto) {
        const stock = parseFloat(producto.stock_actual);
        const precio = parseFloat(producto.precio_venta);
        const minStock = parseFloat(producto.stock_minimo);

        stock_info.innerHTML = '<i class="fa-solid fa-cubes"></i> ' + stock.toFixed(2) + " " + producto.unidad;
        if (stock <= minStock) {
            stock_info.className = "stock-badge stock-low";
        } else {
            stock_info.className = "stock-badge stock-normal";
        }

        precio_info.innerHTML = '<i class="fa-solid fa-tags"></i> S/. ' + precio.toFixed(2);
        precio_info.className = "badge-accent";

        const unidad = producto.unidad ? producto.unidad.toLowerCase().trim() : '';
        const unidadesDecimales = ['kg', 'lt', 'lb', 'gal', 'm', 'cm', 'ml', 'g', 'oz'];
        let esDecimal = false;
        for (let ud of unidadesDecimales) {
            if (unidad.includes(ud)) {
                esDecimal = true;
                break;
            }
        }

        if (esDecimal) {
            cantidad_input.step = "0.01";
            cantidad_input.placeholder = "0.00";
            cantidad_input.value = "1.00";
        } else {
            cantidad_input.step = "1";
            cantidad_input.min = "1";
            cantidad_input.placeholder = "0";
            cantidad_input.value = "1";
        }

        cantidad_input.focus();
    } else {
        stock_info.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Error';
        stock_info.className = "stock-badge stock-low";
    }
}

async function agregarAlCarrito() {
    const id_producto = document.getElementById("select_producto").value;
    const cantidad_raw = document.getElementById("cantidad_producto").value;
    const cantidad = parseFloat(cantidad_raw);

    if (!id_producto) {
        await Modal.warning('Campo Requerido', 'Por favor, seleccione un producto.');
        return;
    }

    if (isNaN(cantidad) || cantidad <= 0) {
        await Modal.warning('Cantidad Inválida', 'La cantidad debe ser un número positivo mayor que cero.');
        return;
    }

    const prod = await obtenerProducto(id_producto);
    if (!prod) {
        await Modal.error('Error', 'No se pudieron cargar los datos del producto. Intente nuevamente.');
        return;
    }

    const stockDisponible = parseFloat(prod.stock_actual);

    const itemExistente = carrito.find(item => item.id_producto == id_producto);
    const cantidadEnCarrito = itemExistente ? itemExistente.cantidad : 0;
    const nuevaCantidadTotal = cantidadEnCarrito + cantidad;

    if (nuevaCantidadTotal > stockDisponible) {
        await Modal.warning('Stock Insuficiente', `Solo quedan ${stockDisponible.toFixed(2)} ${prod.unidad} de este producto.`);
        return;
    }

    if (itemExistente) {
        itemExistente.cantidad = nuevaCantidadTotal;
        itemExistente.subtotal = itemExistente.cantidad * itemExistente.precio_venta;
    } else {
        carrito.push({
            id_producto: prod.id_producto,
            nombre: prod.nombre,
            unidad: prod.unidad,
            precio_venta: parseFloat(prod.precio_venta),
            cantidad: cantidad,
            subtotal: cantidad * parseFloat(prod.precio_venta)
        });
    }

    document.getElementById("select_producto").value = "";
    actualizarInfoProducto();
    renderizarCarrito();
}

function eliminarDelCarrito(index) {
    carrito.splice(index, 1);
    renderizarCarrito();
}

async function actualizarCantidadCarrito(index, nuevaCant) {
    const cantidad = parseFloat(nuevaCant);
    if (isNaN(cantidad) || cantidad <= 0) {
        await Modal.warning('Cantidad Inválida', 'Ingrese una cantidad válida mayor a cero.');
        renderizarCarrito();
        return;
    }

    const item = carrito[index];
    const prod = await obtenerProducto(item.id_producto);
    if (!prod) {
        await Modal.error('Error', 'Error al verificar stock. Intente nuevamente.');
        renderizarCarrito();
        return;
    }
    
    const stockDisponible = parseFloat(prod.stock_actual);

    if (cantidad > stockDisponible) {
        await Modal.warning('Stock Insuficiente', `El stock actual de este producto es ${stockDisponible.toFixed(2)}.`);
        renderizarCarrito();
        return;
    }

    item.cantidad = cantidad;
    item.subtotal = cantidad * item.precio_venta;
    renderizarCarrito();
}

function renderizarCarrito() {
    const cart_body = document.getElementById("cart_body");
    const cart_count_badge = document.getElementById("cart_count_badge");

    if (carrito.length === 0) {
        cart_body.innerHTML = `
            <tr>
                <td colspan="5" class="text-center empty-cart-msg">
                    <i class="fa-solid fa-basket-shopping"></i> El carrito está vacío. Agregue productos.
                </td>
            </tr>
        `;
        cart_count_badge.innerText = "0 items";
        calcularTotales();
        return;
    }

    cart_count_badge.innerText = `${carrito.length} item${carrito.length > 1 ? 's' : ''}`;
    let html = "";

    carrito.forEach((item, index) => {
        html += `
            <tr>
                <td>
                    <span class="font-semibold text-blue">${item.nombre}</span>
                </td>
                <td class="text-right">S/. ${item.precio_venta.toFixed(2)}</td>
                <td class="text-center">
                    <input type="number" 
                           value="${item.cantidad}" 
                           min="0.01" 
                           step="0.01" 
                           class="table-quantity-input" 
                           onchange="actualizarCantidadCarrito(${index}, this.value)">
                    <span style="font-size: 11px; color: #777; display:block;">${item.unidad}</span>
                </td>
                <td class="text-right font-semibold price-text">S/. ${item.subtotal.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn-delete-cart" onclick="eliminarDelCarrito(${index})">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    cart_body.innerHTML = html;
    calcularTotales();
}

function calcularTotales() {
    let total = 0;
    carrito.forEach(item => {
        total += item.subtotal;
    });

    const subtotal = total / 1.18;
    const igv = total - subtotal;

    document.getElementById("lbl_total").innerText = "S/. " + total.toFixed(2);
    document.getElementById("lbl_subtotal").innerText = "S/. " + subtotal.toFixed(2);
    document.getElementById("lbl_igv").innerText = "S/. " + igv.toFixed(2);
}

async function procesarVenta() {
    const id_cliente = document.getElementById("id_cliente").value;
    const id_tipo_comprobante = document.getElementById("id_tipo_comprobante").value;
    const id_metodo_pago = document.getElementById("id_metodo_pago").value;

    if (!id_cliente) {
        await Modal.warning('Campo Requerido', 'Por favor, seleccione un cliente.');
        return;
    }

    if (carrito.length === 0) {
        await Modal.warning('Carrito Vacío', 'El carrito está vacío. Agregue al menos un producto.');
        return;
    }

    const confirmado = await Modal.confirm(
        'Procesar Venta',
        '¿Está seguro de procesar esta venta en el sistema? El stock se actualizará de forma atómica e irreversible.',
        'warning'
    );

    if (!confirmado) {
        return;
    }

    const loader = document.getElementById("loader_overlay");
    loader.style.display = "flex";

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.content : '';

    const payload = {
        csrf_token: csrfToken,
        id_cliente: parseInt(id_cliente),
        id_tipo_comprobante: parseInt(id_tipo_comprobante),
        id_metodo_pago: parseInt(id_metodo_pago),
        productos: carrito.map(item => ({
            id_producto: item.id_producto,
            cantidad: item.cantidad
        }))
    };

    fetch(`${BaseUrl}/Venta/guardar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => ({ status: response.status, body: data }));
        } else {
            return response.text().then(text => {
                throw new Error('El servidor respondió HTML en lugar de JSON.');
            });
        }
    })
    .then(async res => {
        loader.style.display = "none";
        if (res.status === 201 || res.body.status === true) {
            await Modal.success(
                'Venta Registrada',
                `Serie: ${res.body.data.serie}<br>Correlativo: ${res.body.data.numero}<br>Total: S/. ${parseFloat(res.body.data.total).toFixed(2)}`
            );
            carrito = [];
            productosCache = {};
            window.location.href = `${BaseUrl}/Home`;
        } else {
            await Modal.error('Error en la Venta', res.body.message || "Error desconocido.");
        }
    })
    .catch(async err => {
        loader.style.display = "none";
        await Modal.error('Error de Conexión', 'Error al procesar la venta: ' + err.message);
        console.error("Error detallado:", err);
    });
}
