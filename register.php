<?php
require_once('helpers.php');

// Устанавливаем соединение с базой readme
$con = set_connection();

$content = include_template('registration.php', []);

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => 'readme: Регистрация на сайте',
    'user_name' => '',
    'is_auth' => false
]);
print($layout);
