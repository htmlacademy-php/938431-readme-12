<?php
require_once('helpers.php');
require_once('init.php');

// Перенаправляем на главную страницу незалогиненных пользователей
if (!isset($user)) {
    header('Location: /index.php');
    exit();
}

$title = 'readme: моя лента';

$content = include_template('feed.php', []);

$layout = include_template('layout.php', ['page_content' => $content, 'user' => $user]);
print($layout);
