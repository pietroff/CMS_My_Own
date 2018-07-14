
<?php

// nazwa modułu
$CMSModul = 'index';

//podłączamy silnik za pomocą require

require ('./silnik/silnik.php');

// pobieram wartość ze zmiennej konfiguracji i rozdzielam na dwie części

$ModulDoUstawienia = explode('_', $KonfiguracjaWitryny['mstgl']);

//sprawdzam czy pozycja o indeksie zero w tablicy jestrówna s jeśli tak
//przekierowuję usera do pliku z nazwą zawartą w drugim elemencie tablicy
if($ModulDoUstawienia[0] == 's'){
    header("Location: {$ModulDoUstawienia[1]}.php");
}
//sprawdzam czy pozycja o indeksie zero w tablicy jestrówna m jeśli tak
//przekierowuję usera do pliku z nazwą zawartą w drugim elemencie tablicy
if($ModulDoUstawienia[0] == 'm'){
    header("Location: {$ModulDoUstawienia[1]}.php");
}
//sprawdzam czy pozycja o indeksie zero w tablicy jestrówna strona jeśli tak
//przekierowuję usera do pliku strona.php
    elseif ($ModulDoUstawienia[0] == 'strona') {
        header("Location: strona.php?id_strony={$ModulDoUstawienia[1]}");
}