<?php

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = [])
{
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            } else {
                if (is_string($value)) {
                    $type = 's';
                } else {
                    if (is_double($value)) {
                        $type = 'd';
                    }
                }
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function get_noun_plural_form(int $number, string $one, string $two, string $many): string
{
    $number = (int)$number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template($name, array $data = [])
{
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * Функция проверяет доступно ли видео по ссылке на youtube
 * @param string $url ссылка на видео
 *
 * @return string Ошибку если валидация не прошла
 */
function check_youtube_url($url)
{
    $id = extract_youtube_id($url);

    set_error_handler(function () {
    }, E_WARNING);
    $headers = get_headers('https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=' . $id);
    restore_error_handler();

    if (!is_array($headers)) {
        return "Видео по такой ссылке не найдено. Проверьте ссылку на видео";
    }

    $err_flag = strpos($headers[0], '200') ? 200 : 404;

    if ($err_flag !== 200) {
        return "Видео по такой ссылке не найдено. Проверьте ссылку на видео";
    }

    return false;
}

/**
 * Возвращает код iframe для вставки youtube видео на страницу
 * @param string $youtube_url Ссылка на youtube видео
 * @return string
 */
function embed_youtube_video($youtube_url)
{
    $res = "";
    $id = extract_youtube_id($youtube_url);

    if ($id) {
        $src = "https://www.youtube.com/embed/" . $id;
        $res = '<iframe width="760" height="400" src="' . $src . '" frameborder="0"></iframe>';
    }

    return $res;
}

/**
 * Возвращает img-тег с обложкой видео для вставки на страницу
 * @param string $youtube_url Ссылка на youtube видео
 * @return string
 */
function embed_youtube_cover($youtube_url)
{
    $res = "";
    $id = extract_youtube_id($youtube_url);

    if ($id) {
        $src = sprintf("https://img.youtube.com/vi/%s/mqdefault.jpg", $id);
        $res = '<img alt="youtube cover" width="320" height="120" src="' . $src . '" />';
    }

    return $res;
}

/**
 * Извлекает из ссылки на youtube видео его уникальный ID
 * @param string $youtube_url Ссылка на youtube видео
 * @return array
 */
function extract_youtube_id($youtube_url)
{
    $id = false;

    $parts = parse_url($youtube_url);

    if ($parts) {
        if ($parts['path'] === '/watch') {
            parse_str($parts['query'], $vars);
            $id = $vars['v'] ?? null;
        } else {
            if ($parts['host'] === 'youtu.be') {
                $id = substr($parts['path'], 1);
            }
        }
    }

    return $id;
}

//         ******  Мои функции  ******

define('MB', 1048576); // 1Мб в байтах
define('MAX_FILE_SIZE', 2); // 2Мб
define('MAX_HASHTAG_LENGTH', 50); // Максимальная длина хэштега
define('MAX_COMMENT_LENGTH', 255); // Максимальная длина строки типа VARCHAR в MySQL
define('MIN_COMMENT_LENGTH', 4); // Минимальная длина комментария
/**
 * Обрезает текст до заданной длины, не обрезая слов, добавляя многоточие в конце текста.
 * @param string $text Исходный текст
 * @param integer $max_length Максимальная длина обрезанного текста
 * @return string
 */
function cut_excerpt($text, $max_length)
{
    $new_text = trim($text);
    $text_length = mb_strlen($new_text);
    $new_text = mb_substr($new_text, 0, $max_length + 1);
    $position = mb_strrpos($new_text, ' ');
    if (!$position) {
        $position = $max_length + 1;
    }
    $new_text = mb_substr($new_text, 0, $position);
    if (mb_strlen($new_text) < $text_length) {
        $new_text .= '...';
    }
    return $new_text;
}

/**
 * Возвращает разметку с отфильтрованным текстом.
 * Если длина текста превышает заданную, обрезает его, фильтрует и добавляет многоточие и ссылку "Читать далее".
 * @param string $text Исходный текст
 * @param integer $max_length Максимальная длина обрезанного текста
 * @return string
 */
function text_template($text, $max_length = 300)
{
    if (mb_strlen($text) <= $max_length) {
        $result = '<p>' . htmlspecialchars($text) . '</p>';
    } else {
        $result = '<p>' . htmlspecialchars(cut_excerpt($text, $max_length)) . '</p>
    <a class="post-text__more-link" href="#">Читать далее</a>';
    }

    return $result;
}

/**
 * Возвращает дату в отформатированном строковом представлении "ДД-MM-ГГГГ ЧЧ:ММ"
 * @param string $date Дата в формате «ГГГГ-ММ-ДД ЧЧ:ММ:СС»
 */
function format_date($date)
{
    $date = date_create($date);

    return date_format($date, 'd-m-Y H:i');
}

/**
 * Возвращает интервал времени между текущей датой и заданной
 * @param string $date_str Дата в прошлом, от которой отсчитывается интервал до текущего момента
 */
function calc_time_interval($date_str)
{
    $target_date = date_create('now');
    $origin_date = date_create($date_str);
    return date_diff($origin_date, $target_date);
}

/**
 * Увеличивает первый параметр на единицу, если второй параметр - истиный
 * @param int $number Исходное число
 * @param bool $condition
 */
function increment_by_condition($number, $condition)
{
    if ($condition) {
        $number++;
    }
    return $number;
}

/**
 * Возвращает строку вида "5 минут назад" на основании переданного временного интервала
 * Период округляется в большую сторону при условии, что количество предыдущих временных периодов в остатке > 0.
 * Пример: если временной интервал равен 1 час 1 минута 45 секунд - результат будет "2 часа назад"
 * Пример: если временной интервал равен 1 час 0 минут 45 секунд - результат будет "1 час назад"
 * @param object $interval Экземпляр DateInterval
 * @return string Временной интервал в человекочитаемом виде
 */
function generate_interval_text($interval)
{
    $week = 7; // 7 суток
    $passed_seconds = $interval->s;
    $passed_minutes = $interval->i;
    $passed_hours = $interval->h;
    $passed_days = $interval->d % $week;
    $passed_weeks = intdiv($interval->d, $week);
    $passed_months = $interval->m;

    if (!$passed_months and !$passed_weeks and !$passed_days and !$passed_hours) {
        $result = increment_by_condition($passed_minutes, $passed_seconds);
        $result .= get_noun_plural_form($result, ' минута', ' минуты', ' минут');
    } elseif (!$passed_months and !$passed_weeks and !$passed_days) {
        $result = increment_by_condition($passed_hours, $passed_minutes);
        $result .= get_noun_plural_form($result, ' час', ' часа', ' часов');
    } elseif (!$passed_months and !$passed_weeks) {
        $result = increment_by_condition($passed_days, $passed_hours);
        $result .= get_noun_plural_form($result, ' день', ' дня', ' дней');
    } elseif (!$passed_months) {
        $result = increment_by_condition($passed_weeks, $passed_days);

        $result .= get_noun_plural_form($result, ' неделя', ' недели', ' недель');
    } else {
        $result = increment_by_condition($passed_months, $passed_weeks);
        $result .= get_noun_plural_form($result, ' месяц', ' месяца', ' месяцев');
    }

    return $result;
}

/**
 * Возвращает строку вида "5 минут назад" на основании переданной даты в прошлом
 * @param string $date Дата в формате «ГГГГ-ММ-ДД ЧЧ:ММ:СС»
 * @return string
 */
function generate_passed_time_text($date)
{
    $interval = calc_time_interval($date);
    return generate_interval_text($interval);
}

/**
 * Устанавливает соединение с базой readme, устанавливает кодировку и Ресурс соединения
 * @return mysqli Ресурс соединения
 */
function set_connection()
{
    // Устанавливаем соединение
    $link = mysqli_connect('localhost', 'mysql', 'mysql', 'readme');

    if (!$link) {
        print('Ошибка подключения: ' . mysqli_connect_error());
        exit;
    }

    // Устанавливаем кодировку
    mysqli_set_charset($link, 'utf8');

    return $link;
}

/**
 * Отправляет запрос и возвращает результат
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_result Объект результата
 */
function fetch_sql_response($link, $sql, $data)
{
    $stmt = db_get_prepare_stmt($link, $sql, $data);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySql: ' . $error);
        exit;
    }
    return $result;
}

/**
 * Отправляет запрос на получение хэштегов к посту с заданным id
 * @param mysqli $link Ресурс соединения
 * @param int $post_id id поста
 *
 * @return array $hashtags Массив хэштегов
 */
function fetch_hashtags($link, $post_id)
{
    $sql = "SELECT hashtag_title
    FROM hashtag
    INNER JOIN post_hashtag
    ON hashtag.id = hashtag_id
    AND post_id = ?;";

    $result = fetch_sql_response($link, $sql, [$post_id]);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Возвращает html шаблон для футера
 * @param string|null $footer_class Расширенный класс для тега <footer>
 * @return string Итоговый HTML
 */
function include_footer($footer_class = '')
{
    return include_template('footer.php', ['footer_class' => $footer_class ?? '']);
}

/**
 * Генерирует html-разметку для карточки поста с учетом его типа
 * (для страниц Моя лента, Результаты поиска, Профиль пользователя)
 * @param array $post Массив с данными о посте
 * @return string Итоговый HTML
 */
function generate_post_template($post)
{
    $markup = '';
    $templates = [
        'link' => 'post-link.php',
        'photo' => 'post-photo.php',
        'text' => 'post-text.php',
        'quote' => 'post-quote.php',
        'video' => 'post-video.php',
    ];
    $type = $post['type_class'];

    $template = $templates[$type] ?? null;
    if (isset($template)) {
        $markup = include_template($template, ['post' => $post]);
    }
    return $markup;
}

/**
 * Возвращает адресную строку для текущего скрипта с переданным параметром запроса
 * @param string $key Ключ параметра запроса
 * @param string|integer $value Значение параметра запроса
 * @return string url-адрес с обновленными параметрами запроса
 */
function update_query_params($key, $value)
{
    $params = $_GET;

    if ($value) {
        $params[$key] = $value;
    } else {
        unset($params[$key]);
    }
    $query = http_build_query($params);
    return "?" . $query;
}

/**
 * Возвращает значение поля формы
 * @param string $name Имя поля формы
 * @return string|null Значение поля
 */
function get_post_value($name)
{
    return filter_input(INPUT_POST, $name) ?? '';
}

/**
 * Возвращает значение поля поиска
 * @param string $name Имя поля формы
 * @return string|null Значение поля
 */
function get_text_value($name)
{
    $search = filter_input(INPUT_GET, $name) ?? '';
    return trim($search);
}

/**
 * Функция - валидатор заполненности поля
 * @param string $value Значение поля формы
 * @return string|null Текст сообщения об ошибке
 */
function validate_filled($value)
{
    $message = null;
    if (empty($value)) {
        $message = "Это поле должно быть заполнено";
    }
    return $message;
}

/**
 * Функция - валидатор минимальной длины текста в поле
 * @param string $value Значение поля формы
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_min_length($value, $min)
{
    $message = null;
    $length = mb_strlen($value);
    if ($length < $min) {
        $message = "Длина должна быть не менее $min символов";
    }
    return $message;
}

/**
 * Функция - валидатор максимальной длины текста в поле
 * @param string $value Значение поля формы
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_max_length($value, $max)
{
    $message = null;
    $value = trim($value);
    $length = mb_strlen($value);
    if ($length > $max) {
        $message = "Превышена допустимая длина поля: введите не более $max символов";
    }
    return $message;
}


/**
 * Проверяет наличие в Базе данных пользователя с переданным email
 * @param string $email Email
 * @param mysqli $link Ресурс соединения
 * @return string|null $message Текст сообщения о существовании пользователя с таким email
 */
function validate_email_unique($email, $link)
{
    $message = null;
    if ($email) {
        $sql = "SELECT id FROM user WHERE email = '$email';";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            $message = 'Пользователь с этим email уже зарегистрирован';
        }
    }
    return $message;
}


/**
 * Функция - валидатор поля ввода хэштегов
 * @param string $value Значение поля формы
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_hashtag($value)
{
    $message = null;
    $words = explode(" ", $value);
    foreach ($words as $value) {
        if (!preg_match("/^#\w+$/ui", $value)) {
            $message = "Теги должны быть разделены пробелами и начинаться с #.
            Теги могут состоять из букв, цифр и символа подчеркивания.";
            break;
        }
    }
    if (!$message) {
        foreach ($words as $value) {
            if (mb_strlen($value) > MAX_HASHTAG_LENGTH) {
                $message = "Длина хэштега не более " . MAX_HASHTAG_LENGTH . "символов";
                break;
            }
        }
    }
    return $message;
}

/**
 * Функция - валидатор комментария
 * @param string $comment Текст комментария
 * @return array $errors Массив с текстом сообщения об ошибке
 */
function validate_comment($comment)
{
    $errors = [];
    $errors['comment'] = validate_filled($comment);
    if (!$errors['comment']) {
        $errors['comment'] = validate_min_length($comment, MIN_COMMENT_LENGTH) ?? validate_max_length(
            $comment,
            MAX_COMMENT_LENGTH
        );
    }
    return array_diff($errors, array(''));
}

/**
 * Отправляет запрос на запись нового комментария к посту с заданным id, если такой существует
 * @param mysqli $link Ресурс соединения
 * @param int $post_id id поста
 * @param int $user_id id Автора комментария
 * $param string $comment Текст комментария
 *
 * @return array $errors Массив сообщений об ошибках
 */
function add_new_comment($comment, $post_id, $user_id, $link)
{
    $errors = [];
    $sql = "SELECT user_id FROM post WHERE id = ?;";
    $result = fetch_sql_response($link, $sql, [$post_id]);
    if ($result && mysqli_num_rows($result)) {
        // Если пост найден, сохраняем автора поста
        $author = mysqli_fetch_assoc($result);
        $author_id = $author['user_id'];

        // Создаем запрос на запись комментария в базу данных
        $sql_comment = "INSERT INTO comment (comment_text, user_id, post_id)
            VALUES (?,?,?);";
        $data_com = array($comment, $user_id, $post_id);

        $stmt = db_get_prepare_stmt($link, $sql_comment, $data_com);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
            $errors['comment'] = 'Не удалось сохранить ваш комментарий.';
        }
    } else {
        $errors['comment'] = 'Пост не найден. Не удалось записать комментарий';
    }
    if (empty($errors)) {
        header("Location: http://readme/profile.php?id=" . $author_id);
    }

    return $errors;
}

/**
 * Проверяет комментарий к посту, в случае успешной проверки сохраняет в базу
 * и переводит на страницу автора поста
 * @param int $current_user_id ID залогиненного пользователя - автора нового комментария
 * @param mysqli $link Ресурс соединения
 * @return array $errors Массив с сообщениями об ошибках для полей 'email', 'password'
 */
function process_comment_add($current_user_id, $link)
{
    $filters = ['post-id' => FILTER_DEFAULT, 'comment' => FILTER_DEFAULT];
    $comment_post = filter_input_array(INPUT_POST, $filters, true);

    $comment_text = trim($comment_post['comment']);
    $post_id = $comment_post['post-id'];

    // Проверяем поле с текстом комментария на заполненность и на длину текста
    $errors = validate_comment($comment_text);

    if (empty($errors)) {
        // Если нет ошибок валидации, проверяем, что пост с заданным id есть в базе,
        // записываем пост в базу данных и переходим на страницу автора
        $errors = add_new_comment($comment_text, $post_id, $current_user_id, $link);
    }
    return $errors;
}

/**
 * Возвращает раcширение файла
 * @param string $file_type MIME-тип файла
 * @return string Расширение файла (с точкой)
 */
function get_file_ext($file_type)
{
    $type_parts = explode('/', $file_type);
    return '.' . array_pop($type_parts);
}

/**
 * Функция - валидатор файла изображения. (Проверяет, что файл является изображением)
 * @param string $path Путь к файлу
 * @return string|null $message Текст сообщения об ошибке
 */

function validate_image_file($path)
{
    $message = null;
    $required_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($path);
    if (!in_array($file_type, $required_types)) {
        $message = "Загрузите картинку в одном из форматов: gif, jpeg, png";
    }
    return $message;
}

/**
 * Функция - валидатор загруженного файла
 * @param array $file Поле массива $_FILES, соответствующее имени input[type="file"]
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_file($file)
{
    $message = null;
    if (!empty($file['name'])) {
        $file_path = $file['tmp_name'];
        $message = validate_image_file($file_path);
        if (!$message) {
            if ($file['size'] > MAX_FILE_SIZE * MB) {
                $message = "Максимальный размер файла: " . MAX_FILE_SIZE . "Мб";
            }
        }
    }
    return $message;
}

/**
 * Функция - валидатор ссылки из интернета
 * @param string $value url-адрес
 * @return string|null $message Текст сообщения об ошибке
 */

function validate_url($value)
{
    $message = null;
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        $message = "Указан некорректный URL-адрес";
    }
    return $message;
}

