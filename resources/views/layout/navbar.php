<nav class="navbar">
    <a href="/" class="logo">Haarlem Festival</a>

    <ul class="nav-links">
        <li><a href="/">Home</a></li>
        <li><a href="/schedule">Schedule</a></li>
        <li><a href="/cart">Cart (<?= \App\Services\CartService::count() ?>)</a></li>
        <li><a href="/contact">Contact</a></li>

        <?php if (\App\Framework\Auth::isLoggedIn()): ?>
            <li><a href="/profile">Profile</a></li>
            
            <?php if (\App\Framework\Auth::isAdmin()): ?>
                <li><a href="/admin/events">Admin</a></li>
            <?php endif; ?>
            
            <li>
                <form method="POST" action="/logout" style="display:inline;">
                    <?= \App\Framework\Csrf::field() ?>
                    <button type="submit" style="background:none; border:none; color:inherit; cursor:pointer; font:inherit;">
                        Logout
                    </button>
                </form>
            </li>
        <?php else: ?>
            <li><a href="/login">Login</a></li>
            <li><a href="/register">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>
