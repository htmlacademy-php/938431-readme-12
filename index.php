<?php
session_start();
$user = $_SESSION['user'] ?? null;

// Перенаправляем на страницу Моя лента авторизованных пользователей
if ($user) {
    header("Location: /feed.php");
    exit;
}

require_once('helpers.php');

// Устанавливаем соединение с базой readme
$con = set_connection();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = $_POST;
    $errors = authorize_user($form, $con);
    if (empty($errors)) {
        header("Location: /feed.php");
    }
}
$layout = include_template('main.php', ['errors' => $errors]);
print($layout);
