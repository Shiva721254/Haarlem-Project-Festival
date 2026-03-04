<?php
declare(strict_types=1);

use App\Framework\Csrf;

/** @var ?string $success */
/** @var ?string $error */
/** @var ?string $old_email */
?>

<div style="max-width:500px; margin:40px auto;">
  <h1 style="text-align:center; margin-bottom:30px;">Admin Login</h1>

  <?php if (!empty($success)): ?>
    <div style="padding:12px 16px; border-left:4px solid #2e7d32; background:#e8f5e9; margin:15px 0; border-radius:4px;">
      ✅ <?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div style="padding:12px 16px; border-left:4px solid #c62828; background:#ffebee; margin:15px 0; border-radius:4px;">
      ⚠️ <?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="/login" style="background:#f9f9f9; padding:30px; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
    <?= Csrf::field() ?>

    <div style="margin-bottom:16px;">
      <label for="email" style="display:block; margin-bottom:6px; font-weight:bold;">Email Address</label>
      <input id="email" type="email" name="email" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; box-sizing:border-box; font-size:14px;" value="<?= htmlspecialchars($old_email ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div style="margin-bottom:20px;">
      <label for="password" style="display:block; margin-bottom:6px; font-weight:bold;">Password</label>
      <input id="password" type="password" name="password" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; box-sizing:border-box; font-size:14px;">
    </div>

    <button type="submit" style="width:100%; padding:12px; background:#0066cc; color:white; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:16px;">
      Login
    </button>
  </form>

  <p style="text-align:center; margin-top:20px; color:#666;">
    Don't have an account? <a href="/register" style="color:#0066cc; text-decoration:none; font-weight:bold;">Register here</a>
  </p>
</div>
