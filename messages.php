<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = (string) ($_POST['action'] ?? '');
    if (!$id || !in_array($action, ['read', 'unread', 'delete'], true)) {
        flash('error', 'Please choose a valid message action.');
    } elseif ($action === 'delete') {
        $statement = $pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
        $statement->execute(['id' => $id]);
        flash('success', 'Message deleted.');
    } else {
        $statement = $pdo->prepare('UPDATE contact_messages SET is_read = :is_read WHERE id = :id');
        $statement->execute(['is_read' => $action === 'read' ? 1 : 0, 'id' => $id]);
        flash('success', $action === 'read' ? 'Message marked as read.' : 'Message marked as unread.');
    }
    safeRedirect('messages.php');
}
$messages = $pdo->query('SELECT id, name, email, subject, message, is_read, created_at FROM contact_messages ORDER BY created_at DESC')->fetchAll();
$adminTitle = 'Messages';
$currentAdminPage = 'messages';
require __DIR__ . '/_header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Contact inbox</p><h1>Every note <em>matters.</em></h1></div><span class="admin-date"><?= count($messages) ?> total</span></div>
<?php if ($messages): ?><div class="message-grid"><?php foreach ($messages as $message): ?><article class="message-card <?= $message['is_read'] ? '' : 'message-unread' ?>"><div class="message-card-top"><span class="status <?= $message['is_read'] ? 'status-contacted' : 'status-new' ?>"><?= $message['is_read'] ? 'Read' : 'Unread' ?></span><time><?= e(date('M j, Y · g:i a', strtotime($message['created_at']))) ?></time></div><h2><?= e($message['subject']) ?></h2><p class="message-sender"><?= e($message['name']) ?> · <a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></p><p class="message-body"><?= nl2br(e($message['message'])) ?></p><div class="message-actions"><a class="small-button" href="mailto:<?= e($message['email']) ?>?subject=<?= rawurlencode('Re: ' . $message['subject']) ?>">Reply by email</a><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $message['id'] ?>"><input type="hidden" name="action" value="<?= $message['is_read'] ? 'unread' : 'read' ?>"><button class="small-button" type="submit">Mark <?= $message['is_read'] ? 'unread' : 'read' ?></button></form><form method="post" onsubmit="return confirm('Delete this message?')"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $message['id'] ?>"><input type="hidden" name="action" value="delete"><button class="small-button small-button-danger" type="submit">Delete</button></form></div></article><?php endforeach; ?></div><?php else: ?><section class="admin-panel empty-state"><p>Your inbox is clear. Contact form messages will show up here.</p></section><?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
