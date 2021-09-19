<?php
require_once('helpers.php');
$is_auth = rand(0, 1);
$user_name = 'Юлия'; // укажите здесь ваше имя
$title = 'readme: моя лента';

$content = include_template('feed.php', []);

$layout = include_template('layout.php', ['page_content' => $content]);
print($layout);
