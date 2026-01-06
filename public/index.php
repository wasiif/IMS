<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Get some stats for dashboard
$productCount = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$categoryCount = $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$saleCount = $pdo->query('SELECT COUNT(*) FROM sales')->fetchColumn();
?>

<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card welcome-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="bi bi-person-circle me-2"></i>
                            Welcome back, <?php echo htmlspecialchars($username); ?>!
                        </h4>
                        <p class="card-text mb-0">Here's what's happening with your inventory today.</p>
                    </div>
                    <div class="text-end">
                        <small class="text-white-50">
                            <?php echo date('l, F j, Y'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-box display-4 mb-3"></i>
                <h3><?php echo $productCount; ?></h3>
                <p class="mb-0">Total Products</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-tags display-4 mb-3"></i>
                <h3><?php echo $categoryCount; ?></h3>
                <p class="mb-0">Categories</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-receipt display-4 mb-3"></i>
                <h3><?php echo $saleCount; ?></h3>
        <p class="mb-0">Total Sales</p>
    </div>
</div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightning text-warning me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="../products/" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center p-4 text-decoration-none">
                            <i class="bi bi-plus-circle display-4 mb-2"></i>
                            <span>Add Product</span>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../categories/" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center p-4 text-decoration-none">
                            <i class="bi bi-folder-plus display-4 mb-2"></i>
                            <span>Add Category</span>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../sales/" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center p-4 text-decoration-none">
                            <i class="bi bi-cart-plus display-4 mb-2"></i>
                            <span>Record Sale</span>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../reports/" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center p-4 text-decoration-none">
                            <i class="bi bi-graph-up display-4 mb-2"></i>
                            <span>View Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Placeholder -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-activity text-info me-2"></i>Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-clock-history display-4 mb-3 opacity-50"></i>
                    <p class="mb-0">Recent activities will be displayed here</p>
                    <small>This feature will show recent product additions, sales, and inventory changes.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>