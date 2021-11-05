<?php
session_start();
$user = $_SESSION['user'] ?? null;

// Залогиненных пользователей перенаправляем на страницу Моя лента
if ($user) {
    header('Location: /feed.php');
    exit;
}

require_once('helpers.php');

// Устанавливаем соединение с базой readme
$con = set_connection();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;
    $errors = authorize_user($form, $con);
    if (empty($errors)) {
        header("Location: /feed.php");
    }
}

$label = [
    'login' => 'Электронная почта',
    'password' => 'Пароль',
];

$invalid_block = '';

if (count($errors)) {
    $invalid_block = include_template('invalid-block.php', [
        'errors' => $errors,
        'label' => $label,
    ]);
}

$content = include_template('user-login.php', [
    'errors' => $errors,
    'invalid_block' => $invalid_block,
]);

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => 'readme: авторизация',
    'user' => null,
]);

print($layout);
