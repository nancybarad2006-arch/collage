<?php
$adminTitle = $adminTitle ?? 'Dashboard';
$currentAdminPage = $currentAdminPage ?? '';
$adminFlash = takeFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($adminTitle) ?> | Asteria Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<header class="admin-topbar"><a class="brand" href="../index.php"><span class="brand-mark">a<span>.</span></span><span class="brand-name">asteria <small>ADMIN</small></span></a><div class="admin-top-actions"><a href="../index.php">View website ↗</a><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button type="submit">Sign out</button></form></div></header>
<div class="admin-shell">
    <aside class="admin-sidebar"><p class="eyebrow">Workspace</p><nav aria-label="Admin navigation">
        <a class="<?= $currentAdminPage === 'dashboard' ? 'active' : '' ?>" href="index.php"><span>◫</span> Overview</a>
        <a class="<?= $currentAdminPage === 'admissions' ? 'active' : '' ?>" href="admissions.php"><span>＋</span> Admissions</a>
        <a class="<?= $currentAdminPage === 'departments' ? 'active' : '' ?>" href="departments.php"><span>⌘</span> Departments</a>
        <a class="<?= $currentAdminPage === 'gallery' ? 'active' : '' ?>" href="gallery.php"><span>▧</span> Gallery</a>
        <a class="<?= $currentAdminPage === 'messages' ? 'active' : '' ?>" href="messages.php"><span>✉</span> Messages</a>
    </nav><div class="sidebar-bottom"><span class="admin-avatar"><?= e(strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1))) ?></span><span><strong><?= e($_SESSION['admin_username'] ?? 'Administrator') ?></strong><small>Administrator</small></span></div></aside>
    <main class="admin-main">
        <?php if ($adminFlash): ?><div class="alert alert-<?= e($adminFlash['type']) ?>"><?= e($adminFlash['message']) ?></div><?php endif; ?>
