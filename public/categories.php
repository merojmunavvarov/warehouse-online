<?php
require __DIR__ . '/../app/db.php'; require __DIR__ . '/../app/helpers.php'; require __DIR__ . '/../app/auth.php'; require_login();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['_csrf'] ?? ''); $op = $_POST['op'] ?? '';
    try {
        if ($op === 'save') { $id = (int)($_POST['id'] ?? 0); $name = trim($_POST['name'] ?? ''); $desc = trim($_POST['description'] ?? ''); if ($name === '') throw new Exception('Название обязательно'); if ($id) { $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?")->execute([$name,$desc,$id]); } else { $pdo->prepare("INSERT INTO categories (name, description) VALUES (?,?)")->execute([$name,$desc]); } flash('ok', 'Сохранено'); }
        elseif ($op === 'delete') { $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_POST['id']]); flash('ok', 'Удалено'); }
    } catch (Throwable $e) { flash('err', $e->getMessage()); }
    redirect('categories.php');
}
$edit = null; if (!empty($_GET['edit'])) { $st = $pdo->prepare("SELECT * FROM categories WHERE id=?"); $st->execute([(int)$_GET['edit']]); $edit = $st->fetch(); }
$rows = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(); $title = 'Категории'; include __DIR__ . '/../includes/header.php';
?>
<div class="grid2">
    <div><h2 class="section">Список</h2><table class="table"><thead><tr><th>ID</th><th>Название</th><th>Описание</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><?= (int)$r['id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['description']) ?></td><td class="actions"><a href="?edit=<?= $r['id'] ?>" class="btn btn-sm">✏</a><form method="post" onsubmit="return confirm('Удалить?')" style="display:inline"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger">🗑</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <div><h2 class="section"><?= $edit ? 'Редактировать' : 'Добавить' ?></h2><form method="post" class="form"><?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <label>Название<input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required></label>
        <label>Описание<textarea name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea></label>
        <button class="btn btn-primary"><?= $edit ? 'Обновить' : 'Создать' ?></button>
    </form></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>