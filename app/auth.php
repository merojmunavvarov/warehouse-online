<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return isset($_SESSION['user']); }
function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }
function require_login(): void { if (!is_logged_in()) { header('Location: login.php'); exit; } }
function require_admin(): void { require_login(); if (!is_admin()) { http_response_code(403); die('Доступ запрещён'); } }
function login_user(array $u): void { $_SESSION['user'] = ['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']]; }
function logout_user(): void { $_SESSION = []; session_destroy(); }