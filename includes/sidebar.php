<aside class="sidebar">
    <div class="logo">📦 Warehouse</div>
    <nav>
        <a href="index.php">📊 Dashboard</a>
        <a href="products.php">📦 Товары</a>
        <a href="categories.php">🗂 Категории</a>
        <a href="suppliers.php">🚚 Поставщики</a>
        <a href="stock_in.php">➕ Приход</a>
        <a href="stock_out.php">➖ Расход</a>
        <a href="reports.php">📈 Отчеты</a>
        <?php if (is_admin()): ?><a href="users.php">👥 Пользователи</a><?php endif; ?>
    </nav>
</aside>