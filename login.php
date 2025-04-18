<?php
session_start();
if(isset($_SESSION['user'])) {
    header('Location: inicio.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Erika Joyas</title>
    <link rel="shortcut icon" href="Imagenes/Logo1.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="Imagenes/Logo1.png" alt="Logo" class="login-logo">
        </div>

        <!-- Formulario de Inicio de Sesión -->
        <div id="loginForm" class="form active">
            <h2>Iniciar Sesión</h2>
            <div class="input-group">
                <input type="text" id="loginEmail" required>
                <label>Correo Electrónico</label>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="input-group">
                <input type="password" id="loginPassword" required>
                <label>Contraseña</label>
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye toggle-password"></i>
            </div>
            <button type="submit" class="btn" id="loginBtn">Iniciar Sesión</button>
            <p class="switch-form">¿No tienes cuenta? <a href="#" id="showRegister">Regístrate</a></p>
        </div>

        <!-- Formulario de Registro -->
        <div id="registerForm" class="form">
            <h2>Crear Cuenta</h2>
            <div class="profile-upload">
                <div class="profile-pic">
                    <img src="Imagenes/placeholder.png" id="profilePreview">

                </div>
                <input type="file" id="profilePic" accept="image/*" hidden>
            </div>
            <div class="input-group">
                <input type="text" id="regName" required>
                <label>Nombre</label>
                <i class="fas fa-user"></i>
            </div>
            <div class="input-group">
                <input type="email" id="regEmail" required>
                <label>Correo Electrónico</label>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="input-group">
                <input type="password" id="regPassword" required>
                <label>Contraseña</label>
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye toggle-password"></i>
            </div>
            <button type="submit" class="btn" id="registerBtn">Registrarse</button>
            <p class="switch-form">¿Ya tienes cuenta? <a href="#" id="showLogin">Inicia Sesión</a></p>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>