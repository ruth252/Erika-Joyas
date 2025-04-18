document.addEventListener('DOMContentLoaded', () => {
    const userProfile = document.getElementById('userProfile');
    const loginLink = document.getElementById('loginLink');
    const userAvatar = document.querySelector('.user-avatar');
    const userName = document.querySelector('.user-name');
    
    function actualizarUI() {
        const usuario = JSON.parse(localStorage.getItem('usuarioActual'));
        
        if (usuario) {
            loginLink.style.display = 'none';
            userProfile.style.display = 'flex';
            userAvatar.src = usuario.photo || 'Imagenes/default-avatar.png';
            userName.textContent = usuario.name;
        } else {
            loginLink.style.display = 'block';
            userProfile.style.display = 'none';
        }
    }

    // Manejar cierre de sesión
    const cerrarSesion = document.getElementById('cerrarSesion');
    if (cerrarSesion) {
        cerrarSesion.addEventListener('click', (e) => {
            e.preventDefault();
            localStorage.removeItem('usuarioActual');
            window.location.href = 'login.html';
        });
    }

    // Manejar edición de perfil
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfileModal = document.getElementById('editProfileModal');
    const closeModal = document.querySelector('.close');
    
    if (editProfileBtn && editProfileModal) {
        editProfileBtn.onclick = () => {
            const usuario = JSON.parse(localStorage.getItem('usuarioActual'));
            if (usuario) {
                document.getElementById('currentProfilePic').src = usuario.photo;
                document.getElementById('editName').value = usuario.name;
                document.getElementById('editUser').value = usuario.user;
                document.getElementById('editEmail').value = usuario.email;
                editProfileModal.style.display = 'flex';
            }
        };

        closeModal.onclick = () => editProfileModal.style.display = 'none';

        // Manejar cambio de foto
        document.getElementById('newProfilePic').onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('currentProfilePic').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        };

        // Manejar guardado de cambios
        document.getElementById('editProfileForm').onsubmit = function(e) {
            e.preventDefault();
            const usuarios = JSON.parse(localStorage.getItem('usuarios') || '[]');
            const usuarioActual = JSON.parse(localStorage.getItem('usuarioActual'));
            const index = usuarios.findIndex(u => u.user === usuarioActual.user);

            if (index !== -1) {
                const datosActualizados = {
                    ...usuarios[index],
                    name: document.getElementById('editName').value,
                    photo: document.getElementById('currentProfilePic').src,
                    email: document.getElementById('editEmail').value
                };

                usuarios[index] = datosActualizados;
                localStorage.setItem('usuarios', JSON.stringify(usuarios));
                localStorage.setItem('usuarioActual', JSON.stringify(datosActualizados));
                
                editProfileModal.style.display = 'none';
                actualizarUI();
                alert('Perfil actualizado correctamente');
            }
        };
    }

    actualizarUI();
});
