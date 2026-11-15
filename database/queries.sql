-- 1. Получить список всех товаров с их категориями (LEFT JOIN)
SELECT p.name AS product_name, c.name AS category_name, p.price 
FROM products p
LEFT JOIN categories c ON p.category_id = c.id;

-- 2. Посчитать общую стоимость склада (SUM и вычисляемый остаток)
SELECT SUM((COALESCE(si.q, 0) - COALESCE(so.q, 0)) * p.price) AS total_warehouse_value
FROM products p
LEFT JOIN (SELECT product_id, SUM(qty) AS q FROM stock_in GROUP BY product_id) si ON si.product_id = p.id
LEFT JOIN (SELECT product_id, SUM(qty) AS q FROM stock_out GROUP BY product_id) so ON so.product_id = p.id;

-- 3. Найти товары, у которых остаток меньше минимального (WHERE)
SELECT p.name, p.min_stock, (COALESCE(si.q, 0) - COALESCE(so.q, 0)) AS current_stock
FROM products p
LEFT JOIN (SELECT product_id, SUM(qty) AS q FROM stock_in GROUP BY product_id) si ON si.product_id = p.id
LEFT JOIN (SELECT product_id, SUM(qty) AS q FROM stock_out GROUP BY product_id) so ON so.product_id = p.id
WHERE (COALESCE(si.q, 0) - COALESCE(so.q, 0)) <= p.min_stock;