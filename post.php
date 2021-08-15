<?php
require_once('helpers.php');

$is_auth = rand(0, 1);
$user_name = 'Юлия'; // укажите здесь ваше имя
$title = 'readme: публикация';

$post_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$post_id) {
    http_response_code(404);
    exit;
}

// Устанавливаем соединение с базой readme
$con = mysqli_connect('localhost', 'mysql', 'mysql', 'readme');

if (!$con) {
    print('Ошибка подключения: ' . mysqli_connect_error());
    exit;
};

// Устанавливаем кодировку
mysqli_set_charset($con, 'utf8');

// Создаем запрос на получение поста с заданным id
$sql_post = "SELECT
post.*,
t_class as p_type,
comment_count,
like_count
FROM post
INNER JOIN post_type
ON type_id = post_type.id
LEFT JOIN
    (
        SELECT
        comment.post_id,
        COUNT(comment.id) AS comment_count,
        like_count
        FROM comment
        LEFT JOIN
            (
                SELECT post_id, COUNT(id) AS like_count
                FROM post_like
                GROUP BY post_id
            ) AS post_likes
        ON post_likes.post_id = comment.post_id
        GROUP BY comment.post_id
    ) AS post_count
ON post_count.post_id = post.id
WHERE post.id = ?;";

// Запрос на получение комментариев к посту
$sql_comments = "SELECT
comment.dt_add AS c_date,
c_content,
u_name,
u_avatar
FROM comment
INNER JOIN user
ON user.id = comment.user_id
WHERE post_id = ?
ORDER BY c_date DESC
LIMIT 5;";

// Создаем запрос на получение данных о пользователе
$sql_user = "SELECT
user.id,
dt_add,
u_name,
u_avatar,
COUNT(s.id) as subs_count,
posts_count
FROM user
LEFT JOIN subscription AS s
ON s.user_id = user.id
LEFT JOIN
    (
        SELECT user_id, COUNT(*) as posts_count FROM post
        GROUP BY user_id
    ) AS posts
ON posts.user_id = user.id
WHERE user.id = ?
GROUP BY user.id;";

// Создаем подготовленное выражение и отправляем запрос на получение поста
$data_post = [];
$data_post[] = $post_id;
$result = fetch_sql_response($con, $sql_post, $data_post);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    http_response_code(404);
    exit;
}

// Создаем подготовленное выражение и отправляем запрос на получение комментариев поста
$result = fetch_sql_response($con, $sql_comments, $data_post);
$comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Создаем подготовленное выражение и отправляем запрос на получение данных о пользователе
$user_id = $post['user_id'];
$data_user = [];
$data_user[] = $user_id;
$result = fetch_sql_response($con, $sql_user, $data_user);
$user = mysqli_fetch_assoc($result);
$post_content = choose_post_template($post);
$content = include_template('details.php', [
    'comments' => $comments,
    'post' => $post,
    'post_content' => $post_content,
    'user' => $user
]);

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user_name' => $user_name, 'is_auth' => $is_auth]);
print($layout);
print('sql_user: ' . $sql_user);
var_dump($user);

