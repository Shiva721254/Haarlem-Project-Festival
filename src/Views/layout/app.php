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

<main class="container">
    <?= $content ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>

<script src="/assets/js/main.js" defer></script>
</body>
</html>
