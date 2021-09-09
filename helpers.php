<?php
/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * is_date_valid('2019-01-01'); // true
 * is_date_valid('2016-02-29'); // true
 * is_date_valid('2019-04-31'); // false
 * is_date_valid('10.10.2010'); // false
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function is_date_valid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

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

    set_error_handler(function () {}, E_WARNING);
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
        if ($parts['path'] == '/watch') {
            parse_str($parts['query'], $vars);
            $id = $vars['v'] ?? null;
        } else {
            if ($parts['host'] == 'youtu.be') {
                $id = substr($parts['path'], 1);
            }
        }
    }

    return $id;
}

/**
 * @param $index
 * @return false|string
 */
function generate_random_date($index)
{
    $deltas = [['minutes' => 59], ['hours' => 23], ['days' => 6], ['weeks' => 4], ['months' => 11]];
    $dcnt = count($deltas);

    if ($index < 0) {
        $index = 0;
    }

    if ($index >= $dcnt) {
        $index = $dcnt - 1;
    }

    $delta = $deltas[$index];
    $timeval = rand(1, current($delta));
    $timename = key($delta);

    $ts = strtotime("$timeval $timename ago");
    $dt = date('Y-m-d H:i:s', $ts);

    return $dt;
}

// Мои функции

/**
 * Обрезает текст до заданной длины, не обрезая слов.
 * Вариант 1. (с использованием массива)
 * @param string $text Исходный текст
 * @param integer $max_length Максимальная длина обрезанного текста
 * @return string
*/
function cut_excerpt_1($text, $max_length) {
    $words = explode(' ', $text);
    $result_length = 0;

    foreach ($words as $key => $word) {
        $new_length = $result_length + mb_strlen($word);
        $i = $key;
        if ($new_length > $max_length) {
            break;
        } else {
            $result_length = $new_length + 1;
        }
    }

    $chosen_words = array_slice($words, 0, $i);
    return implode(' ', $chosen_words);
}

/**
 * Обрезает текст до заданной длины, не обрезая слов.
 * Вариант 2. (не использует массивы, только функции для строк)
 * @param string $text Исходный текст
 * @param integer $max_length Максимальная длина обрезанного текста
 * @return string
*/
function cut_excerpt_2($text, $max_length) {
    $new_text = trim($text);
    $new_text = mb_substr($new_text, 0, $max_length + 1);
    $position = mb_strrpos($new_text, ' ');
    $new_text = mb_substr($new_text, 0, $position);
    return $new_text;
}

/**
 * Возвращает разметку с отфильтрованным текстом.
 * Если длина текста превышает заданную, обрезает его, фильтрует и добавляет многоточие и ссылку "Читать далее".

 * @param string $text Исходный текст
 * @param integer $max_length Максимальная длина обрезанного текста
 * @return string
*/
function text_template($text, $max_length = 300) {
    if (mb_strlen($text) <= $max_length) {
        $result = '<p>' . htmlspecialchars($text) . '</p>';
    } else {
        $result = '<p>' . htmlspecialchars(cut_excerpt_2($text, $max_length)) . '...</p>
    <a class="post-text__more-link" href="#">Читать далее</a>';
    }

    return $result;
};

/**
 * Добавляет всем элементам массива новое поле с ключом "date" и значением - случайной датой

 * @param array $elements - Исходный массив
*/
function add_dates($elements) {
    foreach ($elements as $key => $element) {
        $elements[$key]['date_add'] = generate_random_date($key);
    }
    return $elements;
}

/**
 * Возвращает дату в отформатированном строковом представлении "ДД-MM-ГГГГ ЧЧ:ММ"
 * @param string $date Дата в формате «ГГГГ-ММ-ДД ЧЧ:ММ:СС»
*/
function format_date($date) {
    $date = date_create($date);

    return date_format($date, 'd-m-Y H:i');
}

