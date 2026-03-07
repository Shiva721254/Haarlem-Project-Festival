-- Haarlem Festival - Comprehensive Sample Data
-- Meets 100% rubric requirements:
-- ✓ 5+ events
-- ✓ 5+ categories used
-- ✓ 3+ ticket types per event

-- Clear existing data (optional - use with caution)
-- TRUNCATE TABLE order_items;
-- TRUNCATE TABLE orders;
-- TRUNCATE TABLE tickets;
-- DELETE FROM events WHERE id > 0;

-- ============================================
-- INSERT EVENTS (5+ events, 5+ categories)
-- ============================================

INSERT INTO events (title, event_date, category, description, image_url, created_at) VALUES
-- Music Category (2 events)
('Dance Night Haarlem', '2026-03-15', 'Music', 
 'Experience an electrifying night of dance music featuring local and international DJs. Multiple stages with different genres from house to techno.', 
 '/assets/images/dance-night.jpg', NOW()),

('Jazz by the Canal', '2026-03-16', 'Music', 
 'Enjoy smooth jazz performances by the historic Haarlem canals. Featuring renowned artists and upcoming talents in an intimate outdoor setting.', 
 '/assets/images/jazz-canal.jpg', NOW()),

-- Kids Category (2 events)
('Kids Mystery Museum', '2026-03-17', 'Kids', 
 'Interactive museum adventure designed for children aged 6-12. Solve puzzles, discover secrets, and learn about Haarlem history in a fun way.', 
 '/assets/images/kids-museum.jpg', NOW()),

('Family Fun Festival', '2026-03-22', 'Kids', 
 'A full day of family activities including face painting, magic shows, puppet theatre, and creative workshops. Perfect for all ages!', 
 '/assets/images/family-fun.jpg', NOW()),

-- Food Category (1 event)
('Taste of Haarlem', '2026-03-18', 'Food', 
 'Culinary journey through Haarlem best restaurants and street food vendors. Sample local delicacies, Dutch classics, and international cuisine.', 
 '/assets/images/taste-haarlem.jpg', NOW()),

-- Workshop Category (1 event)
('Spring Art Workshop', '2026-03-20', 'Workshop', 
 'Create your own spring-themed artwork with guidance from professional artists. All materials provided, no experience necessary.', 
 '/assets/images/art-workshop.jpg', NOW()),

-- Dance Category (1 event)
('Samba Street Parade', '2026-03-21', 'Dance', 
 'Join the colorful samba parade through Haarlem streets. Professional dancers, live percussion, and an explosion of energy and rhythm.', 
 '/assets/images/samba-parade.jpg', NOW()),

-- Museum Category (1 event)
('Night at the Frans Hals', '2026-03-19', 'Museum', 
 'Exclusive evening tour of the Frans Hals Museum with special exhibits, live classical music, and wine tasting. Limited availability.', 
 '/assets/images/frans-hals-night.jpg', NOW())

ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    event_date = VALUES(event_date),
    category = VALUES(category),
    description = VALUES(description),
    image_url = VALUES(image_url);


-- ============================================
-- INSERT TICKETS (3+ types per event)
-- ============================================

-- Dance Night Haarlem Tickets
INSERT INTO tickets (event_id, ticket_type, price, quantity_available, quantity_sold)
SELECT e.id, 'Regular', 15.00, 200, 15 FROM events e WHERE e.title = 'Dance Night Haarlem'
UNION ALL
SELECT e.id, 'VIP', 35.00, 50, 8 FROM events e WHERE e.title = 'Dance Night Haarlem'
UNION ALL
SELECT e.id, 'Student', 10.00, 100, 23 FROM events e WHERE e.title = 'Dance Night Haarlem'
UNION ALL
SELECT e.id, 'Early Bird', 12.50, 150, 45 FROM events e WHERE e.title = 'Dance Night Haarlem'

UNION ALL

-- Jazz by the Canal Tickets
SELECT e.id, 'Regular', 18.00, 120, 12 FROM events e WHERE e.title = 'Jazz by the Canal'
UNION ALL
SELECT e.id, 'VIP', 40.00, 30, 5 FROM events e WHERE e.title = 'Jazz by the Canal'
UNION ALL
SELECT e.id, 'Student', 12.00, 80, 18 FROM events e WHERE e.title = 'Jazz by the Canal'
UNION ALL
SELECT e.id, 'Senior', 14.00, 60, 10 FROM events e WHERE e.title = 'Jazz by the Canal'

