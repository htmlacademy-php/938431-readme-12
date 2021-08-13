<?php
require_once('helpers.php');

function choose_template($post) {
    $result = $post['p_type'];
    switch ($post['p_type']) {
        case 'link':
            $result = include_template('details-link.php', ['title' => $post['p_text'], 'url' => $post['p_url']]);
            break;
        case 'photo';
            $result = include_template('details-photo.php', ['img_url' => $post['p_url']]);
            break;
        case 'quote';
            $result = include_template('details-quote.php', ['text' => $post['p_text'], 'author' => $post['quote_author']]);
            break;
        case 'text';
            $result = include_template('details-text.php', ['text' => $post['p_text']]);
            break;
        case 'video';
            $result = include_template('details-video.php', ['youtube_url' => $post['p_url']]);
            break;
    };

    return $result;
};

$is_auth = rand(0, 1);
$user_name = 'Юлия'; // укажите здесь ваше имя
$title = 'readme: публикация';

$post_id = $_GET['id'] ?? null;
// if (!$post_id) {
//     exit;
// }

// Устанавливаем соединение с базой readme
$con = mysqli_connect('localhost', 'mysql', 'mysql', 'readme');

if (!$con) {
    print('Ошибка подключения: ' . mysqli_connect_error());
    exit;
};

// Устанавливаем кодировку
mysqli_set_charset($con, 'utf8');

// Создаем запрос на получение поста с заданным id
$sql_post = "SELECT post.*, t_class as p_type
FROM post
INNER JOIN post_type
  ON type_id = post_type.id
WHERE post.id = ?;";

// Создаем запрос на получение кол-ва лайков у поста
$sql_likes = "SELECT post_id, COUNT(*) AS l_count
FROM post_like
WHERE post_id = ?
GROUP BY post_id;";

// Создаем запрос на получение кол-ва комментариев у поста
$sql_com_count = "SELECT post_id, COUNT(*) AS c_count
FROM comment
WHERE post_id = ?
GROUP BY post_id;";

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

// Создаем подготовленное выражение и отправляем запрос на получение количества лайков у поста
$result = fetch_sql_response($con, $sql_likes, $data_post);
$likes = mysqli_fetch_assoc($result);

// Создаем подготовленное выражение и отправляем запрос на получение количества комментариев у поста
$result = fetch_sql_response($con, $sql_com_count, $data_post);
$comment = mysqli_fetch_assoc($result);

// Создаем подготовленное выражение и отправляем запрос на получение комментариев поста
$result = fetch_sql_response($con, $sql_comments, $data_post);
$comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Создаем подготовленное выражение и отправляем запрос на получение данных о пользователе
$user_id = $post['user_id'];
$data_user = [];
$data_user[] = $user_id;
$result = fetch_sql_response($con, $sql_user, $data_user);
$user = mysqli_fetch_assoc($result);
$post_content = choose_template($post);
$content = include_template('details.php', [
    'comment_count' => $comment['c_count'],
    'comments' => $comments,
    'likes_count' => $likes['l_count'],
    'post' => $post,
    'post_content' => $post_content,
    'user' => $user
]);

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user_name' => $user_name, 'is_auth' => $is_auth]);
print($layout);
print('sql_user: ' . $sql_user);
var_dump($user);

