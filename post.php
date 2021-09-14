<?php
require_once('helpers.php');

$is_auth = rand(0, 1);
$user_name = 'Юлия'; // укажите здесь ваше имя
$title = 'readme: публикация';

$post_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$post_id) {
    http_response_code(404);
    exit;
}

// Устанавливаем соединение с базой readme
$con = set_connection();

// Создаем запрос на получение поста с заданным id и его типа
$sql_post = "SELECT
    post.*,
    t_class AS p_type
FROM post
INNER JOIN post_type
ON type_id = post_type.id
WHERE post.id = ?;";

// Создаем запрос на получение хэштегов к посту с заданным id
$sql_hash = "SELECT
    title
FROM hashtag
INNER JOIN post_hashtag
ON hashtag.id = hash_id
AND post_id = ?;";

// Запрос на получение количества комментариев к посту
$sql_comment_cnt = "SELECT COUNT(id) AS comment_count
FROM comment
WHERE post_id = ?;";

// Запрос на получение количества лайков к посту
$sql_like_cnt = "SELECT COUNT(id) AS like_count
FROM post_like
WHERE post_id = ?;";

// Запрос на получение комментариев к посту
$sql_comments = "SELECT
    comment.dt_add AS c_date,
    c_content,
    u_name,
    u_avatar
FROM comment
INNER JOIN user
ON user.id = comment.user_id
WHERE post_id = ?
ORDER BY c_date DESC
LIMIT 5;";

// Создаем запрос на получение данных о пользователе
$sql_user = "SELECT
    user.id,
    dt_add,
    u_name,
    u_avatar
FROM user
WHERE user.id = ?;";

// Запрос на получение количества подписчиков пользователя
$sql_subs_cnt = "SELECT COUNT(id) AS subs_count
FROM subscription
WHERE user_id = ?;";

// Запрос на получение количества постов пользователя
$sql_post_cnt = "SELECT COUNT(id) AS posts_count
FROM post
WHERE user_id = ?;";

// Создаем подготовленное выражение и отправляем запрос на получение поста
$data_post = [];
$data_post[] = $post_id;
$result = fetch_sql_response($con, $sql_post, $data_post);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    http_response_code(404);
    exit;
}

// Создаем подготовленное выражение и отправляем запрос на получение хэштегов к посту
$result = fetch_sql_response($con, $sql_hash, $data_post);
$hashtags = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Создаем подготовленное выражение и отправляем запрос на получение количества комментариев к посту
$result = fetch_sql_response($con, $sql_comment_cnt, $data_post);
$c_cnt = mysqli_fetch_assoc($result);

// Создаем подготовленное выражение и отправляем запрос на получение количества лайков у поста
$result = fetch_sql_response($con, $sql_like_cnt, $data_post);
$l_cnt = mysqli_fetch_assoc($result);

// Объединяем полученные данные о посте в один массив
$post = array_merge($post, $c_cnt, $l_cnt);

// Создаем подготовленное выражение и отправляем запрос на получение комментариев поста
$result = fetch_sql_response($con, $sql_comments, $data_post);
$comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Создаем подготовленное выражение и отправляем запрос на получение данных о пользователе
$user_id = $post['user_id'];
$data_user = [];
$data_user[] = $user_id;
$result = fetch_sql_response($con, $sql_user, $data_user);
$user = mysqli_fetch_assoc($result);

// Создаем подготовленное выражение и отправляем запрос на получение количества подписчиков пользователя
$result = fetch_sql_response($con, $sql_subs_cnt, $data_user);
$s_cnt = mysqli_fetch_assoc($result);

// Создаем подготовленное выражение и отправляем запрос на получение количества постов пользователя
$result = fetch_sql_response($con, $sql_post_cnt, $data_user);
$p_cnt = mysqli_fetch_assoc($result);

// Объединяем полученные данные о пользователе в один массив
$user = array_merge($user, $s_cnt, $p_cnt);

$post_content = choose_post_template($post);
$content = include_template('details.php', [
    'comments' => $comments,
    'hashtags' => $hashtags,
    'post' => $post,
    'post_content' => $post_content,
    'user' => $user
]);

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user_name' => $user_name, 'is_auth' => $is_auth]);
print($layout);
