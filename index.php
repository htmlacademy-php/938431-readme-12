<?php
require_once('helpers.php');

$is_auth = rand(0, 1);

$user_name = 'Юлия'; // укажите здесь ваше имя
// Устанавливаем соединение с базой readme
$con = mysqli_connect('localhost', 'mysql', 'mysql', 'readme');

if (!$con) {
    print('Ошибка подключения: ' . mysqli_connect_error());
    exit;
};

// Устанавливаем кодировку
mysqli_set_charset($con, 'utf8');

// Создаем запрос на получение типов постов
$sql = "SELECT t_class, width, height
FROM post_type
ORDER BY id ASC";

$result = mysqli_query($con, $sql);

if (!$result) {
    $error = mysqli_error($con);
    print('Ошибка MySql: ' . $error);
    exit;
}

$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
$types = adapt_post_types($rows);

// Создаем запрос на получение постов с их авторами, отсортированных по популярности
$sql = "SELECT
p_title,
post.dt_add,
p_url,
p_text,
u_name,
u_avatar,
t_class,
watch_count
FROM post
INNER JOIN user
  ON user_id = user.id
INNER JOIN post_type
  ON type_id = post_type.id
ORDER BY watch_count DESC";

$result = mysqli_query($con, $sql);

if (!$result) {
    $error = mysqli_error($con);
    print('Ошибка MySql: ' . $error);
    exit;
}

$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
$posts = array_map('adapt_raw_post', $rows);

$title = 'readme: популярное';

$content = include_template('main.php', ['posts' => $posts, 'types' => $types]);

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user_name' => $user_name, 'is_auth' => $is_auth]);
print($layout);
