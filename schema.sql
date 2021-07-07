DROP DATABASE IF EXISTS readme;
CREATE DATABASE IF NOT EXISTS readme
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;

USE readme;

-- Таблица user. Данные о зарегистрированных пользователях
CREATE TABLE IF NOT EXISTS user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  u_dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  u_email VARCHAR(100) NOT NULL,
  u_password VARCHAR(100),
  u_name VARCHAR(50) NOT NULL,
  u_avatar VARCHAR(255),
  UNIQUE uk_u_email (u_email)
) COMMENT 'Зарегистрированные пользователи';

-- Таблица post_type. Данные о возможных типах постов
CREATE TABLE IF NOT EXISTS post_type (
  id SMALLINT AUTO_INCREMENT PRIMARY KEY,
  t_title VARCHAR(100),
  t_class VARCHAR(50),
  UNIQUE uk_t_title (t_title)
) COMMENT 'Возможные типы постов';

-- Таблица hashtag. Данные о хэштегах существующих на сайте.
CREATE TABLE IF NOT EXISTS hashtag (
  id SMALLINT AUTO_INCREMENT PRIMARY KEY,
  h_title VARCHAR(50) NOT NULL,
  UNIQUE uk_h_title (h_title)
) COMMENT 'Хэштеги, сохраненные на сайте';

-- Таблица post. Данные о постах пользователей
-- Индекс idx_user_id для поля user_id - связи с полем id таблицы user
-- Индекс idx_post_id для поля post_id - связи с полем id таблицы post

CREATE TABLE IF NOT EXISTS post (
  id INT AUTO_INCREMENT PRIMARY KEY,
  p_dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  p_title VARCHAR(255) NOT NULL,
  p_url VARCHAR(255),
  p_text TEXT,
  p_quote_athor VARCHAR(100),
  p_watch_count MEDIUMINT DEFAULT 0,
  p_user_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  p_type_id SMALLINT NOT NULL COMMENT 'Связь с полем id таблицы post_type',
  INDEX idx_user_id (p_user_id) COMMENT 'Индекс поля p_user_id',
  INDEX idx_type_id (p_type_id) COMMENT 'Индекс поля p_type_id',
  FOREIGN KEY fk_user_id (p_user_id) REFERENCES user (id),
  FOREIGN KEY fk_type_id (p_type_id) REFERENCES post_type (id)
) COMMENT 'Посты пользователей';

-- Таблица comment. Данные о всех комментариях к постам, оставленных пользователями
CREATE TABLE IF NOT EXISTS comment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  c_dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  c_content TEXT,
  c_user_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  c_post_id INT NOT NULL COMMENT 'Связь с полем id таблицы post',
  INDEX idx_user_id (c_user_id) COMMENT 'Индекс поля c_user_id',
  INDEX idx_post_id (c_post_id) COMMENT 'Индекс поля c_post_id',
  FOREIGN KEY fk_user_id (c_user_id) REFERENCES user (id),
  FOREIGN KEY fk_post_id (c_post_id) REFERENCES post (id)
) COMMENT 'Комментарии к постам, оставленные пользователями';

-- Таблица message. Сообщения пользователей в чате
CREATE TABLE IF NOT EXISTS message (
  id INT AUTO_INCREMENT PRIMARY KEY,
  m_dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  m_content TEXT,
  m_sender_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  m_receiver_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  INDEX idx_send_id (m_sender_id) COMMENT 'Индекс поля m_sender_id',
  INDEX inx_rec_id (m_receiver_id) COMMENT 'Индекс поля m_receiver_id',
  FOREIGN KEY fk_send_id (m_sender_id) REFERENCES user (id),
  FOREIGN KEY fk_rec_id (m_receiver_id) REFERENCES user (id)
) COMMENT 'Сообщения пользователей в чате';

-- Таблица связей subscription. Данные о подписках пользователей на других пользователей
CREATE TABLE IF NOT EXISTS subscription (
  id INT AUTO_INCREMENT PRIMARY KEY,
  s_user_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  s_subscriber_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  INDEX idx_user_id (s_user_id) COMMENT 'Индекс поля s_user_id',
  INDEX idx_sub_id (s_subscriber_id) COMMENT 'Индекс поля s_subscriber_id',
  FOREIGN KEY fk_send_id (s_user_id) REFERENCES user (id),
  FOREIGN KEY fk_rec_id (s_subscriber_id) REFERENCES user (id)
) COMMENT 'Связи. Подписки пользователей на других пользователей';

-- Таблица связей post_like. Данные о лайках поставленных постам
-- Индекс idx_post_id для поля post_id - связи с полем id таблицы post

CREATE TABLE IF NOT EXISTS post_like (
  id INT AUTO_INCREMENT PRIMARY KEY,
  l_user_id INT NOT NULL COMMENT 'Связь с полем id таблицы user',
  l_post_id INT NOT NULL COMMENT 'Связь с полем id таблицы post',
  INDEX idx_post_id (l_post_id) COMMENT 'Индекс поля l_post_id',
  FOREIGN KEY fk_user_id (l_user_id) REFERENCES user (id),
  FOREIGN KEY fk_post_id (l_post_id) REFERENCES post (id)
) COMMENT 'Связи. Лайки, поставленные постам';

-- Таблица связей post_hashtag. Данные о хэштегах добавленных авторами к постам
-- Индекс idx_hash_id для поля hash_id - связи с полем id таблицы hashtag
-- Индекс idx_post_id для поля post_id - связи с полем id таблицы post

CREATE TABLE IF NOT EXISTS post_hashtag (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ph_post_id INT NOT NULL COMMENT 'Связь с полем id таблицы post',
  ph_hash_id SMALLINT NOT NULL COMMENT 'Связь с полем id таблицы hashtag',
  INDEX idx_hash_id (ph_hash_id) COMMENT 'Индекс поля ph_hash_id',
  INDEX idx_post_id (ph_post_id) COMMENT 'Индекс поля ph_post_id',
  FOREIGN KEY fk_post_id (ph_post_id) REFERENCES post (id),
  FOREIGN KEY fk_hash_id (ph_hash_id) REFERENCES hashtag (id)
) COMMENT 'Связи. Хэштеги, добавленные авторами постам';
