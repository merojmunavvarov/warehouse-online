<?php
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';
require_admin();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['_csrf'] ?? '');
    $op = $_POST['op'] ?? '';
    try {
        if ($op === 'create') {
            $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $pass = $_POST['password'] ?? ''; $role = $_POST['role'] ?? 'storekeeper';
            if (!$name || !$email || strlen($pass) < 6) throw new Exception('Пароль >= 6');
            $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)")->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT),$role]);
            flash('ok', 'Создан');
        } elseif ($op === 'delete') {
            if ((int)$_POST['id'] === current_user()['id']) throw new Exception('Нельзя себя');
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_POST['id']]);
            flash('ok', 'Удалено');
        }
    } catch (Throwable $e) { flash('err', $e->getMessage()); }
    redirect('users.php');
}
$rows = $pdo->query("SELECT id,name,email,role FROM users ORDER BY id")->fetchAll();
$title = 'Пользователи';
include __DIR__ . '/../includes/header.php';
?>
<div class="grid2">
    <div>
        <table class="table"><thead><tr><th>Имя</th><th>Email</th><th>Роль</th><th></th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?>
            <tr><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['role']) ?></td><td><?php if ($r['id'] != current_user()['id']): ?><form method="post" onsubmit="return confirm('Удалить?')"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger">🗑</button></form><?php endif; ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
    <div>
        <h2 class="section">Создать</h2>
        <form method="post" class="form"><?= csrf_field() ?><input type="hidden" name="op" value="create">
            <label>Имя<input type="text" name="name" required></label>
            <label>Email<input type="email" name="email" required></label>
            <label>Пароль<input type="password" name="password" minlength="6" required></label>
            <label>Роль<select name="role"><option value="storekeeper">storekeeper</option><option value="admin">admin</option></select></label>
            <button class="btn btn-primary">Создать</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>ы