document.addEventListener('DOMContentLoaded', () => {
    // Manejo de filtros de categorías
    const botones = document.querySelectorAll('.filtro-btn');
    const productos = document.querySelectorAll('.producto-card');

    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            // Remover clase activo de todos los botones
            botones.forEach(b => b.classList.remove('activo'));
            // Agregar clase activo al botón seleccionado
            boton.classList.add('activo');

            const categoria = boton.getAttribute('data-categoria');
            
            productos.forEach(producto => {
                const productoCategoria = producto.getAttribute('data-categoria');
                
                if (categoria === 'todos' || categoria === productoCategoria) {
                    producto.style.display = 'block';
                } else {
                    producto.style.display = 'none';
                }
            });
        });
    });
});
