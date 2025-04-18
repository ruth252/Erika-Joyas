<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'No hay sesión activa']));
}

$response = ['success' => false, 'message' => '', 'debug' => []];

try {
    if (isset($_FILES['photo'])) {
        $uploadDir = '../uploads/profile_pics/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.jpg';
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photo_url = 'uploads/profile_pics/' . $fileName;
            
            $stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
            $stmt->bind_param("si", $photo_url, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['user_photo'] = $photo_url;
                $response['photo_url'] = $photo_url;
            }
        }
    }

    if (isset($_POST['name']) || isset($_POST['email'])) {
        $name = $_POST['name'] ?? $_SESSION['user_name'];
        $email = $_POST['email'] ?? $_SESSION['user_email'];
        
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $response['success'] = true;
            $response['message'] = 'Perfil actualizado correctamente';
        }
    }

    // Añadir información de depuración
    $response['debug'] = [
        'post' => $_POST,
        'files' => $_FILES,
        'session' => $_SESSION
    ];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['debug']['error'] = $e->getMessage();
}

echo json_encode($response);
