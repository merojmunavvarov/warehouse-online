<?php
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';
require_login();

$pdo = db();
$u = current_user();
$title = 'Мой профиль';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['_csrf'] ?? '');
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    try {
        // Получаем текущий хеш пароля из БД
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$u['id']]);
        $db_pass = $stmt->fetchColumn();

        if (!password_verify($current_pass, $db_pass)) {
            throw new Exception('Текущий пароль введен неверно');
        }
        if (strlen($new_pass) < 6) {
            throw new Exception('Новый пароль должен быть не менее 6 символов');
        }
        if ($new_pass !== $confirm_pass) {
            throw new Exception('Новые пароли не совпадают');
        }

        // Обновляем пароль
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$new_hash, $u['id']]);
        
        flash('ok', 'Пароль успешно изменен!');
        redirect('profile.php');

    } catch (Throwable $e) {
        flash('err', $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="grid2">
    <div>
        <h2 class="section">Информация об аккаунте</h2>
        <div class="form" style="gap: 16px;">
            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding-bottom:10px;">
                <span style="color:#6b7280;">Имя:</span>
                <strong><?= e($u['name']) ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding-bottom:10px;">
                <span style="color:#6b7280;">Роль:</span>
                <span class="tag"><?= e($u['role']) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span style="color:#6b7280;">Email:</span>
                <strong><?= e($_SESSION['user_email'] ?? 'admin@warehouse.local') ?></strong> 
                <!-- (Email мы храним в сессии, но если его там нет, покажет дефолт) -->
            </div>
        </div>
    </div>

    <div>
        <h2 class="section">Смена пароля</h2>
        <form method="post" class="form">
            <?= csrf_field() ?>
            
            <label>Текущий пароль
                <div style="position: relative;">
                    <input type="password" name="current_password" id="curr-pass" required style="width: 100%; padding-right: 40px;">
                    <span onclick="togglePassword('curr-pass', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px;">👁️</span>
                </div>
            </label>

            <label>Новый пароль
                <div style="position: relative;">
                    <input type="password" name="new_password" id="new-pass" minlength="6" required style="width: 100%; padding-right: 40px;">
                    <span onclick="togglePassword('new-pass', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px;">👁️</span>
                </div>
            </label>

            <label>Повторите новый пароль
                <div style="position: relative;">
                    <input type="password" name="confirm_password" id="conf-pass" minlength="6" required style="width: 100%; padding-right: 40px;">
                    <span onclick="togglePassword('conf-pass', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px;">👁️</span>
                </div>
            </label>

            <button class="btn btn-primary" style="margin-top: 10px;">💾 Сохранить новый пароль</button>
        </form>
    </div>
</div>

<script>
    function togglePassword(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') { input.type = 'text'; icon.innerText = '🙈'; } 
        else { input.type = 'password'; icon.innerText = '👁️'; }
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>