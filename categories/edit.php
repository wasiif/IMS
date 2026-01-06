<?php
// Handle all logic before any output

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$category_id = (int)$_GET['id'];
$category = null;
$errors = [];

// Include database connection for early logic
require_once __DIR__ . '/../config/db.php';
$pdo = require __DIR__ . '/../config/db.php';

// Fetch category data
try {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();

    if (!$category) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

$name = $category['name'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Category name is required';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Category name must be less than 100 characters';
    }

    // Check if category name already exists (excluding current category)
    if (empty($errors['name'])) {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?');
            $stmt->execute([$name, $category_id]);
            if ($stmt->fetchColumn() > 0) {
                $errors['name'] = 'A category with this name already exists';
            }
        } catch (PDOException $e) {
            error_log('Database error checking category name: ' . $e->getMessage());
            $errors['general'] = 'A database error occurred. Please try again.';
        }
    }

    // Update category if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
            $stmt->execute([$name, $category_id]);
            header('Location: index.php?success=updated');
            exit;
        } catch (PDOException $e) {
            error_log('Error updating category: ' . $e->getMessage());
            $errors['general'] = 'Failed to update category. Please try again.';
        }
    }
}

$page_title = 'Edit Category';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Category
                </h2>
                <p class="text-muted mb-0">Update category information</p>
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

<!-- Edit Form -->
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
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Category
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
                    <i class="bi bi-info-circle me-2"></i>Category Info
                </h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">ID:</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($category['id']); ?></dd>
                </dl>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb me-2"></i>Tips
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Changes are saved immediately
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Category name must be unique
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>