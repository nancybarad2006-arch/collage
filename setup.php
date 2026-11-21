<?php
require_once __DIR__ . '/../includes/functions.php';
$count = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$errors = [];
if ($count === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9._-]{3,80}$/', $username)) $errors[] = 'Username must be 3–80 characters and use letters, numbers, dots, underscores or hyphens.';
    if (strlen($password) < 12 || strlen($password) > 200) $errors[] = 'Choose a password between 12 and 200 characters.';
    if ($password !== $confirm) $errors[] = 'The passwords do not match.';
    if (!$errors) {
        $statement = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)');
        try {
            $statement->execute(['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'Administrator created. Sign in with your new credentials.');
            safeRedirect('login.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') $errors[] = 'That username is already in use.';
            else throw $exception;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Initial admin setup | Asteria College</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet"><link rel="stylesheet" href="../assets/css/style.css"></head><body class="auth-page"><div class="auth-card"><a class="brand" href="../index.php"><span class="brand-mark">a<span>.</span></span><span class="brand-name">asteria <small>COLLEGE</small></span></a><p class="eyebrow">One-time setup</p><h1>Create your <em>admin.</em></h1>
    <?php if ($count > 0): ?><div class="alert alert-success">Initial setup is complete. An administrator account already exists.</div><a class="button button-dark" href="login.php">Go to sign in <span aria-hidden="true">↗</span></a>
    <?php else: ?><p class="auth-copy">Create the first administrator account. Use a unique password with at least 12 characters.</p><?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" class="site-form"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><div class="field"><label for="username">Username</label><input id="username" name="username" required maxlength="80" autocomplete="username"></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required minlength="12" maxlength="200" autocomplete="new-password"></div><div class="field"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" required minlength="12" maxlength="200" autocomplete="new-password"></div><button class="button button-dark" type="submit">Create admin account <span aria-hidden="true">↗</span></button></form><?php endif; ?>
    <a class="auth-back" href="../index.php">← Back to Asteria College</a></div></body></html>
