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

$profile_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$profile_id) {
    http_response_code(404);
    exit;
}

// Создаем запрос на получение связи из таблицы подписок пользователей
$sql = "SELECT id FROM subscription
    WHERE user_id = ?
    AND subscriber_id = ?;";

$data = array($profile_id, $user['id']);
$result = fetch_sql_response($con, $sql, $data);
$bind = mysqli_fetch_assoc($result);

if (empty($bind)) {
    // Если подписки не существует
    // Создаем запрос на получение пользователя с заданным id
    $sql = "SELECT * FROM user
    WHERE id = ?;";

    $result = fetch_sql_response($con, $sql, [$profile_id]);
    $profile_user = mysqli_fetch_assoc($result);

    if (!empty($profile_user)) {
    // Создаем запись в таблице связей subscription
        $sql = "INSERT INTO subscription (user_id, subscriber_id) VALUES (?, ?);";

        $stmt = db_get_prepare_stmt($con, $sql, $data);
        $result = mysqli_stmt_execute($stmt);

    // TODO: Отправить сообщение пользователю о новом подписчике
    }
} else {
    // Если нужная связь найдена - создаем запрос на ее удаление
    $sql = "DELETE FROM subscription
    WHERE id = ?";

    $stmt = db_get_prepare_stmt($con, $sql, $bind);
    $result = mysqli_stmt_execute($stmt);
}

header("Location: {$_SERVER['HTTP_REFERER']}");
