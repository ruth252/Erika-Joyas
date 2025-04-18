<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$user = $_SESSION['user'];

require_once 'php/db_config.php';

// Obtener los productos favoritos del usuario
$stmt = $conn->prepare("
    SELECT p.* 
    FROM favorites f 
    JOIN products p ON f.product_id = p.id 
    WHERE f.user_id = ?
");
$stmt->execute([$user['id']]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener conteo del carrito
$stmtCart = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
$stmtCart->execute([$user['id']]);
$cartCount = $stmtCart->fetch()['count'];

// Obtener conteo de favoritos
$stmtFav = $conn->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
$stmtFav->execute([$user['id']]);
$favCount = $stmtFav->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoritos - Erika Joyas</title>
    <link rel="shortcut icon" href="Imagenes/Logo1.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<header class="header">
    <div class="logo">
        <img src="Imagenes/Logo1.png" alt="Logo" class="logo-img">
    </div>
    <nav class="menu">
        <ul>
            <li>
                <a href="inicio.php">
                    <i class="fa-solid fa-shop"></i>
                    <span class="menu-text">Inicio</span>
                </a>
            </li>
            <li>
                <a href="products.php">
                    <i class="fa-solid fa-gem"></i>
                    <span class="menu-text">Productos</span>
                </a>
            </li>
            <li>
                <a href="#" id="carritoBtn" class="cart-icon">
                    <div class="cart-wrapper">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="menu-text">Carrito</span>
                        <span class="cart-badge" id="cartCount"><?php echo ($cartCount > 0 ? $cartCount : ''); ?></span>
                    </div>
                </a>
            </li>
            <li>
                <a href="favorites.php" class="favorites-icon">
                    <div class="favorites-wrapper">
                        <i class="fa-solid fa-heart"></i>
                        <span class="menu-text">Favoritos</span>
                        <span class="favorites-badge" id="favoritesCount"><?php echo ($favCount > 0 ? $favCount : ''); ?></span>
                    </div>
                </a>
            </li>
            <li class="user-info">
                <div class="user-menu">
                    <div class="user-profile" id="userProfile">
                        <img src="<?php echo htmlspecialchars($user['photo'] ?? 'Imagenes/placeholder.png'); ?>" 
                             alt="Usuario" 
                             class="user-avatar"
                             onerror="this.src='Imagenes/placeholder.png'">
                        <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                        <div class="user-dropdown">
                            <div class="dropdown-content">
                                <a href="#" id="cerrarSesion">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
</header>

<section class="favorites-section">
    <h2 class="section-title">Mis Favoritos</h2>
    <div class="productos-grid">
        <?php if (count($favorites) > 0): ?>
            <?php foreach ($favorites as $producto): ?>
                <div class="producto-card">
                    <div class="producto-imagen-container">
                        <img src="Imagenes/<?php echo htmlspecialchars($producto['image']); ?>" 
                             alt="<?php echo htmlspecialchars($producto['name']); ?>" 
                             class="producto-imagen">
                        <div class="producto-overlay">
                            <button class="btn-favorite active" 
                                    data-product-id="<?php echo $producto['id']; ?>" 
                                    onclick="toggleFavorito(<?php echo $producto['id']; ?>, this)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="producto-info">
                        <h3 class="producto-nombre"><?php echo htmlspecialchars($producto['name']); ?></h3>
                        <p class="producto-precio">$<?php echo number_format($producto['price'], 2); ?></p>
                        <p class="producto-descripcion"><?php echo htmlspecialchars($producto['description']); ?></p>
                        <button class="producto-btn" onclick="agregarAlCarrito(<?php echo $producto['id']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Agregar al carrito
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-favorites">No tienes productos favoritos aún.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Modal del Carrito -->
<div id="carritoModal" class="modal-carrito">
    <div class="carrito-content">
        <div class="carrito-header">
            <h2><i class="fas fa-shopping-cart"></i> Tu Carrito</h2>
            <span class="close-carrito">&times;</span>
        </div>
        <div id="carrito-items" class="carrito-items">
            <!-- Items del carrito -->
        </div>
        <div class="carrito-resumen">
            <div class="resumen-linea">
                <span>Subtotal:</span>
                <span id="carrito-subtotal">$0.00</span>
            </div>
            <div class="resumen-linea">
                <span>ITBIS (18%):</span>
                <span id="carrito-itbis">$0.00</span>
            </div>
            <div class="resumen-linea total">
                <span>Total:</span>
                <span id="carrito-total">$0.00</span>
            </div>
            <button id="btn-checkout" class="btn-checkout">
                <i class="fas fa-lock"></i> Proceder al Pago
            </button>
        </div>
    </div>
</div>

<!-- Incluir scripts necesarios -->
<script src="user.js"></script>
<script src="js/cart.js"></script>
<script src="js/favorites.js"></script>
<script>
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
            if (!data.isFavorite) {
                const card = button.closest('.producto-card');
                card.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    card.remove();
                    if (document.querySelectorAll('.producto-card').length === 0) {
                        document.querySelector('.productos-grid').innerHTML = 
                            '<p class="no-favorites">No tienes productos favoritos aún.</p>';
                    }
                }, 300);
            }
            actualizarBadgeFavoritos(data.count); // Actualizar inmediatamente el contador
            mostrarNotificacion(data.isFavorite ? 'Producto agregado a favoritos' : 'Producto eliminado de favoritos');
        }
    })
    .catch(error => console.error('Error:', error));
}

