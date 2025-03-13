<?php
// Incluir la configuración de la base de datos
require_once 'app.php';  // Se incluye el archivo de configuración principal
require_once 'database.php'; // Se incluye el archivo de la conexión a la base de datos

use Model\Usuario; 

// Establecer los valores para el nuevo usuario
$nombre = "Administrador";
$apellido = "Admin";
$email = "correo@correo.com";
$password = "123456"; // Contraseña en texto claro

// Hashear el password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Crear el nuevo usuario
$usuario = new Usuario ([
    'nombre' => $nombre,
    'apellido' => $apellido,
    'email' => $email,
    'pass' => $hashed_password,  // Almacenamos el password ya hasheado
]);

// Intentamos guardar el nuevo usuario
$usuario->guardar();

// Mensaje de éxito
echo "Usuario administrador creado correctamente";