<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$user = $_SESSION['user'];

// Obtener el número actual de items en el carrito
require_once 'php/db_config.php';
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
$stmt->execute([$user['id']]);
$cartCount = $stmt->fetch()['count'];

// Conexión a la base de datos
require_once 'php/db_config.php';
$stmt = $conn->query("SELECT * FROM products");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Erika Joyas</title>
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
                    <a href="#" id="favoritosBtn" class="favorites-icon">
                        <div class="favorites-wrapper">
                            <i class="fa-solid fa-heart"></i>
                            <span class="menu-text">Favoritos</span>
                            <span class="favorites-badge" id="favoritesCount"><?php
                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
                                $stmt->execute([$user['id']]);
                                $favoritesCount = $stmt->fetch()['count'];
                                echo ($favoritesCount > 0 ? $favoritesCount : '');
                            ?></span>
                        </div>
                    </a>
                </li>
                <li class="user-info">
                    <div class="user-menu">
                        <div class="user-profile" id="userProfile" style="display: flex;">
                            <img src="<?php echo htmlspecialchars($user['photo'] ?? 'Imagenes/placeholder.png'); ?>" 
                                 alt="Usuario" 
                                 class="user-avatar" 
                                 onerror="this.src='Imagenes/placeholder.png'"
                                 style="width: 40px; height: 40px; object-fit: cover;">
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

    <!-- Sección de Productos -->
    <section class="productos-section">
        <h2 class="section-title">Nuestros Productos</h2>
        
        <div class="filtros-container">
            <button class="filtro-btn activo" data-categoria="todos">Todos</button>
            <button class="filtro-btn" data-categoria="collares">Collares</button>
            <button class="filtro-btn" data-categoria="anillos">Anillos</button>
            <button class="filtro-btn" data-categoria="pulseras">Pulseras</button>
            <button class="filtro-btn" data-categoria="aretes">Aretes</button>
        </div>

        <div class="productos-grid" id="productos-container">
            <?php foreach($productos as $producto): ?>
            <div class="producto-card" data-categoria="<?php echo htmlspecialchars($producto['category']); ?>">
                <div class="producto-imagen-container">
                    <img src="Imagenes/<?php echo htmlspecialchars($producto['image']); ?>" 
                         alt="<?php echo htmlspecialchars($producto['name']); ?>" 
                         class="producto-imagen">
                    <div class="producto-overlay">
                        <button class="btn-favorite <?php
                            $stmtFav = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
                            $stmtFav->execute([$user['id'], $producto['id']]);
                            echo $stmtFav->fetch() ? 'active' : '';
                        ?>" data-product-id="<?php echo $producto['id']; ?>" 
                           onclick="toggleFavorito(<?php echo $producto['id']; ?>, this)">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <?php if($producto['stock'] <= 0): ?>
                        <div class="agotado-badge">Agotado</div>
                    <?php endif; ?>
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
        </div>

        <!-- Modal del Carrito -->
        <div id="carritoModal" class="modal-carrito">
            <div class="carrito-content">
                <div class="carrito-header">
                    <h2><i class="fas fa-shopping-cart"></i> Tu Carrito - <?php echo htmlspecialchars($user['name']); ?></h2>
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

        <!-- Modal de Favoritos -->
        <div id="favoritosModal" class="modal-favoritos">
            <div class="favoritos-content">
                <div class="favoritos-header">
                    <h2><i class="fas fa-heart"></i> Tus Favoritos</h2>
                    <span class="close-favoritos">&times;</span>
                </div>
                <div id="favoritos-items" class="favoritos-items">
                    <!-- Items de favoritos -->
                </div>
            </div>
        </div>
    </section>

    <script src="user.js"></script>
    <script src="js/cart.js"></script>
    <script src="js/favorites.js"></script>
    <script src="js/filters.js"></script>
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/favorites.css">

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
                button.classList.toggle('active');
                actualizarBadgeFavoritos(data.count);
                mostrarNotificacionFavoritos(data.isFavorite ? 'Producto agregado a favoritos' : 'Producto eliminado de favoritos');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function mostrarNotificacionFavoritos(mensaje) {
        const notif = document.createElement('div');
        notif.className = 'notification-favorites';
        notif.textContent = mensaje;
        document.body.appendChild(notif);
        // Aumentar el tiempo a 5 segundos (5000ms)
        setTimeout(() => notif.remove(), 5000);
    }

    function actualizarBadgeFavoritos(count) {
        const badge = document.getElementById('favoritesCount');
        if (badge) {
            badge.textContent = count > 0 ? count : '';
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetch('php/favorite_actions.php?action=list')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.favorites) {
                    data.favorites.forEach(favorite => {
                        const favoriteButton = document.querySelector(`.btn-favorite[data-product-id="${favorite.id}"]`);
                        if (favoriteButton) {
                            favoriteButton.classList.add('active');
                        }
                    });
                }
            });

        fetch('php/favorite_actions.php?action=count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarBadgeFavoritos(data.count);
                }
            });
    });
    </script>
</body>
</html>
