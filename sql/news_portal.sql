CREATE TABLE Categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE Admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_name VARCHAR(150) NOT NULL,
    email VARCHAR(200) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE Journalists (
    journalist_id INT AUTO_INCREMENT PRIMARY KEY,
    journalist_name VARCHAR(150) NOT NULL,
    email VARCHAR(200) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    bio TEXT
);

CREATE TABLE Readers (
    reader_id INT AUTO_INCREMENT PRIMARY KEY,
    reader_name VARCHAR(150) NOT NULL,
    email VARCHAR(200) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE Article (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    publish_date DATETIME NOT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    journalist_id INT NOT NULL,
    category_id INT NOT NULL,
    FOREIGN KEY (journalist_id) REFERENCES Journalists(journalist_id),
    FOREIGN KEY (category_id) REFERENCES Categories(category_id)
);

CREATE TABLE Comment (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    reader_id INT NOT NULL,
    article_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    comment_date DATETIME NOT NULL,
    FOREIGN KEY (reader_id) REFERENCES Readers(reader_id),
    FOREIGN KEY (article_id) REFERENCES Article(article_id)
);

CREATE TABLE Share (
    share_id INT AUTO_INCREMENT PRIMARY KEY,
    reader_id INT NOT NULL,
    article_id INT NOT NULL,
    share_date DATETIME NOT NULL,
    FOREIGN KEY (reader_id) REFERENCES Readers(reader_id),
    FOREIGN KEY (article_id) REFERENCES Article(article_id)
);

CREATE TABLE Bookmarks (
    bookmark_id INT AUTO_INCREMENT PRIMARY KEY,
    reader_id INT NOT NULL,
    article_id INT NOT NULL,
    bookmark_date DATETIME NOT NULL,
    FOREIGN KEY (reader_id) REFERENCES Readers(reader_id),
    FOREIGN KEY (article_id) REFERENCES Article(article_id)
);

CREATE TABLE Feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    reader_id INT NOT NULL,
    journalist_id INT NOT NULL,
    feedback_text TEXT NOT NULL,
    feedback_date DATETIME NOT NULL,
    FOREIGN KEY (reader_id) REFERENCES Readers(reader_id),
    FOREIGN KEY (journalist_id) REFERENCES Journalists(journalist_id)
);

CREATE TABLE Login_Activity (
    login_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NULL,
    journalist_id INT NULL,
    reader_id INT NULL,
    login_time DATETIME NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id),
    FOREIGN KEY (journalist_id) REFERENCES Journalists(journalist_id),
    FOREIGN KEY (reader_id) REFERENCES Readers(reader_id)
);