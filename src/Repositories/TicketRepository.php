<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;

final class TicketRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $sql = "SELECT t.id, t.event_id, t.ticket_type, t.price, t.quantity_available, t.quantity_sold,
                       e.title AS event_title, e.event_date, e.category
                FROM tickets t
                INNER JOIN events e ON e.id = t.event_id
                WHERE t.id = :id
                LIMIT 1";

        return $this->one($sql, ['id' => $id]);
    }

    public function findByEventIds(array $eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        $eventIds = array_values(array_map('intval', $eventIds));
        $placeholders = [];
        $params = [];
        foreach ($eventIds as $index => $eventId) {
            $key = 'event_id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $eventId;
        }

        $sql = "SELECT id, event_id, ticket_type, price, quantity_available, quantity_sold
                FROM tickets
                WHERE event_id IN (" . implode(',', $placeholders) . ")
                ORDER BY event_id ASC, price ASC, id ASC";

        return $this->all($sql, $params);
    }

    public function findByIds(array $ticketIds): array
    {
        if ($ticketIds === []) {
            return [];
        }

        $ticketIds = array_values(array_map('intval', $ticketIds));
        $placeholders = [];
        $params = [];
        foreach ($ticketIds as $index => $ticketId) {
            $key = 'ticket_id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $ticketId;
        }

        $sql = "SELECT t.id, t.event_id, t.ticket_type, t.price, t.quantity_available, t.quantity_sold,
                       e.title AS event_title, e.event_date, e.category
                FROM tickets t
                INNER JOIN events e ON e.id = t.event_id
                WHERE t.id IN (" . implode(',', $placeholders) . ")";

        $rows = $this->all($sql, $params);
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int)$row['id']] = $row;
        }

        return $indexed;
    }
}
