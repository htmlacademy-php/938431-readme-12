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

$search = get_search_value('q');

if ($search) {
    $sql = "SELECT
        post.id,
        p_title,
        post.dt_add AS p_date,
        p_url,
        p_text,
        quote_author,
        user_id,
        u_name,
        u_avatar,
        t_class AS p_type,
        type_id,
        (SELECT COUNT(id) FROM comment WHERE comment.post_id = post.id) AS comment_count,
        (SELECT COUNT(id) FROM post_like l WHERE l.post_id = post.id) AS like_count
    FROM post
    INNER JOIN user
    ON user_id = user.id
    INNER JOIN post_type
    ON type_id = post_type.id
    WHERE MATCH (p_title, p_text) AGAINST (?);";

    // Создаем подготовленное выражение и отправляем запрос на получение подходящих постов
    $result = fetch_sql_response($con, $sql, [$search]);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    if (empty($posts)) {
        $content = include_template('no-results.php', ['search' => $search]);
    } else {
        $content = include_template('search-results.php', ['posts' => $posts,'search' => $search]);
    }

    $title = 'readme: страница результатов поиска';
    $layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user' => $user]);
    print($layout);
} else {
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
}


