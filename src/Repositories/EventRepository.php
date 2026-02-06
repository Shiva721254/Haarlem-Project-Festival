<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;

final class EventRepository extends Repository
{
    public function allOrderedByDate(): array
    {
        $sql = "SELECT id, title, event_date
                FROM events
                ORDER BY event_date ASC, id ASC";

        return $this->all($sql);
    }
}
