let carrito = [];
let productosCache = {};
let productosLista = [];
let clientesLista = [];

document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('select_producto')) return;
    cargarClientes();
    cargarComprobantes();
    cargarPagos();
    cargarCategoriasVenta();
    cargarProductosVenta();
});

async function cargarClientes() {
    try {
        const resultado = await Api.get('clientes');
        if (resultado && resultado.ok) {
            clientesLista = resultado.data.data || [];
            const select = document.getElementById('id_cliente');
            select.textContent = '';
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Seleccionar Cliente --';
            select.appendChild(defaultOpt);
            clientesLista.forEach(cli => {
                const opt = document.createElement('option');
                opt.value = cli.id_cliente;
                opt.textContent = cli.nombre + ' (' + cli.nro_documento + ')';
                opt.setAttribute('data-tipo-doc', cli.id_tipo_documento);
                select.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Error cargando clientes:', e);
    }

    const selectComprobante = document.getElementById('id_tipo_comprobante');
    const selectCliente = document.getElementById('id_cliente');
    const hint = document.getElementById('cliente-hint');

    if (selectComprobante && selectCliente) {
        selectComprobante.addEventListener('change', filtrarClientes);
    }
}

function filtrarClientes() {
    const selectComprobante = document.getElementById('id_tipo_comprobante');
    const selectCliente = document.getElementById('id_cliente');
    const hint = document.getElementById('cliente-hint');
    if (!selectComprobante || !selectCliente) return;

    const esFactura = selectComprobante.value == '2';
    const options = selectCliente.querySelectorAll('option[value]');

    options.forEach(opt => {
        if (opt.value === '') return;
        const tipoDoc = opt.getAttribute('data-tipo-doc');
        if (esFactura) {
            opt.style.display = tipoDoc == '2' ? '' : 'none';
        } else {
            opt.style.display = '';
        }
    });

    const currentOption = selectCliente.querySelector('option[value="' + selectCliente.value + '"]');
    if (currentOption && currentOption.style.display === 'none') {
        selectCliente.value = '';
    }

    if (hint) hint.style.display = esFactura ? 'block' : 'none';
}

async function cargarComprobantes() {
    try {
        const resultado = await Api.get('comprobantes');
        if (resultado && resultado.ok) {
            const select = document.getElementById('id_tipo_comprobante');
            select.textContent = '';
            const data = resultado.data.data || [];
            data.forEach(comp => {
                const opt = document.createElement('option');
                opt.value = comp.id_tipo_comprobante;
                opt.textContent = comp.nombre;
                select.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Error cargando comprobantes:', e);
    }
}

async function cargarPagos() {
    try {
        const resultado = await Api.get('pagos');
        if (resultado && resultado.ok) {
            const select = document.getElementById('id_metodo_pago');
            select.textContent = '';
            const data = resultado.data.data || [];
            data.forEach(pago => {
                const opt = document.createElement('option');
                opt.value = pago.id_metodo_pago;
                opt.textContent = pago.nombre;
                select.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Error cargando pagos:', e);
    }
}

async function cargarCategoriasVenta() {
    try {
        const resultado = await Api.get('categorias');
        if (resultado && resultado.ok) {
            const select = document.getElementById('filtro_categoria_venta');
            select.textContent = '';
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Todas';
            select.appendChild(defaultOpt);
            const data = resultado.data.data || [];
            data.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id_categoria;
                opt.textContent = cat.nombre;
                select.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Error cargando categorías:', e);
    }
}

async function cargarProductosVenta() {
    try {
        const resultado = await Api.get('productos');
        if (resultado && resultado.ok) {
            productosLista = resultado.data.data || [];
            const select = document.getElementById('select_producto');
            select.textContent = '';
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Seleccionar Producto --';
            select.appendChild(defaultOpt);
            productosLista.forEach(prod => {
                const opt = document.createElement('option');
                opt.value = prod.id_producto;
                opt.textContent = prod.nombre;
                opt.setAttribute('data-categoria', prod.id_categoria || prod.categoria);
                opt.setAttribute('data-stock', prod.stock_actual);
                opt.setAttribute('data-precio', prod.precio_venta);
                opt.setAttribute('data-unidad', prod.unidad);
                select.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Error cargando productos:', e);
    }
}

function filtrarProductosVenta() {
    const filtro = document.getElementById('filtro_categoria_venta').value;
    const selectProducto = document.getElementById('select_producto');
    const options = selectProducto.querySelectorAll('option[value]');

    options.forEach(opt => {
        if (opt.value === '') return;
        const cat = opt.getAttribute('data-categoria');
        if (!filtro || cat === filtro || cat === String(filtro)) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });

    const currentOption = selectProducto.querySelector('option[value="' + selectProducto.value + '"]');
    if (currentOption && currentOption.style.display === 'none') {
        selectProducto.value = '';
    }
}

async function obtenerProducto(id_producto) {
    if (!id_producto) return null;
    if (productosCache[id_producto]) return productosCache[id_producto];

    try {
        const resultado = await Api.get('productos/' + id_producto);
        if (resultado && resultado.ok && resultado.data.data) {
            productosCache[id_producto] = resultado.data.data;
            return resultado.data.data;
        }
        return null;
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

    stock_info.textContent = '--';
    stock_info.className = "badge-neutral";
    precio_info.textContent = '--';
    precio_info.className = "badge-neutral";
    cantidad_input.value = "";
    cantidad_input.removeAttribute('step');
    cantidad_input.removeAttribute('min');

    if (!id_producto) return;

    const selectOpt = document.getElementById("select_producto").querySelector('option[value="' + id_producto + '"]');
    if (selectOpt) {
        const stock = parseFloat(selectOpt.getAttribute('data-stock'));
        const precio = parseFloat(selectOpt.getAttribute('data-precio'));
        const unidad = selectOpt.getAttribute('data-unidad') || '';

        stock_info.textContent = stock.toFixed(2) + ' ' + unidad;
        stock_info.className = 'stock-badge stock-normal';

        precio_info.textContent = 'S/. ' + precio.toFixed(2);
        precio_info.className = "badge-accent";

        const unidadLower = unidad.toLowerCase().trim();
        const unidadesDecimales = ['kg', 'lt', 'lb', 'gal', 'm', 'cm', 'ml', 'g', 'oz'];
        const esDecimal = unidadesDecimales.some(ud => unidadLower.includes(ud));

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
        return;
    }

    stock_info.textContent = 'Cargando...';

    const producto = await obtenerProducto(id_producto);

    if (producto) {
        const stock = parseFloat(producto.stock_actual);
        const precio = parseFloat(producto.precio_venta);
        const minStock = parseFloat(producto.stock_minimo);

        stock_info.textContent = stock.toFixed(2) + ' ' + producto.unidad;
        stock_info.className = stock <= minStock ? 'stock-badge stock-low' : 'stock-badge stock-normal';

        precio_info.textContent = 'S/. ' + precio.toFixed(2);
        precio_info.className = "badge-accent";

        const unidad = producto.unidad ? producto.unidad.toLowerCase().trim() : '';
        const unidadesDecimales = ['kg', 'lt', 'lb', 'gal', 'm', 'cm', 'ml', 'g', 'oz'];
        const esDecimal = unidadesDecimales.some(ud => unidad.includes(ud));

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
        stock_info.textContent = 'Error al cargar';
        stock_info.className = "stock-badge stock-low";
    }
}

async function agregarAlCarrito() {
    const id_producto = document.getElementById("select_producto").value;
    const cantidad = parseFloat(document.getElementById("cantidad_producto").value);

    if (!id_producto) {
        await Modal.warning('Campo Requerido', 'Por favor, seleccione un producto.');
        return;
    }

    if (isNaN(cantidad) || cantidad <= 0) {
        await Modal.warning('Cantidad Inválida', 'La cantidad debe ser un número positivo mayor que cero.');
        return;
    }

    const selectOpt = document.getElementById("select_producto").querySelector('option[value="' + id_producto + '"]');
    let prod;

    if (selectOpt) {
        prod = {
            id_producto: id_producto,
            nombre: selectOpt.textContent,
            stock_actual: selectOpt.getAttribute('data-stock'),
            precio_venta: selectOpt.getAttribute('data-precio'),
            unidad: selectOpt.getAttribute('data-unidad')
        };
    } else {
        prod = await obtenerProducto(id_producto);
    }

    if (!prod) {
        await Modal.error('Error', 'No se pudieron cargar los datos del producto.');
        return;
    }

    const stockDisponible = parseFloat(prod.stock_actual);
    const itemExistente = carrito.find(item => item.id_producto == id_producto);
    const cantidadEnCarrito = itemExistente ? itemExistente.cantidad : 0;

    if (cantidadEnCarrito + cantidad > stockDisponible) {
        await Modal.warning('Stock Insuficiente', 'Solo quedan ' + stockDisponible.toFixed(2) + ' ' + prod.unidad + '.');
        return;
    }

    if (itemExistente) {
        itemExistente.cantidad += cantidad;
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
        await Modal.error('Error', 'Error al verificar stock.');
        renderizarCarrito();
        return;
    }

    if (cantidad > parseFloat(prod.stock_actual)) {
        await Modal.warning('Stock Insuficiente', 'El stock actual es ' + parseFloat(prod.stock_actual).toFixed(2) + '.');
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

    cart_body.textContent = '';

    if (carrito.length === 0) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 5;
        td.style.textAlign = 'center';
        td.textContent = 'El carrito está vacío. Agregue productos.';
        tr.appendChild(td);
        cart_body.appendChild(tr);
        cart_count_badge.textContent = "0 items";
        calcularTotales();
        return;
    }

    cart_count_badge.textContent = carrito.length + ' item' + (carrito.length > 1 ? 's' : '');

    carrito.forEach(function(item, index) {
        const tr = document.createElement('tr');

        const tdNombre = document.createElement('td');
        tdNombre.textContent = item.nombre;

        const tdPrecio = document.createElement('td');
        tdPrecio.style.textAlign = 'right';
        tdPrecio.textContent = 'S/. ' + item.precio_venta.toFixed(2);

        const tdCant = document.createElement('td');
        tdCant.style.textAlign = 'center';
        const input = document.createElement('input');
        input.type = 'number';
        input.value = item.cantidad;
        input.min = '0.01';
        input.step = '0.01';
        input.className = 'table-quantity-input';
        input.addEventListener('change', function() { actualizarCantidadCarrito(index, input.value); });
        tdCant.appendChild(input);
        const spanUnidad = document.createElement('span');
        spanUnidad.style.fontSize = '11px';
        spanUnidad.style.color = '#777';
        spanUnidad.style.display = 'block';
        spanUnidad.textContent = item.unidad;
        tdCant.appendChild(spanUnidad);

        const tdSubtotal = document.createElement('td');
        tdSubtotal.style.textAlign = 'right';
        tdSubtotal.className = 'font-semibold price-text';
        tdSubtotal.textContent = 'S/. ' + item.subtotal.toFixed(2);

        const tdAccion = document.createElement('td');
        tdAccion.style.textAlign = 'center';
        const btnDel = document.createElement('button');
        btnDel.type = 'button';
        btnDel.className = 'btn-delete-cart';
        const iconDel = document.createElement('i');
        iconDel.className = 'fa-solid fa-trash-can';
        btnDel.appendChild(iconDel);
        btnDel.addEventListener('click', function() { eliminarDelCarrito(index); });
        tdAccion.appendChild(btnDel);

        tr.appendChild(tdNombre);
        tr.appendChild(tdPrecio);
        tr.appendChild(tdCant);
        tr.appendChild(tdSubtotal);
        tr.appendChild(tdAccion);

        cart_body.appendChild(tr);
    });

    calcularTotales();
}

function calcularTotales() {
    let total = 0;
    carrito.forEach(function(item) { total += item.subtotal; });

    const subtotal = total / 1.18;
    const igv = total - subtotal;

    document.getElementById("lbl_total").textContent = "S/. " + total.toFixed(2);
    document.getElementById("lbl_subtotal").textContent = "S/. " + subtotal.toFixed(2);
    document.getElementById("lbl_igv").textContent = "S/. " + igv.toFixed(2);
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
        await Modal.warning('Carrito Vacío', 'Agregue al menos un producto.');
        return;
    }

    const confirmado = await Modal.confirm(
        'Procesar Venta',
        '¿Está seguro de procesar esta venta? El stock se actualizará.',
        'warning'
    );

    if (!confirmado) return;

    const loader = document.getElementById("loader_overlay");
    if (loader) loader.style.display = "flex";

    try {
        const resultado = await Api.post('ventas', {
            id_cliente: parseInt(id_cliente),
            id_tipo_comprobante: parseInt(id_tipo_comprobante),
            id_metodo_pago: parseInt(id_metodo_pago),
            productos: carrito.map(function(item) {
                return { id_producto: item.id_producto, cantidad: item.cantidad };
            })
        });

        if (loader) loader.style.display = "none";

        if (resultado && resultado.ok) {
            const d = resultado.data.data || resultado.data;
            await Modal.success(
                'Venta Registrada',
                'Serie: ' + d.serie + '\nCorrelativo: ' + d.numero + '\nTotal: S/. ' + parseFloat(d.total).toFixed(2)
            );
            carrito = [];
            productosCache = {};
            window.location.href = BASE_URL + '/Home';
        } else {
            await Modal.error('Error en la Venta', resultado ? resultado.data.message : 'Error desconocido.');
        }
    } catch (error) {
        if (loader) loader.style.display = "none";
        await Modal.error('Error de Conexión', 'Error al procesar la venta: ' + error.message);
    }
}
