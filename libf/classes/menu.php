<?php
class CMenu {
private static $menua=array(
	1 => 'start#Witamy#Witamy',
	2 => 'testy#Testy#Testy',
	3 => 'ankiety#Ankiety#Ankiety',
	12 => 'profil#Profil#Profil'
);
private static $menuu=array(
	1 => 'start#Witamy#Witamy',
	2 => 'testy#Testy#Testy',
	3 => 'ankiety#Ankiety#Ankiety',
	4 => 'wyniki#Wyniki#Wyniki',
	12 => 'profil#Profil#Profil'
);
private static $menun=array(
	1 => 'start#Witamy#Witamy',
	9 => 'etesty#Testy/Ankiety#Testy/Ankiety',
	11 => 'ewyniki#Wyniki#Wyniki',
	8 => 'epytania#Pytania#Pytania',
	10 => 'egrupy#Grupy#Grupy',
	12 => 'profil#Profil#Profil'
);
private static $menud=array(
	1 => 'start#Witamy#Witamy',
	15 => 'eausers#Użytkownicy#Użytkownicy',
	16 => 'eaprzedmioty#Przedmioty#Przedmioty',
	17 => 'eabaza#Baza#Baza',
	12 => 'profil#Profil#Profil',
);
private static $tbkto=array('a','u','n','d');//wszyscy,uczeń,nauczyciel,admin
private static $kto='a';
private static $sid=1;
public static $plik='start';

//Jeżeli nie istnieje menu w cachu wygeneruj menu i zapisz je 
private static function mmenu() {
	$mfile=	CT_CACHE.'menu'.self::$kto.'.html'; //menu file in cache
	if (file_exists($mfile)) {
		$mstr=file_get_contents($mfile);
	} else {
		$tbm=self::${'menu'.self::$kto};
		$mstr='<ul>';
		foreach ($tbm as $sid => $tbd) {
			$tbd=explode('#',$tbd);//0=>plik,1=>tekst w menu,2=>tytuł strony
			$mstr.='<li><a href="?acc='.$sid.'" class="'.$tbd[0].'">'.$tbd[1].'</a></li>';
		}
		$mstr.='</ul>';
		if (!file_put_contents($mfile,$mstr)) echo('Błąd zapisu menu!');
	}
	return $mstr;
}

public static function getMenu($echo=true) {
	$mstr=self::mmenu();
	//ustawienie klasy current
	$mstr=str_replace('class="'.self::$plik.'"','class="current" id="'.self::$plik.'"',$mstr);
	if ($echo) echo $mstr; else return $mstr;
}
// zwraca sid strony, jeżeli $co=null, lub informację://0=>plik,1=>tekst w menu,2=>tytuł strony
// jeżeli $co => {0,1,2}
public static function getPage($kto='a',$sid=1,$co=null) {
	if (in_array($kto,self::$tbkto)) self::$kto=$kto;
	if ($sid==24) {//strona specjalna dla wywołań AJAX
		self::$sid=24;
		self::$plik='getres';
		return $sid;
	} 
	if (array_key_exists($sid,self::${'menu'.self::$kto})) self::$sid=$sid;
	$tb=explode('#',self::${'menu'.self::$kto}[self::$sid]);
	if (is_null($co)) {
		self::$plik=$tb[0];
		return self::$sid;
	}
	return $tb[$co];
}

}
?>