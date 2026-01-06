<?php
$page_title = 'Sales';
require_once __DIR__ . '/../includes/header.php';

// Handle success messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'recorded':
            $success_message = 'Sale recorded successfully!';
            break;
        case 'updated':
            $success_message = 'Sale updated successfully!';
            break;
    }
}

// Handle error messages
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'insufficient_stock':
            $error_message = 'Insufficient stock for this product.';
            break;
        case 'invalid_product':
            $error_message = 'Invalid product selected.';
            break;
    }
}

// Get all sales with product information
try {
    $stmt = $pdo->query('
        SELECT s.*, p.name as product_name, p.price, c.name as category_name
        FROM sales s
        JOIN products p ON s.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY s.sale_date DESC
    ');
    $sales = $stmt->fetchAll();

    // Calculate total revenue
    $totalRevenue = 0;
    foreach ($sales as $sale) {
        $totalRevenue += $sale['price'] * $sale['quantity'];
    }

} catch (PDOException $e) {
    error_log('Error fetching sales: ' . $e->getMessage());
    $error_message = 'Failed to load sales. Please try again.';
    $sales = [];
    $totalRevenue = 0;
}
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-receipt text-primary me-2"></i>Sales
                </h2>
                <p class="text-muted mb-0">View and manage sales records</p>
            </div>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Record Sale
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-receipt display-4 mb-3"></i>
                <h3><?php echo count($sales); ?></h3>
                <p class="mb-0">Total Sales</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-cash display-4 mb-3"></i>
                <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
                <p class="mb-0">Total Revenue</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-calendar-event display-4 mb-3"></i>
                <h3><?php echo count($sales) > 0 ? date('M j', strtotime($sales[0]['sale_date'])) : 'N/A'; ?></h3>
                <p class="mb-0">Latest Sale</p>
            </div>
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

<!-- Sales Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sales Records (<?php echo count($sales); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($sales)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-receipt display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">No sales records found</h5>
                    <p class="text-muted mb-4">Start by recording your first sale.</p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Record First Sale
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['id']); ?></td>
                                <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                <td>
                                    <?php if ($sale['category_name']): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($sale['category_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">No category</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($sale['quantity']); ?></td>
                                <td>$<?php echo number_format($sale['price'], 2); ?></td>
                                <td><strong>$<?php echo number_format($sale['price'] * $sale['quantity'], 2); ?></strong></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($sale['sale_date'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?php echo $sale['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>