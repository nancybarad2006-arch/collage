<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$counts = [
    'admissions' => (int) $pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'new'")->fetchColumn(),
    'departments' => (int) $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn(),
    'gallery' => (int) $pdo->query('SELECT COUNT(*) FROM gallery')->fetchColumn(),
    'messages' => (int) $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn(),
];
$recentAdmissions = $pdo->query('SELECT name, program, status, created_at FROM admissions ORDER BY created_at DESC LIMIT 5')->fetchAll();
$recentMessages = $pdo->query('SELECT name, subject, is_read, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5')->fetchAll();
$adminTitle = 'Overview';
$currentAdminPage = 'dashboard';
require __DIR__ . '/_header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Good to see you, <?= e($_SESSION['admin_username'] ?? 'admin') ?></p><h1>Your college, <em>at a glance.</em></h1></div><span class="admin-date"><?= date('l, F j, Y') ?></span></div>
<div class="admin-stat-grid">
    <a class="admin-stat-card" href="admissions.php"><span>New enquiries</span><strong><?= $counts['admissions'] ?></strong><small>Admissions to review <b>↗</b></small></a>
    <a class="admin-stat-card" href="departments.php"><span>Departments</span><strong><?= $counts['departments'] ?></strong><small>Programs listed <b>↗</b></small></a>
    <a class="admin-stat-card" href="gallery.php"><span>Gallery items</span><strong><?= $counts['gallery'] ?></strong><small>Campus moments <b>↗</b></small></a>
    <a class="admin-stat-card admin-stat-pink" href="messages.php"><span>Unread messages</span><strong><?= $counts['messages'] ?></strong><small>Contact inbox <b>↗</b></small></a>
</div>
<div class="admin-columns">
    <section class="admin-panel"><div class="admin-panel-heading"><div><p class="eyebrow">Admissions</p><h2>Recent enquiries</h2></div><a class="text-link" href="admissions.php">View all →</a></div>
        <?php if ($recentAdmissions): ?><div class="admin-list"><?php foreach ($recentAdmissions as $row): ?><div class="admin-list-row"><span class="list-avatar"><?= e(strtoupper(substr($row['name'], 0, 1))) ?></span><div><strong><?= e($row['name']) ?></strong><small><?= e($row['program']) ?></small></div><span class="status status-<?= e($row['status']) ?>"><?= e(ucfirst($row['status'])) ?></span></div><?php endforeach; ?></div><?php else: ?><p class="muted-copy">No admission enquiries yet.</p><?php endif; ?>
    </section>
    <section class="admin-panel"><div class="admin-panel-heading"><div><p class="eyebrow">Inbox</p><h2>Recent messages</h2></div><a class="text-link" href="messages.php">Open inbox →</a></div>
        <?php if ($recentMessages): ?><div class="admin-list"><?php foreach ($recentMessages as $row): ?><div class="admin-list-row"><span class="list-avatar list-avatar-pink">✉</span><div><strong><?= e($row['subject']) ?></strong><small>From <?= e($row['name']) ?></small></div><?php if (!$row['is_read']): ?><span class="unread-dot" aria-label="Unread"></span><?php endif; ?></div><?php endforeach; ?></div><?php else: ?><p class="muted-copy">No messages in your inbox.</p><?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
