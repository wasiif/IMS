<?php
// Handle all logic before any output

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$sale_id = (int)$_GET['id'];
$sale = null;
$errors = [];

// Include database connection for early logic
require_once __DIR__ . '/../config/db.php';
$pdo = require __DIR__ . '/../config/db.php';

// Fetch sale data
try {
    $stmt = $pdo->prepare('
        SELECT s.*, p.name as product_name, p.quantity as current_stock, p.price
        FROM sales s
        JOIN products p ON s.product_id = p.id
        WHERE s.id = ?
    ');
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch();

    if (!$sale) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error fetching sale: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}

$product_id = $sale['product_id'] ?? '';
$quantity = $sale['quantity'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = trim($_POST['product_id'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');

    $errors = [];

    // Validation
    if (empty($product_id)) {
        $errors['product_id'] = 'Product is required';
    } elseif (!is_numeric($product_id)) {
        $errors['product_id'] = 'Invalid product selected';
    }

    if (empty($quantity)) {
        $errors['quantity'] = 'Quantity is required';
    } elseif (!is_numeric($quantity) || $quantity <= 0 || (int)$quantity != (float)$quantity) {
        $errors['quantity'] = 'Quantity must be a valid positive integer';
    }

    // Check if product exists and get current stock
    $newProduct = null;
    if (empty($errors['product_id'])) {
        try {
            $stmt = $pdo->prepare('SELECT id, name, quantity, price FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $newProduct = $stmt->fetch();

            if (!$newProduct) {
                $errors['product_id'] = 'Selected product does not exist';
            } else {
                // Calculate available stock for the new product
                // If same product, available stock = current stock + old sale quantity
                // If different product, available stock = current stock
                $availableStock = $newProduct['quantity'];
                if ($product_id == $sale['product_id']) {
                    $availableStock += $sale['quantity'];
                }

                if ($availableStock < $quantity) {
                    $errors['quantity'] = 'Insufficient stock. Available: ' . $availableStock;
                }
            }
        } catch (PDOException $e) {
            error_log('Database error checking product: ' . $e->getMessage());
            $errors['general'] = 'A database error occurred. Please try again.';
        }
    }

    // Process sale update if no errors
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Handle stock adjustments
            if ($product_id != $sale['product_id']) {
                // Different product selected
                // Return stock to old product
                $stmt = $pdo->prepare('UPDATE products SET quantity = quantity + ? WHERE id = ?');
                $stmt->execute([$sale['quantity'], $sale['product_id']]);

                // Deduct stock from new product
                $stmt = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?');
                $stmt->execute([$quantity, $product_id]);
            } else {
                // Same product, different quantity
                $quantityDifference = $quantity - $sale['quantity'];
                if ($quantityDifference > 0) {
                    // Need more stock
                    $stmt = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?');
                    $stmt->execute([$quantityDifference, $product_id]);
                } elseif ($quantityDifference < 0) {
                    // Return excess stock
                    $stmt = $pdo->prepare('UPDATE products SET quantity = quantity + ? WHERE id = ?');
                    $stmt->execute([abs($quantityDifference), $product_id]);
                }
                // If quantityDifference == 0, no stock change needed
            }

            // Update sale record
            $stmt = $pdo->prepare('UPDATE sales SET product_id = ?, quantity = ? WHERE id = ?');
            $stmt->execute([$product_id, $quantity, $sale_id]);

            // Commit transaction
            $pdo->commit();

            header('Location: index.php?success=updated');
            exit;

        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            error_log('Error updating sale: ' . $e->getMessage());
            $errors['general'] = 'Failed to update sale. Please try again.';
        }
    }
}

$page_title = 'Edit Sale';
require_once __DIR__ . '/../includes/header.php';

// Get products for dropdown (only products with stock > 0, or the current product)
try {
    $stmt = $pdo->prepare('
        SELECT p.id, p.name, p.quantity, p.price, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.quantity > 0 OR p.id = ?
        ORDER BY p.name ASC
    ');
    $stmt->execute([$sale['product_id']]);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching products: ' . $e->getMessage());
    $products = [];
}
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Sale
                </h2>
                <p class="text-muted mb-0">Update sale information</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Sales
            </a>
        </div>
    </div>
</div>

<!-- Error Messages -->
<?php if (!empty($errors['general'])): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($errors['general']); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Sale Form -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sale Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="product_id" class="form-label">Product *</label>
                            <select class="form-select <?php echo isset($errors['product_id']) ? 'is-invalid' : ''; ?>"
                                    id="product_id" name="product_id" required onchange="updateProductInfo()">
                                <option value="">Select a product</option>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    // Calculate available stock for display
                                    $availableStock = $product['quantity'];
                                    if ($product['id'] == $sale['product_id']) {
                                        $availableStock += $sale['quantity'];
                                    }
                                    ?>
                                    <option value="<?php echo $product['id']; ?>"
                                            data-price="<?php echo $product['price']; ?>"
                                            data-stock="<?php echo $availableStock; ?>"
                                            <?php echo $product_id == $product['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($product['name']); ?>
                                        (Available: <?php echo $availableStock; ?>, $<?php echo number_format($product['price'], 2); ?>)
                                        <?php if ($product['category_name']): ?>
                                            - <?php echo htmlspecialchars($product['category_name']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['product_id'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['product_id']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Quantity *</label>
                            <input type="number" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>"
                                   id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>"
                                   min="1" required onchange="calculateTotal()">
                            <?php if (isset($errors['quantity'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['quantity']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Product Info Display -->
                    <div id="productInfo" class="row mb-3" style="display: none;">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Available Stock:</strong> <span id="availableStock">-</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Unit Price:</strong> $<span id="unitPrice">-</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Total:</strong> $<span id="totalPrice">0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateProductInfo() {
    const select = document.getElementById('product_id');
    const selectedOption = select.options[select.selectedIndex];
    const productInfo = document.getElementById('productInfo');

    if (selectedOption.value) {
        const stock = selectedOption.getAttribute('data-stock');
        const price = selectedOption.getAttribute('data-price');

        document.getElementById('availableStock').textContent = stock;
        document.getElementById('unitPrice').textContent = parseFloat(price).toFixed(2);
        productInfo.style.display = 'block';

        // Set max quantity to available stock
        document.getElementById('quantity').max = stock;

        calculateTotal();
    } else {
        productInfo.style.display = 'none';
        document.getElementById('quantity').max = '';
    }
}

function calculateTotal() {
    const quantity = parseInt(document.getElementById('quantity').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unitPrice').textContent) || 0;
    const total = quantity * unitPrice;
    document.getElementById('totalPrice').textContent = total.toFixed(2);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateProductInfo();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>