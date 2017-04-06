<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
include_cl(array('pytania','testy','przedmioty','grupy'));

	CHtml::addJS('<script type="text/javascript"><!--
		$(document).ready(function(){
			if ($("#ttyp").is(":checked")) $("#skala").attr("disabled","disabled");
			$("#tfrm").submit(function(){
				$(".error").html("");
				if (errl($("#czas").val(),1,3)) {
					$(".error").html("Nie wprowadziłeś czasu rozwiązywania testu (max 3 cyfry oznaczające minuty).");
					return false;
				}
				if (errl($("#wersja").val(),1,3)) {
					$(".error").html("Nie wprowadziłeś oznaczenia wersji (max 3 znaki).");
					return false;
				}
				if (errl($("#zakres").val(),1,50)) {
					$(".error").html("Nie wprowadziłeś zakresu testu (max 50 znaków).");
					return false;
				}

				if (parseInt($("#ilep").val()) > parseInt($("#ilept").val())) {
					$(".error").html("Nie można wylosować więcej pytań niż zawiera test.");
					return false;
				}
				return true;
			});
			function errl(val,min,max) {//funkcja pomocnicza
				var ln=val.length;
				//alert(ln+" "+min+" "+max);
				if (!ln) return true;
				else if (ln < min) return true;
				else if (ln > max) return true;
				else return false;
			}
		});
	//--></script>');

function p_odp($nrpyt,$pyt) {
global $user;
print_r($pyt);
	$typy=array('text','radio','checkbox','radio','radio','hidden');
	$imgdir='imgs/'; //katalog z obrazkami
	$inroz=60; //rozmiar pól  input text
	//idiv - kontener do wyświetlenia dodawanych obrazków
	//tdiv - kontener do wyświetlenia dodawanego tekstu
	echo '<div class="idiv cb"></div>
			<div class="tdiv cb">';
	if (!empty($pyt['txt'])) echo '<textarea class="txt inpw95" name="txt'.$nrpyt.'" title="Wpisz lub wklej tekst do analizy...">'.hhash($pyt['txt'],false).'</textarea>';
	echo	'</div>
			<div class="sdiv cb">';
	//--- pokaż obrazki do pytania
	$media=new media(null,null,$user->uid);
	$media->getTb2('SELECT media.mid,media.opis,media.fname FROM media,mp WHERE media.mid=mp.mid AND mp.pid='.$pyt['pid'].' AND ');
	//$tbmids=CDBase::dbQuery('SELECT mp.mid,media.opis,media.fname FROM mp,media WHERE mp.mid=media.mid AND mp.pid='.$pyt['pid']);
	$ileodp=count($pyt['odpt']);//ile podpunktów
	if ($media->ilew) {
		echo '<div class="dimg">';
		foreach ($media->tb as $k=>$v) {//mid,fname,opis
			if (strpos($v['fname'],$pyt['pid'].'_t_')===false){//obrazki dla całego pytania
				echo '<img src="'.CT_URL.CT_IMGS.$v['fname'].'" class="pytimg" alt="'.$v['opis'].'" />&nbsp;
				Usuń:<input name="idel'.$nrpyt.'[]" type="checkbox" value="'.$v['mid'].'" />';
				unset($media->tb[$k]);
			} else {
				$pyt['odpt'][]=$v['fname'];
			}
		}
		if ($media->ilew=count($media->tb)) {
			$media->tb=array_values($media->tb);
			$ileodp=$media->ilew;
		}
		echo '</div>';
	}
	//---
	for ($i=0; $i<$ileodp; $i++) {
		echo '<div class="odiv cb">';
		if ($media->ilew) {//jest do wyświetlenia obrazek
			$v=current($media->tb);
			$media->set_img($v['fname']);
			$media->mkthumb();
			$oimg= '
					<img src="'.$media->imp.'" class="oimg" width="100" alt="'.$v['opis'].'" />Usuń:<input name="idel'.$nrpyt.'[]" type="checkbox" value="'.$v['mid'].'" />';
			next($media->tb);
		}
		switch($pyt['typ']){
			case 0://text
				if ($media->ilew) {
					echo $oimg;
				} else {
 					$odpt=hhash($pyt['odpt'][$i],false);
					echo '
						<input class="otxt modpt inpw49" type="text" name="t'.$nrpyt.'[]" size="35" value="'.$odpt.'" />';
				}
				echo '<input class="odp modpt inpw49" type="text" name="o'.$nrpyt.'[]" size="35" value="'.hhash($pyt['odp'][$i],false).'" title="Poprawna odpowiedź" />';
			break;
			case 1://radio
			case 2://checkbox
				echo '
						<input class="odp modpt" type="'.$typy[$pyt['typ']].'" name="o'.$nrpyt.'[]" value="'.$i.'" ';
				if (in_array($i,$pyt['odp'])) echo 'checked="checked" ';
				echo ' />&nbsp;';
				if ($media->ilew) {
					echo $oimg;
				} else {
					$odpt=hhash($pyt['odpt'][$i],false);
					echo '<input class="otxt modpt inpw49" type="text" name="t'.$nrpyt.'[]" size="'.$inroz.'" value="'.$odpt.'" title="Poprawna odpowiedź" />';
				}
			break;
			case 3://short text
				//$odp=hhash($pyt['odp'][$i],false);
				echo '
						<input class="otxt modpt" type="hidden" name="o'.$nrpyt.'[]" />
						<input class="odp modpt inpw95" type="text" name="t'.$nrpyt.'[]" value="" title="Odpowiedź" />';
			break;
			case 4://long text
				//$odp=hhash($pyt['odp'][$i],false);
				$odpt=str_replace(CT_ALT,CT_SEP,$pyt['odpt'][$i]);
				echo '
						<input class="otxt modpt" type="hidden" name="t'.$nrpyt.'[]" />
						<textarea class="odp modpt inpw95" name="o'.$nrpyt.'[]" rows="1" title="Odpowiedź...">'.$odpt.'</textarea>';
			break;
			case 5://select
				$odpt=str_replace(CT_ALT,CT_SEP,$pyt['odpt'][$i]);
				echo '
						<input class="odp modpt" type="hidden" name="o'.$nrpyt.'[]" />
						<textarea class="otxt modpt inpw95" name="t'.$nrpyt.'[]" rows="6" title="Odpowiedź...">'.$odpt.'</textarea>';
			break;
			default:
				echo 'Niewspierany typ pytania';
			break;
		}
		echo '</div>';
	}
	echo '
			</div>';
}
//--- adding & editing questions
function ed_Pyt($tid=0) {
global $user;
global $ppage;//pytań na stronę
$totalw=0;//wszystkich pytań (wpisów)
$cpage=CValid::getCnt('cpg'); CValid::vInt($cpage,0,15,1);//nr aktualnej strony
$skip=($cpage-1)*$ppage;//ile pominąć
	echo '<h3>Edycja pytań</h3>';
	$katt=null;//kategoria testu
	if ($tid>0) {//edycja pytań dla testu
		$test=new testy($tid,null,$user->uid);
		$test->getTb2('SELECT testy.*,kategorie.kat FROM testy,kategorie WHERE testy.kid=kategorie.kid AND ');
		$katt=$test->kid;
		$kid=0;
		$totalw=count($test->ord);//wszystkich pytań w teście
		$pyt=new pytania();
		$pyt->getTb2('SELECT pytania.* FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$tid);
		$pyt->setOrd($test->ord);
		if ($pyt->ilew<1) $kid=$katt;
		echo '<h4>'.($test->typ ? 'Ankieta' : 'Test').': <a href="?acc='.CValid::$acc.'&amp;acc3=edt&amp;idt='.$test->tid.'">'.$test->zakres.'</a> ['.$test->wer.']</h4>';
	} else {
		$kid=CValid::$acc2;//kategoria pytań
		$totalw=CDBase::dbQuery('SELECT COUNT(pid) FROM pytania WHERE uid='.$user->uid.' AND kid='.$kid,PDO::FETCH_COLUMN,true);
		$pyt=new pytania();
		if ($cpage>0) $pyt->getTb2('SELECT pytania.* FROM pytania WHERE uid='.$user->uid.' AND kid='.$kid.' LIMIT '.$skip.','.$ppage);
		else $pyt->getTb2('SELECT pytania.* FROM pytania WHERE uid='.$user->uid.' AND kid='.$kid);//wszystko
		echo '<h4>Kategoria: '.CDBase::dbQuery('SELECT kat FROM kategorie Where kid='.$kid,PDO::FETCH_COLUMN,true).'</h4>';
	}

	$inroz=50;//rozmiar pól input text
	$tbkat=CDBase::dbQuery('SELECT kid,kat FROM kategorie WHERE kategorie.uid='.$user->uid.' OR kategorie.uid=1',PDO::FETCH_KEY_PAIR);
	$tbkat[0]='Wybierz kategorię...';
	CHtml::addRes('scripts','pytania.js');
//--- linki nawigacyjne ---
	$navlinks='Pytań: '.$totalw;//dla testów
	if ($tid<1 && $cpage>0) {//tylko dla kategorii pytań
		$num_pages=ceil($totalw/$ppage);//ile stron wpisów
		$navlinks='Strona:';
		if ($num_pages>1) {
			for ($k=0;$k<$num_pages;$k++)
				if ($k != ($cpage-1)) $navlinks.='&nbsp;<a href="?acc=8&acc2='.$kid.'&acc3=edp&cpg='.($k+1).'">'.($k+1).'</a>';
				else $navlinks.='&nbsp;'.($k+1).'';
		} else $navlinks.=' 1';
		$navlinks.='. Pokazano: '.$pyt->ilew.' z '.$totalw;
	}
//--- formularz ---
	echo '<form enctype="multipart/form-data" action="?acc='.CValid::$acc.'" method="post" name="pedd" id="pedd">
			<input type="hidden" name="acc3" value="psv" />';
	echo '<fieldset class="flr">
			<legend>Opcje wszystkich pytań</legend>
			<input type="checkbox" name="kallchk" id="kallchk"'.($kid ? ' checked="checked"' : '').' class="tip" title="Zaznacz, aby wybrać kategorię dla wszystkich pytań. Odznacz, jeżeli pytania w teście mają mieć różne kategorie." />
			Kategoria: '.CHtml::mk_sel('katall','katall',array($tbkat,(int)$kid),'sel280').'<br />
			<input type="checkbox" id="shall" />&nbsp;Pokaż wszystkie pytania
			</fieldset>
				<input type="hidden" name="MAX_FILE_SIZE" value="'.$pyt->maxs.'" />
				<input type="hidden" id="idt" name="idt" value="'.$tid.'" />
				<input type="hidden" name="kidpyt" value="'.$kid.'" />';
	if ($katt) echo '<input type="hidden" id="katt" value="'.$katt.'" />';
	echo '	<input type="hidden" name="ilep" id="ilep" value="'.$pyt->ilew.'" />
				<input type="hidden" name="fpyt" id="fpyt" value="'.(time()+60).'" />
				<input type="hidden" name="skip" id="skip" value="'.$skip.'" />
			<div class="cb"></div>
			<fieldset id="pytin"><legend>['.$navlinks.']</legend>';

	$nrpyt=0;
	$ppid='this.parentNode.id';

	foreach ($pyt->tb as $k => $v) {
		$nrpyt++;
		$pytstr=$v['pyt'];
		$skr=substr($pytstr,0,100);
		if (strrpos($skr,'&',-2)) $skr=substr($skr,0,-2);
		echo '
					<div id="div'.$nrpyt.'" class="pytdiv"><span class="pnrtxt">Pytanie '.$nrpyt.':</span>
					<span class="pskr">'.$skr.'</span>
					<div class="cbut">
					<input class="chkHide" type="checkbox" /><label>Pokaż</label>&nbsp;
					<input class="pdel" type="checkbox" name="pdel[]" value="'.$v['pid'].'" /><label>Usuń z bazy</label>&nbsp;
					<input class="ppub modpt" type="checkbox" name="ppub[]" '.(($v['pub']) ? 'checked="checked"' : '' ).' value="'.$nrpyt.'" /><label>Publiczne</label>&nbsp;
					&middot;<label>Max. punktów:</label> <input class="mpkt modpt" type="text" name="mpkt[]" value="'.$v['mpkt'].'" size="1" '.($v['typ']==3||$v['typ']==4 ? '' : 'readonly="readonly" ').'/>
					</div>
				<div class="divh">';
		echo '<textarea class="pyttxt modpt inpw95" name="pyt[]" rows="2" title="Treść pytania/polecenia">'.$pytstr.'</textarea>';
		echo '
				 <input class="pid" type="hidden" name="tk[]" value="'.$v['pid'].'" />
				 <input class="typ" type="hidden" name="typ[]" value="'.$v['typ'].'" />
				 <input class="ileo" type="hidden" name="ileo[]" value="'.count($v['odpt']).'" />
				 <input class="mod" type="hidden" name="mod[]" />
				 ';

		p_odp($nrpyt,$v); //wyświetla treść odpowiedzi

		echo '<div class="bdiv cb">'
					.CHtml::mk_sel('kat[]',null,array($tbkat,(int)$v['kid']),'katp modpt sel280',$kid).'
					<input type="button" class="btnMup but flr" value="/\" title="Przesuń w górę" />
					<input type="button" class="btnMdo but flr" value="\/" title="Przesuń w dół" />
					<input type="button" class="btnShow but flr" value="Sh" title="Pokaż dane" />
					<input type="button" class="btnRemP but flr" value="Pyt[-]" title="Usuń pytanie" />
					<input type="button" class="btnAddT but flr" value="Txt[+]" title="Dodaj tekst do analizy..." />
					<input type="button" class="btnAddI but flr" value="Obr[+]" title="Dodaj obraz..." />
					<input type="button" class="btnChT but flr" value="Typ[<>]" title="Zmień typ pytania" />
					<input type="button" class="btnRemO but flr" value="Odp[-]" title="Usuń odpowiedź" />
					<input type="button" class="btnAddO but flr" value="Odp[+]" title="Dodaj odpowiedź" />
				</div>
				</div>
				</div>
				<hr />';
	}
	echo '</fieldset>';
	echo '<p class="pb flr">['.$navlinks.']</p>';
	echo '<fieldset id="pytop" class="cb">
			<legend>Rodzaje pytań:</legend>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr><td width="495" class="tah10" style="line-height:2em;">
			<select id="typpyt">
				<option value="1" title="Pytania, w których poprawna jest tylko jedna z podanych odpowiedzi.">Wybór jednokrotny (radio)</option>
				<option value="2" title="Pytania, w których poprawne jest kilka z podanych odpowiedzi.">Wybór wielokrotny (checkbox)</option>
				<option value="0" title="Pytania, w których poprawna jest wpisana odpowiedź, taka sama jak podana przez autora testu.">Pytanie &#8211; odpowiedź (pole text)</option>
				<option value="5" title="Pytania, w których odpowiedź wybiera się z listy wyboru. Autor testu definuje listę w nawiasach kwadratowych w formie: [odpowiedź dobra#odpowiedź zła#odpowiedź zła#...].">Wybór z listy (pole select)</option>
				<option value="3" title="Pytania ankietowe/testowe otwarte typu &quot;Podaj...&quot;, &quot;Wymień...&quot; &#8211; krótkie odpowiedzi">Pytanie otwarte (open text)</option>
				<option value="4" title="Pytania ankietowe/testowe otwarte umożliwiające dłuższą odpowiedź.">Pytanie otwarte (open textarea)</option>
			</select>&nbsp;<button id="btnAdd" class="but" type="button">Dodaj pytanie</button><br />
			<label for="ileodp">Ile odpowiedzi:</label><input type="text" id="ileodp" name="ileodp" size="1" value="4" class="ramka" />&nbsp;
			<label for="addimg">Dołącz obrazek:</label><input type="checkbox" name="addimg" id="addimg" /><br />
				<input type="text" class="ramka" size="2" id="cPyt" title="Podaj numer pytania, które chcesz skopiować." />
				<button id="btnCopy" class="but" type="button">Powiel pytanie</button><br/>
				<p id="pytkom" style="clear:both;margin-top:1em;line-height:1.2em;color:#419FC1;"></p>
			</td>
			<td class="tah10">
				<br /><br /><br />
				<input class="cb but flr" type="submit" value="Zapisz" />&nbsp;
			</td></tr>
			</table>
			</fieldset>';
	echo '<div id="tmp" class="hideme">Przeciągnij mnie.</div>';
	echo '
			</form>';
/*
			1. <input type="radio" name="typp" value="1" checked="checked" title="Pytania, w których poprawna jest tylko jedna z podanych odpowiedzi." />&nbsp;Wybór jednokrotny (radio)<br />
			2. <input type="radio" name="typp" value="2" title="Pytania, w których poprawne jest kilka z podanych odpowiedzi." />&nbsp;Wybór wielokrotny (checkbox)<br />
			3. <input type="radio" name="typp" value="0" title="Pytania, w których poprawna jest wpisana odpowiedź, taka sama jak podana przez autora testu." />&nbsp;Pytanie &#8211; odpowiedź (pole text)<br />
			4. <input type="radio" name="typp" value="5" title="Pytania, w których odpowiedź wybiera się z listy wyboru. Autor testu definuje listę w nawiasach kwadratowych w formie: [odpowiedź dobra#odpowiedź zła#odpowiedź zła#...]." />&nbsp;Wybór z listy (pole select)<br />
			5. <input type="radio" name="typp" value="3" title="Pytania ankietowe/testowe otwarte typu &quot;Podaj...&quot;, &quot;Wymień...&quot;" />&nbsp;Pytanie otwarte (krótkie odpowiedzi)<br />
			6. <input type="radio" name="typp" value="4" title="Pytania ankietowe/testowe otwarte umożliwiające dłuższą odpowiedź." />&nbsp;Pytanie otwarte (długie odpowiedzi)<br />
			<option value="6" title="Pytania, w których odpowiedź udzielana jest na podstawie obrazka.">Pytanie obrazek &#8211; odpowiedź</option>

*/
}

function sav_Pyt() {//zapisywanie pytan
global $user;
//print_r($_FILES); return;
	$tid=CValid::getCnt('idt','p'); CValid::vInt($tid,0,null,0);
	$br='<br /><br />';
	$tadd=array();
	$tk=CValid::getCnt('tk','p',true); //tab pidów
	$mod=CValid::getCnt('mod','p',true); //tab zmodyfikowanych pytan
	$tbpyt=CValid::getCnt('pyt','p',true); //tablica pytan
	$kidpyt=CValid::getCnt('kidpyt','p'); //oryginalna kategoria pytań
	if (!empty($kidpyt)) CValid::$acc2=$kidpyt; //zapisz kategorię zapisywanych pytań
	$katall=CValid::getCnt('katall','p'); //kategoria wszystkich pytań
	$tbtyp=CValid::getCnt('typ','p'); //tab. typów pytań
	$tbkat=CValid::getCnt('kat','p'); //tab. kategorii pytań
	$tbpub=CValid::getCnt('ppub','p',true); //tab. opcji pub
	$tbmpkt=CValid::getCnt('mpkt','p',true); //tab. maks. ilość punktów za pytanie
	$tbdel=CValid::getCnt('pdel','p',true); //tab pidów do usunięcia z bazy
	$tbdelim=array();//mids of removed images
	$fid=CValid::getCnt('fpyt','p');
	//if ($pyt->setFid($user->uid,$fid)) return CMsg::kadd('Ten formularz został już przetworzony!');

	global $ppage;
	$skip=CValid::getCnt('skip','p');
	$pyt=new pytania();
	if ($tid>0) {
		$pyt->getTb2('SELECT pytania.* FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$tid);
	} else {
		if (!is_null($skip)) $pyt->getTb2('SELECT pytania.* FROM pytania WHERE uid='.$user->uid.' AND kid='.$kidpyt.' LIMIT '.$skip.','.$ppage);
		else $pyt->getTb2('SELECT pytania.* FROM pytania WHERE uid='.$user->uid.' AND kid='.$kidpyt);
	}

	if (isset($katall)) {//jeżeli włączono kategorię dla wszystkich pytań
		$kid=$katall;
		$przid=CDBase::dbQuery('SELECT przid FROM kategorie WHERE kid='.$katall,PDO::FETCH_COLUMN,true);
	}
	//print_r($tbkat); echo '<br /><br />';
	foreach ($tbpyt as $k => $ptxt) {
		$nrpyt=$k+1;
		if (in_array($tk[$k],$tbdel)) { unset($tk[$k]); continue; }
		if (in_array($nrpyt,$tbpub)) $pub=1; else $pub=0;
		if (!empty($tk[$k])) $pid=$tk[$k]; else { unset ($tk[$k]); $pid='NULL'; }
		$idel=CValid::getCnt("idel{$nrpyt}",'p'); //tablica mids do usunięcia
		if (!empty($idel)) $tbdelim[$pid]=$idel;
		if ($tbtyp[$k]>6) { CMsg::kadd('Błędny typ pytania '.$nrpyt.'!'); continue; }//typy pytań 0-6
		if (!CValid::vStr($ptxt,1,1024)) { CMsg::kadd('Za krótkie/długie pytanie nr '.$nrpyt.'!'); continue; }
		$odp=CValid::getCnt("o{$nrpyt}",'p');//tablica poprawnych odpowiedzi
		$odpt=CValid::getCnt("t{$nrpyt}",'p');//tablica możliwych odpowiedzi
		if ($tbtyp[$k]<5) { $odp=clrtxt($odp); $odpt=clrtxt($odpt); }//czyszczenie tekstów oprócz typu select
		if (empty($odp)) $odp=array(0);
		if (empty($odpt)) $odpt=array(0);

		if (array_key_exists('t'.$nrpyt,$_FILES)) {//mamy obrazki
			switch ($tbtyp[$k]) {
				case 0://text
				case 3://open short text
				case 4://open long text
					foreach ($_FILES['t'.$nrpyt]['name'] as $l => $m)
						if (empty($m)) unset($odp[$l]);//nie ma obrazka, usuwamy odpowiedź
					$odp=array_values($odp);
				break;
				case 1://radio
				case 2://checkbox
					if ($tbtyp[$k]==1 && !$odp[0]) break;
					foreach ($odp as $l => $m) {
						for ($i=0; $i<=$m; $i++)
						if (empty($_FILES['t'.$nrpyt]['name'][$i])) $odp[$l]--;
					}
				break;
			}
		}

		switch($tbtyp[$k]) {
			case 0://text
				$tbmpkt[$k]=count($odp);
			break;
			case 1://radio
			case 2://checkbox
				$tbmpkt[$k]=1;
			break;
			case 3://short text

				//$tbmpkt[$k]=count($odp);
				foreach ($odp as $l=>$m) { if ($m=='odpowiedź') $odp[$l]=''; }
				$odpt = $odp;
				//print_r (explode(implode(CT_SEP,$odp),'#'));
				//print_r($odp);
			break;
			case 4://textarea
				foreach ($odp as $l=>$m) { if ($m=='odpowiedź') $odp[$l]=''; }
				$odpt = $odp;
			break;
			case 5://select
			if (preg_match_all("/\[(\D+[".CT_ALT."]*\D*)\]/U", $odpt[0], $matches)) {
				foreach ($matches[1] as $l => $match) {
					$pos=strpos($match,'#');
					if ($pos) $odp[$l]=substr($match,0,strpos($match,'#'));//zapisanie poprawnych odpowiedzi
					else $odp[$l]=$match;
				}
			}
				//print_r($odp);echo '<hr />';
				$odp=clrtxt($odp);
				$odpt=clrtxt($odpt);
				$tbmpkt[$k]=count($odp);
			break;
		}
#		}

		$txt=CValid::GetCnt('txt'.$nrpyt,'p'); CValid::vStr($txt,0,1024); $txt=clrtxt($txt);
		//echo nl2br($txt);
		$ptxt=clrtxt($ptxt,false);
		if (empty($katall)) {//kategoria każdego pytania może być inna
			$kid=$tbkat[$k];
			$przid=CDBase::dbQuery('SELECT przid FROM kategorie WHERE kid='.$kid,PDO::FETCH_COLUMN,true);
		}
		if (empty($kid) || $kid < 1) $kid=1;
		if (empty($przid) || $przid < 1) $przid=1;

		$tadd[$k]=array($pid,$przid,$kid,$user->uid,$tbtyp[$k],$ptxt,implode(CT_SEP,$odpt),implode(CT_SEP,$odp),$pub,$tbmpkt[$k],$txt);
	}
//print_r($tadd);
//return;
	//print_r($pyt->tb);//return;
	//echo '<hr />';print_r($_FILES);
	//echo '<hr />';print_r($tadd);
	//echo '<hr />';print_r($mod);return;
//return;
	if ($tid>0) $pyt->norem=1; //nie usuwaj pytań, jeżeli edytujemy pytania dla testu
	$pyt->savTb($tk,$tadd,$mod);
	if (($pyt->tbtids)) {//po usunięciu pytań trzeba zaktualizować testy, które je zawierały
		foreach ($pyt->tbtids as $tid => $tbpids) {
			$ord=CDBase::dbQuery('SELECT ord FROM testy WHERE tid='.$tid,PDO::FETCH_COLUMN,true);
			$ord=explode('#',$ord);
			$ord=array_diff($ord,$tbpids);
			$ord=implode('#',$ord);
			CDBase::dbSetq('UPDATE testy SET ord = ? WHERE tid = ?',array(array($ord,$tid)));
		}
		if (CDBase::dbExec()) CMsg::kadd('Zaktualizowano testy zawierające usunięte pytania.');
	}

	$media = new media(null,null,$user->uid);
	$media->remImgs($tbdelim);
	$media->savImgs($pyt->tk);
	//$media->savImgsP($pyt->tk);
	//$pok=$pyt->success;
	if ($tid>0) {//zapisz listę pytań w teście
		$test=new testy($tid,null,$user->uid);
		$test->savPyt($pyt->tk);
	}
	CMsg::kadd('Pytania przetworzono.');
	//redirect('?acc='.CValid::$acc.'&ik=2');
}

function add_Pyt() {//dodawanie pytań do testu z wybranej kategorii
global $user,$sesja;
	if (isset($sesja->tid)) { $tid=$sesja->tid; unset($sesja->tid); } else $tid=CValid::getCnt('idt','g'); CValid::vInt($tid,0,null,0);
	if (!$tid) return CMsg::eadd('Błędny test!');
	if (isset($sesja->kid)) { $kid=$sesja->kid; unset($sesja->kid); } else $kid=CValid::getCnt('idk','g');
	if (!CValid::vInt($kid,1)) CMsg::eadd('Błędna kategoria!');

	$tbkat=CDBase::dbQuery('SELECT kategorie.kid AS kid,kategorie.kat||" ("||przedmioty.przedm||")" AS katprz FROM kategorie,przedmioty WHERE kategorie.przid=przedmioty.przid AND (kategorie.uid='.$user->uid.' OR kategorie.uid=1)',PDO::FETCH_KEY_PAIR);
	$pyt= new pytania(null,$user->uid,null,$kid);
	$pyt->getTb2('SELECT pid,pyt FROM pytania WHERE ');
	if (empty($pyt->tb)) return CMsg::eadd('Brak pytań w wybranej kategorii!');
	$test=new testy($tid,null,$user->uid);
	$test->getTb2('SELECT tid,zakres,wer,ord FROM testy WHERE ');

	//$maxpyt=CDBase::dbQuery('SELECT COUNT(pid) FROM pytania WHERE uid='.$user->uid.' AND kid='.$kid,PDO::FETCH_COLUMN);
	$maxpyt=$pyt->ilew;
	$ilep=CValid::getCnt('ilep','g'); CValid::vInt($ilep,0,$maxpyt);

	echo ($ilep ? '<h3>Losowanie pytań</h3>' : '<h3>Przypisywanie pytań</h3>');
	echo '<h4>Test: <a href="?acc='.CValid::$acc.'&amp;acc3=edt&amp;idt='.$tid.'">'.$test->zakres.'</a> ['.$test->wer.']</h4>';
	echo '<div class="flr">';
	echo CHtml::mk_sel('saddpk','saddpk',array($tbkat,(int)$kid),'sel260');
	echo '<br /><input type="hidden" id="acc" value="'.CValid::$acc.'" /><input type="hidden" id="maxpyt" value="'.$maxpyt.'" />';
	echo '<input type="button" class="but" id="btnLosP" value="Wylosuj" /> <input type="text" name="ilep" id="ilep" size="1" value="'.$ilep.'" class="tip" title="Wpisz ile pytań wylosować..." /> pytań (maks.: '.$maxpyt.')';
	echo '</div>';
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="paddt" id="paddt">
			<input type="hidden" name="acc3" value="sdp" />
			<input type="hidden" name="idk" value="'.$kid.'" />
			<input type="hidden" name="idt" id="idt" value="'.$tid.'" />
			<table class="tab">';
	CHtml::tbHead(array('Lp.','Pytanie','[+]'));
	if ($ilep>0) {//wylosuj ilep pytań
		shuffle($pyt->tb);
		$pyt->tb=array_slice($pyt->tb,0,$ilep,true);
	}
	$i=0;
	foreach ($pyt->tb as $k => $v) {
		$clrow='tbrow'.($i%2);
		CHtml::tbRow(array(
			++$i,
			$v['pyt'],
			'<input class="chkall" type="checkbox" name="tk[]" value="'.$v['pid'].'" '.(in_array($v['pid'],$test->ord)?'checked="checked" ':'').'/>'
			),array('tc','','tc'),$clrow
		);
	}
	echo '<tr><td></td><td colspan="2" align="right">Wszystkie <input class="selallchk" type="checkbox" title="Zaznacz wszystko">&nbsp;</td></tr>';
	echo '</table>';
	if ($ilep>0) echo '<p class="info">Uwaga: zaznaczone pytania po zapisaniu zastąpią wszystkie inne pytania przypisane do testu.</p>';
	echo '<input class="cb but flr" type="submit" value="Zapisz" /></form>';
}
//--- Zapisz przypisane pytania do testu
function tsv_Pyt() {
global $user,$sesja;
	$tid=CValid::getCnt('idt'); CValid::vInt($tid,0,null,0);
	$kid=CValid::getCnt('idk'); CValid::vInt($kid,1);
	$tk=CValid::getCnt('tk');
	if (!CValid::vIntA($tk,0)) return CMsg::eadd();
	if (is_null($tk)) $tk=array();
	$test=new testy($tid,null,$user->uid);
	$test->getTb2('SELECT tid,ord FROM testy WHERE ');
	$pyt=new pytania();
	$pyt->getTb2('SELECT pytania.pid,pytania.kid FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$tid); //tabela $pid => $kid
	$pyt->getDane('tb','pid','kid');
	$pyt->setOrd($test->ord);
	$tbp=array_keys($pyt->tb);
	//print_r($tbp); echo '<br />';
	foreach ($tk as $pid) {
		if (!array_key_exists($pid,$pyt->tb)) $pyt->tb[$pid]=$kid;
	}
	foreach ($pyt->tb as $pid => $pkid) {
		if ($pkid==$kid && !in_array($pid,$tk)) unset($pyt->tb[$pid]);
	}
	if ($test->savPyt(array_keys($pyt->tb))) {
		CMsg::kadd('Zaktualizowano listę pytań w teście!');//zapisujemy pytania tylko kiedy są one wyświetlone!
		$sesja->tid=$tid;
		$sesja->kid=$kid;
	}
}

function ed_Test() {
global $user,$sesja;
	if (isset($sesja->tid)) { $tid=$sesja->tid; unset($sesja->tid); } else $tid=CValid::getCnt('idt');//jeżeli test został co dopiero dodany, jesgo tid jest w zmiennej $sesja->tid
	CValid::vInt($tid,0,null,0);

	if ($tid==0) {//nowy test
		$ilet=CDBase::dbQuery('SELECT ilet FROM utok WHERE uid='.$user->uid,PDO::FETCH_COLUMN,true);
		$ilet_t=CDBase::dbQuery('SELECT COUNT(tid) FROM testy WHERE uid='.$user->uid,PDO::FETCH_COLUMN,true);
		if ($ilet_t==$ilet) return CMsg::kadd('Wyczerpałeś limit testów. Prosimy o maila z loginem na adres hamlet at hamlet dot edu dot pl, po pozytywnej weryfikacji odeślemy hasło odblokowujące możliwość dodawania kolejnych 5 testów.');
	}

	$test=new testy($tid,null,$user->uid,'','');
	$test->getTb2('SELECT testy.*,kategorie.kat FROM testy,kategorie WHERE testy.kid=kategorie.kid AND ');
	$tbsk=$test->getSkale();
	if ($test->typ) $test->skid=1; else $test->skid=2;
	$tbkat=CDBase::dbQuery('SELECT kategorie.kid AS kid,kategorie.kat||" ("||przedmioty.przedm||")" AS katprz FROM kategorie,przedmioty WHERE kategorie.przid=przedmioty.przid AND (kategorie.uid='.$user->uid.' OR kategorie.uid=1)',PDO::FETCH_KEY_PAIR);
	$maxpyt=count($test->ord);

	echo '<h2>Dodawanie/edycja</h2>';
	echo '<h3>Test: <a href="?acc='.CValid::$acc.'&amp;acc3=edt&amp;idt='.$tid.'">'.$test->zakres.'</a> ['.$test->wer.']</h3>';
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="tfrm" id="tfrm">
			<input type="hidden" name="acc3" value="tfs" />';
	echo '
			<input type="hidden" id="acc" value="'.CValid::$acc.'" />
			<input type="hidden" id="idt" name="idt" value="'.$tid.'" />
			<input type="hidden" name="modt[]" id="modt" />
			<input type="hidden" id="ilept" value="'.$maxpyt.'" />
			<input type="hidden" name="ftest" id="ftest" value="'.time().'" />
			<fieldset class="danefrm">
			<legend>Dane podstawowe</legend>
			<span>Kategoria (przedmiot):</span>'.CHtml::mk_sel('katt','katt',array($tbkat,(int)$test->kid),'modt sel260').'<br />
			<span>Czas:</span><input class="modt" type="text" name="czas" id="czas" size="3" value="'.(is_null($test->czas) ? 0 : $test->czas).'" />&nbsp;&#8211; czas trwania testu w minutach, "0" oznacza brak ograniczeń.<br />
			<span>Wersja:</span><input class="modt" type="text" name="wersja" id="wersja" size="3" value="'.$test->wer.'" />&nbsp;&#8211; numer oznaczający wersję testu, np. "01".<br />
			<span>Zakres:</span><input class="modt" type="text" id="zakres" name="zakres" value="'.$test->zakres.'" />
			</fieldset>
			<fieldset id="topcje">
			<legend>Opcje testu</legend>
			<input class="modt" type="checkbox" name="ttyp" id="ttyp"'.($test->typ ?' checked="checked" ':'').' /><label for="ttyp">Ankieta</label><br />
			<input class="modt" type="checkbox" name="topen" id="topen"'.($test->open ?' checked="checked" ':'').' /><label for="topen">Otwarty [podgląd wyników]</label><br />
			<input class="modt" type="checkbox" name="tpub" id="tpub"'.($test->pub ?' checked="checked" ':'').' /><label for="tpub">Publiczny [dla wszystkich użytkowników]</label><br />
			<input class="modt" type="checkbox" name="losp" id="losp"'.($test->losp ?' checked="checked" ':'').' /><label for="losp">Losowa kolejność pytań</label><br />
			<input class="modt" type="checkbox" name="loso" id="loso"'.($test->loso ?' checked="checked" ':'').' /><label for="loso">Losowa kolejność odpowiedzi</label><br />
			<label for="ilep">Losuj <input class="modt tip" type="text" name="ileplos" id="ileplos" size="1" value="'.$test->ilep.'" title="Wpisz ile pytań ma być losowanych za każdym razem do testu." /> pytań (maks.: '.$maxpyt.')</label><br />
			<label for="skala">Skala:</label><br />
			'.CHtml::mk_sel('skala','skala',array($tbsk,(int)$test->skid),'modt sel280').'
			</fieldset>
			<fieldset id="tklasy">
			<legend>Otwarty :: Grupa</legend>
			';

//		$tbgr=CDBase::dbQuery('SELECT gid,grupa||" ("||opis||")" as grop FROM grupy WHERE grupy.uid='.$user->uid,PDO::FETCH_KEY_PAIR);
//		$tbgr[1]='Testy (Grupa testowa)';
		$tbgr=CDBase::dbQuery('SELECT grupy.gid,grupa||" ("||opis||")" as grop FROM grupy WHERE grupy.uid='.$user->uid,PDO::FETCH_KEY_PAIR);
		$tbgid=CDBase::dbQuery('SELECT gid,open FROM testg WHERE testg.tid='.$tid,PDO::FETCH_KEY_PAIR);

		if (empty($tbgid)) $tbgid=array();
		foreach ($tbgr as $gid => $grop) {
			echo '<input class="tbgropen" type="checkbox" name="tbgropen[]" value="'.$gid.'" ';
			if (isset($tbgid[$gid]) && $tbgid[$gid]) echo 'checked="checked" ';
			echo ' id="gropen'.$gid.'" />&nbsp;::&nbsp;';
			echo '<input class="tbgr" type="checkbox" name="tbgr[]" value="'.$gid.'" ';
			if (array_key_exists($gid,$tbgid)) echo 'checked="checked" ';
			echo ' id="gr'.$gid.'" /><label for="gr'.$gid.'"><a href="'.CHtml::mk_link('grupa',$gid).'">'.$grop.'</a></label><br />';
		}

		echo '
			</fieldset>
			<input class="cb but flr" type="submit" value="Zapisz" />
			</form>';

	if ($tid>0) {//jeżeli test został zapisany
		//if ($test->ilep) $styl=' style="display:none"'; else $styl='';
		echo '<div class="cb losphide">
					<ul>
						<li><a class="but" href="?acc='.CValid::$acc.'&amp;acc3=edp&amp;idt='.$tid.'">Edytuj pytania</a></li>
						<li><a class="but" href="?acc='.CValid::$acc.'&amp;acc3=adp&amp;idt='.$tid.'&amp;idk='.$test->kid.'">Wybierz pytania</a></li>
						<li><a class="but" href="?acc='.CValid::$acc.'&amp;acc3=adp&amp;idt='.$tid.'&amp;idk='.$test->kid.'">Losuj pytania</a></li>
					</ul>
				</div>';
	}
//						<li><input id="baddpk" type="button" value="Dodaj pytania z kategorii" class="but" />:&nbsp;
//						'.CHtml::mk_sel('saddpk','saddpk',array($tbkat,(int)$test->kid),'sel260').
//						'&nbsp;</li>
//
}

function sav_Test() {//zapisywanie testu
global $user,$sesja;
	$tid=CValid::getCnt('idt'); CValid::vInt($tid,0,null,0);
	//nazwa zmiennej - nazwa $_POST lub $_GET, typ#min#max#default, komunikat błędu
	$kid=CValid::getCnt('katt'); CValid::vInt($kid,1,null,1);
	$czas=CValid::getCnt('czas'); CValid::vInt($czas,0,null,0);
	$wer=CValid::getCnt('wersja'); CValid::vStr($wer,1,3,'001');
	$zakres=CValid::getCnt('zakres'); CValid::vStr($zakres,1,200,'zakres'); $zakres=clrtxt($zakres);
	$skid=CValid::getCnt('skala');
	$typ=CValid::getCnt('ttyp'); CValid::vOn($typ); //echo $typ.' ';
	$losp=CValid::getCnt('losp'); CValid::vOn($losp); //echo $losp.' ';
	$loso=CValid::getCnt('loso'); CValid::vOn($loso); //echo $loso.' ';
	$open=CValid::getCnt('topen'); CValid::vOn($open); //echo $open.' ';
	$pub=CValid::getCnt('tpub'); CValid::vOn($pub); //echo $pub.' ';
	$ileplos=CValid::getCnt('ileplos');	CValid::vInt($ileplos,0,null,0);
	$mod=CValid::getCnt('modt');

//var_dump($zakres); return;

	if ($typ) $skid=1;//dla ankiet ustawiamy 'Brak skali'
	if ($tid) $tk[]=$tid; else $tk=$mod=array();

	$test=new testy($tid,null,$user->uid);
	$test->getTb2('SELECT testy.*,kategorie.kat FROM testy,kategorie WHERE testy.kid=kategorie.kid AND ');
	if ($tid === 0) {
		$tid=NULL;
		$test->ord=array();
	}
	$ord=$test->ord;
	if (!empty($ord)) $ord=implode($test->e,$test->ord); else $ord='';
	$tadd[]=array($tid,CDBase::dbQuery('SELECT przid FROM kategorie WHERE kid='.$kid,PDO::FETCH_COLUMN,true),$kid,$user->uid,$typ,$wer,$zakres,$skid,$czas,$ord,$losp,$loso,$open,$pub,$ileplos);
	$test->savTb($tk,$tadd,$mod);

	if ($test->success) {
		if (is_null($tid)) {
			$sesja->tid=$test->tk[0];//zapisz tid nowego testu
			$test->tid=$test->tk[0];
		}
		//if ($ileplos>0) $test->savPyt();//jeżeli test dynamiczny (losowanie n pytań), usuwamy pytania przypisane do testu
		CMsg::kadd('Test zapisano.');
	}
	//zapisz grupy, którym przypisano test
	if ($test->tid) {
		$tbgr=CValid::getCnt('tbgr'); if (empty($tbgr)) $tbgr=array();
		$tbgropen=CValid::getCnt('tbgropen'); if (empty($tbgropen)) $tbgropen=array();
		$testg= new testg($test->tid);
		$testg->idn='gid';
		$testg->getTb2('SELECT gid FROM testg WHERE ');
		$tadd=array();
		foreach ($tbgr as $gid) $tadd[]=array($test->tid,$gid,in_array($gid,$tbgropen)?1:0);
		//print_r($tadd); //return;
		$testg->savTb(array(),$tadd,array());
	}
}
?>