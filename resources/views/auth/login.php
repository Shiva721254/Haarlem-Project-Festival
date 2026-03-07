<?php
declare(strict_types=1);

use App\Framework\Csrf;

/** @var ?string $success */
/** @var ?string $error */
/** @var ?string $old_email */
?>

<div class="auth-container">
  <h1 class="page-title">Admin Login</h1>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success-alt">
      ✅ <?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error">
      ⚠️ <?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="/login" class="form-wrapper">
    <?= Csrf::field() ?>

    <div class="form-group">
      <label for="email" class="form-label">Email Address</label>
      <input id="email" type="email" name="email" required class="form-input" value="<?= htmlspecialchars($old_email ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="form-group mb-3">
      <label for="password" class="form-label">Password</label>
      <input id="password" type="password" name="password" required class="form-input">
    </div>

    <button type="submit" class="btn btn-primary btn-full">
      Login
    </button>
  </form>

  <p class="text-center mt-3 text-muted">
    Don't have an account? <a href="/register" class="link-primary">Register here</a>
  </p>
</div>
