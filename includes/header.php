<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title><?= e($title ?? 'Склад') ?> — Warehouse</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="content">
        <header class="topbar">
            <h1><?= e($title ?? 'Dashboard') ?></h1>
            <?php if ($user): ?>
                <div class="user" style="display: flex; align-items: center; gap: 16px;">
                    <a href="profile.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px; background: #f3f4f6; padding: 6px 12px; border-radius: 20px; transition: background 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'" title="Перейти в профиль">
                        <span style="font-size: 18px;">👤</span>
                        <span style="font-weight: 600; color: #374151;"><?= e($user['name']) ?></span>
                        <span style="font-size: 12px; color: #6b7280;">(<?= e($user['role']) ?>)</span>
                    </a>
                    <a href="logout.php" style="color:#ef4444; font-weight:500; text-decoration:none;">Выйти</a>
                </div>
            <?php endif; ?>
        </header>
        <?php if ($m = flash('ok')): ?><div class="flash ok"><?= e($m) ?></div><?php endif; ?>
        <?php if ($m = flash('err')): ?><div class="flash err"><?= e($m) ?></div><?php endif; ?>