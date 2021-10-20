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
    exit();
}

// Получаем текущий фильтр  из массива $_GET
$filter = filter_input(INPUT_GET, 'filter', FILTER_SANITIZE_NUMBER_INT) ?? 0;

// Создаем запрос на получение типов постов
$sql = "SELECT
    id,
    t_class AS p_type,
    t_title,
    width,
    height
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
    p_title,
    post.dt_add AS p_date,
    p_url,
    p_text,
    quote_author,
    repost_count,
    user_id,
    u_name,
    u_avatar,
    t_class AS p_type,
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
. " ORDER BY post.dt_add ASC;";

$data = array($user['id']);
$result = fetch_sql_response($con, $sql, $data);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Для каждого поста получим набор хэштегов
foreach ($posts as &$post) {
    $post['hashtags'] = fetch_hashtags($con, $post['id']);
}
unset($post);

$title = 'readme: моя лента';

$content = include_template('user-feed.php', ['filter' => $filter, 'posts' => $posts, 'types' => $types]);
$title = 'readme: моя лента';

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user' => $user]);
print($layout);
