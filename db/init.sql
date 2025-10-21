DROP USER IF EXISTS 'testuser'@'localhost';
DROP DATABASE IF EXISTS finance_tracker_db;

CREATE DATABASE finance_tracker_db;
CREATE USER 'testuser'@'localhost' IDENTIFIED BY 'mypassword';
GRANT ALL PRIVILEGES ON finance_tracker_db.* TO 'testuser'@'localhost';
FLUSH PRIVILEGES;

USE finance_tracker_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100)
);

INSERT INTO users (name) VALUES ('Alice'), ('Bob'), ('Charlie');
