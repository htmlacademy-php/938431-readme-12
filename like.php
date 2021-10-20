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

// Создаем запрос на получение поста с заданным id и его типа
$sql = "SELECT * FROM post
    WHERE id = ?;";

$result = fetch_sql_response($con, $sql, [$post_id]);
$post = mysqli_fetch_assoc($result);

// Если пост существует, создаем запись в таблице связей post_like
if (!empty($post)) {
    $sql = "INSERT INTO post_like (user_id, post_id) VALUES (?, ?);";

    $data = array($user['id'], $post_id);
    $stmt = db_get_prepare_stmt($con, $sql, $data);
    $result = mysqli_stmt_execute($stmt);
}

header("Location: {$_SERVER['HTTP_REFERER']}");
