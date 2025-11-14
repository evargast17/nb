<?php
/**
 * Script para generar hashes de contraseñas para los padres
 * Las contraseñas son iguales a sus DNIs
 */

$padres = [
    '12345678' => '12345678',
    '23456789' => '23456789',
    '34567890' => '34567890',
    '45678901' => '45678901',
    '56789012' => '56789012',
    '67890123' => '67890123',
    '78901234' => '78901234',
    '89012345' => '89012345',
    '90123456' => '90123456',
    '01234567' => '01234567',
];

echo "-- Hashes generados para actualizar las contraseñas de padres\n";
echo "-- Usar estos valores en el INSERT de la tabla padres\n\n";

foreach ($padres as $dni => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    echo "-- DNI: $dni | Password: $password\n";
    echo "'$hash',\n\n";
}

echo "\n\n-- Script SQL para actualizar contraseñas:\n\n";
foreach ($padres as $dni => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    echo "UPDATE padres SET password = '$hash' WHERE dni = '$dni';\n";
}
?>
