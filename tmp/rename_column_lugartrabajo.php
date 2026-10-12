<?php
$db = new PDO('mysql:host=localhost;dbname=guataparo', 'UserAdmin', 'Guataparo26');
try {
    $db->exec("ALTER TABLE postulados CHANGE lugartrabajo correo_electronico VARCHAR(255) NULL");
    echo "Column lugartrabajo renamed to correo_electronico successfully.";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
