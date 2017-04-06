<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
include_cl(array('wyniki','grupy'));

function sh_wyniki() {
global $user;
	include_cl('testy');
	$_SESSION['sid']=CValid::$acc; //zapisz sid aktualnej strony dla poprawnych linków generowanych przez getres
	echo '<p class="cb" id="wyniki">';
	//wygenerowanie listy testów/ankiet publicznych
	$tb=CDBase::dbQuery(
		'SELECT testy.tid,testy.zakres||" ["||kategorie.kat||" ("||przedmioty.przedm||")]" AS zakkatprz
		FROM testy,kategorie,przedmioty
		WHERE kategorie.przid=przedmioty.przid AND (kategorie.uid='.$user->uid.' OR kategorie.uid=1) AND testy.kid=kategorie.kid AND testy.typ=0 AND testy.uid='.$user->uid,PDO::FETCH_KEY_PAIR);
	$tb[0]='Wybierz test...';
	echo 'Testy: '.CHtml::mk_sel('tbt','tbt',array($tb,0),'ajax sel260');
	$tb=CDBase::dbQuery(
		'SELECT testy.tid,testy.zakres||" ["||kategorie.kat||" ("||przedmioty.przedm||")]" AS zakkatprz
		FROM testy,kategorie,przedmioty
		WHERE kategorie.przid=przedmioty.przid AND (kategorie.uid='.$user->uid.' OR kategorie.uid=1) AND testy.kid=kategorie.kid AND testy.typ=1 AND testy.uid='.$user->uid,PDO::FETCH_KEY_PAIR);
	$tb[0]='Wybierz ankietę...';
	echo '&nbsp;Ankiety: '.CHtml::mk_sel('tba','tba',array($tb,0),'ajax sel260');
	//wygenerowanie listy grup użytkownika
	$tbgr=CDBase::dbQuery('SELECT gid,grupa||" ("||opis||")" as grop FROM grupy WHERE grupy.uid='.$user->uid,PDO::FETCH_KEY_PAIR);
	$tbgr[0]='Wybierz grupę...';
	if (empty($tbgr)) {
		echo ('<p>Brak danych.</p>');
		return false;
	}
	echo '&nbsp;Grupy: '.CHtml::mk_sel('tgn','tgn',array($tbgr,0),'ajax sel260');

	echo '</p>';
	echo '<div>&nbsp;<img id="load" src="'.get_theme_path(false).'imgs/ajax.gif" alt="Pobieram dane..." /></div>';
}
//-- wyświetla wyniki danej grupy z danego testu
function sh_lstWynTe() {
global $user;
	if (CT_CTDEBUG) echo __FUNCTION__.', '.__FILE__.'<br />';
	include_cl(array('testy','pytania'));
	$tid=CValid::getCnt('idt'); if (!CValid::vInt($tid,1)) return CMsg::eadd('Błędny test!');
	$gid=CValid::getCnt('idg'); if (is_null($gid)) $gid=0;
	if (!CValid::vInt($gid,0)) return CMsg::eadd('Błędna grupa!');

	if ($gid) { //jeżeli przeglądam wyniki grup
		$grupa=CDBase::dbQuery('SELECT grupa FROM grupy WHERE gid='.$gid.' AND uid='.$user->uid,PDO::FETCH_COLUMN,true);
		$tbgr=CDBase::dbQuery('SELECT gunp.uid AS uid, nazwisko FROM gunp,users WHERE gunp.uid=users.uid AND gunp.gid='.$gid,PDO::FETCH_KEY_PAIR);
		if (empty($tbgr)) return CMsg::eadd('Brak wyników!');
		$tbuids=array_keys($tbgr);
		$wyniki=new wyniki(null,$tbuids,$tid,$gid);
		$wyniki->getTb2();
	} else { //wyniki anonimowe z testu publicznego
		$wyniki=new wyniki(null,null,$tid);
		$wyniki->getTb2('SELECT * FROM wyniki WHERE gid=0 AND ');
		$wyniki->getDane('tbuids','uid','uid');
		if (empty($wyniki->tbuids)) return CMsg::kadd('<p class="info">Brak wyników.</p>');
		$war=implode(' AND uid=',$wyniki->tbuids);
		$tbgr=CDBase::dbQuery('SELECT uid,nazwisko FROM users WHERE uid='.$war,PDO::FETCH_KEY_PAIR);
	}
	$tdel=CValid::getCnt('tdel');
	//print_r($tdel);
	if ($tdel) $wyniki->remWyn($tdel); //usuwanie wyników
	//echo ($wyniki->ilew).'<hr />';
	if (!$wyniki->ilew) return CMsg::eadd('Brak wyników.');

	$test=new testy($tid,null,$user->uid);
	$test->explo[]='oceny';
	$test->explo[]='progi';
	$test->getTb2('SELECT testy.tid,testy.typ,testy.wer,testy.zakres,ord,ilep,skale.oceny,skale.progi,przedmioty.przedm
		FROM testy,skale,przedmioty
		WHERE testy.skid=skale.skid AND testy.przid=przedmioty.przid AND ');

	if ($test->typ) echo '<h4>Ankieta: '.$test->zakres.' ['.$test->wer.', '.$test->przedm.']</h4><h5><a href="?acc=11&amp;acc3=aps&amp;idt='.$tid.'&amp;idg='.$gid.'">Podsumowanie (grupa)</a></h5>';
	else echo '<h4>Test: '.$test->zakres.' ['.$test->wer.', '.$test->przedm.']</h4>';
	if ($gid) echo '<h5>Grupa: '.$grupa.'</h5>';
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="wfrm" id="wfrm">
			<input type="hidden" name="acc3" value="lwt" />';
	echo '<table class="tab sortable" id="tbsort">';
	if ($test->typ) {//ankieta
		CHtml::tbHead(array('Lp','Nazwisko','Data','Czas','Usuń','Szczegóły'),array('','','','','unsortable tc','unsortable tc'));	
	} else {//test
		$wyniki->getOceny($test->tb);
		CHtml::tbHead(array('Lp','Nazwisko','Data','Czas','[%][pkt/max]','Ocena','Usuń','Szczegóły'),array('','','','','','','unsortable tc','unsortable tc'));
		echo '<tfoot><tr><td colspan="4">&nbsp;</td><td colspan="2" class="tc pb">Średnia: '.$wyniki->srednia.'</td><td>Wszystko <input class="seltdel" title="Zaznacz wszystko" type="checkbox" /></td></tr></tfoot>';
	}
	$i=0;
	
	echo '<tbody>';
	foreach ($wyniki->tb as $wid => $v) {
		if ($test->typ) //ankieta
		CHtml::tbRow(array(
			++$i,
			'<a href="?acc='.CValid::$acc.'&amp;idu='.$v['uid'].'&amp;acc3=wsh">'.$tbgr[$v['uid']].'</a>',
			date("d.m.Y H:i:s",$v['datas']),
			fCzodp($v['datak']-$v['datas']),
			'<input class="tdel" type="checkbox" name="tdel[]" value="'.$wid.'" />',
			'<a class="but" href="?acc='.CValid::$acc.'&amp;acc2='.$wid.'&amp;acc3=wsd&amp;idu='.$v['uid'].'">Pokaż</a>'
			),array('tc','','','','tc','tc','tc','tc')
		);
		else
		CHtml::tbRow(array(
			++$i,
			'<a href="?acc='.CValid::$acc.'&amp;idu='.$v['uid'].'&amp;acc3=wsh">'.$tbgr[$v['uid']].'</a>',
			date("d.m.Y H:i:s",$v['datas']),
			fCzodp($v['datak']-$v['datas']),
			$v['pro'].'<br />['.$v['ileok'].'/'.$v['mpkt'].']',
			$v['oc'],
			'<input class="tdel" type="checkbox" name="tdel[]" value="'.$wid.'" />',
			'<a class="but" href="?acc='.CValid::$acc.'&amp;acc2='.$wid.'&amp;acc3=wsd&amp;idu='.$v['uid'].'">Pokaż</a>'
			),array('tc','','','','tc','tc','tc','tc')
		);
	}
	echo '</tbody>';
	echo '</table>';
	echo '<input type="hidden" name="idt" value="'.$tid.'" /><input type="hidden" name="idg" value="'.$gid.'" />';
	echo '<input class="cb but flr" type="submit" value="Zapisz" /></form>';
}

function sh_UczWyn() {
	include_cl(array('testy'));
	$idu=CValid::getCnt('idu','g');
	if (!CValid::vInt($idu,2)) return CMsg::eadd();
	CHtml::sh_wyniki($idu);
}

function sh_WDetal() {
	$idu=CValid::getCnt('idu','g');
	if (!CValid::vInt($idu,2)) $idu=0;//wynik anonimowy?
	CHtml::sh_wdetal($idu);
}
// -- podsumowanie ankiety
function sh_Aps() {
	include_cl(array('testy','pytania'));
	$tid=CValid::getCnt('idt'); if (!CValid::vInt($tid,1)) return CMsg::eadd('Błędny test!');
	$gid=CValid::getCnt('idg'); if (!CValid::vInt($gid,0)) return CMsg::eadd('Błędna grupa!');
	$wyniki=new wyniki(null,null,$tid,$gid);
	$wyniki->getTb2();
//print_r($wyniki->tb);echo '<hr />';
	if (!$wyniki->ilew) return CMsg::eadd('Brak wyników.');
	$wyniki->getDane('tbwids','wid','wid');
//echo print_r($wyniki->tbwids);echo '<hr />';
//	foreach ($wyniki->tbwids as $wid) {
//		$tbw=CDBase::dbQuery('SELECT * FROM wynank WHERE wid='.$wid);
		//print_r($tbw);echo '<hr />';
//	}
//	return;
//print_r($wyniki->tbdatas);return;
	$wynank = new wynpyt($wyniki->tbwids,null,1);
	$wynank->getTb2('SELECT OID,pid,odp FROM wynank WHERE ');
	$wynank->getDane('tbpids','pid','pid');
	$pytania = new pytania($wynank->tbpids);
	$pytania->getTb2('SELECT pid,typ,pyt,odpt FROM pytania WHERE ');

	$test = new testy($tid);
	$test->getTb2('SELECT tid,wer,zakres,przedm FROM testy,przedmioty WHERE testy.przid=przedmioty.przid AND ');
	echo '<h4>Ankieta: '.$test->zakres.' ['.$test->wer.', '.$test->przedm.'][Ankiet: '.$wyniki->ilew.']</h4>';
	if ($gid) echo '<h5>Grupa: '.CDBase::dbQuery('SELECT grupa FROM grupy WHERE gid='.$gid,PDO::FETCH_COLUMN,true).'</h5>';
	$tbwyniki=array();
//print_r($wynank->tb);return;
	foreach ($wynank->tb as $oid => $v) {
		$pid=$v['pid'];
		$typp=$pytania->tb[$pid]['typ'];
		if (!array_key_exists($pid,$tbwyniki)) $tbwyniki[$pid]=array();//inicjacja tablicy do zapisywania wyników pytania
		foreach ($v['odp'] as $odp) {
			if ($typp > 2 && $typp <5) $tbwyniki[$pid][]=clrtxt($odp);
			else {//pytania zamknięte
				if (!array_key_exists($odp,$tbwyniki[$pid])) $tbwyniki[$pid][$odp]=0;
				$tbwyniki[$pid][$odp]++;
			}
		}
	}
//print_r($tbwyniki);echo '<hr />';
	$i=0;
	foreach ($tbwyniki as $pid => $tbodp) {
		echo '<div>'.++$i.') '.$pytania->tb[$pid]['pyt'].'</div>';
		echo '<ol class="lpytan">';
		$ileodp=array_sum($tbodp);
		$typp=$pytania->tb[$pid]['typ'];
		foreach ($pytania->tb[$pid]['odpt'] as $k => $odpt) {
			if ($typp > 2 && $typp < 5) {//pytania otwarte
				echo '<li style="list-style-type: none;">';
				echo implode(' || ',$tbodp);
				echo '</li>';
			} else { 
				$procent=0;
				if (isset($tbodp[$k])) $procent=round($tbodp[$k]/$ileodp*100);
				if ($procent>0)
					echo '<li>'.$odpt.'</br><div class="ankdiv"><div style="background-color:#ccc; width:'.$procent.'%; text-align:center;">Odp.: '.(isset($tbodp[$k]) ? $tbodp[$k] : 0).' &#8211; 	'.$procent.'%</div></div></li>';
				else
					echo '<li>'.$odpt.'</br><div class="ankdiv"><div>Brak odpowiedzi.</div></div></li>';
			}
		}
		echo '</ol>';
	}
}
//--- sterowanie ---
//$tbtids=CDBase::dbQuery('SELECT DISTINCT wyniki.tid FROM wyniki LEFT JOIN testy ON wyniki.tid=testy.tid WHERE testy.tid IS NULL',PDO::FETCH_COLUMN);
//$tbwids=CDBase::dbQuery('SELECT wid FROM wyniki WHERE tid=3',PDO::FETCH_COLUMN);
//print_r($tbwids);
//$wynpyt= new wynpyt($tbwids,null,0);
//$wynpyt->getTb2();
//print_r($wynpyt->tb);
CHtml::addRes('scripts','sortable.js');
sh_wyniki();
echo '<div id="response">';
switch (CValid::$acc3) {
	case 'lwt': //listuje
		sh_lstWynTe(); //wyniki testu wybranej grupy
	break; //usuń wyniki z testu grupy
	case 'wsh'://pokazywanie wyników ucznia
	case 'wde'://usuwanie wyników ucznia
		sh_UczWyn();//pokazuje/usuwa wyniki ucznia
	break;
	case 'wsd':
		sh_WDetal(); //pokazuje wyniki szczegółowe ucznia
	break;
	case 'aps'://podsumowanie ankiety
		sh_Aps();
	break;
}
echo '</div>';
?>