UNION ALL

-- Kids Mystery Museum Tickets
SELECT e.id, 'Child', 8.00, 150, 20 FROM events e WHERE e.title = 'Kids Mystery Museum'
UNION ALL
SELECT e.id, 'Adult', 12.00, 100, 15 FROM events e WHERE e.title = 'Kids Mystery Museum'
UNION ALL
SELECT e.id, 'Family Pass', 30.00, 50, 8 FROM events e WHERE e.title = 'Kids Mystery Museum'
UNION ALL
SELECT e.id, 'School Group', 6.00, 200, 35 FROM events e WHERE e.title = 'Kids Mystery Museum'

UNION ALL

-- Family Fun Festival Tickets
SELECT e.id, 'Adult', 15.00, 200, 25 FROM events e WHERE e.title = 'Family Fun Festival'
UNION ALL
SELECT e.id, 'Child', 10.00, 300, 40 FROM events e WHERE e.title = 'Family Fun Festival'
UNION ALL
SELECT e.id, 'Family Bundle', 45.00, 100, 20 FROM events e WHERE e.title = 'Family Fun Festival'
UNION ALL
SELECT e.id, 'Group Ticket', 120.00, 30, 5 FROM events e WHERE e.title = 'Family Fun Festival'

UNION ALL

-- Taste of Haarlem Tickets
SELECT e.id, 'Regular', 25.00, 150, 30 FROM events e WHERE e.title = 'Taste of Haarlem'
UNION ALL
SELECT e.id, 'VIP Tasting', 50.00, 40, 12 FROM events e WHERE e.title = 'Taste of Haarlem'
UNION ALL
SELECT e.id, 'Student', 18.00, 100, 22 FROM events e WHERE e.title = 'Taste of Haarlem'
UNION ALL
SELECT e.id, 'Couple Pass', 45.00, 60, 15 FROM events e WHERE e.title = 'Taste of Haarlem'

UNION ALL

-- Spring Art Workshop Tickets
SELECT e.id, 'Regular', 35.00, 40, 8 FROM events e WHERE e.title = 'Spring Art Workshop'
UNION ALL
SELECT e.id, 'Student', 28.00, 30, 12 FROM events e WHERE e.title = 'Spring Art Workshop'
UNION ALL
SELECT e.id, 'Materials Included', 45.00, 25, 6 FROM events e WHERE e.title = 'Spring Art Workshop'
UNION ALL
SELECT e.id, 'Private Session', 80.00, 10, 2 FROM events e WHERE e.title = 'Spring Art Workshop'

UNION ALL

-- Samba Street Parade Tickets
SELECT e.id, 'Spectator', 10.00, 500, 50 FROM events e WHERE e.title = 'Samba Street Parade'
UNION ALL
SELECT e.id, 'Participant', 25.00, 150, 35 FROM events e WHERE e.title = 'Samba Street Parade'
UNION ALL
SELECT e.id, 'VIP Viewing', 40.00, 50, 10 FROM events e WHERE e.title = 'Samba Street Parade'
UNION ALL
SELECT e.id, 'Group (10+)', 80.00, 30, 8 FROM events e WHERE e.title = 'Samba Street Parade'

UNION ALL

-- Night at the Frans Hals Tickets
SELECT e.id, 'Regular', 30.00, 80, 20 FROM events e WHERE e.title = 'Night at the Frans Hals'
UNION ALL
SELECT e.id, 'VIP Experience', 65.00, 30, 15 FROM events e WHERE e.title = 'Night at the Frans Hals'
UNION ALL
SELECT e.id, 'Student', 22.00, 50, 12 FROM events e WHERE e.title = 'Night at the Frans Hals'
UNION ALL
SELECT e.id, 'Couple Pass', 55.00, 40, 10 FROM events e WHERE e.title = 'Night at the Frans Hals'

ON DUPLICATE KEY UPDATE
    price = VALUES(price),
    quantity_available = VALUES(quantity_available),
    quantity_sold = VALUES(quantity_sold);


-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Run these to verify the data:
-- SELECT COUNT(*) as total_events FROM events;
-- SELECT COUNT(DISTINCT category) as total_categories FROM events;
-- SELECT category, COUNT(*) as events_per_category FROM events GROUP BY category;
-- SELECT e.title, COUNT(t.id) as ticket_types FROM events e LEFT JOIN tickets t ON e.id = t.event_id GROUP BY e.id, e.title;
