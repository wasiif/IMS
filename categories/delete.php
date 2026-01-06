<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Check if ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$category_id = (int)$_GET['id'];

// Check if category exists
try {
    $stmt = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();

    if (!$category) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error checking category existence: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}

// Check if category has associated products
try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
    $stmt->execute([$category_id]);
    $product_count = $stmt->fetchColumn();

    if ($product_count > 0) {
        // Category has products, redirect with error
        header('Location: index.php?error=has_products');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error checking products for category: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}

// Delete the category
try {
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$category_id]);

    header('Location: index.php?success=deleted');
    exit;
} catch (PDOException $e) {
    error_log('Error deleting category: ' . $e->getMessage());
    header('Location: index.php?error=delete_failed');
    exit;
}
?>