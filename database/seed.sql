INSERT INTO tickets (event_id, ticket_type, price, quantity_available, quantity_sold)
SELECT e.id, 'Regular', 15.00, 120, 0 FROM events e WHERE e.title = 'Dance Night Haarlem'
UNION ALL
SELECT e.id, 'VIP', 29.50, 40, 0 FROM events e WHERE e.title = 'Dance Night Haarlem'
UNION ALL
SELECT e.id, 'Student', 10.00, 60, 0 FROM events e WHERE e.title = 'Dance Night Haarlem'
UNION ALL
SELECT e.id, 'Regular', 12.50, 140, 0 FROM events e WHERE e.title = 'Jazz by the Canal'
UNION ALL
SELECT e.id, 'VIP', 24.00, 35, 0 FROM events e WHERE e.title = 'Jazz by the Canal'
UNION ALL
SELECT e.id, 'Student', 8.50, 70, 0 FROM events e WHERE e.title = 'Jazz by the Canal'
UNION ALL
SELECT e.id, 'Regular', 9.00, 100, 0 FROM events e WHERE e.title = 'Kids Mystery Museum'
UNION ALL
SELECT e.id, 'Family', 30.00, 50, 0 FROM events e WHERE e.title = 'Kids Mystery Museum'
UNION ALL
SELECT e.id, 'Student', 7.00, 80, 0 FROM events e WHERE e.title = 'Kids Mystery Museum'
ON DUPLICATE KEY UPDATE
	price = VALUES(price),
	quantity_available = VALUES(quantity_available),
	quantity_sold = VALUES(quantity_sold);
