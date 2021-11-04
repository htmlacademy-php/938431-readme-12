<?php
session_start();
$user = $_SESSION['user'] ?? null;

// Перенаправляем на главную страницу анонимных пользователей
if (!$user) {
    header('Location: /index.php');
    exit;
}

require_once('helpers.php');

// Устанавливаем соединение с базой readme
$con = set_connection();

$post_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$post_id) {
    http_response_code(404);
    exit;
}

define('SHOWED_COMMENTS_ON_START', 2);
define('MAX_TEXT_LENGTH', 255);

$errors = [];
// Проверяем был ли отправлен комментарий к посту
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = process_comment_add($user['id'], $con);
}

// Проверяем параметр запроса о показе комментариев
$showed_comments = filter_input(INPUT_GET, 'comments', FILTER_SANITIZE_SPECIAL_CHARS);
$is_all_comments = $showed_comments === 'all';

// Создаем запрос на получение поста с заданным id и его типа
$sql_post = "SELECT
    post.*,
    type_class,
    (SELECT COUNT(id) FROM comment WHERE post_id = post.id) AS comment_count,
    (SELECT COUNT(id) FROM post_like WHERE post_id = post.id) AS like_count
FROM post
    INNER JOIN post_type
        ON type_id = post_type.id
WHERE post.id = ?";

// Запрос на получение комментариев к посту
$constraint = $is_all_comments ? '' : ' LIMIT ' . SHOWED_COMMENTS_ON_START;
$sql_comments = "SELECT
    comment.date_add AS comment_date,
    comment_text,
    username,
    avatar,
    user_id
FROM comment
    INNER JOIN user
        ON user.id = comment.user_id
WHERE post_id = ?
ORDER BY comment_date DESC"
. $constraint;

// Создаем запрос на получение данных о пользователе
$sql_user = "SELECT
    user.id,
    date_add,
    username,
    avatar,
    (SELECT COUNT(id) FROM subscription WHERE user_id = user.id) AS subscriber_count,
    (SELECT COUNT(id) FROM post WHERE user_id = user.id) AS posts_count
FROM user
WHERE user.id = ?";

// Создаем запрос на получение данных о подписке текущего пользователя
$sql = "SELECT id FROM subscription
WHERE subscriber_id = ?
AND user_id = ?";

// Создаем подготовленное выражение и отправляем запрос на получение поста
$data_post = [];
$data_post[] = $post_id;
$result = fetch_sql_response($con, $sql_post, $data_post);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    http_response_code(404);
    exit;
}

// Создаем подготовленное выражение и отправляем запрос на получение хэштегов к посту
$hashtags = fetch_hashtags($con, $post_id);

// Создаем подготовленное выражение и отправляем запрос на получение комментариев поста
$result = fetch_sql_response($con, $sql_comments, $data_post);
$comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Создаем подготовленное выражение и отправляем запрос на получение данных о пользователе
$user_id = $post['user_id'];
$data_user = [];
$data_user[] = $user_id;
$result = fetch_sql_response($con, $sql_user, $data_user);
$post_author = mysqli_fetch_assoc($result);

// Отправляем запрос на существование у текущего пользователя подписки на автора поста
$result = fetch_sql_response($con, $sql, [$user['id'], $user_id]);
$is_subscribed = (bool) mysqli_num_rows($result);

$is_current_user = $user['id'] == $user_id;

$templates = [
    'link' => 'details-link.php',
    'photo' => 'details-photo.php',
    'text' => 'details-text.php',
    'quote' => 'details-quote.php',
    'video' => 'details-video.php',
];

$template = $templates[$post['type_class']] ?? '';

$post_content = include_template($template, ['post' => $post]);

$content = include_template('details.php', [
    'comments' => $comments,
    'current_user_avatar' => $user['avatar'],
    'errors' => $errors,
    'hashtags' => $hashtags,
    'is_current_user' => $is_current_user,
    'is_subscribed' => $is_subscribed,
    'post' => $post,
    'post_content' => $post_content,
    'user' => $post_author,
]);

$title = 'readme: публикация';

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user' => $user,
]);
print($layout);

// Увеличиваем счетчик просмотров поста
$sql = 'UPDATE post SET watch_count = watch_count + 1
WHERE id = ?;';

$result = fetch_sql_response($con, $sql, [$post_id]);
