DROP DATABASE IF EXISTS readme;
CREATE DATABASE IF NOT EXISTS readme
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;

USE readme;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email VARCHAR(50) NOT NULL,
  u_password VARCHAR(50),
  u_name VARCHAR(50) NOT NULL,
  u_avatar VARCHAR(255),
  UNIQUE uk_email (email)
);

CREATE TABLE IF NOT EXISTS post_types (
  id SMALLINT AUTO_INCREMENT PRIMARY KEY,
  title ENUM('link', 'photo', 'quote', 'text', 'video'),
  class_name ENUM('post-link', 'post-photo', 'post-quote', 'post-text', 'post-video'),
  UNIQUE uk_title (title)
);

CREATE TABLE IF NOT EXISTS hashtags (
  id SMALLINT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(50) NOT NULL,
  UNIQUE uk_title (title)
);

CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  title VARCHAR(255) NOT NULL,
  text_content TEXT,
  quote_athor VARCHAR(255),
  image_url VARCHAR(255),
  video_url VARCHAR(255),
  link_url VARCHAR(255),
  watch_count MEDIUMINT DEFAULT 0,
  user_id INT NOT NULL,
  type_id SMALLINT NOT NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_type_id (type_id),
  FOREIGN KEY fk_user_id (user_id) REFERENCES users (id),
  FOREIGN KEY fk_type_id (type_id) REFERENCES post_types (id)
);


CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  content TEXT,
  user_id INT NOT NULL,
  post_id INT NOT NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_post_id (post_id),
  FOREIGN KEY fk_user_id (user_id) REFERENCES users (id),
  FOREIGN KEY fk_post_id (post_id) REFERENCES posts (id)
);

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  content TEXT,
  sender_id INT NOT NULL,
  recipient_id INT NOT NULL,
  INDEX idx_send_id (sender_id),
  INDEX inx_rec_id (recipient_id),
  FOREIGN KEY fk_send_id (sender_id) REFERENCES users (id),
  FOREIGN KEY fk_rec_id (recipient_id) REFERENCES users (id)
);

CREATE TABLE IF NOT EXISTS subscriptions (
  user_id INT NOT NULL,
  subscriber_id INT NOT NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_sub_id (subscriber_id),
  FOREIGN KEY fk_send_id (user_id) REFERENCES users (id),
  FOREIGN KEY fk_rec_id (subscriber_id) REFERENCES users (id)
);

CREATE TABLE IF NOT EXISTS likes (
  user_id INT NOT NULL,
  post_id INT NOT NULL,
  INDEX idx_post_id (post_id),
  FOREIGN KEY fk_user_id (user_id) REFERENCES users (id),
  FOREIGN KEY fk_post_id (post_id) REFERENCES posts (id)
);

CREATE TABLE IF NOT EXISTS posts_hashes (
  post_id INT NOT NULL,
  hash_id SMALLINT NOT NULL,
  INDEX idx_hash_id (hash_id),
  INDEX idx_post_id (post_id),
  FOREIGN KEY fk_post_id (post_id) REFERENCES posts (id),
  FOREIGN KEY fk_hash_id (hash_id) REFERENCES hashtags (id)
);
