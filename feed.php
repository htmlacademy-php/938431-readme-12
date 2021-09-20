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

// Устанавливаем соединение с базой readme
$con = set_connection();
// Перенаправляем на главную страницу незалогиненных пользователей
if (!isset($user)) {
    header('Location: /index.php');
    exit();
}

$title = 'readme: моя лента';

$content = include_template('feed.php', []);

$layout = include_template('layout.php', ['page_content' => $content, 'user' => $user]);
print($layout);
