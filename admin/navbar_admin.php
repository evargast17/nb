&lt;?php
// Obtener datos del administrador
$admin_nombre = 'Administrador';
if (isset($_SESSION['admin_id'])) {
    $stmt = $conn->prepare("SELECT nombre FROM administradores WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        $admin_nombre = $result['nombre'];
    }
}
?>

&lt;nav class="navbar">
    &lt;div class="container">
        &lt;div class="navbar-brand">
            &lt;h2>&lt;i class="fas fa-graduation-cap">&lt;/i> Sistema de Notas MINEDU 2025&lt;/h2>
        &lt;/div>
        &lt;div class="navbar-menu">
            &lt;span class="user-name">
                &lt;i class="fas fa-user-shield">&lt;/i>
                &lt;?php echo htmlspecialchars($admin_nombre); ?>
            &lt;/span>
            &lt;a href="../admin_logout.php" class="btn btn-outline btn-sm">
                &lt;i class="fas fa-sign-out-alt">&lt;/i> Cerrar Sesión
            &lt;/a>
        &lt;/div>
    &lt;/div>
&lt;/nav>
