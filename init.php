<?php
session_start();

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

// Устанавливаем соединение с базой readme
$con = set_connection();
