<?php
include 'config.php';

if (isset($_GET['id_carrito'])) {
    $id_car = mysqli_real_escape_string($conexion, $_GET['id_carrito']);
    
    // 1. Obtener datos actuales del carrito
    $res = mysqli_query($conexion, "SELECT * FROM carritos WHERE id = $id_car");
    $car = mysqli_fetch_assoc($res);
    
    // 2. Separar nombre y apellido (opcional, basado en el primer espacio)
    $partes = explode(" ", $car['nombre_responsable'], 2);
    $nom = mysqli_real_escape_string($conexion, $partes[0]);
    $ape = mysqli_real_escape_string($conexion, $partes[1] ?? '');
    $tel = mysqli_real_escape_string($conexion, $car['telefono_responsable']);

    // 3. Insertar en tabla personas
    $ins_p = "INSERT INTO personas (nombres, apellidos, telefono) VALUES ('$nom', '$ape', '$tel')";
    
    if (mysqli_query($conexion, $ins_p)) {
        $nuevo_id = mysqli_insert_id($conexion);
        
        // 4. Crear el perfil de emprendedor vinculado
        $negocio = mysqli_real_escape_string($conexion, $car['nombre_carrito']);
        mysqli_query($conexion, "INSERT INTO emprendedores (personas_idpersonas, tipo_negocio, rubro) 
                                VALUES ($nuevo_id, '$negocio', 'General')");
        
        // Redirigir a editar para completar el RUT
        header("Location: editar_persona.php?id=$nuevo_id&status=convertido");
    }
}
?>
