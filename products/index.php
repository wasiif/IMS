<?php
$page_title = 'Products';
require_once __DIR__ . '/../includes/header.php';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$_GET['delete']]);
        $success_message = 'Product deleted successfully!';
    } catch (PDOException $e) {
        error_log('Error deleting product: ' . $e->getMessage());
        $error_message = 'Failed to delete product. Please try again.';
    }
}

// Handle success messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $success_message = 'Product added successfully!';
            break;
        case 'updated':
            $success_message = 'Product updated successfully!';
            break;
        case 'deleted':
            $success_message = 'Product deleted successfully!';
            break;
    }
}

// Handle error messages
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'delete_failed':
            $error_message = 'Failed to delete product. Please try again.';
            break;
    }
}

// Get all products with category names
try {
    $stmt = $pdo->query('
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.name ASC
    ');
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching products: ' . $e->getMessage());
    $error_message = 'Failed to load products. Please try again.';
    $products = [];
}
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-box text-primary me-2"></i>Products
                </h2>
                <p class="text-muted mb-0">Manage your inventory products</p>
            </div>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Product
            </a>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if (isset($success_message)): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Products Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Products (<?php echo count($products); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-box display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">No products found</h5>
                    <p class="text-muted mb-4">Start by adding your first product to the inventory.</p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add First Product
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>
                                    <?php if ($product['category_name']): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">No category</span>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $product['quantity'] > 10 ? 'bg-success' : ($product['quantity'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                        <?php echo htmlspecialchars($product['quantity']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the product "<strong id="deleteProductName"></strong>"?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="deleteLink" href="#" class="btn btn-danger">Delete Product</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(productId, productName) {
    document.getElementById('deleteProductName').textContent = productName;
    document.getElementById('deleteLink').href = 'delete.php?id=' + productId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>