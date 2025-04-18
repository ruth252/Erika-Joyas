<?php
session_start();
require_once '../php/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $category = $_POST['category'];
        $description = trim($_POST['description']);
        $stock = intval($_POST['stock']);
        
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category = ?, description = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $price, $category, $description, $stock, $id]);

        $_SESSION['success_message'] = "Producto actualizado exitosamente.";
        header("Location: admin_panel.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error al actualizar el producto: " . $e->getMessage();
        header("Location: admin_panel.php");
        exit;
    }
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        $_SESSION['error_message'] = "Producto no encontrado.";
        header("Location: admin_panel.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            color: #333;
            margin: 0;
            padding: 0;
        }

        .admin-form {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-header h2 {
            font-size: 1.8rem;
            color: #333;
        }

        .volver-btn {
            padding: 10px 20px;
            background: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .volver-btn:hover {
            background: #555;
        }

        .btn-guardar {
            width: 100%;
            padding: 15px;
            background: #d4af37;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-guardar:hover {
            background: #b5912b;
            transform: translateY(-2px);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 1rem;
            font-weight: bold;
            color: #555;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #d4af37;
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.3);
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group img {
            display: block;
            margin-top: 10px;
            max-width: 100px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 8px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="admin-form" style="border: 2px solid #d4af37;">
        <div class="form-header">
            <h2>Editar Producto</h2>
            <a href="admin_panel.php" class="volver-btn">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($producto['id']); ?>">
            <div class="form-group">
                <label>Nombre del Producto:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($producto['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Precio:</label>
                <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($producto['price']); ?>" required>
            </div>

            <div class="form-group">
                <label>Categoría:</label>
                <select name="category" required>
                    <option value="collares" <?php if ($producto['category'] == 'collares') echo 'selected'; ?>>Collares</option>
                    <option value="anillos" <?php if ($producto['category'] == 'anillos') echo 'selected'; ?>>Anillos</option>
                    <option value="pulseras" <?php if ($producto['category'] == 'pulseras') echo 'selected'; ?>>Pulseras</option>
                    <option value="aretes" <?php if ($producto['category'] == 'aretes') echo 'selected'; ?>>Aretes</option>
                </select>
            </div>

            <div class="form-group">
                <label>Imagen (opcional):</label>
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($producto['image'])): ?>
                    <img src="../Imagenes/<?php echo htmlspecialchars($producto['image']); ?>" alt="Imagen del producto">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="description" rows="4" required><?php echo htmlspecialchars($producto['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Stock disponible:</label>
                <input type="number" name="stock" min="0" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
            </div>

            <button type="submit" class="btn-guardar">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </form>
    </div>

    <script>
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir envío automático
            if (confirm('¿Deseas guardar los cambios?')) {
                this.submit();
            }
        });
    </script>
</body>
</html>