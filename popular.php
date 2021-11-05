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

define('POSTS_PER_PAGE', 6);

$sort_types = [
    'popular' => 'Популярность',
    'likes' => 'Лайки',
    'date' => 'Дата'
];

// Создаем запрос на получение типов постов
$sql = "SELECT *
        FROM post_type
        ORDER BY id ASC";

$result = mysqli_query($con, $sql);

if (!$result) {
    $error = mysqli_error($con);
    print('Ошибка MySql: ' . $error);
    exit;
}

$types = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Получаем текущий фильтр и сортировку и номер страницы из массива $_GET
$filter = filter_input(INPUT_GET, 'filter', FILTER_SANITIZE_NUMBER_INT);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'popular';
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?? 1;

// Создаем ограничение для SQL запроса
$where_condition = '';
if ($filter) {
    $where_condition = " WHERE type_id = " . $filter;
};

$order_param = 'watch_count';
if ($sort === 'likes') {
    $order_param = 'like_count';
} elseif ($sort === 'date') {
    $order_param = 'post_date';
}

// Создаем запрос на получение числа постов
$sql_count = "SELECT COUNT(id) AS count FROM post"
    . $where_condition . ";";

$result = mysqli_query($con, $sql_count);
$count = mysqli_fetch_assoc($result);
$posts_count = $count['count'];
$pages_count = ceil($posts_count / POSTS_PER_PAGE);

// Создаем запрос на получение постов с их авторами,
// количеством лайков и комментариев, отсортированных по популярности
$sql = "SELECT
    post.id,
    post_title,
    post.date_add AS post_date,
    post_url,
    post_text,
    quote_author,
    user_id,
    username,
    avatar,
    user.date_add AS user_date,
    type_class,
    type_id,
    watch_count,
    (SELECT COUNT(id) FROM comment WHERE post_id = post.id) AS comment_count,
    (SELECT COUNT(id) FROM post_like WHERE post_id = post.id) AS like_count
FROM post
    INNER JOIN user
        ON user_id = user.id
    INNER JOIN post_type
        ON type_id = post_type.id"
    . $where_condition
    . " ORDER BY " . $order_param . " DESC
LIMIT ? OFFSET ?";

// Получаем результат
$offset = ($page - 1) * POSTS_PER_PAGE;
$data = [POSTS_PER_PAGE, $offset];
$result = fetch_sql_response($con, $sql, $data);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

$title = 'readme: популярное';

$content = include_template('user-popular.php', [
    'current_user_id' => (int)$user['id'],
    'page' => $page,
    'posts' => $posts,
    'types' => $types,
    'filter' => $filter,
    'sort' => $sort,
    'sort_types' => $sort_types,
    'total_count' => $pages_count,
]);

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user' => $user,
]);
print($layout);