function actualizarBadgeFavoritos(count) {
    const badge = document.getElementById('favoritesCount');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
        } else {
            badge.textContent = '';
            badge.style.display = 'none';
        }
    }
}

function mostrarNotificacion(mensaje) {
    const notif = document.createElement('div');
    notif.className = 'notification-favorites';
    notif.textContent = mensaje;
    document.body.appendChild(notif);
    // Aumentar el tiempo a 5 segundos (5000ms)
    setTimeout(() => notif.remove(), 5000);
}

function agregarAlCarrito(productId) {
    fetch('php/cart_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'add',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('¡Producto agregado al carrito!');
            actualizarBadgeCarrito(data.count);
        } else {
            mostrarNotificacion(data.message || 'Error al agregar al carrito');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al agregar al carrito');
    });
}

function actualizarBadgeCarrito(count) {
    const badge = document.getElementById('cartCount');
    if (badge) {
        badge.textContent = count > 0 ? count : '';
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function actualizarBadgeFavoritos(count) {
    const badge = document.getElementById('favoritesCount');
    if (badge) {
        badge.textContent = count > 0 ? count : '';
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

// Inicializar carrito
document.addEventListener('DOMContentLoaded', () => {
    const carritoBtn = document.getElementById('carritoBtn');
    const carritoModal = document.getElementById('carritoModal');
    const closeCarrito = document.querySelector('.close-carrito');

    if (carritoBtn && carritoModal) {
        carritoBtn.onclick = (e) => {
            e.preventDefault();
            carritoModal.style.display = 'block';
            setTimeout(() => carritoModal.classList.add('show'), 10);
            actualizarCarritoUI();
        };

        closeCarrito.onclick = () => {
            carritoModal.classList.remove('show');
            setTimeout(() => carritoModal.style.display = 'none', 300);
        };

        // Cerrar al hacer clic fuera del carrito
        window.onclick = (e) => {
            if (e.target === carritoModal) {
                carritoModal.classList.remove('show');
                setTimeout(() => carritoModal.style.display = 'none', 300);
            }
        };
    }
});

// Actualizar el manejador del carrito
document.getElementById('carritoBtn').addEventListener('click', function(e) {
    e.preventDefault();
    const carritoModal = document.getElementById('carritoModal');
    carritoModal.classList.add('show');
    actualizarCarritoUI();
});

// Actualizar el manejador para cerrar el carrito
document.querySelector('.close-carrito').addEventListener('click', function() {
    document.getElementById('carritoModal').classList.remove('show');
});

// Cerrar el carrito al hacer clic fuera de él
document.addEventListener('click', function(e) {
    const carritoModal = document.getElementById('carritoModal');
    const carritoBtn = document.getElementById('carritoBtn');
    if (!carritoModal.contains(e.target) && !carritoBtn.contains(e.target)) {
        carritoModal.classList.remove('show');
    }
});
</script>
</body>
</html>
