document.addEventListener('DOMContentLoaded', function() {
    const favoritosBtns = document.querySelectorAll('.btn-favorite');
    favoritosBtns.forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault(); // Prevenir comportamiento por defecto
            e.stopPropagation(); // Detener propagación del evento
            const productId = this.dataset.productId;
            toggleFavorito(productId, this);
        };
    });

    const favoritosBtn = document.querySelector('.favorites-icon');
    // Solo agregar el evento si NO estamos en la página de favoritos
    if (favoritosBtn && !window.location.pathname.includes('favorites.php')) {
        favoritosBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'favorites.php';
        });
    }

    // Cargar estado inicial de favoritos
    cargarFavoritos();
});

function cargarFavoritosUI() {
    fetch('php/favorite_actions.php?action=list')
        .then(response => response.json())
        .then(data => {
            const contenedor = document.getElementById('favoritos-items');
            if (!contenedor) return;

            // Si no hay favoritos, mostrar mensaje
            if (!data.favorites || data.favorites.length === 0) {
                contenedor.innerHTML = `
                    <div class="favoritos-vacio">
                        <i class="fas fa-heart" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                        <p>No tienes productos favoritos</p>
                    </div>
                `;
                return;
            }

            // Mostrar los productos favoritos
            contenedor.innerHTML = data.favorites.map(item => `
                <div class="favoritos-item">
                    <img src="Imagenes/${item.image}" alt="${item.name}">
                    <div class="item-details">
                        <h3>${item.name}</h3>
                        <p class="item-price">$${Number(item.price).toFixed(2)}</p>
                        <button class="producto-btn" onclick="agregarAlCarrito(${item.id})">
                            <i class="fas fa-shopping-cart"></i> Agregar al carrito
                        </button>
                    </div>
                    <button class="btn-eliminar" onclick="eliminarDeFavoritos(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');
        });
}

function toggleFavorito(productId, button) {
    fetch('php/favorite_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'toggle',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('active');
            if (!data.isFavorite && window.location.pathname.includes('favorites.php')) {
                const card = button.closest('.producto-card');
                if (card) {
                    card.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        card.remove();
                        if (document.querySelectorAll('.producto-card').length === 0) {
                            document.querySelector('.productos-grid').innerHTML = 
                                '<p class="no-favorites">No tienes productos favoritos aún.</p>';
                        }
                    }, 300);
                }
            }
            actualizarBadgeFavoritos(data.count);
            mostrarNotificacionFavoritos(data.isFavorite ? 'Producto agregado a favoritos' : 'Producto eliminado de favoritos');
        }
    })
    .catch(error => console.error('Error:', error));
}

function eliminarDeFavoritos(id) {
    fetch('php/favorite_actions.php', {
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
            cargarFavoritosUI();
            actualizarBadgeFavoritos(data.count);
        }
    });
}

function actualizarBadgeFavoritos(count) {
    const badge = document.getElementById('favoritesCount');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
            badge.classList.add('bounce');
            setTimeout(() => badge.classList.remove('bounce'), 300);
        } else {
            badge.style.display = 'none';
        }
    }
}

function mostrarNotificacionFavoritos(mensaje) {
    // Remover notificaciones existentes
    const notificacionesExistentes = document.querySelectorAll('.notification-favorites');
    notificacionesExistentes.forEach(n => n.remove());

    // Crear nueva notificación
    const notif = document.createElement('div');
    notif.className = 'notification-favorites';
    notif.innerHTML = `
        <i class="fas fa-heart"></i>
        <span>${mensaje}</span>
    `;
    document.body.appendChild(notif);

    // Agregar clase para mostrar con animación
    setTimeout(() => notif.classList.add('show'), 10);

    // Remover después de 3 segundos
    setTimeout(() => {
        notif.classList.remove('show');
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

function cargarFavoritos() {
    fetch('php/favorite_actions.php?action=list')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarBadgeFavoritos(data.count);
                const buttons = document.querySelectorAll('.btn-favorite');
                buttons.forEach(button => {
                    const productId = button.dataset.productId;
                    if (data.favorites.includes(parseInt(productId))) {
                        button.classList.add('active');
                    }
                });
            }
        });
}
