<?php
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';
require_login();
$pdo = db();
$title = 'Отчеты';
include __DIR__ . '/../includes/header.php';
$rows = $pdo->query("SELECT p.name,p.sku,p.unit,p.price,p.min_stock,c.name AS category,(COALESCE(si.q,0)-COALESCE(so.q,0)) AS stock FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN (SELECT product_id,SUM(qty) q FROM stock_in GROUP BY product_id) si ON si.product_id=p.id LEFT JOIN (SELECT product_id,SUM(qty) q FROM stock_out GROUP BY product_id) so ON so.product_id=p.id ORDER BY p.name")->fetchAll();
$total = 0; foreach ($rows as $r) $total += $r['stock'] * (float)$r['price'];
?>
<h2 class="section">Остатки</h2>
<table class="table">
    <thead><tr><th>Артикул</th><th>Название</th><th>Категория</th><th>Остаток</th><th>Цена</th><th>Стоимость</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr><td><?= e($r['sku']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['category']) ?></td><td class="<?= $r['stock'] <= $r['min_stock'] ? 'warn' : '' ?>"><?= (int)$r['stock'] ?> <?= e($r['unit']) ?></td><td><?= number_format((float)$r['price'], 2, '.', ' ') ?></td><td><?= number_format($r['stock'] * (float)$r['price'], 2, '.', ' ') ?></td></tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="5">Итого</th><th><?= number_format($total, 2, '.', ' ') ?> TJS</th></tr></tfoot>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>