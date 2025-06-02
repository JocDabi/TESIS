<?php
$contrasena_texto_plano = '1234';
$contrasena_encriptada = password_hash($contrasena_texto_plano, PASSWORD_DEFAULT);

echo "Contraseña en texto plano: " . $contrasena_texto_plano . "\n";
echo "Contraseña encriptada (hash): " . $contrasena_encriptada . "\n";
?>