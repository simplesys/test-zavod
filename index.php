<?php
/**
 * index file
 *
 * Author:    Chuvashin Viacheslav <chuvashin.v@gmail.com>
 * Copyright: 2017 Chuvashin Viacheslav
 * Date:      10,09,2017 15:36
 */

require __DIR__ . '/_functions.php';
require __DIR__ . '/vendor/autoload.php';

use \Curl\Curl;
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">
    <title>Поиск недвижимости</title>
</head>
<body>
<h1 class="title">Поиск недвижимости</h1>
<?php
if (!empty($_POST)) {
    $result = validate();

    if (array_key_exists('error', $result)) {
        echo '<div class="error">';
        foreach ($result['error'] as $error) {
            echo $error . '<br>';
        }
        echo '</div>';
    }
}
?>
<div class="search">
    <form action="index.php" class="search__form" method="post">
        <div class="search__filter">
            <label for="select_ad_type_id">Тип объявления:</label>
            <select id="select_ad_type_id" name="ad_type" class="search__field">
                <?php
                $listItems = ['sale' => 'Продажа', 'rent' => 'Аренда'];

                echoOptions($listItems, 'ad_type');
                ?>
            </select>
        </div>
        <div class="search__filter">
            <label for="select_type_id">Тип недвижимости:</label>
            <select id="select_type_id" name="type" class="search__field">
                <?php
                $listItems = [
                    'disabled1' => 'жилая',
                    'city/flats' => 'квартиры (вторичка)',
                    'city/rooms' => 'комнаты',
                    'city/elite' => 'элитная недвижимость',
                    'city/newflats' => 'новостройки',
                    'disabled2' => 'загородная',
                    'country/houses' => 'дома',
                    'country/cottages' => 'коттеджи',
                    'country/lands' => 'участки',
                    'disabled3' => 'коммерческая',
                    'commerce/offices' => 'офисы',
                    'commerce/comm_new' => 'помещения в строящихся домах',
                    'commerce/service' => 'помещения в сфере услуг',
                    'commerce/different' => 'помещения различного назначения',
                    'commerce/freestanding' => 'отдельно стоящие здания',
                    'commerce/storage' => 'производственно-складские помещения',
                    'commerce/comm_lands' => 'земельные участки'
                ];

                echoOptions($listItems, 'type');
                ?>
            </select>
        </div>
        <div class="search__filter">
            <label for="price_id">Цена <span>(рублей)</span>:</label>
            <input id="price_id" class="search__field" type="text"
                name="price[from]" placeholder="Цена от" value=<?php
                echo !empty($_POST['price']['from']) ? $_POST['price']['from']
                : '' ?>>
            -
            <input class="search__field" type="text" name="price[to]"
                placeholder="Цена до" value=<?php
                echo !empty($_POST['price']['to']) ? $_POST['price']['to'] : ''
            ?>>
        </div>
        <div class="search__filter">
            <label for="rooms_id">Число комнат:</label>
            <select id="rooms_id" class="search__field multi" name="rooms[]" multiple size="3">
                <?php
                $listItems = [
                    '1' => '1 комната', '2' => '2 комнаты',
                    '3' => '3 комнаты', '4' => '4 комнаты', '5' => '5 комнат'
                ];

                echoOptions($listItems, 'rooms');
                ?>
            </select>
        </div>
        <div class="search__filter">
            <input type="checkbox" id="only_photo_id" name="only_photo"
                value="1" <?php
                echo array_key_exists('only_photo', $_POST)
                    && $_POST['only_photo'] === '1'
                    ? 'checked' : '' ?>> только с фото
        </div>
        <input class="search__button" type="submit" value="Найти объявления">
    </form>
</div>

<?php
if (!empty($result)) {
    $url = 'http://www.50.bn.ru/'
        . $result['ad_type'] . '/'
        . $result['type']
        . '/?sort=price_for_sort&sortorder=ASC';

    if (array_key_exists('price', $result)
        && array_key_exists('from', $result['price'])) {
        $url .= '&price%5Bfrom%5D=' . $result['price']['from'];
    }

    if (array_key_exists('price', $result)
        && array_key_exists('to', $result['price'])) {
        $url .= '&price%5Bto%5D=' . $result['price']['to'];
    }

    if (array_key_exists('rooms', $result)) {
        foreach ($result['rooms'] as $room) {
            $url .= '&rooms%5B%5D=' . $room;
        }
    }

    if (array_key_exists('only_photo', $result)) {
        $url .= '&only_photo=' . $result['only_photo'];
    }

    $curl = new Curl();
    $curl->setHeaders(['Content-type' => 'text/html', 'charset' => 'UTF-8']);
    $curl->get($url);
    echo '<div class="result">';

    if ($curl->error) {
        echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
    } else {
        $htmlString = $curl->response;
    }

    $curl->close();

    if (!empty($htmlString)) {
        $pq = phpQuery::newDocumentHTML($htmlString, 'UTF-8');
        echo $pq->find('div.result > table')->htmlOuter();
    } else {
        echo '<h2>Ничего не найдено</h2>';
    }

    echo '</div>';
}
?>
</body>
</html>
