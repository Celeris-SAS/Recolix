<?php


require_once __DIR__ . '/../conexion.php';

try {
    $db   = new conexion();
    $conn = $db->getConexion();

    $ci = 12345678;

    $check = $conn->prepare("SELECT ci_usuario FROM USUARIO WHERE ci_usuario = ?");
    $check->bind_param('i', $ci);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        $rolCorrecto = 'administrador';
        $accesoCorrecto = 'habilitado';
        $upd = $conn->prepare("UPDATE USUARIO SET rol = ?, acceso = ? WHERE ci_usuario = ?");
        $upd->bind_param('ssi', $rolCorrecto, $accesoCorrecto, $ci);
        $upd->execute();
        $upd->close();
        echo "<p style='font-family:sans-serif; padding:1rem;'>";
        echo "<strong>El usuario admin ya existía — se corrigieron rol y acceso.</strong><br><br>";
        echo "CI: <code>$ci</code><br>";
        echo "Contraseña: <code>admin1234</code> (si fue creado antes con este script)<br>";
        echo "Rol actualizado a: <code>$rolCorrecto</code><br>";
        echo "Acceso actualizado a: <code>$accesoCorrecto</code><br><br>";
        echo "<span style='color:#c0392b'>Eliminá este archivo cuando ya no lo necesites.</span>";
        echo "</p>";
        exit;
    }
    $check->close();


    $nombre      = 'Admin';
    $apellido    = 'Test';
    $rol         = 'administrador'; 
    $acceso      = 'habilitado';    
    $email       = 'admin@recolix.com';
    $tel         = '099000000';
    $hash        = password_hash('admin1234', PASSWORD_DEFAULT);
    $idMunicipio = null; 

    $stmt = $conn->prepare(
        "INSERT INTO USUARIO (ci_usuario, id_municipio, primer_nombre, p_apellido, rol, acceso, email, tel, contrasenia)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('iisssssss', $ci, $idMunicipio, $nombre, $apellido, $rol, $acceso, $email, $tel, $hash);
    $stmt->execute();
    $stmt->close();

    echo "<p style='font-family:sans-serif; padding:1rem;'>";
    echo "<strong>Admin creado correctamente.</strong><br><br>";
    echo "CI: <code>$ci</code><br>";
    echo "Contraseña: <code>admin1234</code><br>";
    echo "Rol: <code>$rol</code><br><br>";
    echo "<span style='color:#c0392b'>Eliminá este archivo cuando ya no lo necesites.</span>";
    echo "</p>";

} catch (Exception $e) {
    echo "<p style='color:red; font-family:sans-serif; padding:1rem;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
