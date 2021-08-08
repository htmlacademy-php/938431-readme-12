<?php
require_once('helpers.php');

$is_auth = rand(0, 1);

$user_name = 'Юлия'; // укажите здесь ваше имя
$scriptname = pathinfo(__FILE__, PATHINFO_BASENAME);

// Устанавливаем соединение с базой readme
$con = mysqli_connect('localhost', 'mysql', 'mysql', 'readme');

if (!$con) {
    print('Ошибка подключения: ' . mysqli_connect_error());
    exit;
};

// Устанавливаем кодировку
mysqli_set_charset($con, 'utf8');

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
foreach ($types as &$type) {
    $type['url'] = update_query_params($scriptname, 'filter', $type['id']);
};
unset($type);

// Получаем текущий фильтр из массива $_GET
$filter = $_GET['filter'] ?? 0;

// Создаем ограничение для SQL запроса
$where_condition = '';
if ($filter) {
    $where_condition = " WHERE type_id = " .$filter;
};

// Создаем запрос на получение постов с их авторами,
// количеством лайков и комментариев, отсортированных по популярности
$sql = "SELECT
post.id,
p_title,
post.dt_add as p_date,
p_url,
p_text,
u_name,
u_avatar,
user.dt_add as u_dt_add,
t_class as p_type,
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
ON post_count.post_id = post.id";
$sql .= $where_condition;
$sql .= " ORDER BY watch_count DESC;";


// Получаем результат
$result = mysqli_query($con, $sql);

if (!$result) {
    $error = mysqli_error($con);
    print('Ошибка MySql: ' . $error);
    exit;
}

$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

$title = 'readme: популярное';

$content = include_template('main.php', ['posts' => $posts, 'types' => $types, 'filter' => $filter]);

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user_name' => $user_name, 'is_auth' => $is_auth]);
print($layout);
