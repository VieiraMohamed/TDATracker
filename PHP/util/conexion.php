<?php
$_servidor = "sql208.infinityfree.com";  // El servidor MySQL está en localhost
$_usuario = "if0_38607017";  // El usuario root es común en instalaciones locales
$_contrasena = "sgmFjzu9YNqffX";  // Si no tienes contraseña en root, déjalo vacío
$_base_de_datos = "if0_38607017_tdatracker";  // La base de datos que estás utilizando


// Establecer la conexión
$_conexion = new mysqli($_servidor, $_usuario, $_contrasena, $_base_de_datos);


// Comprobar si hubo un error de conexión
if ($_conexion->connect_error) {
    die("Error de conexión: " . $_conexion->connect_error);
}
