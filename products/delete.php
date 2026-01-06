<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Check if ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = (int)$_GET['id'];

// Check if product exists
try {
    $stmt = $pdo->prepare('SELECT name FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error checking product existence: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}

// Check if product has associated sales (optional - depending on business logic)
// You might want to allow deletion even if there are sales, or prevent it
// For now, we'll allow deletion but log it

// Delete the product
try {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$product_id]);

    header('Location: index.php?success=deleted');
    exit;
} catch (PDOException $e) {
    error_log('Error deleting product: ' . $e->getMessage());
    header('Location: index.php?error=delete_failed');
    exit;
}
?>