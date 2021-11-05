-- Заполняем таблицу post_type (Тип контента постов с размерами соответствующих иконок)
INSERT INTO post_type (type_title, type_class, width, height)
VALUES
  ('Фото', 'photo', 22, 18), #1
  ('Видео', 'video', 24, 16), #2
  ('Текст', 'text', 20, 21), #3
  ('Цитата', 'quote', 21, 20), #4
  ('Ссылка', 'link', 21, 18); #5

-- Заполняем таблицу user (Пользователи)
INSERT INTO user (email, password, username, avatar)
VALUES
  ('vladik@gmail.com', 'oiuy45', 'Владик', 'img/userpic-medium.jpg'), #1
  ('larisa@gmail.com', 'shj4-sk', 'Лариса', 'img/userpic-larisa.jpg'), #2
  ('victor@gmail.com', 'Xsfj23', 'Виктор', 'img/userpic-mark.jpg'), #3
  ('peter@gmail.com', 'xcv6ek', 'Петр', 'img/userpic-petro.jpg'), #4
  ('tanya@gmail.com', 'bnuy40', 'Таня', 'img/userpic-tanya.jpg'), #5
  ('elvira@gmail.com', 'Eqdkf5%', 'Эльвира', 'img/userpic-elvira.jpg'); #6

-- Заполняем таблицу post (Посты)
INSERT INTO post (
  post_title,
  post_url,
  post_text,
  quote_author,
  watch_count,
  user_id,
  type_id)
VALUES
  ('Цитата', NULL, 'Мы в жизни любим только раз, а после ищем лишь похожих', 'Неизвестный автор', 10, 2, 4), #1
  ('Игра престолов', NULL, 'Не могу дождаться начала финального сезона своего любимого сериала! Не могу дождаться начала финального сезона своего любимого сериала! Не могу дождаться начала финального сезона своего любимого сериала! Не могу дождаться начала финального сезона своего любимого сериала!', NULL, 4, 1, 3), #2
  ('Наконец, обработал фотки!', 'img/rock-medium.jpg', NULL, NULL, 25, 3, 1), #3
  ('Моя мечта', 'img/coast-medium.jpg', NULL, NULL, 15, 2, 1), #4
  ('Делюсь ссылкой', 'https://www.htmlacademy.ru', 'Лучшие курсы', NULL, 3, 1, 5), #5
  ('Мачу Пикчу. Древние строения', 'https://youtu.be/RsVq66yJ8HI', NULL, NULL, 5, 1, 2); #6
-- Заполняем таблицу comment (Комментарии)
INSERT INTO comment (comment_text, user_id, post_id)
VALUES
('Автор цитаты был оптимист и романтик. Любить один раз способны очень немногие, большинство не любит вообще ни разу...', 6, 1),
('Спорное утверждение', 4, 1),
('Ну вот, дождались, и что? Сплошное разочарование...', 5, 2),
('Я тоже жду с нетерпением', 3, 2),
('Превосходный кадр! Интересно, какой техникой пользовались', 1, 3),
('Давно мечтаю увидеть Байкал собственными глазами', 2, 3),
('А я уже там побывала три года назад. И опять хочу.', 6, 4),
('Слишком жарко, и северная природа гораздо красивее', 4, 4),
('Согласен, хорошие курсы. Сам учился там', 4, 5);

-- Получаем список постов с сортировкой по популярности с именами авторов и типом контента
SELECT post_title, username, type_class, watch_count FROM post
INNER JOIN user
  ON user_id = user.id
INNER JOIN post_type
  ON type_id = post_type.id
ORDER BY watch_count DESC;

-- Получаем список постов пользователя с id=1
SELECT * FROM post
WHERE user_id = 1;

-- Получаем список комментариев для поста с id=2
SELECT comment_text, username, comment.date_add FROM comment
INNER JOIN user
  ON user_id = user.id
WHERE post_id = 2;

-- Добавляем like. Пользователь с id=4 ставит like посту с id=3
INSERT INTO post_like (user_id, post_id)
VALUES (4, 3), (1, 3), (5, 3), (2, 5), (4, 1), (3, 1);

-- Пользователь с id=5 подписывается на пользователя с id=2 и т.д.
INSERT INTO subscription (user_id, subscriber_id)
VALUES (2, 5), (2, 1), (3, 5), (4, 3);

-- Получить количество лайков у поста
SELECT  COUNT(*) AS likes_count
FROM post_like
WHERE post_id = 3
GROUP BY post_id;

-- Получить количество подписчиков пользователя с id=2
SELECT COUNT(*) AS subscribers_count
FROM subscription
WHERE user_id = 2
GROUP BY user_id;
