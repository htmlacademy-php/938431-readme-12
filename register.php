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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = $_POST;
    $required = ['email', 'login', 'password', 'password-repeat'];

    // Проверяем заполненность обязательных полей
    foreach ($required as $field) {
        if (empty($form[$field])) {
            $errors[$field] = 'Это поле должно быть заполнено';
        }
    }
    // Проверяем, что в базе нет пользователя с введенным email
    if (empty($errors)) {
        $email = filter_var($form['email'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            $sql = "SELECT id FROM user WHERE email = '$email';";
            $result = mysqli_query($con, $sql);
            if (mysqli_num_rows($result) > 0) {
                $errors['email'] = 'Пользователь с этим email уже зарегистрирован';
            }
        } else {
            $errors['email'] = 'Введен некорректный email';
        }
    }

    // Проверяем, что пароль и его повтор совпадают
    if (empty($errors)) {
        if ($form['password'] != $form['password-repeat']) {
            $errors['password'] = 'Введенные пароли не совпадают';
        }
    }
    // Проверяем тип и размер загруженного файла
    if (empty($errors) and !empty($_FILES['file']['name'])) {
        $file = $_FILES['file'];
        $error = validate_file($file);
        if ($error) {
            $errors['file'] = $error;
        }
    }

    if (empty($errors)) {
        // Если загружен файл перемещаем его в папку uploads
        if (!empty($_FILES['file']['name'])) {
            $path = replace_file_to_uploads($_FILES['file']);
        }
        // Создаем запрос на запись нового пользователя
        $login = filter_var($form['login'], FILTER_DEFAULT);
        $password = password_hash($form['password'], PASSWORD_DEFAULT);
        $avatar = $path ?? null;
        $sql = "INSERT INTO user (
            email,
            password,
            avatar,
            username
        )
        VALUES (?, ?, ?, ?);";
        $stmt = db_get_prepare_stmt($con, $sql, [$email, $password, $avatar, $login]);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Записываем данные нового пользователя в сессию
            $new_user_id = mysqli_insert_id($con);
            $sql = "SELECT * FROM user WHERE id = ?";
            $result = fetch_sql_response($con, $sql, [$new_user_id]);
            if ($result && mysqli_num_rows($result)) {
                $new_user = mysqli_fetch_assoc($result);
                $new_user['message_count'] = 0;
                $_SESSION['user'] = $new_user;
                header("Location: /");
            }
        } else {
            $errors['mysql'] = 'Не удалось зарегистрировать аккаунт';
        }
    }
}
$label = [
    'email' => 'Электронная почта',
    'login' => 'Логин',
    'password' => 'Пароль',
    'password-repeat' => 'Повтор пароля',
    'file' => 'Загрузка фото',
    'mysql' => 'Ошибка сохранения на сервер'
];
$invalid_block = '';

if (count($errors)) {
    $invalid_block = include_template('invalid-block.php', [
        'errors' => $errors,
        'label' => $label,
    ]);
}

$content = include_template('registration.php', [
    'label' => $label,
    'errors' => $errors,
    'invalid_block' => $invalid_block,
]);

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => 'readme: Регистрация на сайте',
    'user' => null,
]);

print($layout);
