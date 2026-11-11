<?php
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';
require_login();
$title = 'Dashboard'; $pdo = db();
$countProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$countCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalValue = (float)$pdo->query("SELECT COALESCE(SUM((COALESCE(si.q,0) - COALESCE(so.q,0)) * p.price), 0) FROM products p LEFT JOIN (SELECT product_id, SUM(qty) q FROM stock_in GROUP BY product_id) si ON si.product_id=p.id LEFT JOIN (SELECT product_id, SUM(qty) q FROM stock_out GROUP BY product_id) so ON so.product_id=p.id")->fetchColumn();
include __DIR__ . '/../includes/header.php';
?>
<section class="cards">
    <div class="card"><h3>Товары</h3><p><?= $countProducts ?></p></div>
    <div class="card"><h3>Категории</h3><p><?= $countCategories ?></p></div>
    <div class="card"><h3>Стоимость склада</h3><p><?= number_format($totalValue, 2, '.', ' ') ?> TJS</p></div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>