/**
 * Функция - валидатор ссылки на изображение из интернета
 * @param string $value url-адрес
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_photo_url($value)
{
    $message = null;
    // Если загружен файл - игнорируем поле для ввода ссылки, не валидируем его
    if (!empty($_FILES['file']['name'])) {
        return null;
    }
    // Если не загружен файл - проверяем наличие ссылки
    if (!$value) {
        // Если нет ни файла ни ссылки
        $message = "Одно из полей должно быть заполнено: загрузите файл или введите ссылку на изображение";
    } else {
        // Если нет файла, но есть ссылка, проверяем url
        $message = validate_url($value);
    }
    // Если url корректный, пробуем скачать файл по ссылке
    if (!$message) {
        $loaded_img = file_get_contents($value);
        if (!$loaded_img) {
            $message = "Не удалось загрузить файл по указанной ссылке";
        } else {
            $tmp_path = save_file_to_uploads($value);
            $message = validate_image_file($tmp_path);
        }
    }
    return $message;
}

/**
 * Проверяет авторизационные данные пользователя, открывает сессию в случае успешной авторизации
 * @param array $form Массив $_POST
 * @param mysqli $link Ресурс соединения
 * @return array $errors Массив с сообщениями об ошибках для полей 'email', 'password'
 */
function authorize_user($form, $link)
{
    $errors = [];
    // Проверяем заполненность обязательных полей
    foreach ($form as $key => $value) {
        if (empty($value)) {
            $errors[$key] = 'Это поле должно быть заполнено';
        }
    }

    if (count($errors)) {
        return $errors;
    }
    // Проверяем существование пользователя с введенным email
    $email = filter_var($form['login'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        $sql = "SELECT * FROM user WHERE email = '$email'";
        $result = mysqli_query($link, $sql);
        $user = $result ? mysqli_fetch_array($result, MYSQLI_ASSOC) : null;
        if ($user) {
            // Проверяем пароль и открываем сессию
            if (password_verify($form['password'], $user['password'])) {
                // Создаем запрос на количество непрочитанных сообщений
                $user_id = $user['id'];
                $sql = "SELECT COUNT(id) AS message_count
                        FROM message
                        WHERE receiver_id = '$user_id'
                            AND is_new = TRUE";
                $result = mysqli_query($link, $sql);
                $user['message_count'] = $result ? mysqli_fetch_row($result)[0] : 0;
                $_SESSION['user'] = $user;
            } else {
                $errors['password'] = 'Неверный пароль';
            }
        } else {
            $errors['login'] = 'Пользователь с таким email не найден';
        }
    } else {
        $errors['login'] = 'Введен некорректный email';
    }
    return $errors;
}

/**
 * Загружает файл по ссылке из интернета и сохраняет в папку 'uploads/'
 * @param string $file_url url-адрес
 * @return string $path Путь к файлу на сервере
 */
function save_file_to_uploads($file_url)
{
    $uploaded_file = file_get_contents($file_url);
    $filename = uniqid();
    $path = 'uploads/' . $filename;
    file_put_contents($path, $uploaded_file);
    return $path;
}

/**
 * Перемещает загруженный файл в папку 'uploads/'
 * @param array $file Поле массива $_FILES, соответствующее имени input[type="file"]
 * @return string $path Путь к файлу на сервере
 */
function replace_file_to_uploads($file)
{
    $file_type = mime_content_type($file['tmp_name']);
    $filename = uniqid() . get_file_ext($file_type);
    $path = 'uploads/' . $filename;
    move_uploaded_file($file['tmp_name'], $path);
    return $path;
}

/**
 * Функция - валидатор ссылки на видео с YouTube
 * @param string $value url-адрес
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_video_url($value)
{
    $message = validate_url($value);
    if (!$message) {
        $result = check_youtube_url($value);
        if (gettype($result) === 'string') {
            $message = $result;
        }
    }
    return $message;
}

/**
 * Возвращает адрес фавиконки сайта по url-адресу
 * @param string $url url-адрес
 * @return string $fav_url Адрес к фавиконке
 */
function generate_favicon_url($url)
{
    $parts = parse_url($url);
    return $parts['scheme'] . '://' . $parts['host'] . '/favicon.ico';
}

/**
 * Переименовывает первый найденный ключ, если он совпадает с одним из значений переданого массива
 * @param array $old_keys Массив имен ключей, которые надо заменить
 * @param string $new_key Новое имя ключа
 * @return array Массив с переименованным ключом
 */
function rename_key($old_keys, $new_key, $arr)
{
    $keys = array_keys($arr);
    $values = array_values($arr);
    foreach ($old_keys as $value) {
        $key_index = array_search($value, $keys);
        if ($key_index) {
            $keys[$key_index] = $new_key;
            $arr = array_combine($keys, $values);
            break;
        }
    }
    return $arr;
}

/**
 *Функция - колбэк для сортировки двумерного массива по значению поля 'date_add'
 * @param array $left Элемент сортируемого массива
 * @param array $right Элемент сортируемого массива, следующий за элементом $left
 * @return int Значение -1 или 0 или 1
 */

function compare_date($left, $right)
{
    if ($left['date_add'] === $right['date_add']) {
        return 0;
    }
    return ($left['date_add'] > $right['date_add']) ? -1 : 1;
}
