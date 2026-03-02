<?php
declare(strict_types=1);

/** @var string $title */
/** @var string $content */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ?? 'Haarlem Festival', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>

<?php require __DIR__ . '/navbar.php'; ?>

<?php
use App\Framework\Flash;

$success = Flash::get('success');
$error   = Flash::get('error');
?>

<?php if ($success): ?>
  <div class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>


<main class="container">
    <?= $content ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>

<script src="/assets/js/main.js" defer></script>
</body>
</html>
