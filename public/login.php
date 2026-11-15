<?php
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/auth.php';
require __DIR__ . '/../app/helpers.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); 
    $pass = $_POST['password'] ?? '';
    try {
        $stmt = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1"); 
        $stmt->execute([$email]); 
        $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password'])) { 
            login_user($u); 
            redirect('index.php'); 
        }
        $error = 'Неверный email или пароль';
    } catch (Throwable $e) { 
        $error = 'Ошибка БД: ' . $e->getMessage(); 
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход — Warehouse</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <form class="login-form" method="post">
        <h1>📦 Warehouse</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <input type="email" name="email" placeholder="Email" required>
        
        <div style="position: relative;">
            <input type="password" name="password" id="login-password" placeholder="Пароль" required style="width: 100%; padding-right: 40px;">
            <span onclick="togglePassword('login-password', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px; user-select: none;">👁️</span>
        </div>
        
        <button type="submit">Войти</button>
    </form>

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
</body>
</html>