<?php
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';
require_login();
$pdo = db(); $u = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['_csrf'] ?? '');
    try {
        $product = (int)($_POST['product_id'] ?? 0); $qty = (int)($_POST['qty'] ?? 0); $reason = trim($_POST['reason'] ?? '');
        if (!$product || $qty <= 0) throw new Exception('Товар и количество (>0) обязательны');
        $stock = product_stock($product); if ($qty > $stock) throw new Exception("Недостаточно на складе (доступно: $stock)");
        $pdo->prepare("INSERT INTO stock_out (product_id,qty,reason,user_id) VALUES (?,?,?,?)")->execute([$product,$qty,$reason,$u['id']]);
        flash('ok', 'Расход оформлен');
    } catch (Throwable $e) { flash('err', $e->getMessage()); }
    redirect('stock_out.php');
}
$products = $pdo->query("SELECT p.id,p.name,p.sku, (COALESCE(si.q,0) - COALESCE(so.q,0)) AS stock FROM products p LEFT JOIN (SELECT product_id,SUM(qty) q FROM stock_in GROUP BY product_id) si ON si.product_id=p.id LEFT JOIN (SELECT product_id,SUM(qty) q FROM stock_out GROUP BY product_id) so ON so.product_id=p.id ORDER BY p.name")->fetchAll();
$rows = $pdo->query("SELECT so.*, p.name AS product, u.name AS user FROM stock_out so JOIN products p ON p.id=so.product_id LEFT JOIN users u ON u.id=so.user_id ORDER BY so.created_at DESC LIMIT 100")->fetchAll();
$title = 'Расход';
include __DIR__ . '/../includes/header.php';
?>
<div class="grid2">
    <div>
        <h2 class="section">История</h2>
        <table class="table"><thead><tr><th>Дата</th><th>Товар</th><th>Кол-во</th><th>Причина</th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?><tr><td><?= e($r['created_at']) ?></td><td><?= e($r['product']) ?></td><td class="warn">−<?= (int)$r['qty'] ?></td><td><?= e($r['reason']) ?></td></tr><?php endforeach; ?>
        </tbody></table>
    </div>
    <div>
        <h2 class="section">Оформить</h2>
        <form method="post" class="form"><?= csrf_field() ?>
            <label>Товар<select name="product_id" required><option value="">—</option><?php foreach ($products as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= (int)$p['stock'] ?> шт)</option><?php endforeach; ?></select></label>
            <label>Количество<input type="number" name="qty" min="1" required></label>
            <label>Причина<select name="reason"><option>Продажа</option><option>Списание</option><option>Брак</option></select></label>
            <button class="btn btn-primary">➖ Оформить</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>