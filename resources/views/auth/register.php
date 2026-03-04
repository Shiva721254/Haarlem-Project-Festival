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
    <ul style="margin: 0; padding-left: 20px;">
      <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/register" style="max-width:500px;">
  <?= Csrf::field() ?>

  <div style="margin-bottom:12px;">
    <label for="first_name">First Name *</label><br>
    <input id="first_name" type="text" name="first_name" required 
           style="width:100%; padding:8px;" 
           value="<?= htmlspecialchars($old['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div style="margin-bottom:12px;">
    <label for="last_name">Last Name *</label><br>
    <input id="last_name" type="text" name="last_name" required 
           style="width:100%; padding:8px;"
           value="<?= htmlspecialchars($old['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div style="margin-bottom:12px;">
    <label for="email">Email *</label><br>
    <input id="email" type="email" name="email" required 
           style="width:100%; padding:8px;"
           value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>

  <div style="margin-bottom:12px;">
    <label for="password">Password *</label><br>
    <input id="password" type="password" name="password" required 
           style="width:100%; padding:8px;">
    <small style="color:#666; display:block; margin-top:4px;">
      Must be 8+ characters with uppercase, lowercase, number, and special character
    </small>
  </div>

  <div style="margin-bottom:12px;">
    <label for="password_confirm">Confirm Password *</label><br>
    <input id="password_confirm" type="password" name="password_confirm" required 
           style="width:100%; padding:8px;">
  </div>

  <button type="submit" style="padding:10px 20px; cursor:pointer;">
    Create Account
  </button>
  
  <p style="margin-top:15px;">
    Already have an account? <a href="/login">Login here</a>
  </p>
</form>
