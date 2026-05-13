USE defaultdb;

INSERT INTO users (name, email, phone, password_hash, role, status) VALUES
('System Admin', 'schoolyoro@gmail.com', NULL, '$2y$10$IDApOwVt4JLO1BIxLJB8R.txR5MDiyq2zHgioZzb9s2tXi17C1m.y', 'admin', 'active');

INSERT INTO categories (name) VALUES
('Electronics'), ('Documents'), ('Bags'), ('Keys'), ('Clothing'), ('Accessories'), ('Others');

INSERT INTO locations (name, building) VALUES
('Library Lobby', 'Main Library'),
('Cafeteria', 'Student Center'),
('Computer Lab 2', 'IT Building'),
('Registrar Window', 'Admin Building'),
('Gym Entrance', 'Sports Complex');

INSERT INTO items (
  tracking_code, reporter_id, category_id, location_id, type, title, description,
  date_seen, image_path, contact_email, contact_phone, status
) VALUES
('FND-2026-0001', 1, 2, 4, 'found', 'Student ID - Maria Santos', 'Blue school ID lace with a visible student number.', '2026-05-06', NULL, 'frontdesk@lostfound.test', '09170000001', 'matched'),
('LST-2026-0001', 1, 1, 3, 'lost', 'Wireless Earbuds Case', 'White earbuds charging case, no earbuds inside.', '2026-05-07', NULL, 'schoolyoro@gmail.com', '09170000002', 'open'),
('FND-2026-0002', 1, 4, 1, 'found', 'Silver Key Set', 'Three keys on a blue key ring.', '2026-05-08', NULL, 'frontdesk@lostfound.test', '09170000001', 'open');

INSERT INTO claims (item_id, user_id, claimant_name, claimant_email, claimant_phone, proof_text, status)
VALUES (1, NULL, 'Maria Santos', 'maria@student.test', '09170000003', 'Can provide matching registration card and student number.', 'pending');

INSERT INTO activity_logs (user_id, action, description) VALUES
(1, 'seed', 'Initial database records were loaded.'),
(1, 'claim_created', 'Submitted a sample claim request.');
