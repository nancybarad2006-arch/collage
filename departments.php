<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$editDepartment = null;
if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId) {
        $statement = $pdo->prepare('SELECT * FROM departments WHERE id = :id');
        $statement->execute(['id' => $editId]);
        $editDepartment = $statement->fetch() ?: null;
    }
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $statement = $pdo->prepare('DELETE FROM departments WHERE id = :id');
            $statement->execute(['id' => $id]);
            flash('success', 'Department removed.');
        } else flash('error', 'Invalid department selected.');
        safeRedirect('departments.php');
    }
    if ($action === 'save') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
        $name = trim((string) ($_POST['name'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $icon = trim((string) ($_POST['icon'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $sortOrder = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
        $active = isset($_POST['is_active']) ? 1 : 0;
        if ($name === '' || mb_strlen($name) > 150) $errors[] = 'Enter a department name up to 150 characters.';
        if ($category === '' || mb_strlen($category) > 80) $errors[] = 'Enter a category up to 80 characters.';
        if ($icon === '' || mb_strlen($icon) > 12) $errors[] = 'Enter a short icon or symbol (up to 12 characters).';
        if ($description === '' || mb_strlen($description) > 1000) $errors[] = 'Enter a description up to 1,000 characters.';
        if ($sortOrder === false || $sortOrder < 0) $errors[] = 'Sort order must be a non-negative number.';
        if (!$errors) {
            $values = ['name' => $name, 'category' => $category, 'icon' => $icon, 'description' => $description, 'sort_order' => $sortOrder, 'is_active' => $active];
            if ($id) {
                $values['id'] = $id;
                $statement = $pdo->prepare('UPDATE departments SET name = :name, category = :category, icon = :icon, description = :description, sort_order = :sort_order, is_active = :is_active WHERE id = :id');
                $statement->execute($values);
                flash('success', 'Department updated.');
            } else {
                $statement = $pdo->prepare('INSERT INTO departments (name, category, icon, description, sort_order, is_active) VALUES (:name, :category, :icon, :description, :sort_order, :is_active)');
                $statement->execute($values);
                flash('success', 'Department added.');
            }
            safeRedirect('departments.php');
        }
        $editDepartment = array_merge($_POST, ['id' => $id]);
    }
}
$departments = $pdo->query('SELECT id, name, category, icon, description, sort_order, is_active FROM departments ORDER BY sort_order, name')->fetchAll();
$adminTitle = 'Departments';
$currentAdminPage = 'departments';
require __DIR__ . '/_header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Academic programs</p><h1>Shape your <em>offering.</em></h1></div></div>
<div class="admin-columns admin-columns-edit">
<section class="admin-panel"><div class="admin-panel-heading"><div><p class="eyebrow"><?= $editDepartment ? 'Edit department' : 'New department' ?></p><h2><?= $editDepartment ? 'Update details' : 'Add a department' ?></h2></div></div>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="site-form admin-form"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string) ($editDepartment['id'] ?? '')) ?>">
<div class="field"><label for="name">Department name *</label><input id="name" name="name" maxlength="150" required value="<?= e($editDepartment['name'] ?? '') ?>"></div><div class="form-row"><div class="field"><label for="category">Category *</label><input id="category" name="category" maxlength="80" required placeholder="e.g. Technology" value="<?= e($editDepartment['category'] ?? '') ?>"></div><div class="field"><label for="icon">Icon / symbol *</label><input id="icon" name="icon" maxlength="12" required value="<?= e($editDepartment['icon'] ?? '') ?>"></div></div>
<div class="field"><label for="description">Description *</label><textarea id="description" name="description" rows="4" maxlength="1000" required><?= e($editDepartment['description'] ?? '') ?></textarea></div><div class="form-row"><div class="field"><label for="sort_order">Sort order</label><input id="sort_order" name="sort_order" type="number" min="0" value="<?= e((string) ($editDepartment['sort_order'] ?? 0)) ?>"></div><label class="checkbox-field"><input type="checkbox" name="is_active" value="1" <?= !isset($editDepartment['is_active']) || $editDepartment['is_active'] ? 'checked' : '' ?>> Visible on website</label></div>
<button class="button button-dark" type="submit"><?= $editDepartment ? 'Save changes' : 'Add department' ?> <span aria-hidden="true">↗</span></button><?php if ($editDepartment): ?> <a class="cancel-link" href="departments.php">Cancel</a><?php endif; ?></form></section>
<section class="admin-panel"><div class="admin-panel-heading"><div><p class="eyebrow">Current departments</p><h2><?= count($departments) ?> listed</h2></div></div>
<?php if ($departments): ?><div class="admin-list"><?php foreach ($departments as $department): ?><div class="manage-row"><span class="department-icon"><?= e($department['icon']) ?></span><div class="manage-row-copy"><strong><?= e($department['name']) ?></strong><small><?= e($department['category']) ?> · <?= $department['is_active'] ? 'Visible' : 'Hidden' ?></small></div><a class="small-button" href="departments.php?edit=<?= (int) $department['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Remove this department?')"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $department['id'] ?>"><button class="small-button small-button-danger" type="submit">Delete</button></form></div><?php endforeach; ?></div><?php else: ?><p class="muted-copy">No departments yet. Add one using the form.</p><?php endif; ?>
</section></div>
<?php require __DIR__ . '/_footer.php'; ?>
