DROP DATABASE IF EXISTS readme;
CREATE DATABASE IF NOT EXISTS readme
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;

USE readme;

-- Таблица user. Данные о зарегистрированных пользователях
CREATE TABLE IF NOT EXISTS user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email VARCHAR(100) NOT NULL COMMENT 'Уникальный индекс',
  u_password VARCHAR(100),
  u_name VARCHAR(50) NOT NULL,
  u_avatar VARCHAR(255),
  UNIQUE uk_email (email)
) COMMENT 'Зарегистрированные пользователи';

-- Таблица post_type. Данные о возможных типах постов
CREATE TABLE IF NOT EXISTS post_type (
  id SMALLINT AUTO_INCREMENT PRIMARY KEY,
  title ENUM('link', 'photo', 'quote', 'text', 'video') COMMENT 'Уникальный индекс',
  class_name ENUM('post-link', 'post-photo', 'post-quote', 'post-text', 'post-video'),
  UNIQUE uk_title (title)
) COMMENT 'Возможные типы постов';

-- Таблица hashtag. Данные о хэштегах существующих на сайте.
CREATE TABLE IF NOT EXISTS hashtag (
  id SMALLINT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(50) NOT NULL COMMENT 'Уникальный индекс',
  UNIQUE uk_title (title)
) COMMENT 'Хэштеги, сохраненные на сайте';

-- Таблица post. Данные о постах пользователей
-- Индекс idx_user_id для поля user_id - связи с полем id таблицы user
-- Индекс idx_post_id для поля post_id - связи с полем id таблицы post

CREATE TABLE IF NOT EXISTS post (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  title VARCHAR(255) NOT NULL,
  content_url VARCHAR(255),
  content_text TEXT,
  quote_athor VARCHAR(100),
  watch_count MEDIUMINT DEFAULT 0,
  user_id INT NOT NULL COMMENT 'Индекс',
  type_id SMALLINT NOT NULL COMMENT 'Индекс',
  INDEX idx_user_id (user_id),
  INDEX idx_type_id (type_id),
  FOREIGN KEY fk_user_id (user_id) REFERENCES user (id),
  FOREIGN KEY fk_type_id (type_id) REFERENCES post_type (id)
) COMMENT 'Посты пользователей';

-- Таблица comment. Данные о всех комментариях к постам, оставленных пользователями
CREATE TABLE IF NOT EXISTS comment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  content TEXT,
  user_id INT NOT NULL COMMENT 'Индекс',
  post_id INT NOT NULL COMMENT 'Индекс',
  INDEX idx_user_id (user_id),
  INDEX idx_post_id (post_id),
  FOREIGN KEY fk_user_id (user_id) REFERENCES user (id),
  FOREIGN KEY fk_post_id (post_id) REFERENCES post (id)
) COMMENT 'Комментарии к постам, оставленные пользователями';

-- Таблица message. Сообщения пользователей в чате
CREATE TABLE IF NOT EXISTS message (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  content TEXT,
  sender_id INT NOT NULL COMMENT 'Индекс',
  receiver_id INT NOT NULL COMMENT 'Индекс',
  INDEX idx_send_id (sender_id),
  INDEX inx_rec_id (receiver_id),
  FOREIGN KEY fk_send_id (sender_id) REFERENCES user (id),
  FOREIGN KEY fk_rec_id (receiver_id) REFERENCES user (id)
) COMMENT 'Сообщения пользователей в чате';

-- Таблица связей subscription. Данные о подписках пользователей на других пользователей
CREATE TABLE IF NOT EXISTS subscription (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL COMMENT 'Индекс',
  subscriber_id INT NOT NULL COMMENT 'Индекс',
  INDEX idx_user_id (user_id),
  INDEX idx_sub_id (subscriber_id),
  FOREIGN KEY fk_send_id (user_id) REFERENCES user (id),
  FOREIGN KEY fk_rec_id (subscriber_id) REFERENCES user (id)
) COMMENT 'Связи. Подписки пользователей на других пользователей';

-- Таблица связей post_like. Данные о лайках поставленных постам
-- Индекс idx_post_id для поля post_id - связи с полем id таблицы post

CREATE TABLE IF NOT EXISTS post_like (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  post_id INT NOT NULL COMMENT 'Индекс',
  INDEX idx_post_id (post_id),
  FOREIGN KEY fk_user_id (user_id) REFERENCES user (id),
  FOREIGN KEY fk_post_id (post_id) REFERENCES post (id)
) COMMENT 'Связи. Лайки, поставленные постам';

-- Таблица связей post_hashtag. Данные о хэштегах добавленных авторами к постам
-- Индекс idx_hash_id для поля hash_id - связи с полем id таблицы hashtag
-- Индекс idx_post_id для поля post_id - связи с полем id таблицы post

CREATE TABLE IF NOT EXISTS post_hashtag (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL COMMENT 'Индекс',
  hash_id SMALLINT NOT NULL COMMENT 'Индекс',
  INDEX idx_hash_id (hash_id),
  INDEX idx_post_id (post_id),
  FOREIGN KEY fk_post_id (post_id) REFERENCES post (id),
  FOREIGN KEY fk_hash_id (hash_id) REFERENCES hashtag (id)
) COMMENT 'Связи. Хэштеги, добавленные авторами постам';
