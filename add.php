<?php
require_once('helpers.php');

$title = 'readme: добавление публикации';
$is_auth = rand(0, 1);
$user_name = 'Юлия'; // укажите здесь ваше имя


// Устанавливаем соединение с базой readme
$con = set_connection();

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

// Получаем текущий активный тип поста из массива $_GET
$active_type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?? 'photo';

// Добавляем каждому типу поста ключ "url" для атрибута href ссылки
foreach ($types AS &$type) {
    $type['url'] = update_query_params('type', $type['t_class']);
};
unset($type);


$content = include_template('adding-post.php', ['types' => $types, 'active_type' => $active_type]);

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user_name' => $user_name, 'is_auth' => $is_auth]);
print($layout);

