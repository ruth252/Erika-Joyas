<?php
require_once 'php/db_config.php';
if($conn) {
    echo "Conexión exitosa a la base de datos";
} else {
    echo "Error de conexión";
}
?>
