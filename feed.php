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

// Устанавливаем соединение с базой readme
$con = set_connection();
// Перенаправляем на главную страницу незалогиненных пользователей
if (!isset($user)) {
    header('Location: /index.php');
    exit;
}

// Получаем текущий фильтр  из массива $_GET
$filter = filter_input(INPUT_GET, 'filter', FILTER_SANITIZE_NUMBER_INT) ?? 0;

// Создаем запрос на получение типов постов
$sql = "SELECT *
        FROM post_type
        ORDER BY id ASC";

$result = fetch_sql_response($con, $sql, []);
$types = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Создаем ограничение для SQL запроса
$constraint = '';
if ($filter) {
    $constraint = " AND type_id = " . $filter;
};

$sql = "SELECT
    post.id,
    post_title,
    post.date_add AS post_date,
    post_url,
    post_text,
    quote_author,
    repost_count,
    user_id,
    username,
    avatar,
    type_class,
    type_id,
    (SELECT COUNT(id) FROM comment WHERE post_id = post.id) AS comment_count,
    (SELECT COUNT(id) FROM post_like WHERE post_id = post.id) AS like_count
FROM post
INNER JOIN user
ON user_id = user.id
INNER JOIN post_type
ON type_id = post_type.id
WHERE post.user_id IN
    (SELECT user_id FROM subscription
        WHERE subscriber_id = ?)"
    . $constraint
    . " ORDER BY post.date_add ASC;";

$user_id = (int)$user['id'];
$result = fetch_sql_response($con, $sql, [$user_id]);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Для каждого поста получим набор хэштегов
foreach ($posts as &$post) {
    $post['hashtags'] = fetch_hashtags($con, $post['id']);
}
unset($post);

$title = 'readme: моя лента';

$content = include_template('user-feed.php', [
    'current_user_id' => $user_id,
    'filter' => $filter,
    'posts' => $posts,
    'types' => $types,
]);
$title = 'readme: моя лента';

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user' => $user,
]);
print($layout);
