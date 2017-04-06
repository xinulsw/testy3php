<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }

function ch_users() {
	echo '<form action="?acc='.CValid::$acc.'" method="post">
			<input type="hidden" name="acc3" value="shu" />
			<fieldset class="danefrm">
			<legend>Wybierz kategorię:</legend>
			<span>Anonim</span><input name="kto" type="radio" value="a" /><br />
			<span>Nauczyciele</span><input name="kto" type="radio" value="n" /><br />
			<span>Uczniowie</span><input name="kto" type="radio" value="u" /><br />
			<span>Testowy</span><input name="kto" type="radio" value="t" /><br />
			</fieldset>
			<input type="submit" value="Pokaż" class="but" />
		</form>';
	echo '<hr /><h3>Konto nauczyciela</h3>';
	echo '<form action="?acc='.CValid::$acc.'" method="post">
			<input type="hidden" name="acc3" value="nsv" />
			<fieldset class="danefrm">
			<legend>Podaj dane:</legend>
			<label for="login">Login: </label><input type="text" name="login" size="20" /><br />
			<label for="email">Email: </label><input type="text" name="email" size="20" /><br />
			<label for="tok">Token: </label><input type="text" name="tok" size="20" /><br />
			</fieldset>
			<input type="submit" value="Zapisz" class="but" />
		</form>';
}

function sh_users() {
global $user;
	$kto=CValid::getCnt('kto','p');
	if (!CValid::vStr($kto,1,2)) return CMsg::eadd();
	$tb=CDBase::dbQuery('SELECT  users.*,uinfo.llog FROM users,uinfo WHERE users.uid=uinfo.uid AND '.$kto.'=1');
	$ktotb=array('a'=>'użytkowników anonimowych','n'=>'nauczycieli','u'=>'uczniów');
	echo '<h3>Lista '.$ktotb[$kto].'</h3>';
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="usrfrm" id="usrfrm">
			<input type="hidden" name="acc3" value="gsv" />';
	echo '<table class="tab" id="tbsort">';
	echo '<tr><th>Uid</th><th>Login</th><th>Email</th><th>Nazwisko</th><th>Dostęp</th><th>Act</th><th class="unsortable">Resetuj<br />hasło</th></tr>';
	foreach ($tb as $k => $v) {
		if ($v['d']) continue;
		echo '<tr><td class="tc"><input type="hidden" name="tk[]" value="'.$v['uid'].'" />'.$v['uid'].'</td>
						<td>'.$v['login'].'</td><td>'.$v['email'].'</td><td>'.$v['nazwisko'].'</td><td class="tc">'.fCzdost($v['llog']).'</td>
						<td class="tc"><input type="checkbox" name="act[]"'.($v['a'] ? ' checked="checked"' : '').' /></td><td class="tc"><input type="checkbox" name="tres[]" value="'.$v['uid'].'" /></td></tr>';
	}
	echo '</table>';
	echo '<input class="cb but flr" type="submit" value="Zapisz" /></form>';
}

function sav_nsv() {
global $user;
	$login=CValid::getCnt('login');
	$email=CValid::getCnt('email');
	$tok=CValid::getCnt('tok');
	$uid=CDBase::dbQuery('SELECT uid FROM users WHERE login=\''.$login.'\' AND email=\''.$email.'\' LIMIT 1',PDO::FETCH_COLUMN,true);
	if (is_bool($uid)) return CMsg::eadd('Nie ma podanego użytkownika!');
	$ilet=0;
	$isutok=CDBase::dbQuery('SELECT uid FROM utok WHERE uid='.$uid,PDO::FETCH_COLUMN,true);
	if (empty($tok)) $tok=$user->randomPass();

	if (is_bool($isutok)) {
		CDBase::dbSetq('INSERT INTO utok (uid,tok,ilet) VALUES(?,?,?)',array(array($uid,sha1($tok),$ilet)));
		Cmsg::kadd('Zapisuję dane nowego użytkownika...');
	} else {
		CDBase::dbSetq('UPDATE utok SET tok = ? WHERE uid = ?',array(array(sha1($tok),$uid)));
		CMsg::eadd('Aktualizuję dane użytkownika.');
	}
	if (CDBase::dbExec()) {
		CMsg::kadd('Zapisano dane: '.$login.'/'.$email.'. Token: '.$tok);
		return true;
	}
}

switch (CValid::$acc3) {
	case 'shu': //pokaż użytkowników określonej kategorii
		sh_users();
	break;
	case 'nsv': //dopisz użytkownika, hasło i ilet=5 do tablicy utok
		sav_nsv();
	break;
	case 'gsv': //aktywuj,resetuj konta 
		$act=CValid::getCnt('act','p');
		$tres=CValid::getCnt('tres','p');
		$user->actres($act,$tres);
	break;
	default:
		ch_users();
}
?>