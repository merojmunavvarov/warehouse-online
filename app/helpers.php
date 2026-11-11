<?php
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header("Location: $url"); exit; }
function csrf_token(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">'; }
function csrf_check(?string $t): void { if (!$t || !hash_equals($_SESSION['_csrf'] ?? '', $t)) { http_response_code(419); die('CSRF token mismatch'); } }
function flash(string $k, ?string $v = null) { if ($v === null) { $m = $_SESSION['_flash'][$k] ?? null; unset($_SESSION['_flash'][$k]); return $m; } $_SESSION['_flash'][$k] = $v; }
function product_stock(int $productId): int {
    $in = db()->prepare("SELECT COALESCE(SUM(qty),0) FROM stock_in WHERE product_id = ?"); $in->execute([$productId]);
    $out = db()->prepare("SELECT COALESCE(SUM(qty),0) FROM stock_out WHERE product_id = ?"); $out->execute([$productId]);
    return (int)$in->fetchColumn() - (int)$out->fetchColumn();
}