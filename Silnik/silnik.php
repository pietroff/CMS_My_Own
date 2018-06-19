<?php
set_magic_quotes_runtime(0);

session_start();
session_register('uzy_id');
session_register('uzy_nazwa');
session_register('uzy_emai');
session_register('uzy_szablon');
session_register('uzy_jezyk');
session_register('uzy_czy_admin');
session_register('uzy_zalogowany');

require ('./konfiguracja.php');

if($BazaDanych == 'mysql'){
    include ('./silnik/sterownik_mysql.php');
}
    $Polaczenie = PolaczZBaza($HostBaza, $LoginUzytkownika, $HasloUzytkownika);
if($Polaczenie == FALSE){
    echo 'Połączenie z bazą danych nie powiodło się';
    exit();
}
$WybieranieBazy = WybierzBazeDanych($NazwaBazyDanych);
if($WybieranieBazy == FALSE){
    echo 'Wybieranie bazy danych nie powiodło się';
    exit();
}
function PokazBlad($Wiadomosc,$Plik,$Linia,$ZapytanieSQL) {
    echo 'Błąd bazy danych';
    echo '<br />';
    echo 'W pliku: '.$Plik;
    echo '<br />';
    echo 'W lini: '.$Linia;
    echo '<br />';
    echo 'W zapytaniu: '.$ZapytanieSQL;
    echo 'Informacje od bazy danych';
    echo PokazBladBazyDanych();
}
if($BazaDanych == 'mysql'){
    $ZapytanieKonfiguracja = 'SELECT konfig_nazwa,konfog_wartosc FROM'.$PrefixTabelek.'konfig';
}
if(!WykonajZapytanie($ZapytanieKonfiguracja)){
    PokazBlad('Bład w zapytaniu', __FILE__,__LINE__, $ZapytanieKonfiguracja);
}