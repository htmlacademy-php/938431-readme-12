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
$profile_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$profile_id) {
    http_response_code(404);
    exit;
}

// Создаем запрос на получение данных пользователя с полученным id
$sql = 'SELECT
    user.*,
    (SELECT COUNT(subscriber_id)
        FROM subscription
        WHERE user_id = user.id) AS subs_count,
    (SELECT COUNT(id)
        FROM post
        WHERE user_id = user.id) AS post_count
FROM user
WHERE id = ?;';

// Создаем подготовленное выражение и отправляем запрос
$result = fetch_sql_response($con, $sql, [$profile_id]);
$user_profile = mysqli_fetch_assoc($result);

if(!$user_profile) {
    http_response_code(404);
    exit;
}

// Создаем запрос на получение данных о подписке текущего пользователя
$sql = 'SELECT id FROM subscription
WHERE subscriber_id = ?
AND user_id = ?;';

$result = fetch_sql_response($con, $sql, [$user['id'], $profile_id]);
$is_subscribed = mysqli_num_rows($result) !== 0;

// Типы вкладок на странице
$tab_types = [
    'posts' => 'Посты',
    'likes' => 'Лайки',
    'subscriptions' => 'Подписки'
];

// Получаем выбранную вкладку из массива $_GET
$tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'posts';

switch($tab) {
    // Вкладка ПОСТЫ
    case 'likes':
    // Вкладка ЛАЙКИ
        break;
    case 'subscriptions':
    // Вкладка ПОДПИСКИ
        break;

    default:
        $sql = 'SELECT
            post.*,
            (SELECT COUNT(id) FROM post_like WHERE post_id = post.id) AS like_count,
            t_class AS p_type
        FROM post
        INNER JOIN post_type
        ON type_id = post_type.id
        WHERE post.user_id = ?
        ORDER BY post.dt_add ASC;';

        $result = fetch_sql_response($con, $sql, [$profile_id]);
        $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Для каждого поста получим набор хэштегов и данные автора оригинального поста в случае репоста
        foreach ($posts as &$post) {
            $sql_hash = "SELECT
                title
            FROM hashtag
            INNER JOIN post_hashtag
            ON hashtag.id = hash_id
            AND post_id = ?;";

            $result = fetch_sql_response($con, $sql_hash, [$post['id']]);
            $hashtags = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $post['hashtags'] = $hashtags;
            // В случае репоста делаем запрос на автора оригинального поста и добавляем поле "author" в массив $post
            if ($post['p_repost']) {
                $sql_orig = "SELECT * FROM user
                WHERE user.id = ?;";
                $author_id = $post['orig_user_id'];
                $result = fetch_sql_response($con, $sql_orig, [$author_id]);
                $author = mysqli_fetch_assoc($result);
                $post['author'] = $author;
            }
        }

        $params = ['posts' => $posts];
        $template = 'tab-posts.php';
        break;
}

$is_own_profile = $user_profile['id'] == $user['id'];

$tab_content = include_template($template, $params);

$content = include_template('profile.php', [
    'user' => $user_profile,
    'is_own_profile' => $is_own_profile,
    'is_subscribed' => $is_subscribed,
    'tab_content' => $tab_content
]);

$title = 'readme: профиль';

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user' => $user
]);
print($layout);
