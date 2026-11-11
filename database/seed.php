<?php
require __DIR__ . '/../app/db.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);
db()->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,'admin') ON DUPLICATE KEY UPDATE password=VALUES(password)")->execute(['Admin', 'admin@warehouse.local', $hash]);
db()->exec("INSERT IGNORE INTO categories (name) VALUES ('Электроника'),('Канцелярия'),('Инструменты')");
echo "OK: admin@warehouse.local / admin123\n";