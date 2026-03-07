<?php
declare(strict_types=1);

use App\Framework\Csrf;

/** @var ?string $success */
/** @var ?string $error */
/** @var array $errors */
/** @var array $old */
?>

<h1>Create Account</h1>

<?php if (!empty($success)): ?>
  <div class="flash flash-success">
    <?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="flash flash-error">
    <?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div class="flash flash-error">
    <ul class="list-default">
      <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/register" class="auth-container">
  <?= Csrf::field() ?>

  <div class="form-group">
    <label for="first_name" class="form-label">First Name *</label>
    <input id="first_name" type="text" name="first_name" required class="form-input" value="<?= htmlspecialchars($old['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div class="form-group">
    <label for="last_name" class="form-label">Last Name *</label>
    <input id="last_name" type="text" name="last_name" required class="form-input" value="<?= htmlspecialchars($old['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div class="form-group">
    <label for="email" class="form-label">Email *</label>
    <input id="email" type="email" name="email" required class="form-input" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div class="form-group">
    <label for="password" class="form-label">Password *</label>
    <input id="password" type="password" name="password" required class="form-input">
    <small class="form-hint">
      Must be 8+ characters with uppercase, lowercase, number, and special character
    </small>
  </div>

  <div class="form-group mb-3">
    <label for="password_confirm" class="form-label">Confirm Password *</label>
    <input id="password_confirm" type="password" name="password_confirm" required class="form-input">
  </div>

  <button type="submit" class="btn btn-primary">
    Create Account
  </button>
  
  <p class="mt-2">
    Already have an account? <a href="/login" class="link-primary">Login here</a>
  </p>
</form>
