<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
//redirect('?acc='.CValid::$acc.'&kom=Zaktualizowano konto');
//--- zwróć listę grup do których należy użytkownik
function get_grin($mod) {
global $user;
	if (!$user->uid) return;
	$tbgr=CDBase::dbQuery('SELECT grupy.gid,grupy.grupa,gunp.nrp,gunp.pgr FROM grupy,gunp WHERE grupy.gid=gunp.gid AND gunp.uid='.$user->uid);
	if (empty($tbgr)) return false;
	if ($mod) $heads[]='Usuń';
	echo '<p>Lista grup, do których należysz:</p>';
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="grinfrm" id="grinfrm">
			<input type="hidden" name="acc3" value="gsv" />';
	echo '<table class="tab" id="tbGrin">';
	if ($mod) CHtml::tbHead(array('Lp.','Grupa','Nrg','Pgr','Usuń'));
	else CHtml::tbHead(array('Lp.','Grupa','Nrg','Pgr'));
	$i=0;
	foreach ($tbgr as $gr) //gid,grupa,nrp,pgr
		CHtml::tbRow(array(
			++$i,
			$gr['grupa'],
			$gr['nrp'],
			$gr['pgr'],
			($mod ? '<input type="checkbox" name="tk[]" value="'.$gr['gid'].'" />' : '')
			),array('tc','','tc','tc','tc'));
	echo '</table>';
	echo '<input type="hidden" name="acc2" value="gin" />';
	echo '<input class="cb but flr" type="submit" value="Zapisz" /></form>';
	return true;
}
//--- zwróć listęgrup, które dodał użytkownik
function get_gradd($mod) {
global $user;
	if (!$user->uid) return;
	$tbgr=CDBase::dbQuery('SELECT gid,grupa,opis FROM grupy WHERE grupy.uid='.$user->uid);
	if (empty($tbgr)) return false;
	echo '<p>Lista grup, które dodałeś:</p>';
	echo '<table class="tab" id="tbGradd">';
	if ($mod) CHtml::tbHead(array('Lp.','Grupa','Opis','Usuń'));
	else CHtml::tbHead(array('Lp.','Grupa','Opis'));
	$i=0;
	foreach ($tbgr as $gr)
		CHtml::tbRow(array(
			++$i,
			$gr['grupa'],
			$gr['opis'],
			($mod ? '<input type="checkbox" name="tk[]" value="'.$gr['gid'].'" />' : '')
			),array('tc','','','tc'));
	echo '</table>';
	return true;
}

function get_ImNaz($n,$i) {
	if (empty($n)) return '';
	if ($i) return substr($n,strpos($n,' ')+1); //zwraca imię
	else return substr($n,0,strpos($n,' ')); //zwraca nazwisko
}

