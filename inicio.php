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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="Imagenes/Logo1.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="estilo.css">
    <title>Erika Joyas</title>
</head>
<body>
<!-- Menú -->
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
                            $stmtFavCount = $conn->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
                            $stmtFavCount->execute([$user['id']]);
                            $favoritesCount = $stmtFavCount->fetch()['count'];
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

<!-- carrusel -->
<section class="hero">
    <div class="carousel">
        <div class="carousel-slide active">
            <img src="Imagenes/s3.jpg" alt="Joya elegante">
            <div class="carousel-text">
                <h1 class="animate-text">Brillando con Elegancia</h1>
                <p class="animate-text">Descubre la joyería perfecta para cada ocasión.</p>
                <a href="#sobre-nosotros" class="btn">Saber Más</a>
            </div>
        </div>
        <div class="carousel-slide">
            <img src="Imagenes/s1.jpg" alt="Colección exclusiva">
            <div class="carousel-text">
                <h1 class="animate-text">Lujo y Sofisticación</h1>
                <p class="animate-text">Diseños únicos que realzan tu belleza.</p>
                <a href="#sobre-nosotros" class="btn">Saber Más</a>
            </div>
        </div>
        <div class="carousel-slide">
            <img src="Imagenes/s4.jpg" alt="Brillo y glamour">
            <div class="carousel-text">
                <h1 class="animate-text">Refleja tu Estilo</h1>
                <p class="animate-text">Piezas con un brillo incomparable.</p>
                <a href="#sobre-nosotros" class="btn">Saber Más</a>
            </div>
        </div>
    </div>
    <script>
        let currentIndex = 0;
        const slides = document.querySelectorAll(".carousel-slide");
        const totalSlides = slides.length;

        function showSlide(index) {
            slides.forEach(slide => {
                slide.classList.remove("active");
            });
            slides[index].classList.add("active");
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            showSlide(currentIndex);
        }

        // Iniciar el carrusel
        showSlide(currentIndex);
        setInterval(nextSlide, 5000);
    </script>
</section>
<!-- sobre nosotros -->
<section id="sobre-nosotros" class="about">
    <div class="about-container">
        <div class="about-text">
            <h2 class="moving-title">SOBRE&nbsp;NOSOTROS</h2>
            <p class="animate-fade">En <strong>Erika Joyas</strong>, diseñamos piezas únicas con pasión y excelencia. Cada joya refleja lujo y exclusividad, resaltando la elegancia en cada detalle. Fusionamos tradición y modernidad para crear experiencias inolvidables.</p>
        </div>
        <div class="about-image-container">
            <div class="about-image">
                <img src="Imagenes/joyeria.jpg" alt="Artesano trabajando" class="animate-zoom">
            </div>
            <div class="overlay-shape"></div>
        </div>
    </div>
</section>
<!-- mision vision y valores -->
<section id="mision-vision-valores" class="mvv">
    <div class="mvv-container">
        <div class="mvv-card animate-card">
            <div class="card-icon"><i class="fas fa-bullseye"></i></div>
            <h3 class="card-title">Misión</h3>
            <p>Crear joyas exclusivas que capturen la esencia de la elegancia y distinción, ofreciendo calidad y arte en cada pieza.</p>
        </div>
        <div class="mvv-card animate-card">
            <div class="card-icon"><i class="fas fa-eye"></i></div>
            <h3 class="card-title">Visión</h3>
            <p>Ser la joyería de referencia a nivel internacional, combinando innovación y tradición para superar las expectativas de nuestros clientes.</p>
        </div>
        <div class="mvv-card animate-card">
            <div class="card-icon"><i class="fas fa-gem"></i></div>
            <h3 class="card-title">Valores</h3>
            <p>Compromiso, excelencia y pasión por el diseño, manteniendo siempre la confianza y satisfacción de nuestros clientes.</p>
        </div>
    </div>
