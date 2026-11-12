<?php
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';
require_login();

$title = 'Dashboard';
$pdo = db();

// Получаем статистику
$countProducts   = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$countCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalValue      = (float)$pdo->query("SELECT COALESCE(SUM((COALESCE(si.q,0) - COALESCE(so.q,0)) * p.price), 0) FROM products p LEFT JOIN (SELECT product_id, SUM(qty) q FROM stock_in GROUP BY product_id) si ON si.product_id=p.id LEFT JOIN (SELECT product_id, SUM(qty) q FROM stock_out GROUP BY product_id) so ON so.product_id=p.id")->fetchColumn();

// Получаем товары, которых мало на складе
$lowStock = $pdo->query("
    SELECT p.id, p.name, p.min_stock, (COALESCE(si.q,0) - COALESCE(so.q,0)) AS stock
    FROM products p
    LEFT JOIN (SELECT product_id, SUM(qty) q FROM stock_in GROUP BY product_id) si ON si.product_id=p.id
    LEFT JOIN (SELECT product_id, SUM(qty) q FROM stock_out GROUP BY product_id) so ON so.product_id=p.id
    WHERE (COALESCE(si.q,0) - COALESCE(so.q,0)) <= p.min_stock
    ORDER BY stock ASC LIMIT 5
")->fetchAll();

// Получаем последние операции
$recent = $pdo->query("
    (SELECT 'Приход' AS type, p.name AS product, si.qty, si.created_at FROM stock_in si JOIN products p ON p.id=si.product_id)
    UNION ALL
    (SELECT 'Расход' AS type, p.name AS product, so.qty, so.created_at FROM stock_out so JOIN products p ON p.id=so.product_id)
    ORDER BY created_at DESC LIMIT 5
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- Приветственный баннер -->
<div style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); padding: 32px; border-radius: 20px; color: white; margin-bottom: 32px; box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3); display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="font-size: 26px; margin-bottom: 8px; color: white; font-weight: 700;">Добро пожаловать в систему!</h2>
        <p style="font-size: 15px; opacity: 0.9;">Управляйте складом, контролируйте остатки и следите за поставками в реальном времени.</p>
    </div>
    <div style="font-size: 64px; opacity: 0.8; line-height: 1;">📦</div>
</div>

<!-- Карточки статистики -->
<section class="cards">
    <div class="card" style="border-left: 4px solid #3b82f6;">
        <h3 style="display:flex; justify-content:space-between; align-items:center;">Товары <span style="font-size:24px;">📦</span></h3>
        <p><?= $countProducts ?></p>
    </div>
    <div class="card" style="border-left: 4px solid #10b981;">
        <h3 style="display:flex; justify-content:space-between; align-items:center;">Категории <span style="font-size:24px;">🗂</span></h3>
        <p><?= $countCategories ?></p>
    </div>
    <div class="card" style="border-left: 4px solid #f59e0b;">
        <h3 style="display:flex; justify-content:space-between; align-items:center;">Стоимость склада <span style="font-size:24px;">💰</span></h3>
        <p style="font-size: 26px;"><?= number_format($totalValue, 2, '.', ' ') ?> <span style="font-size: 16px; color:#6b7280; font-weight:500;">TJS</span></p>
    </div>
</section>

<div class="grid2">
    <!-- Левая часть: Последние операции -->
    <div>
        <h2 class="section">🕒 Последние операции</h2>
        <table class="table">
            <thead><tr><th>Тип</th><th>Товар</th><th>Кол-во</th><th>Дата</th></tr></thead>
            <tbody>
            <?php if (!$recent): ?><tr><td colspan="4" class="empty">Операций пока нет</td></tr><?php endif; ?>
            <?php foreach ($recent as $r): ?>
                <tr>
                    <td><span class="tag <?= $r['type']==='Приход'?'in':'out' ?>"><?= e($r['type']) ?></span></td>
                    <td><?= e($r['product']) ?></td>
                    <td><?= (int)$r['qty'] ?></td>
                    <td style="color:#6b7280; font-size:13px;"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Правая часть: Товары ниже минимума -->
    <div>
        <h2 class="section" style="color: #ef4444;">⚠ Требуют внимания</h2>
        <div class="form" style="gap: 12px;">
            <?php if (!$lowStock): ?>
                <div style="text-align:center; padding: 20px; color:#10b981; font-weight:600;">
                    ✅ Все товары в норме
                </div>
            <?php endif; ?>
            <?php foreach ($lowStock as $r): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f3f4f6; padding-bottom:8px;">
                    <span style="font-weight:500; font-size:14px;"><?= e($r['name']) ?></span>
                    <span style="background:#fee2e2; color:#b91c1c; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:700;">
                        Осталось: <?= (int)$r['stock'] ?> шт
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>