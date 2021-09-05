<?php
require_once('helpers.php');

$title = 'readme: добавление публикации';
$is_auth = rand(0, 1);
$user_name = 'Юлия'; // укажите здесь ваше имя
$form_options = [
    'link' => [
        'required' => ['title', 'post-link'],
        'filters' => ['title' => FILTER_DEFAULT, 'post-link' => FILTER_DEFAULT]
    ],
    'photo' => [
        'required' => ['title'],
        'filters' => ['title' => FILTER_DEFAULT]
    ],
    'quote' => [
        'required' => ['title', 'quote-text', 'quote-author'],
        'filters' => ['title' => FILTER_DEFAULT, 'quote-author' => FILTER_DEFAULT, 'quote-text' => FILTER_DEFAULT]
    ],
    'text' => [
        'required' => ['title', 'post-text'],
        'filters' => ['title' => FILTER_DEFAULT, 'post-text' => FILTER_DEFAULT]
    ],
    'video' => [
        'required' => ['title', 'video-url'],
        'filters' => ['title' => FILTER_DEFAULT, 'video-url' => FILTER_DEFAULT]
    ]
];

$rules = [
    'photo-url' => function($value) {
        return validate_photo_url($value);
    },
    'post-link' => function($value) {
        return validate_url($value);
    },
    'video-url' => function($value) {
        return validate_video_url($value);
    }
];

$empty_data = [
    'title' => NULL,
    'photo_url' => NULL,
    'post-link' => NULL,
    'video-url' => NULL,
    'post-text' => NULL,
    'quote-author' => NULL,
    'quote-text' => NULL
];

$sql_add_post = "INSERT INTO post (
  p_title,
  url_img,
  url_site,
  url_video,
  p_text,
  quote_author,
  quote_text,
  watch_count,
  user_id,
  type_id)
VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1, ?);";

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


$title_field = include_template('field-title.php');
$tags_field = include_template('field-tags.php');

$content = include_template('adding-post.php', ['types' => $types, 'active_type' => $active_type, 'title_field' => $title_field, 'tags_field' => $tags_field]);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $active_type = filter_input(INPUT_POST, 'post-type');
    $options = $form_options[$active_type];
    $required = $options['required'];
    $p_filters = $options['filters'];
    $errors = [];
    $post = filter_input_array(INPUT_POST, $p_filters, true);

    foreach ($post as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
        if (in_array($key, $required) and empty($value)) {
            $errors[$key] = 'Это поле должно быть заполнено.';
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
        $data_post = array_merge($empty_data, $post);

        // Создаем подготовленное выражение и отправляем запрос на на запись нового поста
        $stmt = db_get_prepare_stmt($con, $sql_add_post, $data_post);
        $result = mysqli_execute($stmt);

        // В случае успеха перенаправляем на страницу просмотра поста
        if ($result) {
            $post_id = mysqli_insert_id($con);
            header("Location: post.php?id=" . $post_id);
        }
    }
}

$layout = include_template('layout.php', ['page_content' => $content, 'page_title' => $title, 'user_name' => $user_name, 'is_auth' => $is_auth]);
print($layout);
