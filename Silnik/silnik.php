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

//przekształcamy otrzymaną tablicę w tabelę asocjacyjną w której kluczami będą
//wartości w polu konfig_nazwa a wartościami dane wprowadzone do pola konfig_wartosc

for ($i = 0; $i < $IleWYnikow; $i++) {
    $kluczWyniku = $Wynik[$i]['konfig_nazwa'];
    $KonfiguracjaWitryny[$kluczWyniku]= $Wynik[$i]['konfig_wartosc'];
}
// Sprawdzam czy użytkownik jest zalogowany na swoje konto w systemie
//funkcja empty sprawdza czy zmienna $_SESSION['uzy_id'] zawiera jakąs wartość
// jesli nie definiujemy zmienne sesji jako puste
if(empty($_SESSION['uzy_id'])){
    $_SESSION['uzy_id'] = '';
    $_SESSION['uzy_nazwa'] = '';
    $_SESSION['uzy_email'] = '';
    //przy zmiennych _SESSION['uzy_szablon] i _SESSION['uzy_jezyk'] wpisujemy wartość
    //tablicy konfiguracyjnej, dzięki temu dla użytkownika niezalogowanego
    // ustalony zostanie domyślny szablon i język
    $_SESSION['uzy_szablon'] = $KonfiguracjaWitryny['dt'];
    $_SESSION['uzy_jezyk'] = $KonfiguracjaWitryny['dt'];
    $_SESSION['uzy_czy_admin'] = '';
    $_SESSION['uzy_zalogowany'] = '';
    
    //blokowanie użytkowników o określonym numerze ip, sprawdzamy czy taka opcja została 
    //włączona w panelu administratora
    if($KonfiguracjaWitryny['blip'] == tak){
        //jeśli tak to wykonujemy zapytanie pobierające wszystkie numery ip zabronione
        if($BazaDanych == 'mysql'){
            $ZapytanieIp = 'SELECT ip_numer FROM'.$PrefixTabelek.'banujip';
        }
        //sprawdzam poprawność zapytania
        if(!WykonajZapytanie($ZapytanieIp)){
            PokazBlad('Błąd w zapytaniu', __FILE__, __LINE__, $ZapytanieIp);
        }
        //Pobieramy wynik zapytania do zmiennych zawierajacych wynik oraz ich liczbę
        $Wynik = PobierzWynik();
        $IleWYnikow = PobierzIlosc();
        
        //sprawdzamy czy wynik pasuje do IP obecnego użytkownika zapisanego w zmiennej
        // $_SERVER['REMOTE_ADDR']
        for ($i = 0; $i < $IleWYnikow; $i++) {
            if($Wynik[$i]['ip_numer'] == $_SERVER['REMOTE_ADDR']){
                //jeśli wynik pasuje do obecnego IP wyświetlamy komunikat że nie 
                //ma dostępu do strony. w tym celu włączamy odpowiedni plik
                include ('./strony_html'. addslashes($_SESSION['uzy_jezyk']).'/zbanowany_ip.html');
                exit();
            }
        }
    }
    //teraz włączamy zapisany w pliku określony język, w ifie sprawdzam czy język został włączony
    if(!include ('./jezyki/'.addslashes($_SESSION['uzy_jezyk']).'/jezyk.php')){
        //Jeśli język nie został włączony wyświetlamy odpowiedni komunikat
        echo 'Nie mogłem włączyć tego języka: '. addslashes($_SESSION['uzy_jezyk']);
        exit();
    }
    //wyświetlamy szablon strony
    
    function DrukujSzablonStrony(){
        global $CMSModul;
        global $StronaTytul;
        global $SlowaKluczowe;
        global $OpisStrony;
        //sprawdzam czy można włączyć szablon użytkownika
        if(!include ('./szablony/'. addslashes($_SESSION['uzy_szablon']).'/sza-blon.php')){
            // jeśli nie mozna go włączyć wyświetlamy komunikat
            echo 'Nie mogłem włączyć tego szablonu:'. addslashes($_SESSION['uzy_szablon']);
            exit();
        }
    }
}