<?php
declare(strict_types=1);

/**
 * @var string $mode
 * @var array{id?:int,title:string,event_date:string,category?:string} $event
 * @var array<int,string> $errors
 */

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Category list (fully qualified – best practice in views)
$categories = \App\Config\EventCategories::all();

$isEdit = ($mode === 'edit');
$action = $isEdit
    ? '/admin/events/update?id=' . (int)($event['id'] ?? 0)
    : '/admin/events/store';
?>

<h1><?= $isEdit ? 'Edit Event' : 'New Event' ?></h1>

<p><a href="/admin/events">← Back to events</a></p>

<?php if (!empty($errors)): ?>
    <div style="border:1px solid #c00; padding:12px; margin:12px 0;">
        <ul style="margin:0; padding-left:18px;">
            <?php foreach ($errors as $err): ?>
                <li><?= h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= h($action) ?>">

    <!-- Title -->
    <div style="margin:10px 0;">
        <label>Title</label><br>
        <input
            type="text"
            name="title"
            value="<?= h((string)($event['title'] ?? '')) ?>"
            required
            style="width: 320px;"
        >
    </div>

    <!-- Category (DROPDOWN) -->
    <div style="margin:10px 0;">
        <label>Category</label><br>

        <select name="category" required style="width: 332px;">
            <option value="">-- select category --</option>

            <?php foreach ($categories as $c): ?>
                <option value="<?= h($c) ?>"
                    <?= ((string)($event['category'] ?? '') === $c) ? 'selected' : '' ?>>
                    <?= h($c) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Date -->
    <div style="margin:10px 0;">
        <label>Date (YYYY-MM-DD)</label><br>
        <input
            type="date"
            name="event_date"
            value="<?= h((string)($event['event_date'] ?? '')) ?>"
            required
        >
    </div>

    <button type="submit">
        <?= $isEdit ? 'Update' : 'Create' ?>
    </button>
</form>
