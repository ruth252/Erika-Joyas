document.addEventListener('DOMContentLoaded', () => {
    const carritoBtn = document.getElementById('carritoBtn');
    const carritoModal = document.getElementById('carritoModal');
    const closeCarrito = document.querySelector('.close-carrito');

    if (carritoBtn && carritoModal) {
        // Abrir carrito
        carritoBtn.onclick = () => {
            carritoModal.style.display = 'block';
            setTimeout(() => carritoModal.classList.add('show'), 10);
            actualizarCarritoUI();
        };

        // Cerrar con la X
        if (closeCarrito) {
            closeCarrito.onclick = (e) => {
                e.preventDefault();
                carritoModal.classList.remove('show');
                setTimeout(() => {
                    carritoModal.style.display = 'none';
                }, 300);
            };
        }

        // Cerrar al hacer clic fuera del carrito
        window.onclick = (e) => {
            if (e.target === carritoModal) {
                carritoModal.classList.remove('show');
                setTimeout(() => {
                    carritoModal.style.display = 'none';
                }, 300);
            }
        };
    }

    // Actualizar el título del carrito con el nombre del usuario
    fetch('php/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user) {
                const carritoHeader = document.querySelector('.carrito-header h2');
                if (carritoHeader) {
                    carritoHeader.textContent = `Tu Carrito - ${data.user.name}`;
                }
                actualizarContadorCarritoPersistente(data.user.id);
            }
        });

    // Cargar el contador del carrito inmediatamente
    fetch('php/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user) {
                // Cargar el contador del carrito para este usuario
                fetch(`php/cart_handler.php?user_id=${data.user.id}`)
                    .then(response => response.json())
                    .then(cartData => {
                        if (cartData.success) {
                            actualizarBadgeCarrito(cartData.count);
                        }
                    });
                
                const carritoHeader = document.querySelector('.carrito-header h2');
                if (carritoHeader) {
                    carritoHeader.textContent = `Tu Carrito - ${data.user.name}`;
                }
            }
        });

    actualizarCarritoUI();
});

function actualizarContadorCarritoPersistente(userId) {
    fetch(`php/cart_handler.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarBadgeCarrito(data.count);
            }
        });
}

function initFiltros() {
    const botones = document.querySelectorAll('.filtro-btn');
    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            // Remover clase activo de todos los botones
            botones.forEach(b => b.classList.remove('activo'));
            // Añadir clase activo al botón clickeado
            boton.classList.add('activo');
            
            const categoria = boton.dataset.categoria;
            const productos = document.querySelectorAll('.producto-card');
            
            productos.forEach(producto => {
                if (categoria === 'todos' || producto.dataset.categoria === categoria) {
                    producto.style.display = '';
                } else {
                    producto.style.display = 'none';
                }
            });
        });
    });
}

function initCart() {
    const carritoBtn = document.getElementById('carritoBtn');
    const carritoModal = document.getElementById('carritoModal');
    const closeBtn = document.querySelector('.close');

    if (carritoBtn && carritoModal && closeBtn) {
        carritoBtn.onclick = () => {
            carritoModal.style.display = 'block';
            actualizarCarritoUI();
        };
        
        closeBtn.onclick = () => carritoModal.style.display = 'none';
    }
}

function agregarAlCarrito(id) {
    fetch('php/cart_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'add',
            product_id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('¡Producto agregado al carrito!');
            actualizarContadorCarrito(data.count);
            actualizarCarritoUI();
        } else {
            mostrarNotificacion(data.message || 'Error al agregar al carrito');
        }
    })
    .catch(error => mostrarNotificacion('Error al agregar al carrito'));
}

function actualizarCarritoUI() {
    fetch('php/cart_actions.php')
        .then(response => response.json())
        .then(data => {
            const contenedor = document.getElementById('carrito-items');
            if (!contenedor) return; // Si no existe el contenedor, salir de la función

            // Si el carrito está vacío
            if (!data.items || data.items.length === 0) {
                contenedor.innerHTML = `
                    <div class="carrito-vacio">
                        <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                        <p>Tu carrito está vacío</p>
                    </div>`;
                
                // Verificar si existen los elementos antes de actualizarlos
                const subtotalElement = document.getElementById('carrito-subtotal');
                const itbisElement = document.getElementById('carrito-itbis');
                const totalElement = document.getElementById('carrito-total');
                
                if (subtotalElement) subtotalElement.textContent = '$0.00';
                if (itbisElement) itbisElement.textContent = '$0.00';
                if (totalElement) totalElement.textContent = '$0.00';
                
                return;
            }

            contenedor.innerHTML = data.items.map(item => `
                <div class="carrito-item">
                    <img src="Imagenes/${item.image}" alt="${item.name}">
                    <div class="item-details">
                        <h3>${item.name}</h3>
                        <p class="item-price">$${Number(item.price).toFixed(2)}</p>
                        <div class="cantidad-controls">
                            <button onclick="cambiarCantidad(${item.product_id}, -1)" 
                                    ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                            <span>${item.quantity}</span>
                            <button onclick="cambiarCantidad(${item.product_id}, 1)">+</button>
                        </div>
                        <p class="item-subtotal">Subtotal: $${(item.price * item.quantity).toFixed(2)}</p>
                    </div>
                    <button class="btn-eliminar" onclick="eliminarDelCarrito(${item.product_id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');

            // Calcular y mostrar totales
            const subtotal = data.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const itbis = subtotal * 0.18;
            const total = subtotal + itbis;

            document.querySelector('.carrito-resumen').innerHTML = `
                <div class="resumen-linea">
                    <span>Subtotal:</span>
                    <span>$${subtotal.toFixed(2)}</span>
                </div>
                <div class="resumen-linea">
                    <span>ITBIS (18%):</span>
                    <span>$${itbis.toFixed(2)}</span>
                </div>
                <div class="resumen-linea total">
                    <span>Total:</span>
                    <span>$${total.toFixed(2)}</span>
                </div>
                <button class="btn-checkout" onclick="procederPago()">
                    <i class="fas fa-lock"></i> Proceder al Pago ($${total.toFixed(2)})
                </button>
            `;

            actualizarTotales(data.summary);
            document.querySelector('.btn-checkout').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            // En caso de error, también mostrar todo en cero
            document.getElementById('carrito-subtotal').textContent = '$0.00';
            document.getElementById('carrito-itbis').textContent = '$0.00';
            document.getElementById('carrito-total').textContent = '$0.00';
        });
}

