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
            (SELECT COUNT(id)
                FROM subscription
                WHERE user_id = user.id) AS subscriber_count,
            (SELECT COUNT(id)
                FROM post
                WHERE user_id = user.id) AS post_count
        FROM user
        WHERE id = ?';

// Создаем подготовленное выражение и отправляем запрос
$result = fetch_sql_response($con, $sql, [$profile_id]);
$user_profile = mysqli_fetch_assoc($result);

if (!$user_profile) {
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
$get_params = $_GET;

switch ($tab) {
    // Вкладка ЛАЙКИ
    case 'likes':
        // Создаем запрос на получение постов пользователя у которых есть лайки
        $sql = "SELECT
                    post.id,
                    post_url,
                    type_class,
                    type_title,
                    height,
                    width,
                    last_like_date
                FROM post
                INNER JOIN post_type
                    ON type_id = post_type.id
                INNER JOIN (
                    SELECT DISTINCT MAX(date_add) AS last_like_date, post_id
                    FROM post_like
                        GROUP BY post_id
                    ) AS last_likes
                    ON last_likes.post_id = post.id
                WHERE user_id = ?
                ORDER BY last_like_date DESC";

        $result = fetch_sql_response($con, $sql, [$profile_id]);
        $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Для каждого поста получаем данные о последнем лайке и добавляем в массив $post
        foreach ($posts as &$post) {
            $sql_like = "SELECT
                            post_like.date_add AS like_date,
                            user_id AS like_user,
                            avatar,
                            username
                        FROM post_like
                        INNER JOIN user
                            ON user.id = user_id
                        WHERE post_id = ?
                        ORDER BY like_date DESC
                        LIMIT 1";

            $data = [$post['id']];
            $result = fetch_sql_response($con, $sql_like, $data);
            $like = mysqli_fetch_assoc($result);
            $post = array_merge($post, $like);
        }
        unset($post);

        $template = 'tab-likes.php';
        $tab_params = [
            'posts' => $posts
        ];
        break;

    case 'subscriptions':
        // Вкладка ПОДПИСКИ
        // Создаем запрос на получение данных о подписках
        $sql = "SELECT
            subscription.user_id,
            avatar,
            user.date_add AS user_date,
            username,
            subscriber_count,
            posts_count
        FROM subscription
        INNER JOIN user
            ON user.id = subscription.user_id
        LEFT JOIN
            (SELECT COUNT(id) AS subscriber_count, user_id
                FROM subscription
                GROUP BY user_id) AS subs
            ON subs.user_id = subscription.user_id
        LEFT JOIN
            (SELECT COUNT(id) AS posts_count, user_id
                FROM post
                GROUP BY user_id) AS posts
            ON posts.user_id = subscription.user_id
        WHERE subscriber_id = ?";

        $result = fetch_sql_response($con, $sql, [$profile_id]);
        $subscriptions = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Создаем запрос на получение id подписок залогиненного пользователя
        $sql = "SELECT user_id FROM subscription WHERE subscriber_id = ?;";
        $data = [$user['id']];
        $result = fetch_sql_response($con, $sql, $data);
        $logged_user_subscriptions = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $logged_user_subscriptions = array_column($logged_user_subscriptions, 'user_id');

        // В данные о подписках добавим поле с флагом подписан ли залогиненный пользователь на этого автора
        foreach ($subscriptions as &$subscription) {
            $subscription['is_logged_user_subscribed'] = in_array($subscription['user_id'], $logged_user_subscriptions);
        }
        unset($subscription);
        $tab_params = [
            'current_user_id' => (int)$user['id'],
            'users' => $subscriptions
        ];
        $template = 'tab-subscriptions.php';
        break;

    // Вкладка ПОСТЫ
    default:
        $errors = [];
        // Проверяем был ли отправлен комментарий к посту
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Если нет ошибок, записываем комментарий в базу
            $errors = process_comment_add($user['id'], $con);
        }

        $sql = 'SELECT
            post.*,
            (SELECT COUNT(id) FROM post_like WHERE post_id = post.id) AS like_count,
            (SELECT COUNT(id) FROM comment WHERE post_id = post.id) AS comment_count,
            type_class
        FROM post
        INNER JOIN post_type
        ON type_id = post_type.id
        WHERE post.user_id = ?
        ORDER BY post.date_add;';

        $result = fetch_sql_response($con, $sql, [$profile_id]);
        $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Для каждого поста получим набор хэштегов и данные автора оригинального поста в случае репоста
        foreach ($posts as &$post) {
            $post['hashtags'] = fetch_hashtags($con, $post['id']);

            // В случае репоста делаем запрос на автора оригинального поста и добавляем поле "author" в массив $post
            if ($post['is_repost']) {
                $sql = "SELECT * FROM user
                WHERE user.id = ?;";
                $author_id = $post['original_user_id'];
                $result = fetch_sql_response($con, $sql, [$author_id]);
                $author = mysqli_fetch_assoc($result);
                $post['author'] = $author;
            }
            // В случае, если в параметре запроса есть флаг показа комментариев,
            // Создаем запрос на получение комментариев к посту
            // Добавляем полученные данные в массив $post
            $query_param = 'post' . $post['id'];
            if (array_key_exists($query_param, $get_params)) {
                $constraint = ($get_params[$query_param] === 'comments_all') ? '' : ' LIMIT 2';
                $sql_comments = "SELECT
                    comment.*,
                    avatar,
                    username
                FROM comment
                INNER JOIN user
                    ON user.id = user_id
                WHERE post_id = ?"
                    . $constraint;

                $result = fetch_sql_response($con, $sql_comments, [$post['id']]);
                $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);
                $post['comments'] = $comments;
            }
        }
        unset($post);

        $tab_params = [
            'current_user_avatar' => $user['avatar'],
            'errors' => $errors,
            'posts' => $posts
        ];
        $template = 'tab-posts.php';
        break;
}

$is_own_profile = $user_profile['id'] === (int)$user['id'];
$tab_params['is_own_profile'] = $is_own_profile;

$tab_content = include_template($template, $tab_params);

$content = include_template('user-profile.php', [
    'active_tab' => $tab,
    'is_own_profile' => $is_own_profile,
    'is_subscribed' => $is_subscribed,
    'tab_content' => $tab_content,
    'tab_types' => $tab_types,
    'user' => $user_profile
]);

$title = 'readme: профиль';

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user' => $user
]);
print($layout);
