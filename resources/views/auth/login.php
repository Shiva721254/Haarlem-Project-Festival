<?php
declare(strict_types=1);

use App\Framework\Csrf;

/** @var ?string $success */
/** @var ?string $error */
?>

<h1>Admin Login</h1>

<?php if (!empty($success)): ?>
  <div style="padding:10px; border:1px solid #2e7d32; background:#e8f5e9; margin:10px 0;">
    <?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="padding:10px; border:1px solid #c62828; background:#ffebee; margin:10px 0;">
    <?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<form method="POST" action="/login" style="max-width:420px;">
  <?= Csrf::field() ?>

  <div style="margin-bottom:12px;">
    <label for="email">Email</label><br>
    <input id="email" type="email" name="email" required style="width:100%; padding:8px;">
  </div>

  <div style="margin-bottom:12px;">
    <label for="password">Password</label><br>
    <input id="password" type="password" name="password" required style="width:100%; padding:8px;">
  </div>

  <button type="submit" style="padding:10px 14px;">Login</button>
</form>
