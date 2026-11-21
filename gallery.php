<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$errors = [];
$editPhoto = null;
if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId) {
        $statement = $pdo->prepare('SELECT * FROM gallery WHERE id = :id');
        $statement->execute(['id' => $editId]);
        $editPhoto = $statement->fetch() ?: null;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $statement = $pdo->prepare('DELETE FROM gallery WHERE id = :id');
            $statement->execute(['id' => $id]);
            flash('success', 'Gallery photo removed.');
        } else flash('error', 'Invalid gallery item selected.');
        safeRedirect('gallery.php');
    }
    if ($action === 'save') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
        $title = trim((string) ($_POST['title'] ?? ''));
        $imageUrl = trim((string) ($_POST['image_url'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $sortOrder = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
        $active = isset($_POST['is_active']) ? 1 : 0;
        $parsedUrl = filter_var($imageUrl, FILTER_VALIDATE_URL);
        if ($title === '' || mb_strlen($title) > 180) $errors[] = 'Enter a photo title up to 180 characters.';
        if (!$parsedUrl || !in_array(strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME)), ['https', 'http'], true)) $errors[] = 'Enter a valid http or https image URL.';
        if ($category === '' || mb_strlen($category) > 80) $errors[] = 'Enter a category up to 80 characters.';
        if ($sortOrder === false || $sortOrder < 0) $errors[] = 'Sort order must be a non-negative number.';
        if (!$errors) {
            $values = ['title' => $title, 'image_url' => $imageUrl, 'category' => $category, 'sort_order' => $sortOrder, 'is_active' => $active];
            if ($id) {
                $values['id'] = $id;
                $statement = $pdo->prepare('UPDATE gallery SET title = :title, image_url = :image_url, category = :category, sort_order = :sort_order, is_active = :is_active WHERE id = :id');
                $statement->execute($values);
                flash('success', 'Gallery item updated.');
            } else {
                $statement = $pdo->prepare('INSERT INTO gallery (title, image_url, category, sort_order, is_active) VALUES (:title, :image_url, :category, :sort_order, :is_active)');
                $statement->execute($values);
                flash('success', 'Photo added to the gallery.');
            }
            safeRedirect('gallery.php');
        }
        $editPhoto = array_merge($_POST, ['id' => $id]);
    }
}
$photos = $pdo->query('SELECT id, title, image_url, category, is_active FROM gallery ORDER BY sort_order, id DESC')->fetchAll();
$adminTitle = 'Gallery';
$currentAdminPage = 'gallery';
require __DIR__ . '/_header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Campus moments</p><h1>Tell your <em>story.</em></h1></div></div>
<div class="admin-columns admin-columns-edit">
<section class="admin-panel"><div class="admin-panel-heading"><div><p class="eyebrow"><?= $editPhoto ? 'Edit gallery item' : 'New gallery item' ?></p><h2><?= $editPhoto ? 'Update photo' : 'Add a photo' ?></h2></div></div>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="site-form admin-form"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string) ($editPhoto['id'] ?? '')) ?>"><div class="field"><label for="title">Photo title *</label><input id="title" name="title" maxlength="180" required value="<?= e($editPhoto['title'] ?? '') ?>"></div><div class="field"><label for="image_url">Image URL *</label><input id="image_url" name="image_url" type="url" required placeholder="https://example.com/photo.jpg" value="<?= e($editPhoto['image_url'] ?? '') ?>"></div><div class="field"><label for="category">Category *</label><input id="category" name="category" maxlength="80" required placeholder="e.g. Campus life" value="<?= e($editPhoto['category'] ?? '') ?>"></div><div class="form-row"><div class="field"><label for="sort_order">Sort order</label><input id="sort_order" name="sort_order" type="number" min="0" value="<?= e((string) ($editPhoto['sort_order'] ?? 0)) ?>"></div><label class="checkbox-field"><input type="checkbox" name="is_active" value="1" <?= !isset($editPhoto['is_active']) || $editPhoto['is_active'] ? 'checked' : '' ?>> Visible on website</label></div><button class="button button-dark" type="submit"><?= $editPhoto ? 'Save changes' : 'Add to gallery' ?> <span aria-hidden="true">↗</span></button><?php if ($editPhoto): ?> <a class="cancel-link" href="gallery.php">Cancel</a><?php endif; ?></form><p class="form-privacy">Use an image URL you have permission to publish. HTTPS links are recommended.</p></section>
<section class="admin-panel"><div class="admin-panel-heading"><div><p class="eyebrow">Published & hidden</p><h2><?= count($photos) ?> gallery items</h2></div></div><?php if ($photos): ?><div class="admin-list"><?php foreach ($photos as $photo): ?><div class="manage-row"><img class="manage-thumb" src="<?= e($photo['image_url']) ?>" alt=""><div class="manage-row-copy"><strong><?= e($photo['title']) ?></strong><small><?= e($photo['category']) ?> · <?= $photo['is_active'] ? 'Visible' : 'Hidden' ?></small></div><a class="small-button" href="gallery.php?edit=<?= (int) $photo['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Remove this photo?')"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $photo['id'] ?>"><button class="small-button small-button-danger" type="submit">Delete</button></form></div><?php endforeach; ?></div><?php else: ?><p class="muted-copy">No gallery items yet. Add an image to get started.</p><?php endif; ?></section></div>
<?php require __DIR__ . '/_footer.php'; ?>
