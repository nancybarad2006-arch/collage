<?php
require_once __DIR__ . '/../includes/functions.php';
if (!empty($_SESSION['admin_id'])) safeRedirect('index.php');
$error = '';
$notice = takeFlash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $attempts = (int) ($_SESSION['login_attempts'] ?? 0);
    $lastAttempt = (int) ($_SESSION['login_last_attempt'] ?? 0);
    if ($attempts >= 5 && time() - $lastAttempt < 900) {
        $error = 'Too many sign-in attempts. Please wait 15 minutes and try again.';
    } else {
        $statement = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);
        $admin = $statement->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            unset($_SESSION['login_attempts'], $_SESSION['login_last_attempt']);
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                $update = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
                $update->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $admin['id']]);
            }
            safeRedirect('index.php');
        }
        $_SESSION['login_attempts'] = $attempts + 1;
        $_SESSION['login_last_attempt'] = time();
        $error = 'The username or password was not recognized.';
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin sign in | Asteria College</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet"><link rel="stylesheet" href="../assets/css/style.css"></head>
<body class="auth-page"><div class="auth-card"><a class="brand" href="../index.php"><span class="brand-mark">a<span>.</span></span><span class="brand-name">asteria <small>COLLEGE</small></span></a><p class="eyebrow">Administration</p><h1>Welcome <em>back.</em></h1><p class="auth-copy">Sign in to manage your college website.</p>
    <?php if ($notice): ?><div class="alert alert-<?= e($notice['type']) ?>"><?= e($notice['message']) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="site-form"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><div class="field"><label for="username">Username</label><input id="username" name="username" required autocomplete="username" maxlength="80"></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div><button class="button button-dark" type="submit">Sign in <span aria-hidden="true">↗</span></button></form>
    <a class="auth-back" href="../index.php">← Back to Asteria College</a></div></body></html>
