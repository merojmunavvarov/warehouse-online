<?php
require __DIR__ . '/../app/db.php'; 
require __DIR__ . '/../app/helpers.php'; 
require __DIR__ . '/../app/auth.php'; 
require_login();

$pdo = db(); 
$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['_csrf'] ?? '');
    try {
        $product = (int)($_POST['product_id'] ?? 0); 
        $supplier = $_POST['supplier_id'] ?: null; 
        $qty = (int)($_POST['qty'] ?? 0); 
        $price = (float)($_POST['price'] ?? 0); 
        $note = trim($_POST['note'] ?? '');
        
        if (!$product || $qty <= 0) throw new Exception('Товар и количество (>0) обязательны');
        
        $pdo->prepare("INSERT INTO stock_in (product_id,supplier_id,qty,price,note,user_id) VALUES (?,?,?,?,?,?)")
            ->execute([$product,$supplier,$qty,$price,$note,$u['id']]);
        flash('ok', 'Приход оформлен');
    } catch (Throwable $e) { 
        flash('err', $e->getMessage()); 
    }
    redirect('stock_in.php');
}

$products = $pdo->query("SELECT id,name,sku FROM products ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT id,name FROM suppliers ORDER BY name")->fetchAll();
$rows = $pdo->query("SELECT si.*, p.name AS product, s.name AS supplier, u.name AS user FROM stock_in si JOIN products p ON p.id=si.product_id LEFT JOIN suppliers s ON s.id=si.supplier_id LEFT JOIN users u ON u.id=si.user_id ORDER BY si.created_at DESC LIMIT 100")->fetchAll();

$title = 'Приход'; 
include __DIR__ . '/../includes/header.php';
?>
<div class="grid2">
    <div>
        <h2 class="section">История приходов</h2>
        <table class="table">
            <thead><tr><th>Дата</th><th>Товар</th><th>Поставщик</th><th>Кол-во</th><th>Кто</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['created_at']) ?></td>
                    <td><?= e($r['product']) ?></td>
                    <td><?= e($r['supplier']) ?></td>
                    <td class="ok">+<?= (int)$r['qty'] ?></td>
                    <td><?= e($r['user']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="empty">Операций пока нет</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div>
        <h2 class="section">Оформить приход</h2>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <label>Товар
                <select name="product_id" required>
                    <option value=""> Выберите товар </option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['sku']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Поставщик
                <select name="supplier_id">
                    <option value=""> Не указан </option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Количество
                <input type="number" name="qty" min="1" required placeholder="Например: 100">
            </label>
            <label>Закупочная цена
                <input type="number" step="0.01" name="price" value="0">
            </label>
            <label>Примечание
                <textarea name="note" rows="2" placeholder="Например: Накладная №45"></textarea>
            </label>
            <button class="btn btn-primary">➕ Оформить приход</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>