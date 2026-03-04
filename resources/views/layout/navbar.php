<nav class="navbar">
    <a href="/" class="logo">Haarlem Festival</a>

    <ul class="nav-links">
        <?php
        // Get current path for highlighting
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $isActive = fn($route) => $path === $route || strpos($path, $route . '/') === 0;
        ?>
        <li><a href="/" class="<?= $path === '/' ? 'active' : '' ?>">Home</a></li>
        <li><a href="/schedule" class="<?= $isActive('/schedule') ? 'active' : '' ?>">Schedule</a></li>
        <li><a href="/cart" class="<?= $isActive('/cart') ? 'active' : '' ?>">Cart (<?= \App\Services\CartService::count() ?>)</a></li>
        <li><a href="/contact" class="<?= $isActive('/contact') ? 'active' : '' ?>">Contact</a></li>

        <?php if (\App\Framework\Auth::isLoggedIn()): ?>
            <li><a href="/profile" class="<?= $isActive('/profile') && !str_starts_with($path, '/profile/orders') ? 'active' : '' ?>">Profile</a></li>
            <li><a href="/profile/orders" class="<?= $isActive('/profile/orders') ? 'active' : '' ?>">My Orders</a></li>
            
            <?php if (\App\Framework\Auth::isAdmin()): ?>
                <li><a href="/admin" class="<?= $isActive('/admin') && !str_starts_with($path, '/admin/events') && !str_starts_with($path, '/admin/orders') ? 'active' : '' ?>">Admin</a></li>
                <li><a href="/admin/events" class="<?= $isActive('/admin/events') ? 'active' : '' ?>">Events</a></li>
                <li><a href="/admin/orders" class="<?= $isActive('/admin/orders') ? 'active' : '' ?>">Orders</a></li>
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
            <li><a href="/login" class="<?= $isActive('/login') ? 'active' : '' ?>">Login</a></li>
            <li><a href="/register" class="<?= $isActive('/register') ? 'active' : '' ?>">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>