function u_frm() {
global $user;
	$i=0;
	CHtml::addJS('<script type="text/javascript"><!--
		$(document).ready(function(){

			$("#userfrm").submit(function(){
				var error=false;
				$("#error").html("");
				if (errl($("#nlnm").val(),5,15)) {
					$("#error").html("Login powinien mieć od 5 do 15 znaków.");
					return false;
				}
				//alert($("#nlnm").val().indexOf(" "));
				if ($("#nlnm").val().indexOf(" ")>0) {
					$("#error").html("Login nie może zawierać spacji!");
					return false;
				}
				if ($("#slps").length && errl($("#slps").val(),5,20)) {
						$("#error").html("Jeżeli chcesz coś zmienić, wpisz poprawne stare hasło.");
						return false;
				}
				if ($("#nlps1").val().length || $("#nlps2").val().length || $("#kto").val()=="no") {
					//alert (errl($("#nlps1").val(),5,20));
					if (errl($("#nlps1").val(),5,20) || errl($("#nlps2").val(),5,20)) {
						$("#error").html("Nowe hasło powinno mieć od 5 do 20 znaków.");
						return false;
					}
				}
				if ( ($("#nlps1").val() != $("#nlps2").val())) {
					$("#error").html("Podane hasła nie są takie same!");
					return false;
				}
				var email = /^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/;
				if (!email.test($("#nlem").val())) {
					$("#error").html("Błędny email.");
					return false;
				}
				$(".uname").each(function(){
					if ($(this).val().length<3 || $(this).val().length>20) {
						$("#error").html("Imię i/lub nazwisko powinny mieć od 3 do 20 znaków.");
						error=true;
					}
				});
				if (error) return false;
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
	echo '<h3>Dodawanie/edycja konta</h3>';
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="userfrm" id="userfrm">
			<input type="hidden" name="acc3" value="usv" />';
	echo '<input type="hidden" id="change" value="0" />';
	echo '<input type="hidden" id="kto" value="'.$user->kto.'" />';
	echo '<fieldset class="danefrm"><legend>Dane użytkownika</legend>';
	echo '<span>Login:</span><input class="przypis" type="text" id="nlnm" name="nlnm" size="15" value="'.$user->login.'" /><br />';
	if ($user->uid) echo '<span>Stare hasło:</span><input class="pass przypis" type="password" id="slps" name="slps" size="20" /><br />'; 
	echo '<span>Nowe hasło:</span><input class="pass przypis" type="password" id="nlps1" name="nlps1" size="20" /><br />
			<span>Powtórz hasło:</span><input class="pass przypis" type="password" id="nlps2" name="nlps2" size="20" /><br />';
	echo '<span>Email:</span><input class="przypis" type="text" id="nlem" name="nlem" size="20" value="'.$user->email.'" /><br />
			<span>Imię:</span><input class="uname" type="text" name="imie" size="20" value="'.get_ImNaz($user->nazwisko,1).'"/><br />
			<span>Nazwisko:</span><input class="uname" type="text" name="nazwi" size="20" value="'.get_ImNaz($user->nazwisko,0).'" />';
	echo '</fieldset>';
	echo '<div class="tiphide">
		<span id="nlnmc">Podaj login, długość max. 15 znaków.</span>
		<span id="slpsc">Podaj dotychczasowe hasło.</span>
		<span id="nlps1c">Podaj nowe hasło, długość max. 20 znaków.</span>
		<span id="nlps2c">Powtórz hasło, długość max. 20 znaków.</span>
		<span id="nlemc">Podaj email, który pomocny będzie przy odzyskiwaniu zapomnianego hasła.</span>
	</div>';
	echo '<input class="cb but flr" type="submit" value="Zapisz" /></form>';
	CMsg::kadd('Wszystkie dane są obowiązkowe.');
}

function u_sav() {//zapis podstawowych danych użytkownika
global $user;
	$lnm=CValid::getCnt('nlnm','p'); if (!CValid::vStr($lnm,5,20)) return CMsg::eadd('Błąd: login. Min. długość to 5, maks. 20.');
	$lps=CValid::getCnt('nlps1','p');
	$lem=CValid::getCnt('nlem','p'); if (!CValid::vEmail($lem)) return CMsg::eadd('Błąd: mail. Format: login@server.domena.');
	if ($user->uid) {//aktualizacja danych
		$sps=CValid::getCnt('slps','p'); //stare hasło
		if (!CValid::vStr($sps,5,20)) return CMsg::eadd('Błąd: stare hasło.');
		$ret=CDBase::dbIsVal('SELECT uid FROM users WHERE uid = ? AND haslo = ?',array(array($user->uid,sha1($sps))));
		if (!$ret) return CMsg::eadd('Podane stare hasło jest niepoprawne!');
		if ((strcmp($lnm,$user->login) != 0) && CDBase::dbIsVal('SELECT uid FROM users WHERE login = ?',array(array($lnm)))) return CMsg::eadd('Podany login już istnieje.');
		if ((strcmp($lem,$user->email) != 0) && CDBase::dbIsVal('SELECT uid FROM users WHERE email = ?',array(array($lem)))) return CMsg::eadd('Podany mail już istnieje.');
	} else {//nowe konto
		if (strpos($lnm,' ')) return CMsg::eadd('Login nie może zawierać spacji!');
		if (CDBase::dbIsVal('SELECT uid FROM users WHERE login = ?',array(array($lnm)))) return CMsg::eadd('Podany login już istnieje.');
		if (CDBase::dbIsVal('SELECT uid FROM users WHERE email = ?',array(array($lem)))) return CMsg::eadd('Podany mail już istnieje.');
	}
	if (strlen($lps)>0) {
		if (!CValid::vStr($lps,5,20)) return CMsg::eadd('Błąd: hasło. Min. długość to 5, maks. 20.');
		$lps2=CValid::getCnt('nlps2','p');
		if (strcmp($lps,$lps2) != 0) return CMsg::eadd('Nowe podane hasła różnią się!');
		$user->haslo=$lps;
	}
	$imie=CValid::getCnt('imie','p'); $imie[0]=strtoupper($imie[0]);
	$nazwi=CValid::getCnt('nazwi','p'); $nazwi[0]=strtoupper($nazwi[0]);
	$nazwisko=$nazwi.' '.$imie; if (!CValid::vStr($nazwisko,6,35)) return CMsg::eadd('Błąd: nazwisko. Min. długość nazwiska i imienia to 6, maks. 35.');
	$user->login=$lnm;
	$user->email=$lem;
	$user->nazwisko=$nazwisko;
	if (!$user->uid) $user->a=1;//anonimowy
	if ($user->savtb()) {
		if ($user->uid) {
			CMsg::kadd('Zaktualizowano dane konta!');
			u_frm();
		} else {
			echo '<p class="info">Założono konto! Możesz się zalogować.</p>';
		}
	} else CMsg::eadd('Błąd podczas tworzenia konta! Spróbuj jeszcze raz!');
}
//--- aktywacja konta ---
function gfrm_sh() {
global $user;
	CHtml::addJS('<script type="text/javascript"><!--
		$(document).ready(function(){
			//$("#grupa").change(function(){
			//	if ($(this).val() == 1) $("#nrp").val(1);
			//});
			$("#userfrm").submit(function(){
				$("#error").html("");
				//if ($("#grupa").val() == 0) {
				//	$("#error").html("Wybierz grupę.");
				//	return false;
				//}
				if (errl($("#tok").val(),5,10)) {
					$("#error").html("Hasło powinno mieć od 5 do 10 znaków.");
					return false;
				}
				if (!($("#nrp").val()) || isNaN($("#nrp").val())) {
						$("#error").html("Wymagany numer w grupie.");
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

	get_grin(true);
	get_gradd(true);
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="userfrm" id="userfrm">
			<input type="hidden" name="acc3" value="gsv" />';
	echo '<fieldset  class="danefrm"><legend>Wybieranie grup</legend>';
	if (!$user->c) {//użytkownik dodaje konto do grupy
		CMsg::kadd('Wszystkie dane są obowiązkowe!.');
	}
	echo '<span>Wpisz hasło grupy:</span><input class="przypis" type="password" id="tok" name="tok" size="15" /><br />
			<span>Numer w grupie:</span><input class="uczen przypis" type="text" id="nrp" name="nrp" size="3" /><br />
			<span>Podgrupa:</span><label>1</label><input class="uczen przypis" type="radio" name="pgr" value="1" checked="checked" /><label>2</label><input class="uczen przypis" type="radio" name="pgr" value="2" />
			';		
	echo '</fieldset>';
	echo '<div id="info" class="cb">
			<span id="du" class="uinfo hidden">Hasło grupie przypisuje autor grupy.</span>
			</div>';
	echo '<div class="tiphide">
			<span id="tokc">Podaj hasło grupy, uzyskane od nauczyciela.</span>
			<span id="nrpc">Podaj swój numer w dzienniku klasowym.</span>
			<span id="pgrc">Podaj numer grupy, jeżeli zajęcia odbywają się w grupach. W innym przypadku można to pominąć.</span>
		</div>';
	echo '<input class="cb but flr" type="submit" value="Zapisz" /></form>';
}

function g_sav() {
global $user;
	$tk=CValid::getCnt('tk','p');
	if (!is_null($tk)) {
		foreach ($tk as $gid) {
			if ($user->u) {
				//echo 'Usuwam użytkownika '.$user->uid.' z grupy: '.$gid;
				$tbdatas=CDBase::dbQuery('SELECT DISTINCT datas FROM wyniki WHERE uid='.$user->uid.' AND gid='.$gid,PDO::FETCH_COLUMN);
				CDBase::dbSetq('DELETE FROM gunp WHERE gid = ? AND uid = ?',array(array($gid,$user->uid)));//usuń użytwkonika z grupy
				if ($tbdatas) {
					CDBase::dbSetq('DELET FROM wyniki WHERE uid = ? AND gid = ?',array(array($user->uid,$gid)));//usuń wyniki użytkownika w ramach grupy
					CDBase::dbSetq('DELETE FROM wynpyt WHERE datas = ?',array($tbdatas));//usuń odpowiedzi użytkownika w ramach grupy
				}
			}
		}
		if (CDBase::dbExec()) CMsg::kadd('Usunięto konto z wybranych grup, a także powiązane z nimi wyniki i odpowiedzi.');
		$tbgr=CDBase::dbQuery('SELECT grupy.gid,gunp.uid FROM grupy,gunp WHERE grupy.gid=gunp.gid AND gunp.uid='.$user->uid);
		if (empty($tbgr)) {
			CDBase::dbSetq('UPDATE users SET a = ?, u = ? WHERE uid = ?',array(array(1,0,$user->uid)));
			if (CDBase::dbExec()) CMsg::kadd('Zaktualizowano status konta.');
		}
		return;
	}
	if (isset($_POST['acc2'])) return;//użytkownik wysłał formularz zapisywania grup
	$tok=CValid::getCnt('tok','p');
	if (!CValid::vStr($tok,5)) return CMsg::eadd('Błąd: za krótkie hasło.');
	if (!$tb=CDBase::dbIsVal('SELECT gid,ilu FROM grupy WHERE tok = ?',array(array($tok)))) return CMsg::eadd('Błędne hasło.');
	if ($tb['gid'] == 1) {//wbudowana grupa testowa
		$ilu=CDBase::dbQuery('SELECT COUNT(uid) FROM grupy WHERE gid=1',PDO::FETCH_COLUMN,true);
		$nrp=$ilu+1;
	} else {
		$nrp=CValid::getCnt('nrp','p'); //numer porządkowy w grupie
		if (!CValid::vInt($nrp,1,$tb['ilu'])) return CMsg::eadd('Błąd: niewłaściwy numer w grupie.');
	}
	$pgr=CValid::getCnt('pgr'); //podgrupa
	if (!CValid::vInt($pgr,1,2)) return CMsg::eadd('Błąd: niewłaściwa podgrupa.');
	$user->sget(array('a'=>0,'u'=>1,'c'=>1,'gid'=>$tb['gid'],'nrp'=>$nrp,'pgr'=>$pgr));
	if ($user->activate()) CMsg::kadd('Przypisano Twoje konto do wybranej grupy.');
	else CMsg::eadd('Błąd: nie udało się przypisć konta do grupy!');
}

function nfrm_sh() {
global $user;
	CHtml::addJS('<script type="text/javascript"><!--
		$(document).ready(function(){
			$("#userfrm").submit(function(){
				$("#error").html("");
				if (errl($("#tok").val(),8,20)) {
					$("#error").html("Hasło powinno mieć od 8 do 20 znaków.");
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
			$("#rpass").click(function(){
				$("#error").html("Funkcja automatycznego aktywowania konta &quot;autora testów&quot; na razie została wyłączona. Proszę o maila z loginem na adres hamlet at hamlet dot edu dot pl. Po pozytywnej weryfikacji prześlemy kod umożliwiający tworzenie pytań, testów, dodawanie grup itd.");
				return;
				var dane={ acc: "24", acc2: "tok" };
				$.get(
					"index.php",
					dane,
					function(data){
						if (data.indexOf("_")) {
							var res=data.split("_");
							var pass=res[1];
							res=parseInt(res[0]);
						} else {
							var res=parseInt(data);
							var pass="";
						}
						if (!res)
							$("#error").html("Błąd!");
					}
				);
			});
		});
	//--></script>');
	//get_grin(true);
	get_gradd(true);
	echo '<p>Aby tworzyć testy i/lub dodawać grupy, trzeba podać hasło otrzymane na swój adres email z serwisu. Pozytywna weryfikacja umożliwia dodanie 5 grup i 5 testów.
		Aby móc dodać kolejne grupy i/lub testy, trzeba powtarzać procedurę. Aktywacja konta "nauczyciela" wymaga konta nieprzypisanego do żadnej grupy.
		Prosimy o maila z loginem na adres hamlet at hamlet dot edu dot pl, po pozytywnej weryfikacji odeślemy hasło
		odblokowujące możliwość dodawania kategorii, pytań, testów, ankiet oraz grup.</p>';
		
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="userfrm" id="userfrm">
			<input type="hidden" name="acc3" value="nsv" />';
	echo '<fieldset  class="danefrm"><legend>Dodawanie testów/grup</legend>';
	if ($user->n) {//nauczyciel
		$ilet=CDBase::dbQuery('SELECT ilet FROM utok WHERE uid='.$user->uid,PDO::FETCH_COLUMN,true);
		$ilet_t=CDBase::dbQuery('SELECT COUNT(tid) FROM testy WHERE uid='.$user->uid,PDO::FETCH_COLUMN,true);
		echo '<p>Dodałeś '.$ilet_t.' z przyznanych '.$ilet.' testów.</p>';
		if ($ilet_t==$ilet)
			echo '<span>Hasło:</span><input type="password" id="tok" name="tok" size="15" /><br />';
	} else {
		echo '<span>Hasło:</span><input type="password" id="tok" name="tok" size="15" /><br />';
	}
	echo '</fieldset>';
	echo '<input class="cb but flr" type="submit" value="Zapisz" /></form>';
}
//xagXypYfH
function n_sav() {
global $user;
	$tok=CValid::getCnt('tok','p');
	if (!CValid::vStr($tok,8)) return CMsg::eadd('Błąd: za krótkie hasło.');
	$ilet=CDBase::dbQuery('SELECT ilet FROM utok WHERE tok=\''.sha1($tok).'\'',PDO::FETCH_COLUMN,true);
	if (is_bool($ilet)) return CMsg::eadd('Błędne hasło.');
	$ilet_n=0;//nowa liczba testów
	if ($user->n) {//konto nauczyciela zostało aktywowane wcześniej
		$ilet_t=CDBase::dbQuery('SELECT COUNT(tid) FROM testy WHERE uid='.$user->uid,PDO::FETCH_COLUMN,true);
		if ($ilet==$ilet_t) $ilet_n=$ilet+5; //przyznaj kolejne 5 testów
	} else {//nowy nauczyciel
		$user->sget(array('a'=>0,'u'=>0,'n'=>1,'c'=>1));
		if ($user->activate()) CMsg::kadd('Aktywowano konto "nauczyciela".');
		else return CMsg::eadd('Błąd zapisu danych użytkownika.'); 
		$ilet_n=5;
	}
	if ($ilet_n>$ilet) {
		if (CDBase::dbExecStr('UPDATE utok SET tok = "", ilet='.$ilet_n.' WHERE uid='.$user->uid)) CMsg::kadd('Zaktualizowano liczbę testów do '.$ilet_n.'.');
	}
	//redirect('?acc='.CValid::$acc.'&ik=0'); //aktywuj konto nauczyciela
}
//--- sterowanie ---
if (CValid::$acc3 == 'usv') {
	u_sav($user); //save user's data
} else if (!$user->uid) {
	echo '
	<p>Załóż konto:</p>
		<ul>
			<li>wyniki rozwiązanych testów/ankiet będą zapisywane;</li>
			<li>uzyskasz wgląd w błędne/poprawne odpowiedzi;</li>
			<li>dodasz konto do grup utworzonych przez autorów testów;</li>
			<li>utworzysz konto pozwalające na dodawanie testów, pytań i grup.</li>
		</ul>
	';
	u_frm();
} else if (CValid::$acc3 == 'gsh' && $user->uid) gfrm_sh($user);
else if (CValid::$acc3 == 'gsv' && $user->uid) {
	g_sav($user);
	gfrm_sh($user);
} else if (CValid::$acc3 == 'nsh' && $user->uid) nfrm_sh($user);
else if (CValid::$acc3 == 'tok') n_tok($user);
else if (CValid::$acc3 == 'nsv' && $user->uid) n_sav($user);
else {
	u_frm();
	if ($user->uid) echo ('
		<br />
		<ul>
			<li><a href="?acc='.CValid::$acc.'&amp;acc3=gsh">Wybierz grupę &raquo;</a></li>
			<li><a href="?acc='.CValid::$acc.'&amp;acc3=nsh">Dodaj testy/grupy &raquo;</a></li>
		</ul>
	');
}

?>
