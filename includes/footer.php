    </main>
</div>
<script>
    // Автоматическое скрытие уведомлений
    const toasts = document.querySelectorAll('.flash');
    toasts.forEach(toast => {
        setTimeout(() => {
            toast.style.animation = 'slideInRight 0.4s reverse forwards';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    });
</script>
<script src="js/app.js"></script>
</body>
</html>