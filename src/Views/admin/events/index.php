<?php
declare(strict_types=1);

/** @var array<int, array{id:int,title:string,event_date:string}> $events */

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<h1>Admin • Events</h1>

<p><a href="/admin/events/new">+ New event</a></p>

<?php if (empty($events)): ?>
  <p>No events found.</p>
<?php else: ?>
  <table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse; width:100%;">
    <thead>
      <tr>
        <th align="left">ID</th>
        <th align="left">Title</th>
        <th align="left">Date</th>
        <th align="left">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($events as $e): ?>
        <tr>
          <td><?= (int)$e['id'] ?></td>
          <td><?= h((string)$e['title']) ?></td>
          <td><?= h((string)$e['event_date']) ?></td>
          <td>
            <a href="/admin/events/edit?id=<?= (int)$e['id'] ?>">Edit</a>

            <form method="POST" action="/admin/events/delete?id=<?= (int)$e['id'] ?>" style="display:inline;">
              <button type="submit" onclick="return confirm('Delete this event?')">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
