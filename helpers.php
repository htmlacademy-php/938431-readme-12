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

    return true;
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
    $new_text = mb_substr($text, 0, $max_length + 1);
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
        $element['post_date'] = generate_random_date($key);
        $elements[$key] = $element;
    }
    return $elements;
}

/**
 * Возвращает дату в отформатированном строковом представлении "ДД-MM-ГГГГ ЧЧ:ММ"
 * @param string $date Дата в формате «ГГГГ-ММ-ДД ЧЧ:ММ:СС»
*/
function format_date($full_date) {
    $parts = explode(' ', $full_date);
    list($d, $t) = $parts;
    $date_parts = explode('-', $d);
    $date_parts = array_reverse($date_parts);
    return implode('-', $date_parts) . ' ' . substr($t, 0, 5);
}

/**
 * Возвращает количество секунд, прошедших между текущей датой и заданной
 * @param string $date Дата в прошлом, от которой отсчитывается интервал до текущего момента
*/
function calc_passed_time($date) {
    $target_tmst = strtotime('now');
    $origin_tmst = strtotime($date);
    return $target_tmst - $origin_tmst;
}

/**
 * Возвращает округленный вверх результат деления нацело первого аргумента на второй.
 * Округление происходит при условии, что результат деления остатка на третий аргумент >= 1

 * @param int $number Делимое число
 * @param int $base Основной делитель
 * @param int $prev Малый делитель для определения надо ли округлять вверх полученный результат
*/
function div_by_mod_up($number, $base, $prev) {
    $result = intdiv($number, $base);
        if (intdiv(($number % $base), $prev)) {
            $result++;
        }
    return $result;
}
/**
 * Возвращает строку вида "5 минут назад" на основании переданного временного интервала в секундах
 * Период округляется в большую сторону при условии, что количество предыдущих временных периодов в остатке > 0.
 * Пример: если временной интервал равен 1 час 1 минута 45 секунд - результат будет "2 часа назад"
 * Пример: если временной интервал равен 1 час 0 минут 45 секунд - результат будет "1 час назад"
 * @param int $interval Временной интервал в секундах
 * @return string Временной интервал в человекочитаемом виде
*/
function generate_interval_text($interval) {
    $mnt = 60;
    $hour = 3600;
    $day = 86400;
    $week = 604800;
    $five_weeks = 3024000;
    $month = 2628000;

    if ($interval < $hour) {
        $result = ceil($interval / $mnt);
        $result .= get_noun_plural_form($result, ' минута', ' минуты', ' минут');

    } else if ($interval < $day) {
        $result = div_by_mod_up($interval, $hour, $mnt);
        $result .= get_noun_plural_form($result, ' час', ' часа', ' часов');

    } else if ($interval < $week) {
        $result = div_by_mod_up($interval, $day, $hour);
        $result .= get_noun_plural_form($result, ' день', ' дня', ' дней');

    } else if ($interval < $five_weeks) {
        $result = div_by_mod_up($interval, $week, $day);

        $result .= get_noun_plural_form($result, ' неделя', ' недели', ' недель');

    } else {
        $result = div_by_mod_up($interval, $month, $week);
        $result .= get_noun_plural_form($result, ' месяц', ' месяца', ' месяцев');
    }

    return $result .= ' назад';
};

/**
 * Возвращает строку вида "5 минут назад" на основании переданной даты в прошлом
 * @param string $date Дата в формате «ГГГГ-ММ-ДД ЧЧ:ММ:СС»
 * @return string
*/
function generate_passed_time_text($date) {
    $interval = calc_passed_time($date);
    return generate_interval_text($interval);
}
