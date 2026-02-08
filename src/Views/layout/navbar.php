<nav class="navbar">
    <a href="/" class="logo">Haarlem Festival</a>

    <ul class="nav-links">
        <li><a href="/">Home</a></li>
        <li><a href="/schedule">Schedule</a></li>
       <?php if (\App\Framework\Auth::isAdmin()): ?>
  <li><a href="/admin/events">Admin</a></li>
<?php endif; ?>


    </ul>
</nav>
