<?php
require __DIR__ . '/../app/db.php'; require __DIR__ . '/../app/helpers.php'; require __DIR__ . '/../app/auth.php'; require_login();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['_csrf'] ?? ''); $op = $_POST['op'] ?? '';
    try {
        if ($op === 'save') {
            $id = (int)($_POST['id'] ?? 0); $cat = $_POST['category_id'] ?: null; $name = trim($_POST['name'] ?? ''); $sku = trim($_POST['sku'] ?? ''); $unit = trim($_POST['unit'] ?? 'шт'); $price = (float)($_POST['price'] ?? 0); $min = (int)($_POST['min_stock'] ?? 0);
            if ($name === '') throw new Exception('Название обязательно');
            if ($id) { $pdo->prepare("UPDATE products SET category_id=?,name=?,sku=?,unit=?,price=?,min_stock=? WHERE id=?")->execute([$cat,$name,$sku,$unit,$price,$min,$id]); }
            else { $pdo->prepare("INSERT INTO products (category_id,name,sku,unit,price,min_stock) VALUES (?,?,?,?,?,?)")->execute([$cat,$name,$sku,$unit,$price,$min]); }
            flash('ok', 'Сохранено');
        } elseif ($op === 'delete') { $pdo->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_POST['id']]); flash('ok', 'Удалено'); }
    } catch (Throwable $e) { flash('err', $e->getMessage()); }
    redirect('products.php');
}
$edit = null; if (!empty($_GET['edit'])) { $st = $pdo->prepare("SELECT * FROM products WHERE id=?"); $st->execute([(int)$_GET['edit']]); $edit = $st->fetch(); }
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$rows = $pdo->query("SELECT p.*, c.name AS category_name, (COALESCE(si.q,0) - COALESCE(so.q,0)) AS stock FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN (SELECT product_id, SUM(qty) q FROM stock_in GROUP BY product_id) si ON si.product_id=p.id LEFT JOIN (SELECT product_id, SUM(qty) q FROM stock_out GROUP BY product_id) so ON so.product_id=p.id ORDER BY p.name")->fetchAll();
$title = 'Товары'; include __DIR__ . '/../includes/header.php';
?>
<div class="grid2">
    <div><h2 class="section">Список (<?= count($rows) ?>)</h2><table class="table"><thead><tr><th>Артикул</th><th>Название</th><th>Категория</th><th>Остаток</th><th>Цена</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><?= e($r['sku']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['category_name']) ?></td><td class="<?= $r['stock'] <= $r['min_stock'] ? 'warn' : '' ?>"><?= (int)$r['stock'] ?> <?= e($r['unit']) ?></td><td><?= number_format((float)$r['price'], 2, '.', ' ') ?></td><td class="actions"><a href="?edit=<?= $r['id'] ?>" class="btn btn-sm">✏</a><form method="post" onsubmit="return confirm('Удалить?')" style="display:inline"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger">🗑</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <div><h2 class="section"><?= $edit ? 'Редактировать' : 'Добавить' ?></h2><form method="post" class="form"><?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <label>Название<input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required></label>
        <label>Артикул<input type="text" name="sku" value="<?= e($edit['sku'] ?? '') ?>"></label>
        <label>Категория<select name="category_id"><option value="">—</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
        <label>Ед. изм.<input type="text" name="unit" value="<?= e($edit['unit'] ?? 'шт') ?>"></label>
        <label>Цена<input type="number" step="0.01" name="price" value="<?= e($edit['price'] ?? '0') ?>"></label>
        <label>Мин. остаток<input type="number" name="min_stock" value="<?= e($edit['min_stock'] ?? '0') ?>"></label>
        <button class="btn btn-primary"><?= $edit ? 'Обновить' : 'Создать' ?></button>
    </form></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>