<?php
// Handle form submission before any output
$name = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Category name is required';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Category name must be less than 100 characters';
    }

    // Check if category name already exists
    if (empty($errors['name'])) {
        require_once __DIR__ . '/../config/db.php';
        $pdo = require __DIR__ . '/../config/db.php';
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ?');
            $stmt->execute([$name]);
            if ($stmt->fetchColumn() > 0) {
                $errors['name'] = 'A category with this name already exists';
            }
        } catch (PDOException $e) {
            error_log('Database error checking category name: ' . $e->getMessage());
            $errors['general'] = 'A database error occurred. Please try again.';
        }
    }

    // Insert category if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$name]);
            header('Location: index.php?success=added');
            exit;
        } catch (PDOException $e) {
            error_log('Error creating category: ' . $e->getMessage());
            $errors['general'] = 'Failed to add category. Please try again.';
        }
    }
}

$page_title = 'Add Category';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-plus-circle text-success me-2"></i>Add New Category
                </h2>
                <p class="text-muted mb-0">Add a new category to organize your products</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Categories
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

<!-- Add Category Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Category Details
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                               id="name" name="name" value="<?php echo htmlspecialchars($name); ?>"
                               placeholder="Enter category name" maxlength="100" required>
                        <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($errors['name']); ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">Maximum 100 characters</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Add Category
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Guidelines
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use clear, descriptive category names
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Keep names under 100 characters
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Category names must be unique
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>