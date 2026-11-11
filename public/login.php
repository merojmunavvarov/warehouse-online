<?php
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/auth.php';
require __DIR__ . '/../app/helpers.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); $pass = $_POST['password'] ?? '';
    try {
        $stmt = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1"); $stmt->execute([$email]); $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password'])) { login_user($u); redirect('index.php'); }
        $error = 'Неверный email или пароль';
    } catch (Throwable $e) { $error = 'Ошибка БД: ' . $e->getMessage(); }
}
?>
<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Вход</title><link rel="stylesheet" href="css/style.css"></head><body class="login-page">
<form class="login-form" method="post"><h1>📦 Warehouse</h1>
<?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Пароль" required><button type="submit">Войти</button></form></body></html>