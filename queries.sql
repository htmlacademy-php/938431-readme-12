-- Заполняем таблицу post_type (Тип контента постов с размерами соответствующих иконок)
INSERT INTO post_type (t_title, t_class, width, height)
VALUES
  ('Картинка', 'photo', 22, 18), #1
  ('Видео', 'video', 24, 16), #2
  ('Текст', 'text', 20, 21), #3
  ('Цитата', 'quote', 21, 20), #4
  ('Ссылка', 'link', 21, 18); #5

-- Заполняем таблицу user (Пользователи)
INSERT INTO user (email, u_password, u_name, u_avatar)
VALUES
  ('vladik@gmail.com', 'oiuy45', 'Владик', 'userpic.jpg'),
  ('larisa@gmail.com', 'shj4-sk', 'Лариса', 'userpic-larisa-small.jpg'),
  ('victor@gmail.com', 'Xsfj23', 'Виктор', 'userpic-mark.jpg'),
  ('peter@gmail.com', 'xcv6ek', 'Петр', 'userpic-petro.jpg'),
  ('tanya@gmail.com', 'bnuy40', 'Таня', 'userpic-tanya.jpg'),
  ('elvira@gmail.com', 'Eqdkf5%', 'Эльвира', 'userpic-elvira.jpg');

-- Заполняем таблицу post (Посты)
INSERT INTO post (p_title, p_url, p_text, watch_count, user_id, type_id)
VALUES
  ('Цитата', NULL, 'Мы в жизни любим только раз, а после ищем лишь похожих', 10, 2, 4),
  ('Игра престолов', NULL, 'Не могу дождаться начала финального сезона своего любимого сериала! Не могу дождаться начала финального сезона своего любимого сериала! Не могу дождаться начала финального сезона своего любимого сериала! Не могу дождаться начала финального сезона своего любимого сериала!', 4, 1, 3),
  ('Наконец, обработал фотки!', 'rock-medium.jpg', NULL, 25, 3, 1),
  ('Моя мечта', 'coast-medium.jpg', NULL, 15, 2, 1),
  ('Делюсь ссылкой', 'www.htmlacademy.ru', 'Лучшие курсы', 3, 1, 5);

-- Заполняем таблицу comment (Комментарии)
INSERT INTO comment (c_content, user_id, post_id)
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
SELECT p_title, u_name, t_class, watch_count FROM post
INNER JOIN user
  ON user_id = user.id
INNER JOIN post_type
  ON type_id = post_type.id
ORDER BY watch_count DESC;

-- Получаем список постов пользователя с id=1
SELECT * FROM post
WHERE user_id = 1;

-- Получаем список комментариев для поста с id=2
SELECT c_content, u_name, comment.dt_add FROM comment
INNER JOIN user
  ON user_id = user.id
WHERE post_id = 2;

-- Добавляем like. Пользователь с id=4 ставит like посту с id=3
INSERT INTO post_like (user_id, post_id)
VALUES (4, 3);

-- Пользователь с id=5 подписывается на пользователя с id=2
INSERT INTO subscription (user_id, subscriber_id)
VALUES (2, 5);

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
