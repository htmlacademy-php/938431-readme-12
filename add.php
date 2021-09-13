<?php
require_once('helpers.php');

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
foreach ($types AS &$type) {
    $type['url'] = update_query_params('type', $type['t_class']);
};
unset($type);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $opts = [
        'required' => ['title'],
        'filters' => ['title' => FILTER_DEFAULT, 'tags' => FILTER_DEFAULT]
    ];

    $form_options = [
        'link' => [
            'required' => array_merge($opts['required'], ['post-link']),
            'filters' => array_merge($opts['filters'], ['post-link' => FILTER_DEFAULT])
        ],
        'photo' => [
            'required' => $opts['required'],
            'filters' => array_merge($opts['filters'], ['photo-url' =>  FILTER_DEFAULT])
        ],
        'quote' => [
            'required' => array_merge($opts['required'], ['quote-text', 'quote-author']),
            'filters' => array_merge($opts['filters'], ['quote-author' => FILTER_DEFAULT, 'quote-text' => FILTER_DEFAULT])
        ],
        'text' => [
            'required' => array_merge($opts['required'], ['post-text']),
            'filters' => array_merge($opts['filters'], ['post-text' => FILTER_DEFAULT])
        ],
        'video' => [
            'required' => array_merge($opts['required'], ['video-url']),
            'filters' => array_merge($opts['filters'], ['video-url' => FILTER_DEFAULT])
        ]
    ];

    $rules = [
        'photo-url' => function($value) {
            return validate_photo_url($value);
        },
        'post-link' => function($value) {
            return validate_url($value);
        },
        'tags' => function($value) {
            return validate_hashtag($value);
        },
        'video-url' => function($value) {
            return validate_video_url($value);
        }
    ];

    $active_type = get_post_value('post-type');
    $options = $form_options[$active_type];
    $required = $options['required'];
    $p_filters = $options['filters'];
    $post = filter_input_array(INPUT_POST, $p_filters, true);

    foreach ($post as $key => $value) {
        if (in_array($key, $required)) {
            $errors[$key] = validate_filled($value);
        }
        if (isset($rules[$key]) and !empty($value)) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
        if ($key == 'photo-url' and empty($errors[$key])) {
            // Если загружен файл, проверяем его и сохраняем в папку uploads
            if (!empty($_FILES['file']['name'])) {
                $file_photo = $_FILES['file'];
                $error = validate_file($file_photo['tmp_name'], $file_photo['size']);
                if ($error) {
                    $errors['file'] = $error;
                } else {
                    $file_type = get_file_type($file_photo['tmp_name']);
                    $filename = uniqid() . get_file_ext($file_type);
                    $path = 'uploads/' . $filename;
                    move_uploaded_file($file_photo['tmp_name'], $path);
                    $post['photo-url'] = $path;
                }
            } else {
                // Если есть интернет-ссылка, скачиваем файл, проверяем его и сохраняем в папку uploads
                $tmp_path = save_file_to_uploads($value);
                $errors[$key] = validate_file($tmp_path, filesize($tmp_path));
                if (empty($errors[$key])) {
                    $file_type = get_file_type($tmp_path);
                    $file_ext = get_file_ext($file_type);
                    $path = $tmp_path . $file_ext;
                    rename($tmp_path, $path);
                    $post['photo-url'] = $path;
                }
            }
        }
        $errors = array_diff($errors, array(''));
    }
    if (!$errors) {
        // Определяем id активного типа поста и добавляем его в массив $post
        foreach ($types as $value) {
            if ($value['t_class'] == $active_type) {
                $type_id = (int) $value['id'];
                break;
            }
        }
        $post['type_id'] = $type_id;

        // Сохраняем значение поля хэштеги в отдельную переменную, а из массива $post это поле удаляем
        $hash_str = $post['tags'];
        unset($post['tags']);

        // Переименовываем ключи 'post-text' и 'quote-text' => 'text'
        // 'photo-url', 'video-url', 'post-link' => 'url'
        $text_keys = ['post-text', 'quote-text'];
        $url_keys = ['photo-url', 'video-url', 'post-link'];
        $post = rename_key($text_keys, 'text', $post);
        $post = rename_key($url_keys, 'url', $post);

        $empty_data = [
            'title' => NULL,
            'url' => NULL,
            'text' => NULL,
            'quote-author' => NULL
        ];

        $data_post = array_merge($empty_data, $post);

        // Создаем запрос на запись нового поста
        $sql_add_post = "INSERT INTO post (
            p_title,
            p_url,
            p_text,
            quote_author,
            watch_count,
            user_id,
            type_id)
        VALUES (?, ?, ?, ?, 0, 1, ?);";

        // Создаем подготовленное выражение и отправляем запрос на на запись нового поста
        $stmt = db_get_prepare_stmt($con, $sql_add_post, $data_post);
        $result = mysqli_stmt_execute($stmt);

        // В случае успеха отправляем запросы на запись хэштегов к посту
        if ($result) {
            $post_id = mysqli_insert_id($con);
            $hashtags = explode(' ', str_replace('#', '', $hash_str));

            // Запрос на получение id хэштега
            $sql_get_hashid = "SELECT id FROM hashtag
            WHERE title = ?;";

            // Запрос на запись нового хэштега
            $sql_add_hash = "INSERT INTO hashtag
            SET title = ?;";

            // Запрос на запись связи пост - хэштег
            $sql_add_bond = "INSERT INTO post_hashtag
            SET post_id = ?, hash_id = ?;";

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
        // Перенаправляем на страницу просмотра поста
            header("Location: http://readme/post.php?id=" . $post_id);
            exit;
        } else {
            $errors[] = "Ошибка на сервере. Не удалось сохранить ваш пост";
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
    'video-url' => 'Ссылка youtube'
];

$title_field = include_template('field-title.php', [
    'label' => $label['title'],
    'error' => $errors['title'] ?? ''
]);

$tags_field = include_template('field-tags.php', [
    'label' => $label['tags'],
    'error' => $errors['tags'] ?? ''
]);

$invalid_block = include_template('invalid-block.php', [
    'errors' => $errors,
    'label' => $label
]);

$content = include_template('adding-post.php', [
    'types' => $types,
    'active_type' => $active_type,
    'title_field' => $title_field,
    'tags_field' => $tags_field,
    'label' => $label,
    'errors' => $errors,
    'invalid_block' => $invalid_block
]);

$title = 'readme: добавление публикации';
$is_auth = rand(0, 1);
$user_name = 'Юлия'; // укажите здесь ваше имя

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user_name' => $user_name,
    'is_auth' => $is_auth
]);
print($layout);
