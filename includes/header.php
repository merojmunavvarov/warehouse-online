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
                <div class="user">
                    <span><?= e($user['name']) ?> · <?= e($user['role']) ?></span>
                    <a href="logout.php">Выйти</a>
                </div>
            <?php endif; ?>
        </header>
        <?php if ($m = flash('ok')): ?><div class="flash ok"><?= e($m) ?></div><?php endif; ?>
        <?php if ($m = flash('err')): ?><div class="flash err"><?= e($m) ?></div><?php endif; ?>