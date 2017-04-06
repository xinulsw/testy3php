<?php
define('SALT','Nie wszystko złoto, co się świeci.');
define('HASH',md5(dirname(__FILE__) . SALT));
define('SKEY','sess'.HASH);

class CSession {
	public function __set($k,$v) {
		$_SESSION[SKEY][$k] = $v;
	}
	public function __get($k) {
		if (array_key_exists($k,$_SESSION[SKEY]))
		return $_SESSION[SKEY][$k];
	}
	function sget($tb,$k=null,$v=null) {//ustawia, zwraca wartości z tablicy danych
		if (is_array($tb)) { foreach ($tb as $ktb => $vtb) $_SESSION[SKEY][$ktb]=$vtb; return; }
		if (!isset($_SESSION[SKEY][$tb])) $_SESSION[SKEY][$tb]=array();
		if (is_null($v) && array_key_exists($k,$_SESSION[SKEY][$tb])) return $_SESSION[SKEY][$tb][$k];
		if (is_null($k)) $_SESSION[SKEY][$tb][]=$v;
		else {
			//echo $k.'=>'.$v.'<br />';
			$_SESSION[SKEY][$tb][$k]=$v;
		}
		return true;
	}
	public function __toString() {
		return isset($_SESSION[SKEY]) ? print_r($_SESSION[SKEY],true) : "null";
	}
	public function __isset($k) {
		return isset($_SESSION[SKEY][$k]);
	}
	public function __unset($k) {
		unset($_SESSION[SKEY][$k]);
	}
}
//---------------------------------------------------------------
class SessMan {
	static function sessionStart($name, $limit = 0, $path = '/', $domena = null, $secure = null) {
		session_name($name . '_sess');// Set the cookie name
		$https = isset($secure) ? $secure : isset($_SERVER['HTTPS']);// Set SSL level
		$domena = (	is_null($domena) ? $_SERVER['HTTP_HOST'] : $domena);
		session_set_cookie_params($limit, $path, $domena, $https, true); // Set session cookie options
		session_start();

		if(self::validateS()) {// Make sure the session hasn't expired, and destroy it if it has
			if(!self::preventHijacking()) {// Check to see if the session is new or a hijacking attempt
				// Reset session data and regenerate id
				$_SESSION = array();
				$_SESSION['IPaddress'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
				$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
				self::regenerateSession();
			} elseif(rand(1, 100) <= 5) {// Give a 5% chance of the session id changing on any request
				self::regenerateSession();
			}
		} else {
			$_SESSION = array();
			session_destroy();
			session_start();
		}
	}
	static function regenerateSession() {
		if(isset($_SESSION['OBSOLETE'])) return;// If this session is obsolete it means there already is a new id
		// Set current session to expire in 10 seconds
		$_SESSION['OBSOLETE'] = true;
		$_SESSION['EXPIRES'] = time() + 10;
		session_regenerate_id(false);// Create new session without destroying the old one
		$newSession = session_id();// Grab current session ID and close both sessions to allow other scripts to use them
		session_write_close();
		session_id($newSession);// Set session ID to the new one, and start it back up again
		session_start();
		// Now we unset the obsolete and expiration values for the session we want to keep
		unset($_SESSION['OBSOLETE']);
		unset($_SESSION['EXPIRES']);
	}

	static function validateS() {
		if(isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES'])) return false;
		if(isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time()) return false;
		return true;
	}

	static function preventHijacking() {
		if(!isset($_SESSION['IPaddress']) || !isset($_SESSION['userAgent'])) return false;

		if( $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']
			&& !( strpos($_SESSION['userAgent'], "Trident") !== false
				&& strpos($_SERVER['HTTP_USER_AGENT'], "Trident") !== false)) return false;

		$sessionIpSegment = substr($_SESSION['IPaddress'], 0, 7);
		$remoteIpHeader = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		$remoteIpSegment = substr($remoteIpHeader, 0, 7);

		if ($_SESSION['IPaddress'] != $remoteIpHeader) return false;
		if( $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) return false;

		return true;
	}
}

//--- funkcje obsługi użytkownika -------------------------------
class user extends CBase{
	var $val=null; //obiekt obsługi błędów
	var $remTime = 7200;//dwie godziny
	var $CookieDomain = '';
	var $CookieName = 'testySP';
	var $keys=array('uid','login','haslo','email','nazwisko','a','u','n','d','c');
	var $mintok=8; //minimalna długość haseł i tokenów
	
	function __construct() {
		global $sesja;
		$this->dane=array('uid'=>null,'login'=>'','email'=>'','nazwisko'=>'','a'=>0,'u'=>0,'n'=>0,'d'=>0,'c'=>0); //tablica danych podstawowych
		$this->CookieDomain = ($this->CookieDomain == '' ? $_SERVER['HTTP_HOST'] : $this->CookieDomain);
		if (!$this->uid && isset($_POST['lnm'])) {
			CMsg::kadd('Logowanie...');
			$l = CValid::getCnt('lnm'); //nazwa użytkownika
			$p = CValid::getCnt('lps'); //hasło użytkownika
			if (CT_CTDEBUG) CMsg::kadd($l.' '.$p);
			if (!CValid::vStr($l,5,15) || !CValid::vStr($p,5,20)) CMsg::kadd('<b>Zaloguj się</b> lub <a href="?acc=12">załóż konto!</a>.'); 
			else $this->login($l,$p,true,true);
		}
		$this->startSession();
		if(!empty($sesja->uDane)) $this->is_user($sesja->uDane);
		if (isset($_COOKIE[$this->CookieName]) && !$this->uid) {
			if (CT_CTDEBUG) CMsg::kadd('Wczytano ciasteczka!');
			$u = unserialize(base64_decode($_COOKIE[$this->CookieName]));
			$this->login($u['ulog'],$u['pass'],false,true);
		}
		if (isset($_GET['ul'])) $this->logout();
		if ($this->uid) $this->sav_uinfo();
		$this->set_kto();
	}

	function startSession() {
		global $sesja;
		if (!isset($_SESSION)) {
			//session_name('Testy3_sess');// Set the cookie name
			//$https = isset($secure) ? $secure : isset($_SERVER['HTTPS']);// Set SSL level
			//session_set_cookie_params(0, '/', $_SERVER['HTTP_HOST'], $https, true); // Set session cookie options
			session_start();
			$sesja->testy3='testy3';
			if (CT_CTDEBUG) CMsg::kadd('Uruchomiono sesję: '.session_id());		
		} else {
			if (!isset($sesja->testy3)) {
				session_regenerate_id();
				$sesja->testy3='testy3';
			}
		}
	}

	function login($ulog, $pass, $remember=false, $loadUser=true) {
		$ulog = rescape($ulog);
		$pass = rescape($pass);
		if ($loadUser && $this->is_user('',$ulog,$pass)) {
			if ($remember) { // zapisanie ciasteczka
			  $cookie = base64_encode(serialize(array('ulog'=>$ulog,'pass'=>$pass,'czas'=>time())));
			  $a = setcookie($this->CookieName,$cookie,time()+$this->remTime,'/',$this->CookieDomain,false,true);
			  if (CT_CTDEBUG) CMsg::kadd('Zapisano ciasteczka!');
			}
			CMsg::kadd('Witaj '.$ulog.'! Zostałeś zalogowany.');
			return true;
		}
		CMsg::eadd('<b>Błędny login lub hasło!</b>');
		return false;
	}

	function is_user($sid,$ulog=NULL,$pass=NULL) {
		global $sesja;
		if (!empty($ulog)) {
			$pass = sha1($pass);
			if (!isset($sesja->uDane)) {
				$sid=CDBase::dbQuery('SELECT sid FROM uinfo,users WHERE uinfo.uid=users.uid AND login=\''.$ulog.'\' AND haslo=\''.$pass.'\'',PDO::FETCH_COLUMN,true);
				//var_dump($sid);
				if ($sid) {
					CMsg::kadd('Wygląda na to, że się ostatnio nie wylogowałeś! Próbuję przywrócić sesję...');
					session_id($sid);
					//if (!CDBase::dbExecStr('UPDATE uinfo SET sid = "" WHERE sid = \''.$sid.'\'')) CMsg::eadd('Błąd sesji.');
				}
			}
			CDBase::dbSetq('SELECT * FROM users WHERE login = ? AND haslo = ? LIMIT 1',array(array($ulog,$pass)));
		} else {
			CDBase::dbSetq('SELECT * FROM users INNER JOIN uinfo ON users.uid=uinfo.uid WHERE uinfo.sid = ? LIMIT 1',array(array($sid)));
		}
		$ret=array();
		CDBase::dbExec($ret);
		if (!empty($ret[0])) {
			$this->dane=array_merge($this->dane,$ret[0]);
			return true;
		}
		return false;
	}

	function sav_uinfo() {
		global $sesja;
		$sid=session_id();
		if ($sid==$sesja->uDane) return true;
		if (!CDBase::dbIsVal('SELECT uid FROM uinfo WHERE uid = ?',array(array($this->uid)))) {
			CDBase::dbSetq('INSERT INTO uinfo (uid,sid,tid,llog,ip) VALUES (?,?,?,?,?)', array(array($this->uid,$sid,0,time(),$_SERVER['REMOTE_ADDR'])));
		} else {
			CDBase::dbSetq('UPDATE uinfo SET sid = ?, llog = ?, ip = ? WHERE uid = ?',
			array(array($sid,time(),$_SERVER['REMOTE_ADDR'],$this->uid)),
			array(array(PDO::PARAM_STR,PDO::PARAM_INT,PDO::PARAM_STR,PDO::PARAM_INT)));
		}
		$ret=CDBase::dbExec();
		if (!$ret) return CMsg::eadd('Błąd zapisu danych dodatkowych użytkownika.');
		$sesja->uDane = $sid;
		return true;
	}

	function logout($redirectTo = '') {
		global $sesja;
		//echo $sesja->uDane;
		if (isset($sesja->uDane)) {
			if (!CDBase::dbExecStr('UPDATE uinfo SET sid = "" WHERE sid = \''.$sesja->uDane.'\'')) CMsg::eadd('Błąd sesji.');
		}
		setcookie($this->CookieName,'', time()-($this->remTime*3),'/',$this->CookieDomain,false,true);
		$this->dane=array('u'=>0,'n'=>0,'d'=>0,'a'=>0);
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
    		$params = session_get_cookie_params();
    		setcookie(session_name(),'',time()-($this->remTime*3),$params["path"], $params["domain"],$params["secure"],$params["httponly"]);
		}
		if (session_destroy()) CMsg::kadd('Zostałeś wylogowany.');
		else CMsg::kadd('Wylogowany?');
		if ($redirectTo != '' && !headers_sent()) {
			header('Location: '.$redirectTo);
			exit;//To ensure security
		}
	}

	function activate() {
		if ($this->d) return;
		$tbk=array('a','u','n','c','uid');
		$tbv=array($this->a,$this->u,$this->n,$this->c,$this->uid);
		if (!CDBase::dbIsVal('SELECT uid FROM users WHERE a = ? AND u = ? AND n = ? AND c = ? AND uid = ?',array($tbv))) {
			CDBase::dbSetq('UPDATE users SET a = ?, u = ?, n = ?, c = ? WHERE uid = ?',array($tbv));
		}
		if ($this->u) {
			$tbv=array($this->gid,$this->uid,$this->nrp,$this->pgr);
			if (!CDBase::dbIsVal('SELECT gid FROM gunp WHERE gid = ? AND uid = ? AND nrp = ? AND pgr = ?',array($tbv))) {
				CDBase::dbSetq('INSERT INTO gunp (gid,uid,nrp,pgr) VALUES(?,?,?,?)',array($tbv));
			}
		}
		if (CDBase::dbExec()) return true;
		return false;
	}

	function savtb() {//tab. asocjacyjna z kluczami: uid#login#haslo#nazwisko#email#tbprawa#klid
		if (isset($this->gid)) unset($this->gid);
		if (strlen($this->haslo)<40) $this->haslo=sha1($this->haslo);
		$this->llog=time();
		if (!$this->uid) { //tworzymy nowego użytkownika
			CDBase::dbSetq('INSERT INTO users VALUES (?,?,?,?,?,?,?,?,?,?)',
				array(array(NULL,$this->login,$this->haslo,$this->email,$this->nazwisko,$this->a,$this->u,$this->n,$this->d,$this->c)));
			$ret = CDBase::dbExec();
			$uid = CDBase::dbGetLastId();
			if ($ret && $uid) { //zapisujemy info dodatkowe
				CDBase::dbSetq('INSERT INTO uinfo (uid,sid,tid,llog,ip) VALUES (?,?,?,?,?)', array(array($uid,'',0,time(),$_SERVER['REMOTE_ADDR'])));
				$ret=CDBase::dbExec();
				if (!$ret) return CMsg::eadd('Błąd zapisu dodatkowych danych użytkownika.'); 
			} else return CMsg::eadd('Błąd zapisu podstawowych danych użytkownika.');
		} else {
			CDBase::dbSetq('UPDATE users SET login = ?, haslo = ?, email = ?, nazwisko = ?, a = ?, u = ?, n = ?, d = ?, c = ? WHERE uid = ?',
				array(array($this->login,$this->haslo,$this->email,$this->nazwisko,$this->a,$this->u,$this->n,$this->d,$this->c,$this->uid)));
			$ret = CDBase::dbExec();
		}
		if ($ret) return true;
		return false; 
	}

	function actres($tbact,$tbres) {//deaktywacja i resetowanie haseł użytkowników
		if (!empty($tbact)) {
			//print_r($act);
		}

		if (!empty($tbres)) {
			foreach ($tbres as $k => $uid) {
				$tbres[$uid]=current(CDBase::dbIsVal('SELECT login FROM users WHERE uid = ?',array(array($uid))));
				unset($tbres[$k]);
			}
			foreach ($tbres as $uid => $login) {
				if (!empty($login)) $tb=array(sha1($login),$uid);
					CDBase::dbSetq('UPDATE users SET haslo = ? WHERE uid = ?',array($tb));
			}
			if (CDBase::dbExec()) CMsg::kadd('Zresetowano dane użytkowników.');
		}
	}

	function get_users($co='*',$kto,$inner=null) {
		$q='SELECT '.$co.' FROM users';
		if (!is_null($inner)) $q.=$inner;
		$q.=' WHERE '.$kto.'=1';	
		$ret=CDBase::dbQuery($q);
		return $ret;
	}
	function randomPass($length=10, $chrs = '1234567890qQwWeErRtTyYuUiIoOpPaAsSdDfFgGhHjJkKlLzZxXcCvVbBnNmM*()!@#$%^&'){
		$pwd='';
		for($i = 0; $i < $length; $i++) $pwd .= $chrs{mt_rand(0, strlen($chrs)-1)};
		return $pwd;
	}
	function set_kto() {
		$kto='no';//nikt
		if ($this->d) $kto='d';//admin
		else if ($this->a) $kto='a';//anonim
		else if ($this->u) $kto='u';//uczen
		else if ($this->n) $kto='n';//nauczyciel
		$this->kto=$kto;
	}
}
//-----------------------------------------------------------------
?>
