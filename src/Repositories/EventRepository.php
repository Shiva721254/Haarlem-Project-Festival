<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;

final class EventRepository extends Repository
{
    public function allOrderedByDate(): array
    {
        $sql = "SELECT id, title, event_date, category
                FROM events
                ORDER BY event_date ASC, id ASC";

        return $this->all($sql);
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT id, title, event_date, category
                FROM events
                WHERE id = :id";

        return $this->one($sql, ['id' => $id]);
    }

    public function create(string $title, string $eventDate, string $category): int
{
    $sql = "INSERT INTO events (title, event_date, category)
            VALUES (:title, :event_date, :category)";

    $this->exec($sql, [
        'title' => $title,
        'event_date' => $eventDate,
        'category' => $category,
    ]);

    return (int)$this->pdo()->lastInsertId();
}




   public function updateById(
    int $id,
    string $title,
    string $eventDate,
    string $category
): int {
    $sql = "UPDATE events
            SET title = :title,
                event_date = :event_date,
                category = :category
            WHERE id = :id";

    return $this->exec($sql, [
        'id' => $id,
        'title' => $title,
        'event_date' => $eventDate,
        'category' => $category,
    ]);
}


public function allForSchedule(?string $category): array
{
    if ($category === null || $category === '') {
        $sql = "SELECT id, title, event_date, category
                FROM events
                ORDER BY event_date ASC, id ASC";
        return $this->all($sql);
    }

    $sql = "SELECT id, title, event_date, category
            FROM events
            WHERE category = :category
            ORDER BY event_date ASC, id ASC";

    return $this->all($sql, ['category' => $category]);
}





    public function deleteById(int $id): int
    {
        $sql = "DELETE FROM events WHERE id = :id";
        return $this->exec($sql, ['id' => $id]);
    }
}
