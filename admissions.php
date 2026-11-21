<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$statuses = ['new', 'contacted', 'admitted', 'declined'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = (string) ($_POST['status'] ?? '');
    if (!$id || !in_array($status, $statuses, true)) {
        flash('error', 'Please select a valid admission and status.');
    } else {
        $statement = $pdo->prepare('UPDATE admissions SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
        flash('success', 'Admission status updated.');
    }
    safeRedirect('admissions.php');
}
$admissions = $pdo->query('SELECT id, name, email, phone, program, message, status, created_at FROM admissions ORDER BY created_at DESC')->fetchAll();
$adminTitle = 'Admissions';
$currentAdminPage = 'admissions';
require __DIR__ . '/_header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Student enquiries</p><h1>Admissions <em>inbox.</em></h1></div><span class="admin-date"><?= count($admissions) ?> total</span></div>
<section class="admin-panel admin-table-panel"><div class="admin-panel-heading"><div><p class="eyebrow">Applications & enquiries</p><h2>Keep every conversation moving.</h2></div></div>
<?php if ($admissions): ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Applicant</th><th>Program & contact</th><th>Message</th><th>Received</th><th>Status</th></tr></thead><tbody>
<?php foreach ($admissions as $row): ?><tr><td><strong><?= e($row['name']) ?></strong><small><?= e($row['email']) ?></small><small><?= e($row['phone']) ?></small></td><td><?= e($row['program']) ?></td><td class="message-cell"><?= $row['message'] ? e($row['message']) : '<span class="muted-copy">No note provided</span>' ?></td><td><?= e(date('M j, Y', strtotime($row['created_at']))) ?></td><td><form method="post" class="inline-status"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><select name="status" aria-label="Status for <?= e($row['name']) ?>"><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= $row['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select><button class="small-button" type="submit">Save</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><div class="empty-state"><p>No enquiries yet. New admission forms will appear here.</p></div><?php endif; ?>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
