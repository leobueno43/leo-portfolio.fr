-- Table pour les billets d'événements
CREATE TABLE IF NOT EXISTS event_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    ticket_code VARCHAR(100) UNIQUE NOT NULL,
    qr_code_path VARCHAR(255),
    pdf_path VARCHAR(255),
    is_scanned TINYINT(1) DEFAULT 0,
    scanned_at DATETIME NULL,
    scanned_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (scanned_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_event_id (event_id),
    INDEX idx_user_id (user_id),
    INDEX idx_ticket_code (ticket_code),
    INDEX idx_is_scanned (is_scanned)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vue pour les statistiques des billets par événement
CREATE OR REPLACE VIEW ticket_statistics AS
SELECT 
    e.id as event_id,
    e.title as event_title,
    COUNT(et.id) as total_tickets,
    SUM(CASE WHEN et.is_scanned = 1 THEN 1 ELSE 0 END) as scanned_tickets,
    SUM(CASE WHEN et.is_scanned = 0 THEN 1 ELSE 0 END) as pending_tickets,
    ROUND(SUM(CASE WHEN et.is_scanned = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(et.id), 2) as attendance_rate
FROM events e
LEFT JOIN event_tickets et ON e.id = et.event_id
GROUP BY e.id, e.title;
