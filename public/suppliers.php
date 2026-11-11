<?php
require __DIR__ . '/../app/db.php'; require __DIR__ . '/../app/helpers.php'; require __DIR__ . '/../app/auth.php'; require_login();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['_csrf'] ?? ''); $op = $_POST['op'] ?? '';
    try {
        if ($op === 'save') { $id = (int)($_POST['id'] ?? 0); $data = [trim($_POST['name']??''), trim($_POST['phone']??''), trim($_POST['email']??''), trim($_POST['address']??'')]; if ($data[0] === '') throw new Exception('Название обязательно'); if ($id) { $pdo->prepare("UPDATE suppliers SET name=?,phone=?,email=?,address=? WHERE id=?")->execute([...$data,$id]); } else { $pdo->prepare("INSERT INTO suppliers (name,phone,email,address) VALUES (?,?,?,?)")->execute($data); } flash('ok', 'Сохранено'); }
        elseif ($op === 'delete') { $pdo->prepare("DELETE FROM suppliers WHERE id=?")->execute([(int)$_POST['id']]); flash('ok', 'Удалено'); }
    } catch (Throwable $e) { flash('err', $e->getMessage()); }
    redirect('suppliers.php');
}
$edit = null; if (!empty($_GET['edit'])) { $st = $pdo->prepare("SELECT * FROM suppliers WHERE id=?"); $st->execute([(int)$_GET['edit']]); $edit = $st->fetch(); }
$rows = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll(); $title = 'Поставщики'; include __DIR__ . '/../includes/header.php';
?>
<div class="grid2">
    <div><h2 class="section">Список</h2><table class="table"><thead><tr><th>ID</th><th>Название</th><th>Телефон</th><th>Email</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><?= (int)$r['id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['email']) ?></td><td class="actions"><a href="?edit=<?= $r['id'] ?>" class="btn btn-sm">✏</a><form method="post" onsubmit="return confirm('Удалить?')" style="display:inline"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger">🗑</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <div><h2 class="section"><?= $edit ? 'Редактировать' : 'Добавить' ?></h2><form method="post" class="form"><?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <label>Название<input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required></label>
        <label>Телефон<input type="text" name="phone" value="<?= e($edit['phone'] ?? '') ?>"></label>
        <label>Email<input type="email" name="email" value="<?= e($edit['email'] ?? '') ?>"></label>
        <label>Адрес<input type="text" name="address" value="<?= e($edit['address'] ?? '') ?>"></label>
        <button class="btn btn-primary"><?= $edit ? 'Обновить' : 'Создать' ?></button>
    </form></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>