/**
 * Возвращает интервал времени между текущей датой и заданной
 * @param string $date Дата в прошлом, от которой отсчитывается интервал до текущего момента
*/
function calc_time_interval($date_str) {
    $target_date = date_create('now');
    $origin_date = date_create($date_str);
    return date_diff($origin_date, $target_date);
}

/**
 * Увеличивает первый параметр на единицу, если второй параметр - истиный

 * @param int $number Исходное число
 * @param bool $cond
*/
function increment_by_condition($number, $cond) {
    if ($cond) $number++;
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
function generate_interval_text($interval) {
    $week = 7; // 7 суток
    $passed_seconds = $interval -> s;
    $passed_minutes = $interval -> i;
    $passed_hours = $interval -> h;
    $passed_days = $interval -> d % $week;
    $passed_weeks = intdiv($interval -> d, $week);
    $passed_months = $interval -> m;

    if (!$passed_months and !$passed_weeks and !$passed_days and !$passed_hours) {
        $result = increment_by_condition($passed_minutes, $passed_seconds);
        $result .= get_noun_plural_form($result, ' минута', ' минуты', ' минут');

    } else if (!$passed_months and !$passed_weeks and !$passed_days) {
        $result = increment_by_condition($passed_hours, $passed_minutes);
        $result .= get_noun_plural_form($result, ' час', ' часа', ' часов');

    } else if (!$passed_months and !$passed_weeks) {
        $result = increment_by_condition($passed_days, $passed_hours);
        $result .= get_noun_plural_form($result, ' день', ' дня', ' дней');

    } else if (!$passed_months) {
        $result = increment_by_condition($passed_weeks, $passed_days);

        $result .= get_noun_plural_form($result, ' неделя', ' недели', ' недель');

    } else {
        $result = increment_by_condition($passed_months, $passed_weeks);
        $result .= get_noun_plural_form($result, ' месяц', ' месяца', ' месяцев');
    }

    return $result;
};

/**
 * Возвращает строку вида "5 минут назад" на основании переданной даты в прошлом
 * @param string $date Дата в формате «ГГГГ-ММ-ДД ЧЧ:ММ:СС»
 * @return string
*/
function generate_passed_time_text($date) {
    $interval = calc_time_interval($date);
    return generate_interval_text($interval);
}

/**
 * Устанавливает соединение с базой readme, устанавливает кодировку и Ресурс соединения
 * @return mysqli Ресурс соединения
 */
function set_connection() {
    // Устанавливаем соединение
    $con = mysqli_connect('localhost', 'mysql', 'mysql', 'readme');

    if (!$con) {
        print('Ошибка подключения: ' . mysqli_connect_error());
        exit;
    };

    // Устанавливаем кодировку
    mysqli_set_charset($con, 'utf8');

    return $con;
}

/**
 * Отправляет запрос и возвращает результат
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli Объект результата
 */
function fetch_sql_response($link, $sql, $data) {
   $stmt = db_get_prepare_stmt($link, $sql, $data);
    mysqli_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySql: ' . $error);
        exit;
    }
    return $result;
}

/**
 * Выбирает html шаблон в зависимости от полученного типа поста
 * @param $post array Массив с данными о посте
 * @return string Итоговый HTML
 */
function choose_post_template($post) {
    $result = $post['p_type'];
    switch ($post['p_type']) {
        case 'link':
            $result = include_template('details-link.php', ['title' => $post['p_title'], 'url' => $post['url_site']]);
            break;
        case 'photo';
            $result = include_template('details-photo.php', ['img_url' => $post['url_img']]);
            break;
        case 'quote';
            $result = include_template('details-quote.php', ['text' => $post['quote_text'], 'author' => $post['quote_author']]);
            break;
        case 'text';
            $result = include_template('details-text.php', ['text' => $post['p_text']]);
            break;
        case 'video';
            $result = include_template('details-video.php', ['youtube_url' => $post['url_video']]);
            break;
    };

    return $result;
};

/**
 * Возвращает адресную строку для текущего скрипта с переданным параметром запроса
 * @param string $key Ключ параметра запроса
 * @param string|integer $value Значение параметра запроса
 * @return string url-адрес с обновленными параметрами запроса
 */
