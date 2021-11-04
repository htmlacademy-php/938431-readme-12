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

// Если пост существует, создаем или удаляем запись в таблице связей post_like
if (!empty($post)) {
    // Если залогиненный пользователь пытается поставить лайк собственному посту, не делаем записей, возвращаемся на страницу
    $user_id = (int)$user['id'];

    if ($user_id === $post['user_id']) {
        header("Location: {$_SERVER['HTTP_REFERER']}");
    }

    // Проверяем в таблице связей post_like существование записи о лайке текущего пользователя данному посту
    $sql = "SELECT COUNT(*) AS like_count
            FROM post_like
            WHERE post_id = ?
                AND user_id = ?";

    $data = [$post_id, $user_id];
    $result = fetch_sql_response($con, $sql, $data);
    $likes = mysqli_fetch_assoc($result);
    $is_like = (bool)$likes['like_count'];

    if ($is_like) {
        // Если есть записи - удаляем их, снимаем лайк
        $sql = "DELETE FROM post_like
                WHERE post_id = ?
                    AND user_id = ?";

        $stmt = db_get_prepare_stmt($con, $sql, $data);
        $result = mysqli_stmt_execute($stmt);
    } else {
        // Если нет такого лайка - добавляем запись о нем
        $sql = "INSERT INTO post_like (post_id, user_id) VALUES (?, ?)";
        $stmt = db_get_prepare_stmt($con, $sql, $data);
        $result = mysqli_stmt_execute($stmt);
    }
}

header("Location: {$_SERVER['HTTP_REFERER']}");
