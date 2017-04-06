<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
require_once('epyte.php');

function sh_testy() {
global $user;
	if (CValid::$acc3 == 'tsv') {//zapisywanie tabeli testów
		$kid=CValid::getCnt('paramId','p');
		$testy = new testy(null,null,$user->uid,$kid);
		$testy->getTb2('SELECT * FROM testy WHERE ');
		CHtml::sav_tab($testy);
		if ($testy->success) CMsg::kadd('<p>Zapisano testy!</p>');	
	}
	if (empty($kid)) $kid=0;
	CHtml::addRes('scripts','tbdelrow.js');
	$tb=CDBase::dbQuery('SELECT kategorie.kid AS kid,kategorie.kat||" ("||przedmioty.przedm||")" AS katprz FROM kategorie,przedmioty WHERE kategorie.przid=przedmioty.przid AND (kategorie.uid='.$user->uid.' OR kategorie.uid=1)',PDO::FETCH_KEY_PAIR);
	$tb[0]='Wybierz kategorię...';
	echo '<p class="cb">';
	echo 'Kategoria: '.CHtml::mk_sel('tbk','tbk',array($tb,(int)$kid),'ajax sel260');
	echo '&nbsp;&nbsp;&nbsp;<a class="but" href="?acc='.CValid::$acc.'&amp;acc3=new">Nowy test lub ankieta</a>';
	echo '</p>';
	echo '<div id="response">';
	if (CValid::$acc3 == 'tsv') {//zapisywanie
		if (!mk_tbtesty($user->uid,$kid)) CMsg::eadd('Brak testów!'); //wyświetl tabelę testów
	}
	echo '<div>&nbsp;<img id="load" src="'.get_theme_path(false).'imgs/ajax.gif" alt="Pobieram dane..." /></div>';
	echo '</div>';
}
//--- sterowanie ---
//print_r($_POST); return;
switch (CValid::$acc3) {
	case 'new':
	case 'edt':
		ed_Test(); //dodawanie/edycja testu
	break;
	case 'tfs':
		sav_Test(); //zapisywanie testu
		ed_Test();
	break;
	case 'edp': //edytuj pytania
		$tid=CValid::getCnt('idt','g');
		CValid::vInt($tid,0,null,0);
		ed_Pyt($tid); //edytuj pytania
	break;
	case 'psv': sav_Pyt(); break; //zapisz pytania
	case 'adp': add_Pyt(); break; //wybierz/wylosuj pytania do testu z wybranej kategorii
	case 'sdp': tsv_Pyt(); add_Pyt(); break; //dodaj pytania do testu z wybranej kategorii
	default:
		if (isset($sesja->tid)) unset($sesja->tid);
		sh_testy();
}
?>