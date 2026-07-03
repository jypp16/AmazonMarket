// =====================================================
// AMAZON MARKET - Sistema de Alertas Modales
// Reemplaza alerts y confirms nativos del navegador
// =====================================================

const Modal = {
    // Cola de modales para evitar superposición
    _active: false,

    /**
     * Muestra un modal de información con botón Aceptar
     * @param {string} titulo - Título del modal
     * @param {string} mensaje - Mensaje a mostrar
     * @param {string} tipo - 'success', 'error', 'warning', 'info'
     * @returns {Promise<void>}
     */
    alert(titulo, mensaje, tipo = 'info') {
        return new Promise((resolve) => {
            this._close();
            this._active = true;

            const iconMap = {
                success: 'fa-circle-check',
                error: 'fa-circle-xmark',
                warning: 'fa-triangle-exclamation',
                info: 'fa-circle-info'
            };

            const colorMap = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            overlay.innerHTML = `
                <div class="modal-container modal-alert">
                    <div class="modal-icon" style="background-color: ${colorMap[tipo]}15; color: ${colorMap[tipo]}">
                        <i class="fa-solid ${iconMap[tipo]}"></i>
                    </div>
                    <h3 class="modal-title">${titulo}</h3>
                    <p class="modal-message">${mensaje}</p>
                    <div class="modal-actions">
                        <button class="modal-btn modal-btn-primary" id="modal_ok_btn">
                            <i class="fa-solid fa-check"></i> Aceptar
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);

            setTimeout(() => overlay.classList.add('modal-active'), 10);

            const btnOk = document.getElementById('modal_ok_btn');
            btnOk.addEventListener('click', () => {
                this._close();
                resolve();
            });

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    this._close();
                    resolve();
                }
            });
        });
    },

    /**
     * Muestra un modal de confirmación con Aceptar y Cancelar
     * @param {string} titulo - Título del modal
     * @param {string} mensaje - Mensaje a mostrar
     * @param {string} tipo - 'warning', 'danger', 'info'
     * @returns {Promise<boolean>} true si acepta, false si cancela
     */
    confirm(titulo, mensaje, tipo = 'warning') {
        return new Promise((resolve) => {
            this._close();
            this._active = true;

            const iconMap = {
                warning: 'fa-triangle-exclamation',
                danger: 'fa-circle-exclamation',
                info: 'fa-circle-question'
            };

            const colorMap = {
                warning: '#f59e0b',
                danger: '#ef4444',
                info: '#3b82f6'
            };

            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            overlay.innerHTML = `
                <div class="modal-container modal-confirm">
                    <div class="modal-icon" style="background-color: ${colorMap[tipo]}15; color: ${colorMap[tipo]}">
                        <i class="fa-solid ${iconMap[tipo]}"></i>
                    </div>
                    <h3 class="modal-title">${titulo}</h3>
                    <p class="modal-message">${mensaje}</p>
                    <div class="modal-actions">
                        <button class="modal-btn modal-btn-secondary" id="modal_cancel_btn">
                            <i class="fa-solid fa-xmark"></i> Cancelar
                        </button>
                        <button class="modal-btn modal-btn-danger" id="modal_accept_btn">
                            <i class="fa-solid fa-check"></i> Aceptar
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);

            setTimeout(() => overlay.classList.add('modal-active'), 10);

            const btnAccept = document.getElementById('modal_accept_btn');
            const btnCancel = document.getElementById('modal_cancel_btn');

            btnAccept.addEventListener('click', () => {
                this._close();
                resolve(true);
            });

            btnCancel.addEventListener('click', () => {
                this._close();
                resolve(false);
            });

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    this._close();
                    resolve(false);
                }
            });
        });
    },

    /**
     * Muestra un modal de éxito
     */
    success(titulo, mensaje) {
        return this.alert(titulo, mensaje, 'success');
    },

    /**
     * Muestra un modal de error
     */
    error(titulo, mensaje) {
        return this.alert(titulo, mensaje, 'error');
    },

    /**
     * Muestra un modal de advertencia
     */
    warning(titulo, mensaje) {
        return this.alert(titulo, mensaje, 'warning');
    },

    /**
     * Cierra el modal activo
     */
    _close() {
        const overlay = document.querySelector('.modal-overlay');
        if (overlay) {
            overlay.classList.remove('modal-active');
            setTimeout(() => overlay.remove(), 300);
        }
        this._active = false;
    }
};

// Funciones globales compatibles con el código existente
function showModalAlert(titulo, mensaje, tipo = 'info') {
    return Modal.alert(titulo, mensaje, tipo);
}

function showModalConfirm(titulo, mensaje, tipo = 'warning') {
    return Modal.confirm(titulo, mensaje, tipo);
}
