<?php
function PolaczZBaza($HostBaza, $LoginUzytkownika, $HasloUzytkownika) {
    if(mysql_connect($HostBaza, $LoginUzytkownika, $HasloUzytkownika)){
        return TRUE;
    } else {
     return FALSE;   
    }    
}

function WybierzBazeDanych($NazwaBazyDanych) {
    if(mysql_select_db($NazwaBazyDanych)){
        return TRUE;
    } else {
        return FALSE;
    }
}

function WykonajZapytanie($ZapytanieDoBazy){
    global $WynikZapytania;
    $WynikZapytania = mysql_query($ZapytanieDoBazy);
    if($WynikZapytania){
        return TRUE;
    } else {
        return FALSE;
    }
}

function PokazBladBazyDanych() {
    $Blad['text'] = mysql_error();
    $Blad['number'] = mysql_errno();
    $InformacjaOBledzie = 'Nazwa: '.$Blad['text'].' Numer: '.$Blad['number'];
    return $InformacjaOBledzie;
}

function PobierzWynik() {
    global $WynikZapytania;
    while ($wiersz = mysql_fetch_array($WynikZapytania,MYSQLI_ASSOC)){
        $RezultatZapytania[] = $wiersz;
    }
    return $RezultatZapytania;
}

function PobierzIlosc() {
    global $WynikZapytania;
    $IloscRekordow = mysql_num_rows($WynikZapytania);
    return $IloscRekordow;
}