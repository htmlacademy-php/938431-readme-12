<?php
require_once('helpers.php');

$is_auth = rand(0, 1);

$user_name = 'Юлия'; // укажите здесь ваше имя
$sort_types = [
    'popular' => 'Популярность',
    'likes' => 'Лайки',
    'date' => 'Дата'
];

// Устанавливаем соединение с базой readme
$con = set_connection();

// Создаем запрос на получение типов постов
$sql = "SELECT id, t_class AS p_type, width, height
FROM post_type
ORDER BY id ASC";

$result = mysqli_query($con, $sql);

if (!$result) {
    $error = mysqli_error($con);
    print('Ошибка MySql: ' . $error);
    exit;
}

$types = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Добавляем каждому типу поста ключ "url" для атрибута href ссылки
foreach ($types AS &$type) {
    $type['url'] = update_query_params('filter', $type['id']);
};
unset($type);

// Получаем текущий фильтр и сортировку из массива $_GET
$filter = filter_input(INPUT_GET, 'filter', FILTER_SANITIZE_NUMBER_INT);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'popular';

// Создаем ограничение для SQL запроса
$where_condition = '';
if ($filter) {
    $where_condition = " WHERE type_id = " .$filter;
};

$order_param = 'watch_count';
if ($sort === 'likes') {
    $order_param = 'like_count';
} elseif ($sort === 'date') {
    $order_param = 'p_date';
}

// Создаем запрос на получение постов с их авторами,
// количеством лайков и комментариев, отсортированных по популярности
$sql = "SELECT
    post.id,
    p_title,
    post.dt_add AS p_date,
    p_url,
    p_text,
    u_name,
    u_avatar,
    user.dt_add AS u_dt_add,
    t_class AS p_type,
    type_id,
    watch_count,
    comment_count,
    like_count
FROM post
INNER JOIN user
ON user_id = user.id
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
ON post_count.post_id = post.id"
. $where_condition
. " ORDER BY " . $order_param . " DESC;";

// Получаем результат
$result = mysqli_query($con, $sql);

if (!$result) {
    $error = mysqli_error($con);
    print('Ошибка MySql: ' . $error);
    exit;
}

$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

$title = 'readme: популярное';

$content = include_template('main.php', ['posts' => $posts, 'types' => $types, 'filter' => $filter, 'sort' => $sort, 'sort_types' => $sort_types]);

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user_name' => $user_name, 'is_auth' => $is_auth]);
print($layout);