// Función para proceder al pago
function procederPago() {
    const total = document.querySelector('.total span:last-child').textContent;
    if (confirm(`¿Deseas proceder al pago por ${total}?`)) {
        // Aquí iría la lógica de pago
        alert('Redirigiendo al sistema de pago...');
    }
}

// Asegurarse de que los totales se actualicen correctamente
function actualizarTotales(summary) {
    const subtotalElement = document.getElementById('carrito-subtotal');
    const itbisElement = document.getElementById('carrito-itbis');
    const totalElement = document.getElementById('carrito-total');

    // Verificar si los elementos existen antes de actualizar
    if (subtotalElement && itbisElement && totalElement) {
        subtotalElement.textContent = `$${summary.subtotal}`;
        itbisElement.textContent = `$${summary.itbis}`;
        totalElement.textContent = `$${summary.total}`;
    }
}

// Asegurarse de que el badge del carrito se actualice
function actualizarBadgeCarrito(count) {
    const badge = document.getElementById('cartCount');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function cambiarCantidad(id, cambio) {
    fetch('php/cart_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'update',
            product_id: id,
            change: cambio
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) actualizarCarritoUI();
        else mostrarNotificacion(data.message);
    });
}

function eliminarDelCarrito(id) {
    if (!confirm('¿Estás seguro de eliminar este producto?')) return;

    fetch('php/cart_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'remove',
            product_id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Producto eliminado del carrito');
            actualizarCarritoUI();
            
            // Actualizar el contador del carrito
            const badge = document.getElementById('cartCount');
            if (badge) {
                const currentCount = parseInt(badge.textContent) - 1;
                if (currentCount <= 0) {
                    badge.style.display = 'none';
                } else {
                    badge.textContent = currentCount;
                }
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

function mostrarNotificacion(mensaje) {
    const notif = document.createElement('div');
    notif.className = 'notification';
    notif.textContent = mensaje;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 3000);
}

function actualizarContadorCarrito(count) {
    const badge = document.getElementById('cartCount');
    if (badge) {
        badge.textContent = count;
        badge.style.display = 'flex';
    }
}
