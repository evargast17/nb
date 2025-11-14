<?php
// Script para generar hashes de contraseñas

$passwords = [
    '12345678' => password_hash('12345678', PASSWORD_DEFAULT),
    '87654321' => password_hash('87654321', PASSWORD_DEFAULT),
    'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
];

echo "Hashes generados:\n\n";
foreach ($passwords as $pass => $hash) {
    echo "Contraseña: $pass\n";
    echo "Hash: $hash\n";
    echo "Verificación: " . (password_verify($pass, $hash) ? 'OK' : 'FAIL') . "\n\n";
}
?>
