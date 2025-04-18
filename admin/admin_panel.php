<?php
session_start();
require_once '../php/db_config.php';

// Obtener productos
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar eliminación de producto
if (isset($_GET['delete_id'])) {
    try {
        $id = $_GET['delete_id'];

        // Eliminar referencias en el carrito
        $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
        $stmt->execute([$id]);

        // Eliminar producto
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success_message'] = "Producto eliminado exitosamente";
        header("Location: admin_panel.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al eliminar el producto: " . $e->getMessage();
    }
}

// Manejar edición de producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    try {
        $id = $_POST['edit_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $stock = $_POST['stock'];

        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category = ?, description = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $price, $category, $description, $stock, $id]);

        $_SESSION['success_message'] = "Producto actualizado exitosamente";
        header("Location: admin_panel.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar el producto: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../estilo.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Estilos generales */
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-edit {
            background-color: #4caf50;
            color: white;
            transition: background-color 0.3s ease;
        }
        .btn-edit:hover {
            background-color: #45a049;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
            transition: background-color 0.3s ease;
        }
        .btn-delete:hover {
            background-color: #e53935;
        }

        /* Modal de edición */
        #editModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #editModal .modal-content {
            background: white;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s ease;
        }
        #editModal .modal-content h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        #editModal .modal-content label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        #editModal .modal-content input,
        #editModal .modal-content select,
        #editModal .modal-content textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        #editModal .modal-content input:focus,
        #editModal .modal-content select:focus,
        #editModal .modal-content textarea:focus {
            border-color: #d4af37;
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.3);
            outline: none;
        }
        #editModal .modal-content textarea {
            resize: vertical;
            min-height: 100px;
        }
        #editModal .modal-content .btn {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 8px;
        }
        #editModal .modal-content .btn-edit {
            background-color: #4caf50;
            color: white;
        }
        #editModal .modal-content .btn-edit:hover {
            background-color: #45a049;
        }
        #editModal .modal-content .btn-delete {
            background-color: #f44336;
            color: white;
        }
        #editModal .modal-content .btn-delete:hover {
            background-color: #e53935;
        }

        /* Animación del modal */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Panel de Administración</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <!-- Barra de búsqueda -->
        <div class="search-bar">
            <input type="text" id="searchProduct" placeholder="Buscar producto...">
            <button class="add-btn" onclick="window.location.href='agregar_producto.php'">
                <i class="fas fa-plus"></i> Nuevo Producto
            </button>
        </div>

        <!-- Tabla de productos -->
        <div class="products-table">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No hay productos disponibles</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($productos as $producto): ?>
                        <tr data-id="<?php echo $producto['id']; ?>">
                            <td>
                                <img src="../Imagenes/<?php echo htmlspecialchars($producto['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($producto['name']); ?>" 
                                     class="product-thumb">
                            </td>
                            <td><?php echo htmlspecialchars($producto['name']); ?></td>
                            <td>$<?php echo number_format($producto['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($producto['category']); ?></td>
                            <td><?php echo $producto['stock']; ?></td>
                            <td class="actions">
                                <button class="btn btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($producto)); ?>)">Editar</button>
                                <a href="?delete_id=<?php echo $producto['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de edición -->
    <div id="editModal">
        <div class="modal-content">
            <h2>Editar Producto</h2>
            <form method="POST" action="editar_producto.php">
                <input type="hidden" name="id" id="edit_id">
                <div>
                    <label>Nombre:</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div>
                    <label>Precio:</label>
                    <input type="number" name="price" id="edit_price" step="0.01" required>
                </div>
                <div>
                    <label>Categoría:</label>
                    <select name="category" id="edit_category" required>
                        <option value="collares">Collares</option>
                        <option value="anillos">Anillos</option>
                        <option value="pulseras">Pulseras</option>
                        <option value="aretes">Aretes</option>
                    </select>
                </div>
                <div>
                    <label>Descripción:</label>
                    <textarea name="description" id="edit_description" rows="4" required></textarea>
                </div>
                <div>
                    <label>Stock:</label>
                    <input type="number" name="stock" id="edit_stock" min="0" required>
                </div>
                <button type="submit" class="btn btn-edit">Guardar Cambios</button>
                <button type="button" class="btn btn-delete" onclick="closeEditModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script src="admin.js"></script>
    <script>
        // Asegurarse de que el modal esté cerrado al cargar la página
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('editModal').style.display = 'none';
        });

        function openEditModal(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_category').value = product.category;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Evitar que el modal se cierre automáticamente al hacer clic fuera del contenido
        document.getElementById('editModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
