<?php
function isTestStart() {
global $sesja;
	if (isset($sesja->test['tid'])) { //może test jest rozwiązywany
		if ($sesja->test['typ']) {//ankieta
			echo '<p>Wygląda na to, że rozpocząłeś wypełnianie ankiety, ale nie odpowidziałeś na wszystkie pytania. Możesz:</p>
			<ul>
				<li><a href="?acc='.CValid::$acc.'&amp;idt='.$sesja->test['tid'].'">Dokończyć</a> ankietę,</li>
				<li><a href="?acc='.CValid::$acc.'&amp;acc2=del">Anulować</a> wypełnianie.</li>
			</ul>
		';
		} else {
			echo '<p>Wygląda na to, że rozpocząłeś rozwiązywanie testu i go nie skończyłeś! Możesz:</p>
				<ul>
					<li><a href="?acc='.CValid::$acc.'&amp;idt='.$sesja->test['tid'].'">Dokończyć</a> test</li>
					<li><a href="?acc='.CValid::$acc.'&amp;acc2=del">Anulować</a> test, ale jeżeli jest to test dla grupy, wyniki zostaną zapisane.</li>
				</ul>
			';
		}
		return true;
	}
	return false;
}
//----------------------------------------------------------------------------
function sh_testy($user) {
	include_cl(array('grupy'));
	if (isTestStart()) return;
	include_cl(array('przedmioty'));
	$przedmioty=CDBase::dbQuery('SELECT przedmioty.przid,przedmioty.przedm FROM przedmioty,testy WHERE przedmioty.przid=testy.przid',PDO::FETCH_KEY_PAIR);
	$przedmioty[0]='Wybierz przedmiot...';
	echo '<p class="cb">';
	if ($user->c) {
		if ($user->u) {
			$tbgr=CDBase::dbQuery('SELECT grupy.gid,grupy.grupa||" ("||grupy.opis||")" as grupa FROM grupy,gunp WHERE grupy.gid=gunp.gid AND gunp.uid='.$user->uid,PDO::FETCH_KEY_PAIR);
			$tbgr[0]='Wybierz grupę...';
			if (empty($tbgr)) return CMsg::kadd('Nie należysz do żadnej grupy');
			(CValid::$acc==3) ? $id='ank' : $id='tek';
			echo 'Testy dla grup:&nbsp;'.CHtml::mk_sel($id,$id,array($tbgr,0),'ajax sel260').'&nbsp;&nbsp;&nbsp;&nbsp;';
		}
	}
	(CValid::$acc==3) ? $id='anp' : $id='tep';
	echo '<br />Testy publiczne:&nbsp;'.CHtml::mk_sel($id,$id,array($przedmioty,0),'ajax sel260').'</p>';
	echo '<div id="response"><p>&nbsp;</p></div><div id="load"><img src="'.get_theme_path(false).'imgs/ajax.gif" alt="Loading content..." /></div>';
}
//---------------------------------------
function fpytodp($tbp) {//wyświetl odpowiedzi do pytania $tbp
global $sesja;
	$odpt=$tbp['odpt'];
	$pid=$tbp['pid'];
	$typy=array('text','radio','checkbox','text','textarea','select');
	if (isset($sesja->tbimgs[$pid])) {
		foreach ($sesja->tbimgs[$pid] as $img) {
			if (strpos($img[0],$pid.'_t_')===false) $tbimg[]=$img; else $tbimgp[]=$img; // do poszczególnych tabel trafiają obrazki do całego pytania lub do możliwych odpowiedzi
		}
	}
	if (isset($tbimgp)) $ileodp=count($tbimgp); else $ileodp=count($odpt);//ilość podpunktów
	if (isset($sesja->tbn[$pid])) $tbn=$sesja->tbn[$pid];//jeżeli pytanie było już wyświetlane, nie zmieniaj kolejności
	else {//tbn nie istnieje, trzeba ją utworzyć
		$tbn=range(0,$ileodp-1);//utworzenie tabeli z wartościami <0;($count-1)>
		if ($sesja->test['loso'] && !isset($sesja->run) && ($tbp['typ']<3 || $tbp['typ']>5)) {
			shuffle($tbn); //losowe odpowiedzi tylko dla typów 0-2,6
		}
		$sesja->sget('tbn',$pid,$tbn);
	}
	if (isset($sesja->odpi[$pid])) $odpi=$sesja->odpi[$pid]; else $odpi=array();//tablica udzielonych odpowiedzi

//--pokaż obrazki do pytania
	if (isset($tbimg)) {
		echo '<div class="dimg">';
		foreach ($tbimg as $img) {//fname,opis
			if (strpos($img[0],$pid.'_t_')===false) { //wywietl obrazek dla całego pytania
				echo '<img src="'.CT_URL.CT_IMGS.$img[0].'" class="pytimg" alt="'.$img[1].'" />&nbsp;';
			}
		}
		echo('</div>');
	}
//--koniec obrazki do pytania

	echo '
				<div class="pytodp"><ol class="lpytan">';
	for ($i=0; $i<$ileodp; $i++) {
		if (isset($tbimgp)) {
			$img=$tbimgp[$tbn[$i]];//indeks obrazka musi odpowiadać indeksowi podpunktu
		}
		switch ($tbp['typ']) {
		case 0://pytania typu text
			if (isset($tbimgp)) echo '<li><img class="imgt" src="'.CT_IMGS.$img[0].'" alt="'.$img[1].'" />';
			else echo '<li>'.fhtml($odpt[$tbn[$i]]).'&nbsp;';
			echo '<input class="pwybor itext" type="text" name="p'.$pid.'[]" id="p'.$pid.$i.'" size="40" value="'.(isset($odpi[$i]) ? hhash($odpi[$i],false) : '').'" /></li>';
		break;
		case 1://typ radio
		case 2://typ checkbox
			//było po pierwszym input <input type="hidden" name="tbto'.$pid.'[]" value="'.$tbn[$i].'" />
			echo '<li><input class="pwybor itext" type="'.$typy[$tbp['typ']].'" name="p'.$pid.'[]" id="p'.$pid.$i.'" value="'.$i.'" ';
			if (in_array($i,$odpi)) echo 'checked="checked" ';
			echo '/>';
			if (isset($tbimgp)) echo '<img class="imgt" src="'.CT_IMGS.$img[0].'" alt="'.$img[1].'" />';
			else echo '&nbsp;'.fhtml($odpt[$tbn[$i]]).'</li>';
		break;
		case 3://typ short user text
			echo '<li>';
			if (isset($tbimgp)) echo '<img class="imgt" src="'.CT_IMGS.$img[0].'" alt="'.$img[1].'" />';
			echo '<input class="pwybor itext popen" type="text" name="p'.$pid.'[]" id="p'.$pid.$i.'" size="100" value="'.(isset($odpi[$i]) ? hhash($odpi[$i],false) : '...'.fhtml($odpt[$tbn[$i]])).'" /></li>';
		break;
		case 4://long user textarea
			echo '<li>';
			if (isset($tbimgp)) echo '<img class="imgt" src="'.CT_IMGS.$img[0].'" alt="'.$img[1].'" />';
			echo '<textarea class="pwybor itext popen" name="p'.$pid.'[]" id="p'.$pid.$i.'" cols="85" rows="2">'.(isset($odpi[$i]) ? hhash($odpi[$i],false) : '...'.fhtml($odpt[$tbn[$i]])).'</textarea></li>';
		break;
		case 5://select
			$odpt=htmlspecialchars_decode(fhtml($odpt[0]),ENT_QUOTES);
			if (preg_match_all("/\[(\D+[#]*\D*)\]/U", $odpt, $matches)) {// poprzedni wzorzec "/\[(\D+[.#]\D+)\]/U"
				$tbn=range(0,count($matches)-1);
				foreach ($matches[1] as $k => $match) {
					$tb=explode('#',$match);
					if (count($tb)>1) { //kilka odpowiedzi select
						shuffle($tb);
						array_unshift($tb,'Wybierz odpowiedź...');
						if (isset($odpi[$k])) $idw=hhash($odpi[$k],false); else $idw=null;//odtworzenie udzielonych odpowiedzi
						$str='<select class="seltxt" name="p'.$pid.'[]">'.CHtml::mk_opts(array($tb,$idw,1)).'</select>';
					} else { //jedna poprawna odpowiedź text
						$str='<input class="pwybor itext" name="p'.$pid.'[]" id="p'.$pid.$k.'" size="20" value="'.(isset($odpi[$k]) ? hhash($odpi[$k],false) : '').'" />';
					}
					$odpt=str_replace('['.$match.']',$str,$odpt);
				}
				echo '<li><p>'.$odpt.'</p></li>';
			}
		break;
		default:
			echo 'Błąd!';
		}
	}
	echo '</ol></div>';
}
//----------------------------------------------------------------------------
function ftclean() {//wyczyść dane z sesji dotyczące rozwiązanego testu
global $sesja;
	$tb=array('test','tbpids','tbodpu','tbodp','tbsav','tbmpkt','tbn','odpi','run','tboc','tbtyp','tbimgs','uid','nazwisko','gid');
	foreach ($tb as $v) unset($sesja->$v);
}
//----------------------------------------------------------------------------
function mk_test($user) {
global $sesja;
include_cl(array('testy','pytania'));
	if (isset($sesja->test['tid'])) {//test został zainicjowany i jest rozwiązywany
		if (!isset($sesja->tbpids)) return CMsg::eadd('Niepełne dane rozwiązywanego testu!');
		$sesja->run=1;
		$pytania=new pytania();
		$pytania->getTb2('SELECT pytania.* FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$sesja->test['tid']);
		if ($sesja->test['losp'] || $sesja->test['ilep']>0) {//przywróc właściwą kolejność pytań lub odrzuć niewylosowane pytania
			foreach ($sesja->tbpids as $pid) $tbpyt[$pid]=$pytania->tb[$pid];
			$pytania->tb=$tbpyt;
			$pytania->ids=$sesja->tbpids;
			$pytania->ilew=$sesja->test['ilep'];
		}
	} else { //test trzeba zainicjować
		ftclean();//wyczyść ewentualne dane poprzedniego testu
		$tid=CValid::getCnt('idt');
		if (!CValid::vInt($tid,1)) return CMsg::eadd('Niepoprawny identyfikator testu.');
		$test=new testy($tid);
		$test->getTb2('SELECT tid,typ,wer,zakres,skid,czas,ord,losp,loso,pub,kat,ilep FROM testy,kategorie WHERE testy.kid=kategorie.kid AND ');
		$ord=$test->ord;
		if (empty($ord)) return CMsg::eadd('Brak pytań!');
		$pytania=new pytania();
		$pytania->getTb2('SELECT pytania.* FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$tid);
		if ($test->ilep>0) {//losujemy pulę pytań z testu
			shuffle($pytania->tb);
			$pytania->tb=array_slice($pytania->tb,0,$test->ilep,true);
			foreach ($pytania->tb as $k => $v) $tbpyt[$v['pid']]=$v;
			$pytania->tb=$tbpyt;
			$pytania->ids=array_keys($pytania->tb);
			$pytania->ilew=$test->ilep;
		}

		$tmptb=current($test->tb);
		$tmptb['ilepyt']=count($pytania->ids);
		$tmptb['czas']=$test->czas;
		$tmptb['stime']=time();//czas rozpoczęcia
		if ($tmptb['losp']) shuffle($pytania->ids);//losuj kolejność pytań
		$sesja->tbpids=$pytania->ids;
		$sesja->test=$tmptb;
		foreach ($pytania->ids as $pid) {//
			$v=$pytania->tb[$pid];
			$sesja->sget('tbtyp',$pid,$v['typ']);//tbtyp[$pid]=$v['typ']; //tablica typów pytań
			$sesja->sget('tbodpu',$pid,array());//tbodp[$pid]=array(); //tablica udzielanych odpowiedzi - kolejność wg
			$sesja->sget('tbodp',$pid,$v['odp']); //tablica poprawnych odpowiedzi
			$sesja->sget('tboc',$pid,0); //tablica poprawności odpowiedzi, inicjalnie wszystkie odpowiedzi błędne
			$sesja->sget('tbmpkt',$pid,$v['mpkt']);//maksymalna ilość punktów za pytanie
		}
		//pobierz obrazki i materiały dodatkowe
		$war=implode(' OR mp.pid=',$sesja->tbpids);
		$ret=CDBase::dbQuery('SELECT pid,fname,opis FROM mp,media WHERE mp.mid=media.mid AND (mp.pid='.$war.')');
		if ($ret) {
			foreach ($ret as $v) {
				$tb[$v['pid']][]=array($v['fname'],$v['opis']);
			}
			$sesja->tbimgs=$tb;
		} else $sesja->tbimgs=array();
		$sesja->uid=$user->uid;$sesja->nazwisko=$user->nazwisko;
		//jeżeli użytkownik nie wybiera testu przez listę grup, wartość gid wyniesie 0;
		$gid=CValid::getCnt('idg','g'); if (!CValid::vInt($gid,1)) $gid=0;
		$sesja->gid=$gid;
		CMsg::kadd('Zainicjowano dane nowego(ej) testu/ankiety!');
	}
	//return;
	//print_r($_SESSION);return;
	//-- pokaż pytania ----------------------------------------------------------------------
	CHtml::addRes('scripts','cdown.js');
	$kod='';
	echo ('<h3>'.$sesja->test['zakres'].' ['.$sesja->test['wer'].'_'.$sesja->test['tid'].']<br />
	<span class="subh3">Kategoria: '.$sesja->test['kat'].'</span><br />
	<span class="subh3">Pytań: <strong>'.$sesja->test['ilepyt'].'</strong></span>
	</h3>');
	echo '<div id="tinfo">
				<p><span class="pb">Użytkownik: '.($user->uid ? $user->nazwisko : 'Anonim').'. Grupa: '.$sesja->gid.'.</span><br />
				Czas rozpoczęcia: '.date("d.m.Y H:i:s",$sesja->test['stime']).'</p>
			</div>';

	$nrpyt=0;
	echo('
			<div id="pytania">');
	//print_r($test->pyt->tb);
	foreach ($pytania->ids as $pid) {//$test->pyt->tb
		$v=$pytania->tb[$pid];
		$nrpyt++;
		echo('
			<div id="pyt'.$nrpyt.'" class="pyt">
				<input type="hidden" name="tk[]" value="'.$v['pid'].'" />
				<input type="hidden" name="typy[]" id="t'.$nrpyt.'" value="'.$v['typ'].'" />
				<input type="hidden" id="pmod'.$nrpyt.'" value="0" />
				<p class="pyt_tresc"><strong>'.$nrpyt.')</strong> ['.$v['mpkt'].' pkt.] '.nl2br(fhtml($v['pyt'])).'</p>');
//--pokaż tekst do analizy
	if (!empty($v['txt'])) echo '<div class="dtxt">'.nl2br(fhtml($v['txt'])).'</div>';
//--pokaż odpowiedzi pytania
		fpytodp($v);//$kod.=fpytodp($v);
//--koniec odpowiedzi pytania
		echo '
			</div>';
	}
	echo '
			</div>';
	echo '
	<form name="test" id="test" method="post" action="?acc='.CValid::$acc.'&amp;acc2=oct">';
	if ($sesja->test['czas']) {
		$czas=time()-$sesja->test['stime'];//ile sekund upłynęło od początku testu
		if ( $czas <= ($sesja->test['czas']*60) ) {//jeżeli pozostały czas jest mniejszy/równy od czasu testu w min+1 min
			$czas=($sesja->test['czas']*60)-$czas;
			$sek=$czas % 60;//sekund
			($sek < 10 ? $sek='0'.$sek : $sek);
			$czas=floor($czas/60);//minut
			$sesja->sget('test','rtime',$czas.':'.$sek);
		} else { //przekroczono czas rozwiązywania testu, dajmy 2 sekundy
			$sesja->sget('test','rtime','00:02');
		}
		echo('
			<div>Czas rozwiązywania testu: '.$sesja->test['czas'].' min.</div>
			<div id="czas1">Pozostały czas: <input type="text" id="czas" name="rtime" value="'.$sesja->test['rtime'].'" size="5" /></div>
			<div id="czaskom">&nbsp;</div>');
	}

	echo '
		<div id="ask">&nbsp;</div>
		<div class="clearfix"></div>
		<input class="but" id="zapisz" type="button" value="Zapisz odpowiedź" />
		<div class="flr">
			Skończyła(e)m: <input type="checkbox" id="sure" />&nbsp;
			<input class="but frmbut" type="submit" value="'.($sesja->test['typ']>0?'Zapisz ankietę':'Oceń test').'" />
		</div>
		<input type="hidden" name="idt" value="'.$sesja->test['tid'].'" />
		<input type="hidden" name="acc" value="'.CValid::$acc.'" />
		<input type="hidden" name="op" id="op" value="ret" />
	</form>
		<input type="hidden" id="ilep" value="'.$sesja->test['ilepyt'].'" />
		<input type="hidden" id="cask" value="1" />
		<div id="answered"></div>
		<div id="load"><img src="'.get_theme_path(false).'imgs/ajax.gif" alt="Working..." /></div>';
//koniec pokaż pytania
}
//----------------------------------------------------------------------------
//Przygotowanie tablicy z odpowiedziami i zapisanie jej w sesji i/lub w tablicy wynpyt
function fodp($user) {
global $sesja;
	$MAX_INP_DL=1024; //maksymalna długość odpowiedzi input text.
	$MAX_TXT_DL=1024; //maksymalna długość odpowiedzi textarea.
	$tk=CValid::getCnt('tk','p');//tablica zawiera pidy
	if (empty($tk)) return;
	$typy=CValid::getCnt('typy','p');//tablica typów pytań
	foreach ($tk as $k => $pid) {
		$tbto=$sesja->tbn[$pid];	//CValid::getCnt('tbto'.$pid,'p');	//tablica losowych indeksów odpowiedzi
		$tbp=CValid::getCnt('p'.$pid,'p');	//tablica zaznaczonych indeksów odpowiedzi lub odpowiedź tekstowa
		if (empty($tbp)) {
			if (isset($_POST['op'])) echo 0;//jeżeli nie udzielono odpowiedzi podczas rozwiązywania testu online
			return;
		}
		$tbp=clrtxt($tbp);
		$sesja->sget('odpi',$pid,$tbp);		//tablica udzielanych odpowiedzi - kolejność z formularza użytkownika
		$odp=array();
		if ($typy[$k] == 5) $odp=$tbp; //w przypadku dyktanda odpowiedzi zapisujemy tak jak dostajemy z $_POST
		else
			foreach ($tbp as $k2 => $v2) {
				switch ($typy[$k]) {
					case 0://typ input text
					case 6://obrazek - pytanie
						if (strlen($v2)>$MAX_INP_DL) $v2=substr($v2,0,$MAX_INP_DL);
						//$v2=clrtxt($v2);
						$odp[$tbto[$k2]]=$v2;
						ksort($odp);
					break;
					case 1:
					case 2:
						$odp[]=$tbto[$v2];
						sort($odp);
					break;
					case 3:
						if (strlen($v2)>$MAX_INP_DL) $v2=substr($v2,0,$MAX_INP_DL);
						//$v2=clrtxt($v2);
						$odp[$tbto[$k2]]=$v2;
					break;
					case 4:
						if (strlen($v2)>$MAX_INP_DL) $v2=substr($v2,0,$MAX_TXT_DL);
						//$v2=clrtxt($v2);
						$odp[$tbto[$k2]]=$v2;
					break;
					default:
						;
				}
			//if ($typy[$k]>0) $odp[]=$tbto[$v2];
			//else { clrtxt($v2); $odp[$tbto[$k2]]=$v2; }
			//if (!array_diff($_SESSION['tboodp'][$pid],$odp)) $oc=1; //ustalanie poprawności odpowiedzi
			}
		$sesja->sget('tbodpu',$pid,$odp);
		$sesja->sget('test','rtime',CValid::getCnt('rtime','p'));//zapisanie pozostałego czasu
		if (isset($_POST['op'])) echo 1; //zwróć informację, że zapisano niepustą odpowiedź
	}
}
//--------------------------------- niewykorzystywana ----
function savWynPyt($uid) {//zapisz odpowiedź na pytanie(a)
global $sesja;
	$tadd=array();
	if (is_null($uid)) $uid=0;
	foreach ($sesja->tbodp as $pid => $odp) {
		if ($sesja->test['typ']) { //ankieta
			if (!empty($odp))//zapisujemy tylko udzielone odpowiedzi
				$tadd[]=array($uid,$pid,$sesja->test['tid'],$sesja->test['stime'],implode('#',$odp));
		} else { //test
			$tadd[]=array($uid,$pid,$sesja->test['tid'],$sesja->test['stime'],implode('#',$odp),$sesja->tboc[$pid]);
		}
	}
	if ($sesja->test['typ']) {//ankieta
		CDBase::dbSetq('INSERT INTO wynank VALUES(?,?,?,?,?)',$tadd);
	} else {//test
		CDBase::dbSetq('INSERT INTO wynpyt VALUES(?,?,?,?,?,?)',$tadd);
	}
	return true;
}
//---------------------------------
function ocen($user) {
global $sesja;
	//zapisanie tabeli odpowiedzi w sesji w razie gdyby użytkownik nie zapisał odpowiedzi i nacisnął "skończyłem"
	if (is_null($sesja->test['tid'])) return CMsg::eadd('Brak testu do oceny!');
	include_cl(array('testy','pytania','wyniki'));
	fodp($user);
	$datak=time(); //moment zakończenia
	$czaso=$datak-$sesja->test['stime']; //czas rozwiązywania
	//echo date("d.m.Y H:i:s",$sesja->test['stime']).'<br />';
	//echo date("d.m.Y H:i:s",$datak).'<br />';
	//echo date("H:i:s",$czaso).'<br />';
	//if (($czaso/60) > $sesja->test['czas']) $datak=$sesja->test['stime']+$sesja->test['czas']*60;
	if (empty($sesja->tbodpu)) return CMsg::eadd('Test być może już rozwiązałeś...');

	$tbo=array('ok'=>0);//tablica oceny testu
	$ileodp = (isset($sesja->odpi) ? count($sesja->odpi) : 0);
	$ilep=count($sesja->tbodpu); //liczba pytań
	if ($sesja->test['typ']) {//ankieta => przygotowanie danych do zapisania
		$ileok=$ileodp; //zmiast ilości punktów zapisujemy ilość udzielonych odpowiedzi
		echo '
		<table>
			<tr class="pb"><td>Użytkownik (grupa):</td><td>'.($user->uid ? $user->nazwisko : 'Anonim').' ('.$sesja->gid.')</td></tr>
			<tr><td>Data:</td><td>'.date("d.m.Y H:i:s",$sesja->test['stime']).'</td></tr>
			<tr><td>Czas rozw.:</td><td>'.fCzodp($czaso).'</td></tr>
			<tr><td>Pytań:</td><td>'.$ilep.'</td></tr>
			<tr><td>Odpowiedzi:</td><td>'.$ileodp.'</td></tr>
			</table>
			<p>Dziękujemy za wypełnienie ankiety.</p>';
	} else {//test => przygotowanie danych
		$ilen=0; //liczba pytań nieocenialnych, tzn. odpowiedzi otwartych
		$ileok=0;//ilość punktów uzyskanych za odpowiedzi
		$ilebad=0;//	'bad'=>0,					//ilość złych odp
		$ocena=1; //ocena
		$procent=0;//ocena w procentach
		$skala=CDBase::dbQuery('SELECT oceny,progi FROM skale WHERE skid='.$sesja->test['skid']);//skala
		if ($skala) {
			$skala=current($skala);
			$skala['oceny']=explode('#',$skala['oceny']);
			$skala['progi']=explode('#',$skala['progi']);
		}

		foreach ($sesja->tbodpu as $pid => $odp) {//porównywanie odpowiedzi
			$tbwyn=array();
			$iledb=0;
			if ($sesja->tbtyp[$pid]<1)//pytania text, ważne są wartości i indeksy
				$tbwyn=array_diff_assoc($sesja->tbodp[$pid],$odp);
			else if ($sesja->tbtyp[$pid] == 2) {//checkbox
				//print_r($sesja->tbodp[$pid]); echo '<br />';
				//print_r($odp); echo '<br />';
				$tbwyn=array_equal($sesja->tbodp[$pid],$odp);
				//print_r($tbwyn);
			} else if ($sesja->tbtyp[$pid] != 3 && $sesja->tbtyp[$pid] != 4) {
				//print_r($sesja->tbodp[$pid]); echo '<br />';
				//print_r($odp); echo '<br />';
				$tbwyn=array_diff($sesja->tbodp[$pid],$odp);
				//print_r($tbwyn);
			}
			$ilezle=count($tbwyn);

			switch($sesja->tbtyp[$pid]) {
				case 0://text
				case 6://img - text
					$iledb = count($sesja->tbodp[$pid])-$ilezle;
					$ilebad += $ilezle;
				break;
				case 1://radio
				case 2://checkbox
					if ($ilezle == 0) $iledb=$sesja->tbmpkt[$pid]; else $ilebad+=$sesja->tbmpkt[$pid];
				break;
				case 3://nie oceniamy odpowiedzi otwartych (tekstowych)
				case 4:
					$ilen += $sesja->tbmpkt[$pid];
					$sesja->sget('tboc',$pid,'');//-($_SESSION['tbmpkt'][$pid]);//wartość pusta oznacza nieocenione
					continue 2;
				break;
				case 5://dyktanda
					$iledb = count($sesja->tbodp[$pid])-$ilezle;
					$ilebad += $ilezle;
				break;
			}
			$ileok+=$iledb;
			$sesja->sget('tboc',$pid,$iledb); //ilość uzyskanych punktów

		}
		$maxpkt=$ileok+$ilebad; //pomijamy nieoceniane
		if ($ileok) {
			$ocena=$ileok/$maxpkt;
			$procent=round($ocena*100,2);
			$ocena=fOcena($ocena,$skala);
		} else $ocena=1;

		echo '
		<table>
			<tr class="pb"><td>Użytkownik (grupa):</td><td>'.($user->uid ? $user->nazwisko : 'Anonim').' ('.$sesja->gid.')</td></tr>
			<tr><td>Data:</td><td>'.date("d.m.Y H:i:s",$sesja->test['stime']).'</td></tr>
			<tr><td>Czas rozw.:</td><td>'.fCzodp($czaso).'</td></tr>
			<tr><td>Pytań:</td><td>'.$ilep.'</td></tr>
			<tr><td>Odpowiedzi:</td><td>'.$ileodp.'</td></tr>
			<tr><td>Punktów:</td><td>'.$ileok.' z maks. '.$maxpkt.' pkt.</td></tr>
			<tr><td>Ocena:</td><td>'.$ocena.' ('.$procent.'%)</td></tr>
			<tr><td>Skala:</td><td>'.fSkala($skala).'</td></tr>';
		if ($ilen) echo '<tr><td colspan="2"><b>Uwaga</b>:
				<ul>
			 	<li><strong>Uwaga: Ocena nie jest ostateczna</strong>, ponieważ uwzględnia tylko pytania zamknięte.<br />
			 	 Pytania otwarte muszą zostać ocenione przez autora testu.<br />Nieprzyznano: '.$ilen.' pkt.</li>
			 	</ul>
			 </td></tr>';

		echo '</table>';
	}
//return;
	if ($user->uid || ($sesja->test['typ'])) {//jeżeli zalogowany użytkownik lub ankieta zapisujemy odpowiedzi
		if (is_null($user->uid)) $user->uid=0;
		$ip=$_SERVER['REMOTE_ADDR'];
		$host=gethostbyaddr($ip);
		if (strpos($host,'.')>1) $host=substr($host,0,strpos($host,'.'));
		$tadd[]=array(NULL,$user->uid,$sesja->gid,$sesja->test['tid'],$host,$sesja->test['stime'],$datak,$ileok,$maxpkt);
//print_r($tadd);echo '<hr />';
		CDBase::dbSetq('INSERT INTO wyniki VALUES(?,?,?,?,?,?,?,?,?)',$tadd);
		if (CDBase::dbExec()) $wid=CDBase::dbGetLastId();
//var_dump($wid);
		if (empty($wid)) return CMsg::eadd('Błąd zapisu wyniku.');
	//przygotuj zapisanie wyników savWynPyt($user->uid);
		$tadd=array();
		foreach ($sesja->tbodpu as $pid => $odp) {
			if ($sesja->test['typ']) { //ankieta
				if (!empty($odp))//zapisujemy tylko udzielone odpowiedzi
					$tadd[]=array($wid,$pid,implode('#',$odp));
			} else { //test
				$tadd[]=array($wid,$pid,implode('#',$odp),$sesja->tboc[$pid]);
			}
		}
		//print_r($tadd);echo '<hr />';
		if ($sesja->test['typ']) CDBase::dbSetq('INSERT INTO wynank VALUES(?,?,?)',$tadd); //ankieta
		else CDBase::dbSetq('INSERT INTO wynpyt VALUES(?,?,?,?)',$tadd);//test
		//koniec zapisu wyników
		if (CDBase::dbExec()) CMsg::kadd('Zapisano odpowiedzi.'); else CMsg::eadd('Błąd zapisu odpowiedzi.');
	}
	ftclean();//wyczyść dane z sesji dotyczące rozwiązanego testu
}
?>