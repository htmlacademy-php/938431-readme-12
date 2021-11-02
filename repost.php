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

// Создаем запрос на получение поста с заданным id
$sql_post = "SELECT * FROM post
    WHERE post.id = ?;";

$result = fetch_sql_response($con, $sql_post, [$post_id]);
$post = mysqli_fetch_assoc($result);
if (!empty($post)) {
    // Создаем запрос на запись нового поста
    $sql_add_post = "INSERT INTO post (
            post_title,
            post_url,
            post_text,
            quote_author,
            user_id,
            is_repost,
            original_user_id,
            type_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?);";

    $data_post = [];
    $data_post[] = $post['post_title'];
    $data_post[] = $post['post_url'];
    $data_post[] = $post['post_text'];
    $data_post[] = $post['quote_author'];
    $data_post[] = $user['id'];
    $data_post[] = 1;
    $data_post[] = $post['user_id'];
    $data_post[] = $post['type_id'];
    // Начинаем транзакцию
    mysqli_begin_transaction($con);

    // Создаем подготовленное выражение и отправляем запрос на на запись нового поста
    $stmt = db_get_prepare_stmt($con, $sql_add_post, $data_post);
    $result_post = mysqli_stmt_execute($stmt);

    if ($result_post) {
        // В случае успеха отправляем запросы на запись хэштегов к посту
        $repost_id = mysqli_insert_id($con);

        // Создаем запрос на получение id хэштегов к посту с заданным id
        $sql_hash = "SELECT hashtag_id FROM post_hashtag
            WHERE post_id = ?";
        $result = fetch_sql_response($con, $sql_hash, [$post_id]);
        $hashtags = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $result_hash = true;

        foreach ($hashtags as $hash) {
            // Запрос на запись связи пост - хэштег
            $sql_add_hash = "INSERT INTO post_hashtag
                SET post_id = ?, hashtag_id = ?";

            $data_hash = array($repost_id, $hash['hash_id']);
            $stmt = db_get_prepare_stmt($con, $sql_add_hash, $data_hash);
            $result_hash = mysqli_stmt_execute($stmt);
            if (!$result_hash) {
                break;
            }
        }
        // Увеличиваем счетчик репостов оригинального поста
        $sql = "UPDATE post
            SET repost_count = repost_count + 1
            WHERE id = ?";

        $stmt = db_get_prepare_stmt($con, $sql, [$post_id]);
        $result_count = mysqli_stmt_execute($stmt);
    }

    // Фиксируем изменения в случае успешного выполнения всех запросов или откатываем транзакцию
    if ($result_post && $result_hash && $result_count) {
        mysqli_commit($con);
    } else {
        mysqli_rollback($con);
    }

    header("Location: http://readme/profile.php?id=" . $user['id']);
}
