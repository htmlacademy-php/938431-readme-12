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

// Получаем id пользователя из параметра запроса
$user_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$user_id) {
    http_response_code(404);
    exit;
}

// Создаем запрос на получение данных пользователя с полученным id
$sql = "SELECT
    user.*,
    (SELECT COUNT(subscriber_id)
        FROM subscription
        WHERE user_id = user.id) AS subs_count,
    (SELECT COUNT(id)
        FROM post
        WHERE user_id = user.id) AS post_count
FROM user
WHERE id = ?;";

// Создаем подготовленное выражение и отправляем запрос
$result = fetch_sql_response($con, $sql, [$user_id]);
$user_profile = mysqli_fetch_assoc($result);

if(!$user_profile) {
    http_response_code(404);
    exit;
}

$content = include_template('profile.php', ['user' => $user_profile]);
$title = 'readme: профиль';

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user' => $user
]);
print($layout);
