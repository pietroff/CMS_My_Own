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
}
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
    //deklaracja nagłówka
    function DrukujNaglowek($StronaTytul,$SlowaKluczowe,$OpisStrony){
        //deklarujemy zmienne globalne
        global $KonfiguracjaWitryny;
        global $JezykStrony;
        
        //deklarujemy elementy jeżyka XHTML
        echo '<?xml version="1.0" encoding="'.$JezykStrony['kodowanie'].'"?>';
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"';
        echo '"http://localhost/dtd/xhtmlll.dtd">';
        echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$JezykStrony['jezyk'].'">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset='.$JezykStrony['kodowanie'].'" />';
        echo '<meta http-equiv="Content-language" content="'.$JezykStrony['jezyk_okresl'].'" />';
        echo '<meta name="copyright" content="home-tec.pl THE BEST CMS">';
        //opis strony
        echo '<meta name="description" content="'.$OpisStrony.'" />';
        
        //Słowa kluczowe
        echo '<meta name="keywords" content="'.$SlowaKluczowe.'" />';
        echo '<meta name="robots" content="all" />';
        echo '<meta name="rating" content="general" />';
        echo '<meta name="resource-type" content="document" />';
        echo '<meta name="generator" content="home-tec.pl THE BEST CMS" />';
        //włączamy plik ze stylami css
        
        echo '<link rel="stylesheet" type="text/css" href="szablony/'.addslashes($_SESSION[uzy_szablon]).'/style.css" />';
        
        //tytuł strony
        
        echo '<title>'.$StronaTytul.''.$KonfiguracjaWitryny['nazwa'].'</title>';
        echo '</head>';
        echo '<body>';
        
        //deklarujemy tabelę w której będzie logo naszej firmy
        echo '<table class="tabela_logo">';
        echo '<tr>';
        echo '<td class="komorka_logo"></td>';
        echo '</tr>';
        echo '</table>';
    }
    //funkcja która wyświetli ścieżkę dostępu do strony
    function DrukujSciezke(){
        global $URLDlaModulu;
        //wyświetlamy ścieżkę
        echo $URLDlaModulu;
    }
    //funkcja wyświetlająca bloki po lewej stronie witryny
    function DrukujLeweMenu(){
        global $BazaDanych;
        global $PrefixTabelek;
        
        //sprawdzam która baza danych jest włączona i deklaruję odpowiednie zapytanie
        if($BazaDanych == 'mysql'){
            $ZapytanieBlok = 'SELECT * FROM '.$PrefixTabelek.'bloki WHERE blok_strona = "1" AND blok_aktywny = "t" ORDER BY blok_pozycja ASC';
        }
        //Za pomoca interfejsu wykonujemy zapytanie do bazy
        if(!WykonajZapytanie($ZapytanieBlok)){
            PokazBlad('Błąd w zapytaniu', __FILE__, __LINE__, $ZapytanieBlok);
        }
        //Pobieramy wyniki i zapisujemy je w zmiennych
        $Wynik = PobierzWynik();
        $IleWynikow = PobierzIlosc();
        //Deklarujemy tabelę w języku XHTMLoraz przypisaną do niej klasą
        echo '<table class="l_menu" cellpadding="0" cellspacing="0">';
        
        //Wyświetlamy wszystkie bloki ustawione po tej stronie witryny
        
        for ($i = 0; $i < $IleWynikow; $i++) {
            echo '<tr>';
            echo '<td class="l_1">'. stripslashes($Wynik[$i]['blok_pokaz']).'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="l_2">';
            // za pomocą include włączam plik zawierający dany blok aby go wyświetlić
            include ('./bloki/'.$Wynik[$i]['blok_nazwa'].'/'.$Wynik[$i]['blok_nazwa'].'.php');
            echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="l_3"></td>';
            echo '</tr>';
            //zamykamy lewą tabelę
        }  
            echo '</table>';
        }
        //funkacja deklarująca bloki po prawej stronie
        
        function DrukujPraweMenu(){
            global $BazaDanych;
            global $PrefixTabelek;
            
            if($BazaDanych == 'mysql'){
                $ZapytanieBlok = 'SELECT * FROM '.$PrefixTabelek.'bloki WHERE blok_strona = "p" AND blok_aktywny = "t" ORDER BY blok_pozycja ASC';
            }
             //Za pomoca interfejsu wykonujemy zapytanie do bazy
        if(!WykonajZapytanie($ZapytanieBlok)){
            PokazBlad('Błąd w zapytaniu', __FILE__, __LINE__, $ZapytanieBlok);
        }
        //Pobieramy wyniki i zapisujemy je w zmiennych
        $Wynik = PobierzWynik();
        $IleWynikow = PobierzIlosc();
        //Deklarujemy tabelę w języku XHTMLoraz przypisaną do niej klasą dla css
        echo '<table class="p_menu" cellpadding="0" cellspacing="0">';
        
        //Wyświetlamy wszystkie bloki ustawione po tej stronie witryny
        
        for ($i = 0; $i < $IleWynikow; $i++) {
            echo '<tr>';
            echo '<td class="p_1">'. stripslashes($Wynik[$i]['blok_pokaz']).'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="p_2">';
            // za pomocą include włączam plik zawierający dany blok aby go wyświetlić
            include ('./bloki/'.$Wynik[$i]['blok_nazwa'].'/'.$Wynik[$i]['blok_nazwa'].'.php');
            echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="p_3"></td>';
            echo '</tr>';
            //zamykamy prawą tabelę         
            } 
             echo '</table>';
        }
        //deklarujemy stopkę
        function DrukujStopke(){
            echo '<div class="stopka">';
            echo 'Copyright &copy; 2006 <a href="//home-tec.pl"> Home-TEC.pl PRO CMS';
            echo '</div>';
            echo '</body>';
            echo '</html>';
        }
        //Deklarujemy funkcję wyświetlającą błąd
        
        function DrukujBlad($TrescInformacji){
            global $JezykStrony;
            echo '<div class="niepoprwanie">';
            echo $TrescInformacji;
            echo '<br />';
            echo $JezykStrony['wroc_blad'];
            echo '</div>';
        }
        //deklarujemy funkcję która poinforuje użytkownika o poprawnym zakończeniu akcji
        function DrukujPoprawne($TrescInformacji){
            global $JezykStrony;
            echo '<div class="poprawnie">';
            echo $TrescInformacji;
            echo '<br />';
            echo $JezykStrony['poprawnie_idz_dalej'];
            echo '</div>';
        }
        //funkcja do logowania na konto administratora
        
        function PokazOknoLogowania(){
            global $JezykAdmin;
            
            ?>
<form method="post" action="zaloguj.php">
    <table>
        <tr>
            <td><?php echo $JezykAdmin['nazwa_konta']; ?>;</td>   
            <td><input type="text" name="login_konta"></td>
        </tr>
        <tr>
            <td><?php echo $JezykAdmin['haslo_dostepu']; ?>;</td>   
            <td><input type="password" name="haslo_konta"></td>
        </tr>
        <tr>
            <td></td><td>
                <input type="hidden" name="logowanie" value="tak">
                    <input type="submit" value="<?php echo $JezykAdmin[zaloguj_mnie]; ?>">
            </td>
        </tr>
    </table>
</form>
            <?php
        }
        ?>