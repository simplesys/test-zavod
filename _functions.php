<?php
/**
 * Functions
 *
 * Author:    Chuvashin Viacheslav <chuvashin.v@gmail.com>
 * Copyright: 2017 Chuvashin Viacheslav
 * Date:      10,09,2017 20:56
 */

/**
 * Валидация формы
 *
 * @return array
 */
function validate(): array {
    if (empty($_POST)) {
        return [];
    } else {
        $result = $_POST;
    }

    if (array_key_exists('ad_type', $result)) {
        $result['ad_type'] = checkInArray($result['ad_type'], ['sale', 'rent']);
    }

    if (array_key_exists('type', $result)) {
        $result['type'] = checkInArray(
            $result['type'], [
                'city/flats',
                'city/rooms',
                'city/elite',
                'city/newflats',
                'country/houses',
                'country/cottages',
                'country/lands',
                'commerce/offices',
                'commerce/comm_new',
                'commerce/service',
                'commerce/different',
                'commerce/freestanding',
                'commerce/storage',
                'commerce/comm_lands'
            ]
        );
    }

    foreach (['price', 'rooms'] as $paramName) {
        if (array_key_exists($paramName, $result)) {
            foreach ($result[$paramName] as $key => $value) {
                $result[$paramName][$key] = checkInt($value);
            }
        }
    }

    if (array_key_exists('only_photo', $result)) {
        $result['only_photo'] = checkInt($result['only_photo']);
    }

    return checkErrors($result);
}

/**
 * Проверка формы на ошибки
 *
 * @param array $result
 *
 * @return array
 */
function checkErrors(array $result) {
    if (empty($result['ad_type'])) {
        $result['error'][] = 'Неверно указан тип объявления';
    }

    if (empty($result['type'])) {
        $result['error'][] = 'Неверно указан тип недвижимости';
    }

    if (array_key_exists('price', $result)
        && array_key_exists('from', $result['price'])
        && $result['price']['from'] > 0
        && array_key_exists('to', $result['price'])
        && $result['price']['from'] > $result['price']['to']) {
        $result['error'][] = 'Начальная сумма не может быть больше конечной';
    }

    return $result;
}

/**
 * Поиск содержится ли значение в предопределённом массиве
 *
 * @param string $checkValue
 * @param array $checkArray
 *
 * @return string
 */
function checkInArray(string $checkValue, array $checkArray): string {
    if (!in_array($checkValue, $checkArray)) {
        return '';
    } else {
        return $checkValue;
    }
}

/**
 * Преобразовать значение в число
 *
 * @param $value
 *
 * @return int|string
 */
function checkInt($value) {
    if ($value !== '') {
        return (int) $value;
    } else {
        return '';
    }
}

/**
 * Вывод элементов option в форме
 *
 * @param array $listOptions
 * @param string $selectName
 */
function echoOptions(array $listOptions, string $selectName) {
    foreach ($listOptions as $key => $itemName) {
        if (strpos($key, 'disabled') !== false) {
            echo '<option disabled="disabled">'
                . $itemName . '</option>';
        } else {
            $selected = (!empty($_POST) && $_POST[$selectName] === $key)
                || (is_array($_POST[$selectName]) && in_array($key, $_POST[$selectName]))
                ? 'selected' : '';
            echo '<option value="' . $key . '" ' . $selected . ' >'
                . $itemName . '</option>';
        }
    }
}
