document.addEventListener('DOMContentLoaded', () => {
    // Elementos DOM
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const showRegister = document.getElementById('showRegister');
    const showLogin = document.getElementById('showLogin');
    
    // Cambiar entre formularios
    showRegister.addEventListener('click', (e) => {
        e.preventDefault();
        loginForm.classList.remove('active');
        registerForm.classList.add('active');
    });

    showLogin.addEventListener('click', (e) => {
        e.preventDefault();
        registerForm.classList.remove('active');
        loginForm.classList.add('active');
    });

    // Manejar vista previa de imagen
    const profilePic = document.getElementById('profilePic');
    const preview = document.getElementById('profilePreview');

    profilePic.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2000000) { // 2MB límite
                alert('La imagen es demasiado grande. Máximo 2MB');
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    // Registro modificado
    document.getElementById('registerBtn').addEventListener('click', (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'register');
        formData.append('name', document.getElementById('regName').value);
        formData.append('email', document.getElementById('regEmail').value);
        formData.append('password', document.getElementById('regPassword').value);
        
        // Asegurarnos de enviar la imagen solo si se seleccionó una
        const preview = document.getElementById('profilePreview');
        if (preview.src && !preview.src.includes('placeholder.png')) {
            formData.append('photo', preview.src);
        }

        fetch('php/auth.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('¡Registro exitoso! Por favor inicia sesión');
                showLogin.click();
            } else {
                alert(data.message || 'Error al registrar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });

    // Login modificado
    document.getElementById('loginBtn').addEventListener('click', (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('email', document.getElementById('loginEmail').value);
        formData.append('password', document.getElementById('loginPassword').value);

        fetch('php/auth.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirigir directamente sin usar sessionStorage
                window.location.href = 'inicio.php';
            } else {
                alert('Correo o contraseña incorrectos');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });

    // Toggle password visibility mejorado
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input[type="password"]');
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }
        });
    });
});
