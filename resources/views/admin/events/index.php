<?php
declare(strict_types=1);

/** @var array<int, array{id:int,title:string,event_date:string}> $events */
?>

<h1>Admin • Events</h1>

<p><a href="/admin/events/new">+ New event</a></p>

<?php if (empty($events)): ?>
  <p>No events found.</p>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th class="text-left">ID</th>
        <th class="text-left">Title</th>
        <th class="text-left">Date</th>
        <th class="text-left">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($events as $e): ?>
        <tr>
          <td><?= (int)$e['id'] ?></td>
          <td><?= h((string)$e['title']) ?></td>
          <td><?= h((string)$e['event_date']) ?></td>
          <td>
            <a href="/admin/events/edit?id=<?= (int)$e['id'] ?>" class="link-primary">Edit</a>

            <form method="POST" action="/admin/events/delete?id=<?= (int)$e['id'] ?>" class="d-inline">
              <?= \App\Framework\Csrf::field() ?>
              <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this event?')">
                Delete
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
