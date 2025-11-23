CREATE DATABASE news_portal;
USE news_portal;

-- categories
CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL
);

-- admin
CREATE TABLE admin (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(80)
);

-- journalists
CREATE TABLE journalists (
  journalist_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(30),
  last_name VARCHAR(30),
  email VARCHAR(80),
  password VARCHAR(255) NOT NULL,
  address VARCHAR(255)
);

-- readers
CREATE TABLE readers (
  reader_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(30),
  last_name VARCHAR(30),
  phone VARCHAR(20),
  email VARCHAR(80) UNIQUE,
  registration_date DATE,
  password VARCHAR(255) NOT NULL
);

-- article
CREATE TABLE article (
  article_id INT AUTO_INCREMENT PRIMARY KEY,
  featured_image LONGBLOB,
  view_count BIGINT DEFAULT 0,
  title VARCHAR(100),
  status VARCHAR(20),
  admin_id INT,
  journalist_id INT,
  category_id INT,
  publish_date DATE,
  FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE SET NULL,
  FOREIGN KEY (journalist_id) REFERENCES journalists(journalist_id) ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- bookmarks
CREATE TABLE bookmarks (
  bookmark_id INT AUTO_INCREMENT PRIMARY KEY,
  bookmark_date DATE,
  reader_id INT NOT NULL,
  article_id INT NOT NULL,
  FOREIGN KEY (reader_id) REFERENCES readers(reader_id) ON DELETE CASCADE,
  FOREIGN KEY (article_id) REFERENCES article(article_id) ON DELETE CASCADE
);

-- feedback
CREATE TABLE feedback (
  feedback_id INT AUTO_INCREMENT PRIMARY KEY,
  rating INT CHECK (rating BETWEEN 1 AND 5),
  submission_date DATE,
  message VARCHAR(255),
  reader_id INT NOT NULL,
  FOREIGN KEY (reader_id) REFERENCES readers(reader_id) ON DELETE CASCADE
);

-- share
CREATE TABLE share (
  share_id INT AUTO_INCREMENT PRIMARY KEY,
  platform VARCHAR(30),
  article_id INT NOT NULL,
  reader_id INT NOT NULL,
  share_date DATE,
  FOREIGN KEY (article_id) REFERENCES article(article_id) ON DELETE CASCADE,
  FOREIGN KEY (reader_id) REFERENCES readers(reader_id) ON DELETE CASCADE
);

-- comments
CREATE TABLE comment (
  comment_id INT AUTO_INCREMENT PRIMARY KEY,
  is_approved BOOLEAN DEFAULT 0,
  comment_text TEXT,
  comment_date DATE,
  reader_id INT NOT NULL,
  article_id INT NOT NULL,
  FOREIGN KEY (reader_id) REFERENCES readers(reader_id) ON DELETE CASCADE,
  FOREIGN KEY (article_id) REFERENCES article(article_id) ON DELETE CASCADE
);

-- login activity
CREATE TABLE login_activity (
  activity_id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT,
  journalist_id INT,
  reader_id INT,
  article_id INT,
  status VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE SET NULL,
  FOREIGN KEY (journalist_id) REFERENCES journalists(journalist_id) ON DELETE SET NULL,
  FOREIGN KEY (reader_id) REFERENCES readers(reader_id) ON DELETE SET NULL,
  FOREIGN KEY (article_id) REFERENCES article(article_id) ON DELETE SET NULL
);

-- reports
CREATE TABLE reports (
  report_id INT AUTO_INCREMENT PRIMARY KEY,
  reason TEXT,
  report_date DATE,
  status VARCHAR(20),
  article_id INT,
  reader_id INT,
  FOREIGN KEY (article_id) REFERENCES article(article_id) ON DELETE CASCADE,
  FOREIGN KEY (reader_id) REFERENCES readers(reader_id) ON DELETE SET NULL
);

-- sample inserts
INSERT INTO categories (name)
VALUES ('Politics'), ('Technology'), ('Sports');

INSERT INTO admin (username, password, email)
VALUES ('admin01', '{bcrypt}', 'admin01@newsportal.com');

INSERT INTO journalists (first_name, last_name, email, password, address)
VALUES ('Rafiq','Hasan','rafiq@portal.com','{bcrypt}','Lake Rd, Dhanmondi');

INSERT INTO readers (first_name,last_name,phone,email,registration_date,password)
VALUES ('Abdullah','Karim','0171234567','abdullah@gmail.com',CURDATE(),'{bcrypt}');

INSERT INTO article (view_count,title,status,admin_id,journalist_id,category_id,publish_date)
VALUES (0,'Govt unveils new policy','Published',1,1,1,CURDATE());
