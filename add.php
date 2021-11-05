<?php
session_start();
$user = $_SESSION['user'] ?? null;

// Перенаправляем на главную страницу анонимных пользователей
if (!$user) {
    header('Location: /index.php');
    exit;
}

require_once('helpers.php');
require_once('mail-init.php');

define('MAX_TITLE_LENGTH', 100);
define('MAX_QUOTE_LENGTH', 100);
define('MAX_NAME_LENGTH', 100);
define('MAX_TEXT_LENGTH', 65535);
define('MAX_URL_LENGTH', 255);

// Устанавливаем соединение с базой readme
$con = set_connection();

// Создаем запрос на получение типов постов
$sql = "SELECT *
FROM post_type
ORDER BY id ASC";

$result = mysqli_query($con, $sql);

if (!$result) {
    $error = mysqli_error($con);
    print('Ошибка MySql: ' . $error);
    exit;
}

$types = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Получаем текущий активный тип поста из массива $_GET
$active_type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?? 'text';

// Добавляем каждому типу поста ключ "url" для атрибута href ссылки
foreach ($types as &$type) {
    $type['url'] = update_query_params('type', $type['type_class']);
};
unset($type);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Для каждого типа поста определим опции: обязательные поля формы и применяемые фильтры
    $form_options = [
        'link' => [
            'required' => ['post-link'],
            'filters' => ['post-link']
        ],
        'photo' => [
            'required' => [],
            'filters' => ['photo-url']
        ],
        'quote' => [
            'required' => ['quote-text', 'quote-author'],
            'filters' => ['quote-author', 'quote-text']
        ],
        'text' => [
            'required' => ['post-text'],
            'filters' => ['post-text']
        ],
        'video' => [
            'required' => ['video-url'],
            'filters' => ['video-url']
        ]
    ];

    foreach ($form_options as &$option) {
        $option['required'] = array_merge(['title'], $option['required']);
        $option['filters'] = array_merge(['title', 'tags'], $option['filters']);
        $option['filters'] = array_fill_keys($option['filters'], FILTER_DEFAULT);
    }
    unset($option);

    // Определяем правила валидации полей формы
    $rules = [
        'title' => function ($value) {
            return validate_max_length($value, MAX_TITLE_LENGTH);
        },
        'photo-url' => function ($value) {
            return validate_max_length($value, MAX_URL_LENGTH) ?? validate_photo_url($value);
        },
        'post-link' => function ($value) {
            return validate_max_length($value, MAX_URL_LENGTH) ?? validate_url($value);
        },
        'post-text' => function ($value) {
            return validate_max_length($value, MAX_TEXT_LENGTH);
        },
        'quote-text' => function ($value) {
            return validate_max_length($value, MAX_QUOTE_LENGTH);
        },
        'quote-author' => function ($value) {
            return validate_max_length($value, MAX_NAME_LENGTH);
        },
        'video-url' => function ($value) {
            return validate_max_length($value, MAX_URL_LENGTH) ?? validate_video_url($value);
        },
        'tags' => function ($value) {
            return validate_hashtag($value);
        },
    ];

    $active_type = get_post_value('post-type');
    $options = $form_options[$active_type];
    $required = $options['required'];
    $post_filters = $options['filters'];
    $post = filter_input_array(INPUT_POST, $post_filters, true);

    foreach ($post as $key => $value) {
        if (in_array($key, $required)) {
            $errors[$key] = validate_filled($value);
        }
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            if ($key === 'photo-url') {
                $errors['file'] = validate_file($_FILES['file']);
                $errors[$key] = $rule($value);
            } elseif (!empty($value)) {
                $errors[$key] = $rule($value);
            }
        }
    }
    $errors = array_diff($errors, array(''));

    if (empty($errors) && $active_type === 'photo') {
        // Если загружен файл и нет ошибок, сохраняем его в папку uploads
        if (!empty($_FILES['file']['name'])) {
            $file_photo = $_FILES['file'];
            $path = replace_file_to_uploads($file_photo);
            $post['photo-url'] = $path;
        } else {
            // Если есть интернет-ссылка, скачиваем файл и сохраняем в папку uploads
            $tmp_path = save_file_to_uploads($value);
            $file_type = mime_content_type($tmp_path);
            $file_ext = get_file_ext($file_type);
            $path = $tmp_path . $file_ext;
            rename($tmp_path, $path);
            $post['photo-url'] = $path;
        }
    }

    if (empty($errors)) {
        // Определяем id активного типа поста и добавляем его в массив $post
        foreach ($types as $value) {
            if ($value['type_class'] === $active_type) {
                $type_id = (int)$value['id'];
                break;
            }
        }
        $post['type_id'] = $type_id;

        // Сохраняем значение поля хэштеги в отдельную переменную, а из массива $post это поле удаляем
        $hash_str = trim($post['tags']);
        unset($post['tags']);

        // Переименовываем ключи 'post-text' и 'quote-text' => 'text'
        // 'photo-url', 'video-url', 'post-link' => 'url'
        $text_keys = ['post-text', 'quote-text'];
        $url_keys = ['photo-url', 'video-url', 'post-link'];
        $post = rename_key($text_keys, 'text', $post);
        $post = rename_key($url_keys, 'url', $post);

        $empty_data = array_fill_keys(['title', 'url', 'text', 'quote-author'], null);
        $empty_data['user_id'] = $user['id'];

        $data_post = array_merge($empty_data, $post);

        // Создаем запрос на запись нового поста
        $sql_add_post = "INSERT INTO post (
            post_title,
            post_url,
            post_text,
            quote_author,
            watch_count,
            user_id,
            type_id)
        VALUES (?, ?, ?, ?, 0, ?, ?)";

        // Создаем подготовленное выражение и отправляем запрос на на запись нового поста
        $stmt = db_get_prepare_stmt($con, $sql_add_post, $data_post);
        $result = mysqli_stmt_execute($stmt);

        // В случае успеха отправляем запросы на запись хэштегов к посту
        if ($result) {
            $post_id = mysqli_insert_id($con);
            $hashtags = $hash_str ? explode(' ', str_replace('#', '', $hash_str)) : [];
            // Запрос на получение id хэштега
            $sql_get_hashid = "SELECT id FROM hashtag
            WHERE hashtag_title = ?";

            // Запрос на запись нового хэштега
            $sql_add_hash = "INSERT INTO hashtag
            SET hashtag_title = ?";

            // Запрос на запись связи пост - хэштег
            $sql_add_bond = "INSERT INTO post_hashtag
            SET post_id = ?, hashtag_id = ?;";

            foreach ($hashtags as $hash) {
                $data_hash = array($hash);
                // Проверяем, есть ли такой хэштег в таблице
                $result = fetch_sql_response($con, $sql_get_hashid, $data_hash);
                $result = mysqli_fetch_assoc($result);
                if (isset($result['id'])) {
                    $hash_id = $result['id'];
                } else {
                    // Если нет - создаем запрос на запись нового хэштега и получаем его id
                    $stmt = db_get_prepare_stmt($con, $sql_add_hash, $data_hash);
                    $result = mysqli_stmt_execute($stmt);
                    if ($result) {
                        $hash_id = mysqli_insert_id($con);
                    }
                }
                // В случае успеха отправляем запрос на запись в таблицу связей хэштегов и постов
                if ($hash_id) {
                    $data_hash = array($post_id, $hash_id);
                    $stmt = db_get_prepare_stmt($con, $sql_add_bond, $data_hash);
                    mysqli_stmt_execute($stmt);
                }
            }
            //  Если доступен почтовый сервер, отправляем сообщения подписчикам о публикации нового поста
            try {
                $transport->start();
                $sql = "SELECT
                    subscriber_id,
                    username,
                    email
                FROM subscription
                INNER JOIN user
                    ON user.id = subscriber_id
                WHERE user_id = ?;";

                $author_id = $user['id'];
                $result = fetch_sql_response($con, $sql, [$author_id]);
                if ($result && mysqli_num_rows($result)) {
                    $subscribers = mysqli_fetch_all($result, MYSQLI_ASSOC);

                    $text_message = 'Пользователь ' . $user['username'] . 'только что опубликовал новую запись „' . $data_post['title'] . '“. Посмотрите её на странице пользователя:';
                    $author_url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . '/profile.php?id=' . $user['id'];

                    foreach ($subscribers as $subscriber) {
                        $recipient = [];
                        $recipient[$subscriber['email']] = $subscriber['username'];
                        $message = new Swift_Message();
                        $message->setFrom(['keks@phpdemo.ru' => 'keks@phpdemo.ru']);
                        $message->setTo($recipient);
                        $message->setSubject('Новая публикация от пользователя' . $user['username']);

                        $message_content = include_template('subscriber-email.php', [
                            'recipient_name' => $subscriber['username'],
                            'text' => $text_message,
                            'url' => $author_url
                        ]);

                        $message->setBody($message_content, 'text/html');
                        $result = $mailer->send($message);
                    }
                }
            } catch (\Swift_TransportException $ex) {
                $_SESSION['email_error'] = $ex->getMessage();
            }
            // Перенаправляем на страницу просмотра поста
            header("Location: http://readme/post.php?id=" . $post_id);
            exit;
        } else {
            $errors['mysql'] = "Не удалось сохранить ваш пост";
        }
    }
}

$label = [
    'photo-url' => 'Ссылка из интернета',
    'post-link' => 'Ссылка',
    'post-text' => 'Текст поста',
    'quote-author' => 'Автор',
    'quote-text' => 'Текст цитаты',
    'tags' => 'Теги',
    'title' => 'Заголовок',
    'file' => 'Загрузка фото',
    'video-url' => 'Ссылка youtube',
    'mysql' => 'Ошибка на сервере'
];

$title_field = include_template('field-title.php', [
    'label' => $label['title'],
    'error' => $errors['title'] ?? ''
]);

$tags_field = include_template('field-tags.php', [
    'label' => $label['tags'],
    'error' => $errors['tags'] ?? ''
]);

$invalid_block = '';
if (count($errors)) {
    $invalid_block = include_template('invalid-block.php', [
        'errors' => $errors,
        'label' => $label,
    ]);
}

$content = include_template('adding-post.php', [
    'types' => $types,
    'active_type' => $active_type,
    'title_field' => $title_field,
    'tags_field' => $tags_field,
    'label' => $label,
    'errors' => $errors,
    'invalid_block' => $invalid_block,
]);

$title = 'readme: добавление публикации';

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user' => $user,
]);
print($layout);
