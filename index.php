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

    // Проверяем заполненность обязательных полей
    foreach ($form as $key => $value) {
        if (empty($form[$key])) {
            $errors[$key] = 'Это поле должно быть заполнено';
        }
    }

    // Проверяем существование пользователя с введенным email
    if (empty($errors)) {
        $email = filter_var($form['login'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            $sql = "SELECT* FROM user WHERE email = '$email';";
            $result = mysqli_query($con, $sql);
            $user = $result ? mysqli_fetch_array($result, MYSQLI_ASSOC) : null;
            if ($user) {
                // Проверяем пароль и открываем сессию
                if (password_verify($form['password'], $user['u_password'])) {
                    // Создаем запрос на количество непрочитанных сообщений
                    $user_id = $user['id'];
                    $sql = "SELECT COUNT(id) AS m_count
                        FROM message
                        WHERE receiver_id = '$user_id'
                        AND is_new = TRUE;";
                    $result = mysqli_query($con, $sql);
                    $user['m_count'] = $result ? mysqli_fetch_assoc($result)['m_count'] : 0;

                    $_SESSION['user'] = $user;
                    header("Location: /feed.php");
                } else {
                    $errors['password'] = 'Неверный пароль';
                }
            } else {
                $errors['login'] = 'Пользователь с таким email не найден';
            }
        } else {
            $errors['login'] = 'Введен некорректный email';
        }
    }
}
$layout = include_template('main.php', ['errors' => $errors]);
print($layout);
