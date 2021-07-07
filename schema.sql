DROP DATABASE IF EXISTS readme;
CREATE DATABASE IF NOT EXISTS readme
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;

USE readme;

-- Таблица user. Данные о зарегистрированных пользователях
CREATE TABLE IF NOT EXISTS user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email VARCHAR(100) NOT NULL,
  u_password VARCHAR(100),
  u_name VARCHAR(50) NOT NULL,
  u_avatar VARCHAR(255),
  UNIQUE uk_email (email)
) COMMENT 'Зарегистрированные пользователи';

-- Таблица post_type. Данные о возможных типах постов
CREATE TABLE IF NOT EXISTS post_type (
  id SMALLINT AUTO_INCREMENT PRIMARY KEY,
  t_title VARCHAR(50),
  UNIQUE uk_title (t_title)
) COMMENT 'Возможные типы постов';

-- Таблица hashtag. Данные о хэштегах существующих на сайте.
CREATE TABLE IF NOT EXISTS hashtag (
  id SMALLINT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(50) NOT NULL,
  UNIQUE uk_title (title)
) COMMENT 'Хэштеги, сохраненные на сайте';

-- Таблица post. Данные о постах пользователей
CREATE TABLE IF NOT EXISTS post (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  p_title VARCHAR(255) NOT NULL,
  p_url VARCHAR(255),
  p_text TEXT,
  quote_athor VARCHAR(100),
  watch_count MEDIUMINT DEFAULT 0,
  user_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  type_id SMALLINT NOT NULL COMMENT 'Связь с полем id таблицы post_type',
  INDEX idx_user_id (user_id) COMMENT 'Индекс поля user_id',
  INDEX idx_type_id (type_id) COMMENT 'Индекс поля type_id',
  FOREIGN KEY fk_user_id (user_id) REFERENCES user (id),
  FOREIGN KEY fk_type_id (type_id) REFERENCES post_type (id)
) COMMENT 'Посты пользователей';

-- Таблица comment. Данные о всех комментариях к постам, оставленных пользователями
CREATE TABLE IF NOT EXISTS comment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  c_content TEXT,
  user_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  post_id INT NOT NULL COMMENT 'Связь с полем id таблицы post',
  INDEX idx_user_id (user_id) COMMENT 'Индекс поля user_id',
  INDEX idx_post_id (post_id) COMMENT 'Индекс поля post_id',
  FOREIGN KEY fk_user_id (user_id) REFERENCES user (id),
  FOREIGN KEY fk_post_id (post_id) REFERENCES post (id)
) COMMENT 'Комментарии к постам, оставленные пользователями';

-- Таблица message. Сообщения пользователей в чате
CREATE TABLE IF NOT EXISTS message (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  m_content TEXT,
  sender_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  receiver_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  INDEX idx_send_id (sender_id) COMMENT 'Индекс поля sender_id',
  INDEX inx_rec_id (receiver_id) COMMENT 'Индекс поля receiver_id',
  FOREIGN KEY fk_send_id (sender_id) REFERENCES user (id),
  FOREIGN KEY fk_rec_id (receiver_id) REFERENCES user (id)
) COMMENT 'Сообщения пользователей в чате';

-- Таблица связей subscription. Данные о подписках пользователей на других пользователей
CREATE TABLE IF NOT EXISTS subscription (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  subscriber_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  INDEX idx_user_id (user_id) COMMENT 'Индекс поля user_id',
  INDEX idx_sub_id (subscriber_id) COMMENT 'Индекс поля subscriber_id',
  FOREIGN KEY fk_send_id (user_id) REFERENCES user (id),
  FOREIGN KEY fk_rec_id (subscriber_id) REFERENCES user (id)
) COMMENT 'Связи. Подписки пользователей на других пользователей';

-- Таблица связей post_like. Данные о лайках поставленных постам
CREATE TABLE IF NOT EXISTS post_like (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  post_id INT NOT NULL COMMENT 'Связь с полем id таблицы post',
  INDEX idx_post_id (post_id) COMMENT 'Индекс поля post_id',
  INDEX idx_user_id (user_id) COMMENT 'Индекс поля user_id',
  FOREIGN KEY fk_user_id (user_id) REFERENCES user (id),
  FOREIGN KEY fk_post_id (post_id) REFERENCES post (id)
) COMMENT 'Связи. Лайки, поставленные постам';

-- Таблица связей post_hashtag. Данные о хэштегах добавленных авторами к постам
CREATE TABLE IF NOT EXISTS post_hashtag (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL COMMENT 'Связь с полем id таблицы post',
  hash_id SMALLINT NOT NULL COMMENT 'Связь с полем id таблицы hashtag',
  INDEX idx_hash_id (hash_id) COMMENT 'Индекс поля hash_id',
  INDEX idx_post_id (post_id) COMMENT 'Индекс поля post_id',
  FOREIGN KEY fk_post_id (post_id) REFERENCES post (id),
  FOREIGN KEY fk_hash_id (hash_id) REFERENCES hashtag (id)
) COMMENT 'Связи. Хэштеги, добавленные авторами постам';
