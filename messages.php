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

// Устанавливаем соединение с базой readme
$con = set_connection();

$logged_user_id = $user['id'];
// Получаем список пользователей, с которыми есть переписка
$sql = "SELECT DISTINCT
    user.id,
    u_avatar,
    u_name
FROM user
WHERE id IN (SELECT DISTINCT sender_id FROM message WHERE receiver_id = ?)
    OR id IN (SELECT DISTINCT receiver_id FROM message WHERE sender_id = ?);";
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
            AND is_new = TRUE;";
    $stmt = db_get_prepare_stmt($con, $sql, [$active_user_id, $logged_user_id]);
    $result = mysqli_stmt_execute($stmt);

    // Обновляем данные о количестве непрочитанных сообщений в сессии
    $sql = "SELECT COUNT(id) AS m_count
        FROM message
        WHERE receiver_id = '$logged_user_id'
            AND is_new = TRUE;";
    $result = mysqli_query($con, $sql);
    $user['m_count'] = $result ? mysqli_fetch_assoc($result)['m_count'] : 0;
    $_SESSION['user'] = $user;
}

// Получим данные последнего сообщения для каждого участника переписки
foreach ($recipients as &$recipient) {
    $sql = "SELECT
        dt_add,
        m_content,
        sender_id
    FROM message
        WHERE sender_id IN (?, ?)
            AND receiver_id IN (?, ?)
        ORDER BY dt_add DESC
        LIMIT 1;";

    $data = array($recipient['id'], $logged_user_id, $recipient['id'], $logged_user_id);
    $result = fetch_sql_response($con, $sql, $data);
    $message = mysqli_fetch_assoc($result);

    // Получим количество непрочитанных сообщений от каждого участника переписки
    $sql = "SELECT COUNT(id) AS new_count
    FROM message
    WHERE sender_id = ?
        AND receiver_id = ?
        AND is_new = TRUE;";

    $data = array_slice($data, 0, 2);
    $result = fetch_sql_response($con, $sql, $data);
    $new_count = mysqli_fetch_assoc($result);

    // Объединим полученные данные
    $recipient = array_merge($recipient, $message, $new_count);
}

unset($recipient);

// Отсортируем полученный массив по полю с датой последнего сообщения
function compare_date($a, $b) {
    if ($a['dt_add'] == $b['dt_add']) {
        return 0;
    }
    return ($a['dt_add'] > $b['dt_add']) ? -1 : 1;;
};

usort($recipients, 'compare_date');

if (!empty($recipients)) {
    // Добавим каждому элементу массива поле с датой в строковом формате
    $months = array(1 => 'янв', 'фев', 'мар', 'апр', 'мая', 'июня', 'июля', 'авг', 'сент', 'окт', 'нояб', 'дек');

    foreach ($recipients as &$recipient) {
        $message_dt = strtotime($recipient['dt_add']);
        if (date('d-m-Y') === date('d-m-Y', $message_dt)) {
            $recipient['format_dt'] = date('H:i', $message_dt);
        } else {
            $recipient['format_dt'] = date('d ' . $months[date('n', $message_dt)]);
        }
    }
    unset($recipient);
}

$messages = [];

// Если выбранного пользователя нет в массиве $recipients, создаем запрос и добавляем данные пользователя в начало массива
if (empty($recipients) or !in_array($active_user_id, array_column($recipients, 'id'))) {
    $sql = "SELECT
        id,
        u_avatar,
        u_name FROM user WHERE id = ?;";
    $result = fetch_sql_response($con, $sql, [$active_user_id]);
    if ($result && mysqli_num_rows($result)) {
        $active_user = mysqli_fetch_assoc($result);
        $empty_data = [
            'id' => null,
            'u_avatar' => null,
            'u_name' => null,
            'dt_add' => null,
            'm_content' => null,
            'sender_id' => null,
            'new_count' => null,
            'format_dt' => null
        ];
        $active_user = array_merge($empty_data, $active_user);
        array_unshift($recipients, $active_user);
    }
} else {
    // Если с пользователем есть чат, создаем запрос на получение сообщений
    $sql = "SELECT
    message.*,
    user.id AS u_id,
    u_avatar,
    u_name
    FROM message
    INNER JOIN user
        ON user.id = sender_id
    WHERE (sender_id = ? AND receiver_id = ?)
        OR (receiver_id = ? AND sender_id = ?)
    ORDER BY dt_add;";
    $data = [$active_user_id, $logged_user_id, $active_user_id, $logged_user_id];
    $result = fetch_sql_response($con, $sql, $data);
    if ($result && mysqli_num_rows($result)) {
        $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

$errors = [];
// Проверяем был ли отправлено сообщение
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $filters = ['active-user-id' => FILTER_DEFAULT, 'message' => FILTER_DEFAULT];
    $m_post = filter_input_array(INPUT_POST, $filters, true);
    // Проверяем поле с текстом сообщения на заполненность
    $message = trim($m_post['message']);
    $errors['message'] = validate_filled($message);
    $errors = array_diff($errors, array(''));
    $receiver_id = $m_post['active-user-id'];

    // Если нет ошибок валидации, проверяем, что пользователь, которому адресовано сообщение есть в базе и не равен автору сообщения
    if (empty($errors) and $m_post['active-user-id'] === $user['id']) {
        $errors['message'] = 'Невозможно отправить сообщение самому себе.';
    }
    if (empty($errors)) {
        $sql = "SELECT * FROM user WHERE id = ?;";
        $data = [$m_post['active-user-id']];
        $result = fetch_sql_response($con, $sql, $data);
        if (mysqli_num_rows($result) === 0) {
            $errors['message'] = 'Пост не найден. Не удалось записать комментарий';
        } else {
            // Создаем запрос на запись сообщения в базу данных
            $sql_message = "INSERT INTO message (m_content, sender_id, receiver_id)
                VALUES (?,?,?);";
            $data_message = array($message, $user['id'], $m_post['active-user-id']);

            $stmt = db_get_prepare_stmt($con, $sql_message, $data_message);
            $result = mysqli_stmt_execute($stmt);

            if (!$result) {
                $errors['message'] = 'Не удалось сохранить ваш комментарий.';
            } else {
                header("Location: http://readme/messages.php?id=" . $receiver_id);
            }
        }
    }

}


$content = include_template('user-messages.php', [
    'active_user_id' => $active_user_id,
    'errors' => $errors,
    'logged_user' => $user,
    'messages' => $messages,
    'recipients' => $recipients
]);

$title = 'readme: личные сообщения';

$layout = include_template('layout.php', [
    'page_content' => $content,
    'page_title' => $title,
    'user' => $user
]);
print($layout);
