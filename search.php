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
    $is_hashtag = substr($search, 0, 1) === '#';
    if ($is_hashtag) {
        // Поиск по хэштегу. Создаем запрос на получение постов с искомым хэштегом
        $hash = substr($search, 1);
        $sql_hash = "SELECT
        post_id
        FROM post_hashtag
        INNER JOIN hashtag
        ON hashtag.id = post_hashtag.hash_id
        WHERE title = ?;";

        $result = fetch_sql_response($con, $sql_hash, [$hash]);
        $posts_ids = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $posts_ids = array_column($posts_ids, 'post_id');
        $comma_separated_ids = implode(',', $posts_ids);

        // Проверяем, что есть посты с искомым хэштегом
        if (empty($posts_ids)) {
            $posts = [];
            $sql = '';
        } else {
            $where_condition = ' WHERE post.id IN ('. $comma_separated_ids .')
            ORDER BY p_date DESC;';
            $data = [];
        }
    } else {
        // Полнотекстовый поиск
        $where_condition = ' WHERE MATCH (p_title, p_text) AGAINST (?);';
        $data = [$search];
    }
    // Создаем запрос на получение постов
    $sql = 'SELECT
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
    ON type_id = post_type.id'
    . $where_condition;

    if ($sql) {
        // Создаем подготовленное выражение и отправляем запрос на получение подходящих постов
        $result = fetch_sql_response($con, $sql, $data);
        $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

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


