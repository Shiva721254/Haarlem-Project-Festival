<?php
declare(strict_types=1);

/**
 * @var string $mode
 * @var array{id?:int,title:string,event_date:string,category?:string} $event
 * @var array<int,string> $errors
 */

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
    <div class="alert alert-error">
        <ul class="list-default">
            <?php foreach ($errors as $err): ?>
                <li><?= h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= h($action) ?>">
     <?= \App\Framework\Csrf::field() ?>

    <!-- Title -->
    <div class="form-group">
        <label class="form-label">Title</label>
        <input type="text" name="title" value="<?= h((string)($event['title'] ?? '')) ?>" required class="form-input" style="width: 320px;">
    </div>

    <!-- Category (DROPDOWN) -->
    <div class="form-group">
        <label class="form-label">Category</label>
        <select name="category" required class="form-input" style="width: 332px;">
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
    <div class="form-group">
        <label class="form-label">Date (YYYY-MM-DD)</label>
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
