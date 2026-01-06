<?php
// Handle form submission before any output
$name = '';
$price = '';
$quantity = '';
$category_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');

    $errors = [];

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Product name is required';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Product name must be less than 100 characters';
    }

    if (empty($price)) {
        $errors['price'] = 'Price is required';
    } elseif (!is_numeric($price) || $price < 0) {
        $errors['price'] = 'Price must be a valid positive number';
    }

    if (empty($quantity)) {
        $errors['quantity'] = 'Quantity is required';
    } elseif (!is_numeric($quantity) || $quantity < 0 || (int)$quantity != (float)$quantity) {
        $errors['quantity'] = 'Quantity must be a valid non-negative integer';
    }

    if (empty($category_id)) {
        $errors['category_id'] = 'Category is required';
    } elseif (!is_numeric($category_id)) {
        $errors['category_id'] = 'Invalid category selected';
    }

    // Check if category exists
    if (empty($errors['category_id'])) {
        require_once __DIR__ . '/../config/db.php';
        $pdo = require __DIR__ . '/../config/db.php';
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
            $stmt->execute([$category_id]);
            if ($stmt->fetchColumn() == 0) {
                $errors['category_id'] = 'Selected category does not exist';
            }
        } catch (PDOException $e) {
            error_log('Database error checking category: ' . $e->getMessage());
            $errors['general'] = 'A database error occurred. Please try again.';
        }
    }

    // Check if product name already exists
    if (empty($errors['name']) && empty($errors['general'])) {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE name = ?');
            $stmt->execute([$name]);
            if ($stmt->fetchColumn() > 0) {
                $errors['name'] = 'A product with this name already exists';
            }
        } catch (PDOException $e) {
            error_log('Database error checking product name: ' . $e->getMessage());
            $errors['general'] = 'A database error occurred. Please try again.';
        }
    }

    // Insert product if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO products (name, price, quantity, category_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $price, $quantity, $category_id]);
            header('Location: index.php?success=added');
            exit;
        } catch (PDOException $e) {
            error_log('Error creating product: ' . $e->getMessage());
            $errors['general'] = 'Failed to add product. Please try again.';
        }
    }
}

$page_title = 'Add Product';
require_once __DIR__ . '/../includes/header.php';

// Get categories for dropdown
try {
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC');
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching categories: ' . $e->getMessage());
    $categories = [];
}
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Add Product
                </h2>
                <p class="text-muted mb-0">Add a new product to your inventory</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Products
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

<!-- Product Form -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                   id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select <?php echo isset($errors['category_id']) ? 'is-invalid' : ''; ?>"
                                    id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                            <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['category_id']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>"
                                       id="price" name="price" value="<?php echo htmlspecialchars($price); ?>"
                                       step="0.01" min="0" required>
                                <?php if (isset($errors['price'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['price']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity *</label>
                            <input type="number" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>"
                                   id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>"
                                   min="0" required>
                            <?php if (isset($errors['quantity'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['quantity']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>