function update_query_params($key, $value) {
    $params = $_GET;

    if ($value) {
        $params[$key] = $value;
    } else {
        unset($params[$key]);
    }
    $query = http_build_query($params);
    $url = "?" . $query;
    return $url;
};

/**
 * Возвращает значение поля формы
 * @param string $name Имя поля формы
 * @return string|null Значение поля
 */
function get_post_value($name) {
    return filter_input(INPUT_POST, $name) ?? '';
}

/**
 * Функция - валидатор заполненности поля
 * @param string $value Значение поля формы
 * @return string|null Текст сообщения об ошибке
 */
function validate_filled($value) {
    if (empty($value)) {
        return "Это поле должно быть заполнено";
    }
}

/**
 * Функция - валидатор поля ввода хэштегов
 * @param string $value Значение поля формы
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_hashtag($value) {
    $message = null;
    $words = explode( " ", $value);
    foreach ($words as $value) {
        if (!preg_match("/^#\w+$/ui", $value)) {
            $message = "Теги должны начинаться с #, состоять из одного слова из букв, цифр и символа подчеркивания, разделяться пробелами";
            break;
        }
    }
    return $message;
}

/**
 * Возвращает MIME-тип загруженного файла
 * @param string $file_name путь к файлу
 * @return string MIME-тип файла
 */
function get_file_type($file_name) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    return finfo_file($finfo, $file_name);
}

/**
 * Возвращает раcширение файла
 * @param array $file_type MIME-тип файла
 * @return string Расширение файла (с точкой)
 */
function get_file_ext($file_type) {
    $type_arr = explode('/', $file_type);
    return  '.' . array_pop($type_arr);
}

define('MAX_FILE_SIZE', 2097152); // 2Мб в байтах

/**
 * Функция - валидатор загруженного файла
 * @param string $file_path Путь к файлу
 * @param string $file_size Размер файла
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_file($file_path, $file_size) {
    $message = null;
    $required_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = get_file_type($file_path);
    if (!in_array($file_type, $required_types)) {
        $message = "Загрузите картинку в одном из форматов: gif, jpeg, png";
    } elseif ($file_size > MAX_FILE_SIZE) {
        $message = "Максимальный размер файла: 2Мб";
    }
    return $message;
}


/**
 * Функция - валидатор ссылки из интернета
 * @param string $value url-адрес
 * @return string|null $message Текст сообщения об ошибке
 */

function validate_url($value) {
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        return "Указан некорректный URL-адрес";
    }
}

/**
 * Функция - валидатор ссылки на изображение из интернета
 * @param string $value url-адрес
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_photo_url($value) {
    // Проверяем загружен ли файл
    if (!empty($_FILES['userpic-file-photo']['name'])) {
        $message = null;
    } elseif (empty($value)) {
        $message = "Одно из полей должно быть заполнено: загрузите файл или введите ссылку на изображение";
    } else {
        $message = validate_url($value);
        if (!$message) {
            $loaded_img = file_get_contents($value);
            if (!$loaded_img) {
                $message = "Не удалось загрузить файл по указанной ссылке";
            }
        }
    }
    return $message;
}

/**
 * Загружает файл по ссылке из интернета и сохраняет в папку 'uploads/'
 * @param string $file_url url-адрес
 * @return string $path Путь к файлу на сервере
 */
function save_file_to_uploads($file_url) {
    $uploaded_file = file_get_contents($file_url);
    $filename = uniqid();
    $path = 'uploads/' . $filename;
    file_put_contents($path, $uploaded_file);
    return $path;
}

/**
 * Функция - валидатор ссылки на видео с YouTube
 * @param string $value url-адрес
 * @return string|null $message Текст сообщения об ошибке
 */
function validate_video_url($value) {
    $message = validate_url($value);
    if (!$message) {
        $result = check_youtube_url($value);
        if (gettype($result) == 'string') {
            $message = $result;
        }
    }
    return $message;
}

