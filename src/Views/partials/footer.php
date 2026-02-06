<?php
declare(strict_types=1);

$year = (int)date('Y');

return '
<footer class="site-footer">
  <div class="container footer-inner">
    <p>© ' . $year . ' Haarlem Festival</p>
    <p class="muted">Built with PHP + Docker • Sprint 1</p>
  </div>
</footer>
';
