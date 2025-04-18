document.addEventListener('DOMContentLoaded', function() {
    // Búsqueda de productos
    const searchInput = document.getElementById('searchProduct');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Configuración del modal
    const modal = document.getElementById('editModal');
    if (modal) {
        const closeBtn = modal.querySelector('.close');
        if (closeBtn) {
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    }

    // Manejar el formulario de edición
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'edit');

            fetch('../php/product_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Producto actualizado correctamente');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al actualizar el producto');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
    }
});

// Funciones globales
window.editarProducto = function(id) {
    fetch(`../php/product_actions.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const producto = data.product;
                const modal = document.getElementById('editModal');
                
                // Llenar el formulario
                document.getElementById('editId').value = producto.id;
                document.getElementById('editName').value = producto.name;
                document.getElementById('editPrice').value = producto.price;
                document.getElementById('editCategory').value = producto.category;
                document.getElementById('editDescription').value = producto.description;
                document.getElementById('editStock').value = producto.stock;
                
                // Mostrar la imagen actual
                const imagenPreview = document.getElementById('currentImage');
                if (imagenPreview && producto.image) {
                    imagenPreview.src = '../Imagenes/' + producto.image;
                    imagenPreview.style.display = 'block';
                }
                
                // Mostrar el modal
                modal.style.display = 'block';
            } else {
                alert('Error al cargar el producto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar el producto');
        });
};

window.eliminarProducto = function(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
        fetch('../php/product_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // En lugar de recargar la página, eliminamos la fila específica
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // Si no hay más productos, mostrar mensaje
                        const tbody = document.querySelector('tbody');
                        if (tbody.children.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No hay productos disponibles</td></tr>';
                        }
                    }, 300);
                }
                alert('Producto eliminado correctamente');
            } else {
                alert(data.message || 'Error al eliminar el producto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el producto');
        });
    }
};
