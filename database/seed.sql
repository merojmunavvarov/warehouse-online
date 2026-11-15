-- Создание администратора по умолчанию
-- Пароль: admin123 (хеш сгенерирован алгоритмом Bcrypt)
INSERT INTO users (name, email, password, role) 
VALUES (
    'Admin', 
    'admin@warehouse.local', 
    '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlD.LW8v3x9g5wB5FjO4Yk3k1x2e3u', 
    'admin'
) ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Базовые категории
INSERT IGNORE INTO categories (name, description) VALUES 
('Электроника', 'Компьютеры, телефоны, аксессуары'),
('Канцелярия', 'Ручки, бумага, папки'),
('Инструменты', 'Молотки, отвертки, дрели');