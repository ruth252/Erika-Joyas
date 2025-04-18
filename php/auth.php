<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'photo' => $user['photo'] ? $user['photo'] : 'Imagenes/placeholder.png'
                ];
                echo json_encode(['success' => true, 'user' => $_SESSION['user']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
            }
            break;

        case 'register':
            try {
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $photo = isset($_POST['photo']) ? $_POST['photo'] : null;

                // Asegurarnos de guardar la imagen si se proporcionó una
                if ($photo && strpos($photo, 'data:image') === 0) {
                    $photo = $photo; // Guardamos la imagen en base64
                }

                $stmt = $conn->prepare("INSERT INTO users (name, email, password, photo) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $password, $photo]);
                
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error al registrar usuario']);
            }
            break;

        case 'update_profile':
            if (!isset($_SESSION['user'])) {
                echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
                exit;
            }

            $user_id = $_SESSION['user']['id'];
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $photo = $_POST['photo'] ?? null;
            
            try {
                // Verificar si se está actualizando la contraseña
                if (!empty($_POST['new_password']) && !empty($_POST['current_password'])) {
                    // Primero verificar la contraseña actual
                    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();

                    if (!password_verify($_POST['current_password'], $user['password'])) {
                        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
                        exit;
                    }

                    // Si la contraseña actual es correcta, actualizar todo incluyendo la nueva contraseña
                    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $query = "UPDATE users SET name = ?, email = ?, photo = ?, password = ? WHERE id = ?";
                    $params = [$name, $email, $photo, $new_password, $user_id];
                } else {
                    // Si no hay cambio de contraseña, actualizar solo los otros campos
                    $query = "UPDATE users SET name = ?, email = ?, photo = ? WHERE id = ?";
                    $params = [$name, $email, $photo, $user_id];
                }

                $stmt = $conn->prepare($query);
                $stmt->execute($params);

                // Actualizar sesión
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['photo'] = $photo;

                echo json_encode([
                    'success' => true,
                    'user' => $_SESSION['user'],
                    'message' => 'Perfil actualizado correctamente'
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar perfil: ' . $e->getMessage()]);
            }
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
}
?>
