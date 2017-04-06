<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }

//$acc2='nic';
//if (isset($_GET['acc2'])) $acc2=substr(trim($_GET['acc2']),0,3);

function showerr() {
	echo '<p>Witaj Josefie...</p>';
	exit();
}
	switch (CValid::$acc2) {
		case 'tok'://request for password
		//na razie nieużywane!
		return;
			if (!$user->uid) showerr();
			$send=0;
			$kom='';
			$rpass=rand_pass();
			$ilet=CDBase::dbQuery('SELECT ilet FROM utok WHERE uid='.$user->uid,PDO::FETCH_COLUMN,true);
			if ($ilet) {
				$ilet_t=CDBase::dbQuery('SELECT COUNT(tid) FROM testy WHERE uid='.$user->uid,PDO::FETCH_COLUMN,true);
				if (($ilet == $ilet_t)) {
					CDBase::dbSetq('UPDATE utok SET tok = ? WHERE uid = ?',array(array(sha1($rpass),$user->uid)));
					$send=CDBase::dbExec();
				}
			} else {//jeżeli nie przyznano żadnego hasła
				CDBase::dbSetq('DELETE FROM utok WHERE uid = ?',array(array($user->uid)));
				CDBase::dbSetq('INSERT INTO utok (uid,tok) VALUES (?,?)',array(array($user->uid,sha1($rpass))));
				$send=CDBase::dbExec();
			}
			if ($send && mail($user->email,'Aktywacja konta nauczyciela','Twoje hasło: '.$rpass)) ; else $send=0;
			echo $send.(CT_CTDEBUG ? ('_'.$kom.$rpass) : $kom);
		break;
		case 'tep'://lista testów z danego przedmiotu(testy.php)
			$przid=CValid::getCnt('acc3','g');
			if (!CValid::vInt($przid,1)) noret('Brak przedmiotu!');
			$testy=CDBase::dbQuery('SELECT tid,zakres,wer,czas,ord,kat FROM testy,kategorie WHERE testy.kid=kategorie.kid AND testy.typ=0 AND testy.pub=1 AND testy.przid='.$przid);
			if (empty($testy)) noret('Brak testów.');
			sh_testy($testy);
		break;
		case 'anp'://lista ankiet z danego przedmiotu(ankiety.php)
			$przid=CValid::getCnt('acc3','g');
			if (!CValid::vInt($przid,1)) noret('Brak przedmiotu!');
			$testy=CDBase::dbQuery('SELECT tid,zakres,wer,czas,ord,kat FROM testy,kategorie WHERE testy.kid=kategorie.kid AND testy.typ=1 AND testy.pub=1 AND testy.przid='.$przid);
			if (empty($testy)) noret('Brak ankiet!');
			sh_testy($testy,null,1);
		break;
		case 'tek'://lista testów dla wybranej grupy
			$gid=CValid::getCnt('acc3','g');
			if (!CValid::vInt($gid,1)) noret('Brak grupy!');
			$testy=CDBase::dbQuery('SELECT testy.tid,zakres,wer,czas,ord,kat FROM testy,kategorie,testg WHERE testy.tid=testg.tid AND testy.kid=kategorie.kid AND testy.typ=0 AND testg.gid='.$gid);
			if (empty($testy)) noret('Brak testów!');
			sh_testy($testy,$gid);
		break;
		case 'tgn': //zwróć listę testów danego nauczyciela dla danej grupy, wywoływane w ewyniki.php
			$gid=CValid::getCnt('acc3','g');
			if (!CValid::vInt($gid,1)) noret('Brak grupy!');
			$testy=CDBase::dbQuery('SELECT testy.tid,zakres,wer,kat FROM testy,kategorie,testg WHERE testy.tid=testg.tid AND testy.kid=kategorie.kid AND testg.gid='.$gid.' AND testy.uid='.$user->uid);
			if (empty($testy)) { noret('Brak danych.'); return; }
			header("Content-Type: text/html; charset=utf-8");
			$tbgr=CDBase::dbQuery('SELECT gunp.uid AS uid, nazwisko FROM gunp,users WHERE gunp.uid=users.uid AND gunp.gid='.$gid,PDO::FETCH_KEY_PAIR);
			if (!empty($tbgr)) {
				asort($tbgr,SORT_LOCALE_STRING);
				$tbgr[0]='Wybierz ucznia...';
				echo '<p id="wynucz">Uczniowie: '.CHtml::mk_sel('uwn','uwn',array($tbgr,0),'ajax sel260').'</p>';
			} else echo '<p class="info">Grupa pusta!</p>';
			sh_testyn($testy,$gid);
		break;
		case 'tbt': //lista wyników testu danego nauczyciela
			include_cl(array('testy','wyniki'));
			$tid=CValid::getCnt('acc3','g'); if (!CValid::vInt($tid,1)) noret('Brak testu!');
			$wyniki=new wyniki(null,null,$tid);
			$wyniki->order = 'ORDER BY datak DESC';
			$wyniki->getTb2();
			header("Content-Type: text/html; charset=utf-8");
			if (empty($wyniki->ilew)) { echo '<p class="info">Brak wyników!</p>'; return; }
			$wyniki->getDane('tbuids','wid','uid');
			$tbnazwiska=CDBase::dbQuery('SELECT uid,nazwisko FROM users WHERE uid='.implode(' OR uid=',$wyniki->tbuids),PDO::FETCH_KEY_PAIR);
			$test=new testy($tid,null,$user->uid);
			$test->explo[]='oceny';
			$test->explo[]='progi';
			$test->getTb2('SELECT testy.tid,testy.typ,testy.wer,testy.zakres,ord,ilep,skale.oceny,skale.progi,przedmioty.przedm
				FROM testy,skale,przedmioty
				WHERE testy.skid=skale.skid AND testy.przid=przedmioty.przid AND ');

			echo '<table class="tab sortable" id="tbsort">';
			echo '<h4>Test: '.$test->zakres.' ['.$test->wer.', '.$test->przedm.']</h4>';
			$wyniki->getOceny($test->tb);
			CHtml::tbHead(array('Lp.','Nazwisko','Data','Czas','[%][pkt/max]','Ocena','Grupa','Szczegóły'),array('','','','','','unsortable tc'));				
			$i=0;
			foreach ($wyniki->tb as $wid => $v) {
				$grupa='Brak';
				if ($v['gid']) {
					$grupa=CDBase::dbQuery('SELECT grupa FROM grupy WHERE gid='.$v['gid'],PDO::FETCH_COLUMN,true);
					$grupa='<a href="?acc=11&amp;acc3=lwt&amp;idt='.$tid.'&amp;idg='.$v['gid'].'">'.$grupa.'</a>';
				}
				$nazwisko='Anonim';
				if (isset($tbnazwiska[$v['uid']])) $nazwisko='<a href="?acc=11&amp;idu='.$v['uid'].'&amp;acc3=wsh">'.$tbnazwiska[$v['uid']].'</a>';
				$styl='tbrow'.($i+1)%2;
				CHtml::tbRow(array(
					++$i,
					'<a href="?acc=11&amp;idu='.$v['uid'].'&amp;acc3=wsh">'.$nazwisko.'</a>',
					date("d.m.Y H:i:s",$v['datas']),
					fCzodp($v['datak']-$v['datas']),
					$v['pro'].'<br />['.$v['ileok'].'/'.$v['mpkta'].']',
					$v['oc'],
					$grupa,
					'<a class="but" href="?acc=11&amp;acc2='.$v['wid'].'&amp;acc3=wsd&amp;idu='.$v['uid'].'">Pokaż</a>'
					),array('tc','','','','tc','tc','tc','tc'),$styl
				);
			}
			echo '</table>';
		break;
		case 'tba'://lista wyników ankiet danego nauczyciela
			include_cl(array('testy','wyniki'));
			$tid=CValid::getCnt('acc3','g');
			if (!CValid::vInt($tid,1)) noret('Brak testu!');
			$wyniki=new wyniki(null,null,$tid);
			$wyniki->getTb2();
			header("Content-Type: text/html; charset=utf-8");
			if (empty($wyniki->ilew)) {echo '<p class="info">Brak wyników!</p>'; return; }
			$wyniki->getDane('tbuids','wid','uid');
			$tbnazwiska=CDBase::dbQuery('SELECT uid,nazwisko FROM users WHERE uid='.implode(' OR uid=',$wyniki->tbuids),PDO::FETCH_KEY_PAIR);
			$test=new testy($tid,null,$user->uid);
			$test->getTb2('SELECT testy.tid,testy.typ,testy.wer,testy.zakres,ord,przedmioty.przedm
				FROM testy,przedmioty
				WHERE testy.przid=przedmioty.przid AND ');

			echo '<table class="tab sortable" id="tbsort">';
			echo '<h4>Ankieta: '.$test->zakres.' ['.$test->wer.', '.$test->przedm.']</h4><h5><a href="?acc=11&amp;acc3=aps&amp;idt='.$tid.'&amp;idg=0">Podsumowanie</a></h5>';
			CHtml::tbHead(array('Lp.','Nazwisko','Data','Czas','Grupa','Szczegóły'),array('','','','','','unsortable tc'));
			$i=0;
			foreach ($wyniki->tb as $wid => $v) {
				$grupa='Brak';
				if ($v['gid']) {
					$grupa=CDBase::dbQuery('SELECT grupa FROM grupy WHERE gid='.$v['gid'],PDO::FETCH_COLUMN,true);
					$grupa='<a href="?acc=11&amp;acc3=lwt&amp;idt='.$tid.'&amp;idg='.$v['gid'].'">'.$grupa.'</a>';
				}
				$nazwisko='Anonim';
				if (isset($tbnazwiska[$v['uid']])) $nazwisko='<a href="?acc=11&amp;idu='.$v['uid'].'&amp;acc3=wsh">'.$tbnazwiska[$v['uid']].'</a>';
				$styl='tbrow'.($i+1)%2;
				CHtml::tbRow(array(
					++$i,
					$nazwisko,
					date("d.m.Y H:i:s",$v['datas']),
					fCzodp($v['datak']-$v['datas']),
					$grupa,
					'<a class="but" href="?acc=11&amp;acc2='.$v['wid'].'&amp;acc3=wsd&amp;idu='.$v['uid'].'">Pokaż</a>'
					),array('tc','','','','tc','tc'),$styl
				);
			}
			echo '</table>';
		break;
		case 'tbk'://lista testów z wybranej kategorii (etesty)
			include_cl(array('testy'));
			$kid=CValid::getCnt('acc3','g');
			if (!mk_tbtesty($user->uid,$kid)) noret();
		break;
		case 'uwn': //zwróć wyniki ucznia
			include_cl(array('testy','wyniki'));
			$idu=CValid::$acc3;
			$_SESSION['gid']=CValid::getCnt('idg','g');
			CHtml::sh_wyniki($idu);
		break;
		case 'ank'://lista ankiet dla wybranej grupy
			$gid=CValid::getCnt('acc3','g');
			if (!CValid::vInt($gid,1)) noret('Brak grupy!');
			$testy=CDBase::dbQuery('SELECT testy.tid,zakres,wer,czas,ord,kat FROM testy,kategorie,testg WHERE testy.tid=testg.tid AND testy.kid=kategorie.kid AND testy.typ=1 AND testg.gid='.$gid);
			if (empty($testy)) noret('Brak ankiet!');
			sh_testy($testy,$gid,1);
		break;
		case 'img'://obrazek
			$imn=$_GET['imn'];//image file name
			$media=new media();
			$media->set_img($imn);
			if ($media->mkthumb()) $media->show();
		break;
		case 'ocn'://ocenianie pytania otwartego
			$winfo=explode('#',CValid::getCnt('acc3','g'));
			//rowid,wid,uid,tid,datas,ilepkt lub ileok,idu,tid,datas
			header("Content-Type: text/html; charset=utf-8");
			if (count($winfo)<3) {//zaktualizuj ilość dobrych odpowiedzi w tabeli wynik
				if (updWyniki($winfo)) echo 'Zaktualizowano ilość poprawnych odpowiedzi. Przeładuj stronę (F5)!';
				else echo 'Nie zaktualizowano wyniku (ileok)!';
				return;
			}
			//zapisanie wyniku pytania otwartego i aktualizacja wyniku dobrych odpowiedzi
			$success=-1;
			$rowid=array_shift($winfo);
			$wid=array_shift($winfo);
			$ilepkt=array_pop($winfo);
			//print_r($winfo);
			CDBase::dbSetq('UPDATE wynpyt set wyn = ? WHERE rowid = ?',array(array($ilepkt,$rowid)));
			if (CDBase::dbExec()) {
				$success=1;
				$sum=CDBase::dbQuery('SELECT sum(wyn) FROM wynpyt WHERE wid='.$wid,PDO::FETCH_COLUMN,true);
				if (updWyniki(array($sum,$wid))) $success=2; else $success=0;
			}
			if ($success>1) echo 'Zaktualizowano wynik. Przeładuj stronę [F5]!';
			else if ($success>0) echo 'Błąd: nie zaktualizowano wyniku!';
			else echo 'Błąd: nie zaktualizowano punktów i oceny!';
		break;
		default:
			showerr();
	}
//--- aktualizacja ilości poprawnych odpowiedzi w tabeli wyniki
function updWyniki($tbd) {
	CDBase::dbSetq('UPDATE wyniki SET ileok = ? WHERE wid = ?',array($tbd));
	if (CDBase::dbExec()) return true;
	return false;
} 


//--- lista testów przypisanych danej grupie
function sh_testy($tb,$gid=null,$typ=0) {
	foreach ($tb as $k => $v) if (empty($v['ord'])) unset($tb[$k]);
	if (empty($tb)) noret('Brak testów!');
	CHtml::addRes('scripts','sortable.js');
	if ($typ) $acc=3; else $acc=2;
	header("Content-Type: text/html; charset=utf-8");
	echo '<table class="tab sortable" id="tbsort">';
	CHtml::tbHead(array('Lp.','Kategoria','Zakres','Wersja','Czas [min.]','Pytań','Akcja'),array('','','','','','','unsortable'));
	$i=0;
	foreach ($tb as $k => $v) {
		$styl='tbrow'.($i+1)%2;
		CHtml::tbRow(array(
				++$i,
				$v['kat'],
				$v['zakres'],
				$v['wer'],
				$v['czas'],
				count(explode('#',$v['ord'])),
				'<a class="but" href="?acc='.$acc.'&amp;idt='.$v['tid'].'&amp;idg='.$gid.'">Rozwiąż</a>'
			),array('tc','','','tc','','tc'),$styl
		);
	}
	echo '</table>';
}
//-- lista testów danego nauczyciela rozwiązanych przez daną grupę
function sh_testyn($tb,$gid,$typ=0) {
	CHtml::addRes('scripts','sortable.js');
	echo '<table class="tab sortable" id="tbsort">';
	CHtml::tbHead(array('Lp.','Kategoria','Zakres','Wersja','Szczegóły'),array('','','','tc','unsortable tc'));
	$i=0;
	foreach ($tb as $k => $v) {
		$styl='tbrow'.($i+1)%2;
		CHtml::tbRow(array(
			++$i,
			$v['kat'],
			$v['zakres'],
			$v['wer'],
			'<a class="but" href="?acc=11&amp;acc3=lwt&amp;idt='.$v['tid'].'&amp;idg='.$gid.'">Pokaż</a>'
			),array('tc','','','tc','tc'),$styl
		);
	}
	echo '</table>';
}
//---brak wyników---
function noret($kom='Brak danych') {
	header("Content-Type: text/html; charset=utf-8");
	echo '<p class="info">'.$kom.'</p>';
	exit();
}
?>