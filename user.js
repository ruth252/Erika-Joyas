document.addEventListener('DOMContentLoaded', () => {
    function loadUserInfo() {
        // Intentar obtener datos de la sesión PHP primero
        fetch('php/check_session.php')
            .then(response => response.json())
            .then(data => {
                if (data.user) {
                    updateUserUI(data.user);
                } else {
                    // Si no hay sesión PHP, redirigir al login
                    window.location.href = 'login.php';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.href = 'login.php';
            });
    }

    function updateUserUI(user) {
        const userProfile = document.getElementById('userProfile');
        const userAvatar = userProfile.querySelector('.user-avatar');
        const userName = userProfile.querySelector('.user-name');

        if (userProfile && userAvatar && userName) {
            // Añadir timestamp para evitar caché de la imagen
            const timestamp = new Date().getTime();
            userAvatar.src = user.photo ? `${user.photo}?t=${timestamp}` : 'Imagenes/placeholder.png';
            userName.textContent = user.name;
            userProfile.style.display = 'flex';
        }
    }

    loadUserInfo();
    
    // Manejar cierre de sesión
    const cerrarSesion = document.getElementById('cerrarSesion');
    if (cerrarSesion) {
        cerrarSesion.addEventListener('click', (e) => {
            e.preventDefault();
            fetch('php/auth.php?action=logout')
                .then(() => {
                    sessionStorage.removeItem('usuarioActual');
                    window.location.href = 'login.php';
                });
        });
    }

    // Manejar edición de perfil
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfileModal = document.getElementById('editProfileModal');
    
    if (editProfileBtn && editProfileModal) {
        const closeModal = editProfileModal.querySelector('.close');
        const editProfileForm = document.getElementById('editProfileForm');
        const currentProfilePic = document.getElementById('currentProfilePic');
        const newProfilePic = document.getElementById('newProfilePic');

        editProfileBtn.onclick = () => {
            // Obtener usuario actual de la sesión
            fetch('php/check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (data.user) {
                        currentProfilePic.src = data.user.photo || 'Imagenes/placeholder.png';
                        document.getElementById('editName').value = data.user.name;
                        document.getElementById('editEmail').value = data.user.email;
                        editProfileModal.style.display = 'flex';
                    }
                });
        };

        closeModal.onclick = () => editProfileModal.style.display = 'none';

        newProfilePic.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5242880) { // 5MB
                    alert('La imagen es demasiado grande. El tamaño máximo es 5MB');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    currentProfilePic.src = e.target.result;
                    currentProfilePic.style.display = 'block'; // Asegurar que la imagen sea visible
                }
                reader.readAsDataURL(file);
            }
        };

        // Actualizar el manejo de mostrar/ocultar contraseña
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
        });

        editProfileForm.onsubmit = (e) => {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'update_profile');
            formData.append('name', document.getElementById('editName').value);
            formData.append('email', document.getElementById('editEmail').value);
            
            // Manejar la foto del perfil
            const newProfilePicInput = document.getElementById('newProfilePic');
            if (newProfilePicInput.files[0]) {
                formData.append('photo', newProfilePicInput.files[0]);
            }

            // Validar contraseñas si se están cambiando
            const newPassword = document.getElementById('newPassword').value;
            const currentPassword = document.getElementById('currentPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword || currentPassword) {
                if (!currentPassword) {
                    alert('Debes ingresar tu contraseña actual');
                    return;
                }
                if (!newPassword) {
                    alert('Debes ingresar la nueva contraseña');
                    return;
                }
                if (newPassword !== confirmPassword) {
                    alert('Las contraseñas nuevas no coinciden');
                    return;
                }
                formData.append('current_password', currentPassword);
                formData.append('new_password', newPassword);
            }

            fetch('php/upload_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta del servidor:', data); // Añadir para depuración
                
                if (data.success) {
                    // Actualizar la imagen inmediatamente si hay una nueva URL
                    if (data.photo_url) {
                        const timestamp = new Date().getTime();
                        const userAvatar = document.querySelector('.user-avatar');
                        const currentProfilePic = document.getElementById('currentProfilePic');
                        
                        const newPhotoUrl = `${data.photo_url}?t=${timestamp}`;
                        userAvatar.src = newPhotoUrl;
                        currentProfilePic.src = newPhotoUrl;
                    }
                    
                    alert('Perfil actualizado correctamente');
                    editProfileModal.style.display = 'none';
                    
                    // Forzar recarga de datos
                    loadUserInfo();
                } else {
                    console.error('Error del servidor:', data.debug); // Añadir para depuración
                    alert(data.message || 'Error al actualizar perfil');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar perfil');
            });
        };
    }

    // Remover el evento de navegación a favoritos del menú principal
    const favoritosBtn = document.getElementById('favoritosBtn');
    if (favoritosBtn && !window.location.pathname.includes('favorites.php')) {
        favoritosBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = 'favorites.php';
        });
    }
});
