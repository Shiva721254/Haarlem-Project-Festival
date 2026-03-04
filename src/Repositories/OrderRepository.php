<?php

namespace App\Repositories;

class OrderRepository extends Repository
{
    /**
     * Create a new order
     *
     * @param int|null $user_id
     * @param string $customer_email
     * @param string|null $customer_name
     * @param float $total_amount
     * @param array $items [ticket_id => quantity]
     * @return int Order ID
     */
    public function create(?int $user_id, string $customer_email, ?string $customer_name, float $total_amount, array $items): int
    {
        $this->db->beginTransaction();

        try {
            // Insert order header
            $stmt = $this->db->prepare('
                INSERT INTO orders (user_id, customer_email, customer_name, total_amount, status)
                VALUES (?, ?, ?, ?, "pending")
            ');
            $stmt->execute([$user_id, $customer_email, $customer_name, $total_amount]);
            $order_id = $this->db->lastInsertId();

            // Insert order items and update ticket quantity_sold
            foreach ($items as $ticket_id => $quantity) {
                $stmt = $this->db->prepare('
                    INSERT INTO order_items (order_id, ticket_id, quantity, price_at_purchase)
                    SELECT ?, ?, ?, price FROM tickets WHERE id = ?
                ');
                $stmt->execute([$order_id, $ticket_id, $quantity, $ticket_id]);

                // Increment quantity_sold
                $stmt = $this->db->prepare('
                    UPDATE tickets SET quantity_sold = quantity_sold + ? WHERE id = ?
                ');
                $stmt->execute([$quantity, $ticket_id]);
            }

            $this->db->commit();
            return $order_id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Find order by ID with items
     *
     * @param int $order_id
     * @return array|null Order with nested order_items
     */
    public function findById(int $order_id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT 
                o.*,
                oi.id as item_id,
                oi.ticket_id,
                oi.quantity,
                oi.price_at_purchase,
                t.ticket_type,
                e.title as event_title,
                e.event_date
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN tickets t ON oi.ticket_id = t.id
            LEFT JOIN events e ON t.event_id = e.id
            WHERE o.id = ?
            ORDER BY oi.id
        ');
        $stmt->execute([$order_id]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return null;
        }

        // Normalize result: first row is order header, rest are items
        $order = [
            'id' => $rows[0]['id'],
            'user_id' => $rows[0]['user_id'],
            'customer_email' => $rows[0]['customer_email'],
            'customer_name' => $rows[0]['customer_name'],
            'total_amount' => $rows[0]['total_amount'],
            'status' => $rows[0]['status'],
            'stripe_payment_intent_id' => $rows[0]['stripe_payment_intent_id'],
            'created_at' => $rows[0]['created_at'],
            'updated_at' => $rows[0]['updated_at'],
            'items' => []
        ];

        foreach ($rows as $row) {
            if ($row['item_id'] !== null) {
                $order['items'][] = [
                    'id' => $row['item_id'],
                    'ticket_id' => $row['ticket_id'],
                    'quantity' => $row['quantity'],
                    'price_at_purchase' => $row['price_at_purchase'],
                    'ticket_type' => $row['ticket_type'],
                    'event_title' => $row['event_title'],
                    'event_date' => $row['event_date']
                ];
            }
        }

        return $order;
    }

    /**
     * Find all orders by user ID
     *
     * @param int $user_id
     * @return array
     */
    public function findByUserId(int $user_id): array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC
        ');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    /**
     * Update order status
     *
     * @param int $order_id
     * @param string $status
     * @param string|null $stripe_intent_id
     * @return bool
     */
    public function updateStatus(int $order_id, string $status, ?string $stripe_intent_id = null): bool
    {
        if ($stripe_intent_id) {
            $stmt = $this->db->prepare('
                UPDATE orders
                SET status = ?, stripe_payment_intent_id = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ');
            return $stmt->execute([$status, $stripe_intent_id, $order_id]);
        } else {
            $stmt = $this->db->prepare('
                UPDATE orders
                SET status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ');
            return $stmt->execute([$status, $order_id]);
        }
    }

    /**
     * Find order by Stripe payment intent ID
     *
     * @param string $stripe_intent_id
     * @return array|null
     */
    public function findByStripeIntentId(string $stripe_intent_id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM orders WHERE stripe_payment_intent_id = ? LIMIT 1
        ');
        $stmt->execute([$stripe_intent_id]);
        return $stmt->fetch();
    }
}
