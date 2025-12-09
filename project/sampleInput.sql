-- sample input
-- Categories
INSERT INTO Categories (category_name) VALUES
('Politics'),
('Sports'),
('Technology'),
('Entertainment'),
('Health');

-- Admin
INSERT INTO Admin (admin_name, email, password_hash) VALUES
('Main Admin', 'admin@example.com', SHA2('admin123',256));

-- Journalists
INSERT INTO Journalists (journalist_name, email, bio, password_hash) VALUES
('Rafiq Hasan', 'rafiq@news.com', 'Senior political analyst', SHA2('pass123',256)),
('Mehedi Karim', 'mehedi@news.com', 'Tech reporter', SHA2('pass123',256));

-- Readers
INSERT INTO Readers (reader_name, email, password_hash) VALUES
('John Reader', 'john@example.com', SHA2('reader123',256)),
('Alice Reader', 'alice@example.com', SHA2('reader123',256));

-- Articles
INSERT INTO Article (title, content, publish_date, journalist_id, category_id) VALUES
('Election Update', 'Latest election news...', NOW(), 1, 1),
('New Smartphone Released', 'Tech details...', NOW(), 2, 3);

-- Comments
INSERT INTO Comment (reader_id, article_id, comment_text, comment_date) VALUES
(1, 1, 'Informative.', NOW()),
(2, 2, 'Nice review.', NOW());

-- Share
INSERT INTO Share (reader_id, article_id, share_date) VALUES
(1, 1, NOW());

-- Bookmarks
INSERT INTO Bookmarks (reader_id, article_id, bookmark_date) VALUES
(2, 2, NOW());

-- Feedback
INSERT INTO Feedback (reader_id, journalist_id, feedback_text, feedback_date) VALUES
(1, 1, 'Great reporting.', NOW());

-- Login Activity
INSERT INTO Login_Activity (admin_id, login_time) VALUES
(1, NOW());