</section>
<!-- Novedades-->
<section id="novedades" class="novedades">
    <h2 class="section-title">Novedades</h2>
    <div class="novedades-container">
        <div class="novedad-card">
            <img src="Imagenes/j3.jpg" alt="Novedad 1" class="novedad-img">
            <div class="novedad-content">
                <h3>Joya del Mes</h3>
                <p>Descubre nuestra joya más exclusiva, ideal para cualquier ocasión especial.</p>
                <a href="products.php" class="novedad-btn">Ver más</a>
            </div>
        </div>
        <div class="novedad-card">
            <img src="Imagenes/j4.jpg" alt="Novedad 2" class="novedad-img">
            <div class="novedad-content">
                <h3>Colección Limitada</h3>
                <p>Una colección única y limitada para los amantes del lujo y la exclusividad.</p>
                <a href="products.php" class="novedad-btn">Ver más</a>
            </div>
        </div>
        <div class="novedad-card">
            <img src="Imagenes/j5.jpg" alt="Novedad 3" class="novedad-img">
            <div class="novedad-content">
                <h3>Diseños Atemporales</h3>
                <p>Joyas que nunca pasan de moda, para quienes aprecian la elegancia clásica.</p>
                <a href="products.php" class="novedad-btn">Ver más</a>
            </div>
        </div>
        <div class="novedad-card">
            <img src="Imagenes/j6.jpg" alt="Novedad 4" class="novedad-img">
            <div class="novedad-content">
                <h3>Edición Especial</h3>
                <p>Para quienes buscan algo único, nuestra edición especial es ideal.</p>
                <a href="products.php" class="novedad-btn">Ver más</a>
            </div>
        </div>
    </div>
</section>
<!-- Sección de Contacto -->
<section id="contacto" class="contacto">
    <div class="contacto-container">
        <!-- Imagen -->
        <div class="contacto-imagen">
            <img src="Imagenes/j2.jpg" alt="Imagen de contacto">
        </div>
        <!-- Formulario -->
        <div class="contacto-formulario">
            <h2 class="section-title">Contáctanos</h2>
            <p class="descripcion">¿Tienes alguna pregunta o deseas más información? Completa el siguiente formulario y nos pondremos en contacto contigo lo antes posible.</p>
            <form action="#" method="post" class="formulario">
                <div class="form-group">
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre" required>
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Gmail" required>
                </div>
                <div class="form-group">
                    <textarea id="mensaje" name="mensaje" placeholder="Escribe tu mensaje..." rows="5" required></textarea>
                </div>
                <button type="submit" class="btn">Enviar</button>
            </form>
        </div>
    </div>
</section>
<!-- footer -->
<footer class="footer">
    <div class="footer-content">
        <!-- Logo -->
        <div class="footer-logo">
            <img src="Imagenes/_Logo EJ (3).png" alt="Logo" class="logo-img">
        </div>
        <!-- Redes sociales -->
        <div class="footer-socials">
            <div class="social-item">
                <a href="https://www.instagram.com/erikajoyasrd/" target="_blank" class="social-link"><i class="fab fa-instagram"></i> Instagram</a>
            </div>
            <div class="social-item">
                <a href="https://www.facebook.com/erikajoyasrd" target="_blank" class="social-link"><i class="fab fa-facebook"></i> Facebook</a>
            </div>
            <div class="social-item">
                <a href="https://api.whatsapp.com/send?phone=18093164122&text=Hola!%20Me%20gustar%C3%ADa%20saber%20mas%20sobe%20la%20venta%20de%20sus%20accesorios!" target="_blank" class="social-link"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            </div>
        </div>
        <!-- Mapa -->
        <div class="footer-map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3762.116055539732!2d-70.70751242591164!3d19.450562481832602!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8eb1cf50a1ed116b%3A0x824584ee7a4abbab!2sErika%20Joyas%20RD!5e0!3m2!1ses-419!2sdo!4v1742606402115!5m2!1ses-419!2sdo" width="300" height="150" style="border:0;"></iframe>
        </div>
        <!-- Horarios -->
        <div class="footer-hours">
            <h3>Horario de Atención</h3>
            <ul>
                <li>Lunes - Viernes: 9:00 AM - 6:00 PM</li>
                <li>Sábado: 10:00 AM - 4:00 PM</li>
                <li>Domingo: Cerrado</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="company-phrase">
            "Tu mejor opción en joyería de alta calidad"
        </div>
        <p>&copy; 2025 ERIKA JOYAS. Todos los derechos reservados (Ruth Mercado).</p>
    </div>
</footer>
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

<script src="user.js"></script>
<script src="js/cart.js"></script>
<script src="js/favorites.js"></script>
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

function actualizarBadgeFavoritos(count) {
    const badge = document.getElementById('favoritesCount');
    if (badge) {
        badge.textContent = count > 0 ? count : '';
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function mostrarNotificacionFavoritos(mensaje) {
    const notif = document.createElement('div');
    notif.className = 'notification-favorites';
    notif.textContent = mensaje;
    document.body.appendChild(notif);
    // Aumentar el tiempo a 5 segundos (5000ms)
    setTimeout(() => notif.remove(), 5000);
}
</script>
</body>
</html>
