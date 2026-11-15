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
        // Создание пользователя
        if ($op === 'create') { 
            $name = trim($_POST['name'] ?? ''); 
            $email = trim($_POST['email'] ?? ''); 
            $pass = $_POST['password'] ?? ''; 
            $role = $_POST['role'] ?? 'storekeeper'; 
            if (!$name || !$email || strlen($pass) < 6) throw new Exception('Пароль должен быть не менее 6 символов'); 
            $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)")->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT),$role]);
            flash('ok', 'Пользователь создан');
        } 
        // Сброс пароля
        elseif ($op === 'reset_password') {
            $newPass = password_hash('12345678', PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$newPass, (int)$_POST['id']]);
            flash('ok', 'Пароль сброшен на 12345678');
        }
        // Удаление пользователя
        elseif ($op === 'delete') {
            if ((int)$_POST['id'] === current_user()['id']) throw new Exception('Вы не можете удалить самого себя');
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_POST['id']]);
            flash('ok', 'Пользователь удален');
        }
    } catch (Throwable $e) { 
        flash('err', $e->getMessage()); 
    }
    redirect('users.php');
}

$rows = $pdo->query("SELECT id,name,email,role FROM users ORDER BY id")->fetchAll();
$title = 'Пользователи';
include __DIR__ . '/../includes/header.php';
?>
<div class="grid2">
    <div>
        <h2 class="section">Список сотрудников</h2>
        <table class="table">
            <thead><tr><th>Имя</th><th>Email</th><th>Роль</th><th>Действия</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['name']) ?></td>
                    <td><?= e($r['email']) ?></td>
                    <td><span class="tag" style="<?= $r['role'] == 'admin' ? 'background:#e0e7ff; color:#3730a3;' : 'background:#f3f4f6;' ?>"><?= e($r['role']) ?></span></td>
                    <td style="display:flex; gap:6px;">
                        <?php if ($r['id'] != current_user()['id']): ?>
                            <!-- Кнопка сброса пароля -->
                            <form method="post" style="display:inline;" onsubmit="return confirm('Сбросить пароль сотрудника на 12345678?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="op" value="reset_password">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-sm" style="background:#f59e0b; color:white; border:none;" title="Сбросить пароль">🔑</button>
                            </form>
                            
                            <!-- Кнопка удаления -->
                            <form method="post" style="display:inline;" onsubmit="return confirm('Удалить пользователя?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="op" value="delete">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-sm btn-danger" title="Удалить">🗑</button>
                            </form>
                        <?php else: ?>
                            <!-- Кнопка перехода в свой профиль -->
                            <a href="profile.php" class="btn btn-sm" style="background:#2563eb; color:white; border:none; text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="Перейти в мой профиль">👤 Мой профиль</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div>
        <h2 class="section">Создать сотрудника</h2>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="op" value="create">
            <label>Имя<input type="text" name="name" required placeholder="Например: Иван"></label>
            <label>Email<input type="email" name="email" required placeholder="ivan@warehouse.local"></label>
            
            <label>Пароль
                <div style="position: relative;">
                    <input type="password" name="password" id="new-user-password" minlength="6" required style="width: 100%; padding-right: 40px;">
                    <span onclick="togglePassword('new-user-password', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px; user-select: none;">👁️</span>
                </div>
            </label>
            
            <label>Роль
                <select name="role">
                    <option value="storekeeper">Кладовщик (storekeeper)</option>
                    <option value="admin">Администратор (admin)</option>
                </select>
            </label>
            <button class="btn btn-primary">Создать</button>
        </form>
    </div>
</div>

<script>
    function togglePassword(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerText = '🙈';
        } else {
            input.type = 'password';
            icon.innerText = '👁️';
        }
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>