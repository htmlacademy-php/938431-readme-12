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

$showed_comments = filter_input(INPUT_GET, 'comments', FILTER_SANITIZE_SPECIAL_CHARS);
$is_all_comments = $showed_comments === 'all';

// Создаем запрос на получение поста с заданным id и его типа
$sql_post = "SELECT
    post.*,
    t_class AS p_type,
    (SELECT COUNT(id) FROM comment WHERE post_id = post.id) AS comment_count,
    (SELECT COUNT(id) FROM post_like WHERE post_id = post.id) AS like_count
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

// Запрос на получение комментариев к посту
$constraint = $is_all_comments ? ';' : ' LIMIT 2;';
$sql_comments = "SELECT
    comment.dt_add AS c_date,
    c_content,
    u_name,
    u_avatar
FROM comment
INNER JOIN user
ON user.id = comment.user_id
WHERE post_id = ?
ORDER BY c_date DESC"
. $constraint;

// Создаем запрос на получение данных о пользователе
$sql_user = "SELECT
    user.id,
    dt_add,
    u_name,
    u_avatar,
    (SELECT COUNT(id) FROM subscription WHERE user_id = user.id) AS subs_count,
    (SELECT COUNT(id) FROM post WHERE user_id = user.id) AS posts_count
FROM user
WHERE user.id = ?;";

// Создаем запрос на получение данных о подписке текущего пользователя
$sql = "SELECT id FROM subscription
WHERE subscriber_id = ?
AND user_id = ?;";

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

// Создаем подготовленное выражение и отправляем запрос на получение комментариев поста
$result = fetch_sql_response($con, $sql_comments, $data_post);
$comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Создаем подготовленное выражение и отправляем запрос на получение данных о пользователе
$user_id = $post['user_id'];
$data_user = [];
$data_user[] = $user_id;
$result = fetch_sql_response($con, $sql_user, $data_user);
$p_user = mysqli_fetch_assoc($result);

// Отправляем запрос на существование у текущего пользователя подписки на автора поста
$result = fetch_sql_response($con, $sql, [$user['id'], $user_id]);
$is_subscribed = mysqli_num_rows($result) !== 0;

$errors = [];
// Проверяем был ли отправлен комментарий к посту
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $filters = ['post-id' => FILTER_DEFAULT, 'comment' => FILTER_DEFAULT];
    $c_post = filter_input_array(INPUT_POST, $filters, true);
    // Проверяем поле с текстом комментария на заполненность и на длину текста
    $comment = trim($c_post['comment']);
    $errors['comment'] = validate_filled($comment);
    if (!$errors['comment']) {
        $errors['comment'] = validate_min_length($comment, 4);
        $errors = array_diff($errors, array(''));
    }
    // Если нет ошибок валидации, проверяем, что пост с заданным id есть в базе
    if (empty($errors)) {
        $sql = "SELECT COUNT(id) as count FROM post WHERE id = ?;";
        $data = [$c_post['post-id']];
        $result = fetch_sql_response($con, $sql, $data);
        if (mysqli_num_rows($result) === 0) {
            $errors['comment'] = 'Пост не найден. Не удалось записать комментарий';
        } else {
            // Создаем запрос на запись комментария в базу данных
            $sql_com = "INSERT INTO comment (c_content, user_id, post_id)
                VALUES (?,?,?);";
            $data_com = array($comment, $user['id'], $post_id);

            $stmt = db_get_prepare_stmt($con, $sql_com, $data_com);
            $result = mysqli_stmt_execute($stmt);

            if (!$result) {
                $errors['comment'] = 'Не удалось сохранить ваш комментарий.';
            } else {
                header("Location: http://readme/profile.php?id=" . $user_id);
            }
        }
    }

}

$is_current_user = $user['id'] == $user_id;

$post_content = choose_post_template($post);
$content = include_template('details.php', [
    'comments' => $comments,
    'current_user_avatar' => $user['u_avatar'],
    'errors' => $errors,
    'hashtags' => $hashtags,
    'is_all_comments' => $is_all_comments,
    'is_current_user' => $is_current_user,
    'is_subscribed' => $is_subscribed,
    'post' => $post,
    'post_content' => $post_content,
    'user' => $p_user
]);

$title = 'readme: публикация';

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user' => $user]);
print($layout);

// Увеличиваем счетчик просмотров поста
$sql = 'UPDATE post SET watch_count = watch_count + 1
WHERE id = ?;';

$result = fetch_sql_response($con, $sql, [$post_id]);

