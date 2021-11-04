<?php
session_start();
$user = $_SESSION['user'] ?? null;

// Перенаправляем на главную страницу анонимных пользователей
if (!$user) {
    header('Location: /index.php');
    exit;
}

require_once('helpers.php');
require_once('mail-init.php');
define('MAX_TEXT_LENGTH', 65535);

// Устанавливаем соединение с базой readme
$con = set_connection();

$errors = [];
// Проверяем был ли отправлено сообщение
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = ['active-user-id' => FILTER_DEFAULT, 'message' => FILTER_DEFAULT];
    $message_post = filter_input_array(INPUT_POST, $filters, true);
    // Проверяем поле с текстом сообщения на заполненность
    $message = trim($message_post['message']);
    $errors['message'] = validate_filled($message) ?? validate_max_length($message, MAX_TEXT_LENGTH);
    $errors = array_diff($errors, array(''));
    $receiver_id = $message_post['active-user-id'];

    // Если нет ошибок валидации, проверяем, что пользователь, которому адресовано сообщение есть в базе и не равен автору сообщения
    if (empty($errors) and $message_post['active-user-id'] === $user['id']) {
        $errors['message'] = 'Невозможно отправить сообщение самому себе.';
    }
    if (empty($errors)) {
        $sql = "SELECT * FROM user WHERE id = ?;";
        $data = [$message_post['active-user-id']];
        $result = fetch_sql_response($con, $sql, $data);
        if (mysqli_num_rows($result) === 0) {
            $errors['message'] = 'Пользователь не найден';
        }
    }
    if (empty($errors)) {
        // Создаем запрос на запись сообщения в базу данных
        $sql_message = "INSERT INTO message (message_text, sender_id, receiver_id)
            VALUES (?,?,?);";
        $data_message = array($message, $user['id'], $message_post['active-user-id']);

        $stmt = db_get_prepare_stmt($con, $sql_message, $data_message);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
            $errors['message'] = 'Не удалось сохранить ваше сообщение.';
        } else {
            header("Location: http://readme/messages.php?id=" . $receiver_id);
        }
    }
}

$logged_user_id = $user['id'];
// Получаем список пользователей, с которыми есть переписка
$sql = "SELECT DISTINCT
            user.id,
            avatar,
            username
        FROM user
        WHERE id IN (SELECT DISTINCT sender_id FROM message WHERE receiver_id = ?)
            OR id IN (SELECT DISTINCT receiver_id FROM message WHERE sender_id = ?)";
$result = fetch_sql_response($con, $sql, [$logged_user_id, $logged_user_id]);

$recipients = [];
if ($result && mysqli_num_rows($result)) {
    $recipients = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Получаем id выбранного пользователя - участника переписки из массива $_GET
$active_user_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT) ?? 0;

// Создаем запрос, на изменение статуса сообщений от выбранного пользователя на "Прочитано"
if ($active_user_id) {
    $sql = "UPDATE message
            SET is_new = FALSE
            WHERE sender_id = ?
                AND receiver_id = ?
                AND is_new = TRUE";
    $stmt = db_get_prepare_stmt($con, $sql, [$active_user_id, $logged_user_id]);
    $result = mysqli_stmt_execute($stmt);

    // Обновляем данные о количестве непрочитанных сообщений в сессии
    $sql = "SELECT COUNT(id) AS message_count
            FROM message
            WHERE receiver_id = '$logged_user_id'
                AND is_new = TRUE";
    $result = mysqli_query($con, $sql);
    $user['message_count'] = $result ? mysqli_fetch_row($result)[0] : 0;
    $_SESSION['user'] = $user;
}

// Получим данные последнего сообщения для каждого участника переписки
foreach ($recipients as &$recipient) {
    $sql = "SELECT
                date_add,
                message_text,
                sender_id
            FROM message
            WHERE sender_id IN (?, ?)
                AND receiver_id IN (?, ?)
            ORDER BY date_add DESC
            LIMIT 1";

    $data = array($recipient['id'], $logged_user_id, $recipient['id'], $logged_user_id);
    $result = fetch_sql_response($con, $sql, $data);
    $message = mysqli_fetch_assoc($result);

    // Получим количество непрочитанных сообщений от каждого участника переписки
    $sql = "SELECT COUNT(id) AS new_count
            FROM message
            WHERE sender_id = ?
                AND receiver_id = ?
                AND is_new = TRUE";

    $data = array_slice($data, 0, 2);
    $result = fetch_sql_response($con, $sql, $data);
    $new_count = mysqli_fetch_assoc($result);

    // Объединим полученные данные
    $recipient = array_merge($recipient, $message, $new_count);
}

unset($recipient);

// Отсортируем полученный массив по полю с датой последнего сообщения
function compare_date($left, $right)
{
    if ($left['date_add'] === $right['date_add']) {
        return 0;
    }
    return ($left['date_add'] > $right['date_add']) ? -1 : 1;
};

usort($recipients, 'compare_date');

if (!empty($recipients)) {
    // Добавим каждому элементу массива поле с датой в строковом формате
    $months = array(1 => 'янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек');

    foreach ($recipients as &$recipient) {
        $message_date = strtotime($recipient['date_add']);
        if (date('d-m-Y') === date('d-m-Y', $message_date)) {
            $recipient['format_date'] = date('H:i', $message_date);
        } else {
            $recipient['format_date'] = date('d ' . $months[date('n', $message_date)]);
        }
    }
    unset($recipient);
}

$messages = [];

// Если выбранного пользователя нет в массиве $recipients, создаем запрос и добавляем данные пользователя в начало массива
if (empty($recipients) or !in_array($active_user_id, array_column($recipients, 'id'))) {
    $sql = "SELECT
        id,
        avatar,
        username FROM user WHERE id = ?;";
    $result = fetch_sql_response($con, $sql, [$active_user_id]);
    if ($result && mysqli_num_rows($result)) {
        $active_user = mysqli_fetch_assoc($result);
        $data_keys = [
            'id',
            'avatar',
            'username',
            'date_add',
            'message_text',
            'sender_id',
            'new_count',
            'format_date',
        ];
        $empty_data = array_fill_keys($data_keys, null);
        $active_user = array_merge($empty_data, $active_user);
        array_unshift($recipients, $active_user);
    }
} else {
    // Если с пользователем есть чат, создаем запрос на получение сообщений
    $sql = "SELECT
    message.*,
    user.id AS user_id,
    avatar,
    username
    FROM message
    INNER JOIN user
        ON user.id = sender_id
    WHERE (sender_id = ? AND receiver_id = ?)
        OR (receiver_id = ? AND sender_id = ?)
    ORDER BY date_add;";
    $data = [$active_user_id, $logged_user_id, $active_user_id, $logged_user_id];
    $result = fetch_sql_response($con, $sql, $data);
    if ($result && mysqli_num_rows($result)) {
        $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}


$content = include_template('user-messages.php', [
    'active_user_id' => (int) $active_user_id,
    'errors' => $errors,
    'logged_user' => $user,
    'messages' => $messages,
    'recipients' => $recipients,
]);

$title = 'readme: личные сообщения';

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user' => $user,
]);
print($layout);
