function agregarAlCarrito(id) {
    if (!id) return;

    fetch('php/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('cartCount');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            }
            alert('¡Producto agregado al carrito!');
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar al carrito');
    });
}
