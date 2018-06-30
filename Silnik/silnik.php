<?php
// zerujemy aby wszystkie zmienne przychodziły w takiej formie jakiej zostały przesłane
set_magic_quotes_runtime(0);
//Rejestrujemy zmienne w sesji użytkownika
session_start();
session_register('uzy_id');
session_register('uzy_nazwa');
session_register('uzy_emai');
session_register('uzy_szablon');
session_register('uzy_jezyk');
session_register('uzy_czy_admin');
session_register('uzy_zalogowany');
// dołączamy plik z konfiguracją bazy danych za pomocą require
require ('./konfiguracja.php');
// wykrywamy na jaką wartość jest ustawiona $BazaDanych 
// jeśli na mysql to za pomocą include dołączamy sterownik
if($BazaDanych == 'mysql'){
    include ('./silnik/sterownik_mysql.php');
}
// jak sterownik jest podłączony odpalamy połączenie z bazą
    $Polaczenie = PolaczZBaza($HostBaza, $LoginUzytkownika, $HasloUzytkownika);
    //Sprawdzamy czy połączenie zostało nawiązane
if($Polaczenie == FALSE){
    //Jeśli bark to wyświetlamy komunikat
    echo 'Połączenie z bazą danych nie powiodło się';
    //kończymy działanie skryptu
    exit();
}
//jeśli połączenie zostało nawiązane wybieramy bazę danych 
$WybieranieBazy = WybierzBazeDanych($NazwaBazyDanych);
//sprawdzamy czy baza danych została wybrana prawidłowo
//sprawdzamy czy zmienna w której przechowywany jest wynik wybierania bazy jest
//równa false
if($WybieranieBazy == FALSE){
    echo 'Wybieranie bazy danych nie powiodło się';
    //kończymy działanie skryptu
    exit();
}
//deklaracja funkcji która wyświetli informację o błędzie jeśli pojawi się taki 
// podczas wykonywania zapytania do bazy
function PokazBlad($Wiadomosc,$Plik,$Linia,$ZapytanieSQL) {
    echo 'Błąd bazy danych';
    echo '<br />';
    echo 'W pliku: '.$Plik;
    echo '<br />';
    echo 'W lini: '.$Linia;
    echo '<br />';
    echo 'W zapytaniu: '.$ZapytanieSQL;
    //pobieramy informację zwrócone przez serwer na temat błędu
    echo 'Informacje od bazy danych';
    echo PokazBladBazyDanych();
}
//pobieramy tablicę konfiguracyjną z systemu i sprawdzamy jaki interfejs bazy danych 
//jest włączony do pliku
if($BazaDanych == 'mysql'){
    //wybieramy wszystkie dane z tabeli konfiguracyjnej
    $ZapytanieKonfiguracja = 'SELECT konfig_nazwa,konfog_wartosc FROM'.$PrefixTabelek.'konfig';
}
//sprawdzamy poprawność zapytania jeśli pojawia się błąd wywołujemy funkcję
// wyświetlająca błąd
if(!WykonajZapytanie($ZapytanieKonfiguracja)){
    //do funkcji przekazujemy info o błędzie wraz z zapytaniem
    PokazBlad('Bład w zapytaniu', __FILE__,__LINE__, $ZapytanieKonfiguracja);
}
//jeśli zapytanie było porawne to do zmiennej$wynik pobieramy wynik a 
// do zmiennej $IleWynikow liczbe rekordow

$Wynik = PobierzWynik();
$IleWYnikow = PobierzIlosc();