<?php if(!defined('IN_CT')) { die('You cannot load this page directly.'); }
class baza extends CBase{
	var $db=null;
	var $res=null;
	var $q=array(); //query table
	var $qins=''; //insert sth
	var $p=array(); //params of statment
	var $pins=array(); //params of insert statment
	var $r=array(); //tabela rowids
	var $ile=0; //liczniki kwerend udanych
	var $debug=null;
	
	function __construct($dbfile) {
		try {
			$this->db=new PDO("sqlite:dane/baza/db.sq3");
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			CMsg::kadd("Connection OK!");
		} catch(PDOException $e) {
			$this->b_err($e->getMessage());
		}
		$this->b_mode(0);
	}

	function b_setq($op,$q,$sth=true) {
		//echo $op.' '.$q.'<br />';
		switch ($op) {
			case 'sel':
				if (is_array($q)) {
					$qstr='SELECT '.$q['co'].' FROM '.$q['tbn'];
					if (isset($q['inner'])) $qstr.=' INNER JOIN '.$q['inner'];
					if (isset($q['war'])) $qstr.=' WHERE '.$q['war'];
					if (isset($q['lim'])) $qstr.=' LIMIT '.$q['lim'];
					if (isset($q['ord'])) $qstr.=' ORDER BY '.$q['ord']; 
				} else $qstr=$q;
			break;
			case 'ins':
				if (is_array($q) && isset($q['keys'])) {
					$qstr='INSERT INTO '.$q['tbn'];
					$qstr.=' ('.implode(',',$q['keys']).')';
					$qmark=array_fill(0,count($q['keys']),'?');
					$qstr.=' VALUES ('.implode(',',$qmark).')';
				}  else $qstr=$q;
				if ($sth) $this->qins=$this->db->prepare($qstr);
				else $this->qins=$qstr;
				if ($this->debug) CMsg::kadd($qstr);
				return true;
			break;
			case 'upd':
				if (is_array($q)) {
					$sstr=array();
					foreach ($q['keys'] as $key) $sstr[]=$key.' = ?';
					$qstr='UPDATE '.$q['tbn'].' SET '.implode(',',$sstr).' WHERE '.$q['war'].' = ?';
				} else $qstr=$q;
			break;
			case 'del':
				if (is_array($q)) {
					$qstr='DELETE FROM '.$q['tbn'].' WHERE '.$q['war'].' = ?';
				} else $qstr=$q;
			break;
			default:
				$qstr=$q;
		};
		if ($sth) $this->q[]=$this->db->prepare($qstr);
		else $this->q[]=$qstr;
		if ($this->debug) CMsg::kadd($qstr);
		$this->ile=count($this->q);
		return ($this->ile-1);
	}

	function b_update($tbn,$keys,$war,$tbv) {
		if (!is_array($keys)) $keys=array($keys);
		if (!is_array($tbv)) return CMsg::eadd('Błąd danych (b_update)');
		$k=$this->b_setq('upd',array('tbn'=>$tbn,'keys'=>$keys,'war'=>$war));
		$this->p[$k]=$tbv;
		return $this->b_execute();
	}

	function b_query(&$ret=null) {
		$q=current($this->q);
		//echo $q.'<br />';
		try {
			$this->res=$this->db->query($q);
		} catch(PDOException $e) {
			$this->b_err($e->getMessage());
		}
		$ret=$this->res->fetchAll($this->mode);
		$this->b_err();
		$this->b_reset('q',true);
		if (empty($ret)) return false;
		return true;
	}

	function b_query_num($q=null,$mode=0) {
		if ($this->b_query($q,$mode) && $this->res->fetchColumn()) return true;
		return false;
	}

	function b_exec() {
		if ($this->ile<1) { CMsg::eadd('Brak danych do wykonania!(b_exec)'); return true; }
		$ret=0;
		foreach ($this->q as $qstr)
			try {
				//echo $qstr.'<br />';
				$ret=$this->db->exec($qstr);
			} catch(PDOException $e) {
				$this->b_err($e->getMessage());
			}	
		$this->b_reset('q',true);
		$this->b_err();
		return $ret;
	}

	function b_execute(&$ret=null) {
		if ($this->ile<1) { CMsg::eadd('Brak danych do usunięcia/zaktualizowania!(b_execute)'); return true; }
		if ($this->ile>1) $this->db->beginTransaction();
		foreach ($this->q as $k => $sth) {
				//var_dump($sth);echo '<br />';
				//var_dump($this->p[$k]);echo '<br />';
			foreach ($this->p[$k] as $params) {
				try {
					foreach ($params as $n => $p) {
						$sth->bindParam(($n+1),$params[$n]);
					}
					$sth->execute();
				} catch(PDOException $e) {
					$this->b_err($e->getMessage());
				}
			}
		}
		if ($this->ile>1) $this->db->commit();
		$this->b_reset(null,true);
		if (is_array($ret)) $ret=$sth->fetchAll($this->mode);
		CMsg::kadd('Zaktualizowano/usunięto ['.$sth->rowCount().']');
		return $sth->rowCount();
	}

	function b_exec_ins() {
		if (empty($this->qins)) { CMsg::kadd('Brak danych do zapisania.(b_exec_ins)'); return true; }
		$ret=0;
		if (is_string($this->qins)) $this->qins=$this->db->prepare($this->qins);
		foreach ($this->pins as $k => $params) {
			//print_r($params); return;
			try {
				foreach ($params as $n => $p)
					$this->qins->bindParam(($n+1),$params[$n]);
				$this->qins->execute();
			} catch(PDOException $e) {
				$this->b_err($e->getMessage());
			}
			$this->r[$k]=$this->db->lastInsertId();
			$ret+=$this->qins->rowCount();
		}
		if ($ret) CMsg::kadd('Zapisano ['.$ret.'].');
		$this->b_reset('qins');
		return $ret;
	}

	function b_exec_all() {
		if ($this->b_exec_ins() && $this->b_execute()) return true;
		return false;
	}
	
//do tabeli parametrów zapisz tablicę wartości dla zapytania o indeksie k
//$tbn - nazwa tablicy
//$co - pola do pobrania
//$keys - pola do warunku
//$tbv - wartości do warunku
//$lim - limit
//$nmp - nazwa pole, które należy zwrócić
	function b_isval($tbn,$co,$keys,$tbv,$lim=true) {
		$keys=mktb($keys); $tbv=mktb($tbv);
		$this->b_vprep($tbv,true);
		$war='';
		foreach ($keys as $k => $v) $tbv[$k]=$v.'='.$tbv[$k];
		$war=implode(' AND ', $tbv);
		$lim ? $war.=' LIMIT 1' :  '';
		$q='SELECT '.$co.' FROM '.$tbn.' WHERE '.$war;
		//echo $q;
		if ($this->debug) CMsg::kadd($q);
		try {
			$this->res=$this->db->query($q,$this->mode);
		} catch(PDOException $e) {
			$this->b_err($e->getMessage());
		}
		$ret=$this->res->fetchAll($this->mode);
		if (empty($ret)) return false;
		if ($co==='OID') return $ret[0]['rowid'];
		if ($lim && (strpos($co,',') === false)) return $ret[0][$co];
		return $ret;
	}
//--- fix keys tabela.pole na pole
	function fixKeys($tb) {//zamienia klucze tabela.pole na pole
		if (!is_array($tb)) return $tb;
		foreach ($tb as $k => $v) {
			unset($tb[$k]);
			if (is_array($v)) $tb[$k]=$this->fixKeys($v);
			else {
				if (strpos($k,'.')) $k=substr($k,strpos($k,'.')+1);
				$tb[$k]=$v;
			}
		}
		return $tb;
	}
//--- funkcja formatująca wyrażenia set lub where
	function b_vprep(&$tbv) {
		foreach ($tbv as $k => $v) {
			if ($v==='NULL') $tbv[$k]=NULL;
			else if (!is_numeric($v)) $tbv[$k]='\''.$v.'\'';
		}
	}

	function b_err($errstr=null) {
		if (is_null($errstr)) {
			foreach ($this->db->errorInfo() as $e)
				if ($e!='00000') CMsg::eadd($e);
		} else CMsg::eadd($errstr);
	}

	function b_type($n=0) {
		return $n ? 'PDO::PARAM_STR' : 'PDO::PARAM_INT';
	}

	function b_mode($mode=0) {
		if ($mode<0 || $mode>12) $mode=0; 
		$fetch_style=array(
			PDO::FETCH_ASSOC,
			PDO::FETCH_BOTH,
			PDO::FETCH_BOUND,
			PDO::FETCH_CLASS,
			PDO::FETCH_INTO,
			PDO::FETCH_LAZY,
			PDO::FETCH_NUM,
			PDO::FETCH_OBJ,
			PDO::FETCH_COLUMN,
			PDO::FETCH_UNIQUE,
			PDO::FETCH_GROUP,
			PDO::FETCH_FUNC,
			PDO::FETCH_KEY_PAIR
			);
		$this->mode=$fetch_style[$mode];
	}

//--- funkcja czyści tablice i ew. licznik poprawnych zapytań
	function b_reset($co='',$ile=false) {
		if ($co == 'q') $this->q=$this->p=array();
		else if ($co == 'qins') { $this->qins=''; $this->pins=array(); }
		else { $this->q=$this->p=$this->pins=array(); $this->qins=''; }
		if ($ile) $this->ile=0;
	}
	
	function druk_q($q) {
		if (!is_array($q)) $q=array($q);
		foreach ($q as $v) CMsg::kadd($v);
	}

}

function mk_db($db) {
//a - active, n - nauczyciel, d - admin, u - uczen, t - test
// ----- Konfiguracja konta administratora --------------------------------
// Przed uruchomieniem serwisu podaj:
// Login; Hasło; Email; Nazwisko i imię Administratora
$l = 'redrob'; $p = 'border05!'; $em ='admin@centrum.vot.pl'; $nazw ='Wrona Ireneusz';
// Login; Hasło; Email; Nazwisko i imię Nauczyciela testowego
$l2 = 'nauczyciel'; $p2 = 'nauczyciel'; $em2 = 'nauczyciel@centrum.vot.pl'; $nazw2 = 'Nauczyciel Testowy';
// Login; Hasło; Email; Nazwisko i imię Ucznia testowego
$l3 = 'uczen'; $p3 = 'uczen05'; $em3 = 'uczen@centrum.vot.pl'; $nazw3 = 'Uczen Testowy';
//Hasło szkoły Centrum
$tok = 'centrum';
// -------------------------------------------------------------------------
$db->q[]="BEGIN;
	CREATE TABLE users (
		uid INTEGER PRIMARY KEY NOT NULL,
		login CHAR(20) UNIQUE NOT NULL,
		haslo CHAR(50) NOT NULL,
		email CHAR(40) UNIQUE NOT NULL,
		nazwisko CHAR(40) NOT NULL,
		a BOOLEAN,
		u BOOLEAN,
		n BOOLEAN,
		d BOOLEAN,
		c BOOLEAN);
	INSERT INTO users VALUES (NULL,'$l','".sha1($p)."','$em','$nazw',0,0,1,1,1);
	INSERT INTO users VALUES (NULL,'$l2','".sha1($p2)."','$em2','$nazw2',0,0,1,0,1);
	INSERT INTO users VALUES (NULL,'$l3','".sha1($p3)."','$em3','$nazw3',0,1,0,0,1);
	COMMIT;";
// -- tabela z danymi dodatkowymi użytkownika, np. ostatni rozwiązywany test
$db->q[]="BEGIN;
	CREATE TABLE uinfo (
		uid INTEGER,
		sid CHAR(50) DEFAULT '',
		tid INTEGER DEFAULT 0,
		llog INTEGER DEFAULT 0,
		ip CHAR(40) DEFAULT '');
	INSERT INTO uinfo(uid) VALUES(1);
	INSERT INTO uinfo(uid) VALUES(2);
	INSERT INTO uinfo(uid) VALUES(3);
	COMMIT;";

//Tabela użytkownik(nauczyciel)-token
$db->q[]="BEGIN;
	CREATE TABLE utok (
		uid INTEGER NOT NULL,
		tok CHAR(50) NOT NULL DEFAULT '',
		ilet INTEGER NOT NULL DEFAULT 0);
	INSERT INTO utok VALUES (1,'used',0);
	INSERT INTO utok VALUES (2,'used',5);
	COMMIT;";

$db->ile=3;
if ($db->b_exec()) CMsg::kadd('Utworzono tabele users, uinfo i utok.');
//$db->q[]="BEGIN;
//	CREATE TABLE ufrm (
//		uid INTEGER,
//		fid TEXT);
//	COMMIT;";

//Tabela stron: a - dla wszystkich, d - dla admina, n - dla naucz, z - dla zalogowanego, p - pokazać
$stronytb=array(
	1 => array(0,0,'start','Witamy','Witamy','a#p'),
	2 => array(0,0,'testy','Testy','Testy','a#p'),
	3 => array(0,0,'ankiety','Ankiety','Ankiety','a#p'),
	4 => array(0,0,'wyniki','Wyniki','Wyniki','a#p'),
	5 => array(0,0,'edycja','Edycja','Edycja','n#p'),
	8 => array(5,1,'epytania','Pytania','Pytania','n#p'),
	9 => array(5,2,'etesty','Testy/Ankiety','Testy/Ankiety','n#p'),
	10 => array(5,3,'egrupy','Grupy','Grupy','n#p'),
	11 => array(5,4,'ewyniki','Wyniki','Wyniki','n#p'),
	12 => array(0,0,'profil','Profil','Profil','a#p'),
	13 => array(0,0,'eadmin','Administracja','Administracja','d#p'),
	14 => array(13,1,'eakonta','Konta','Konta','d#p'),
	15 => array(13,2,'eausers','Użytkownicy','Użytkownicy','d#p'),
	16 => array(13,3,'eaprzedmioty','Przedmioty','Przedmioty','d#p'),
	17 => array(13,4,'eabaza','Baza','Baza','d#p'),
	24 => array(0,0,'getres','','','a')
);
$kod='';
foreach ($stronytb as $k => $v) {
	$kod.='INSERT INTO strony (sid,paid,pord,plik,tyt,tmenu,perm) VALUES ('.$k.',';
	foreach ($v as $v2) $kod.='\''.$v2.'\',';
	$kod=substr($kod,0,-1);
	$kod.=');';
}
$db->q[]="BEGIN;
	CREATE TABLE strony (
		sid INT PRIMARY KEY NOT NULL,
		paid INTEGER,
		pord INTEGER,
		plik CHAR(10),
		tyt CHAR(30),
		tmenu CHAR(20),
		perm CHAR(10));
	$kod
	COMMIT;";
$db->ile=1;
if ($db->b_exec()) CMsg::kadd('Utworzono tabele: strony.');

//Tabela przedmiotów
$przedmioty = array(
	'brak przedmiotu','religia','etyka','j. polski','j. angielski',
	'j. niemiecki','j. rosyjski','j. francuski','j. włoski','j. hiszpański',
	'j. łaciński','historia','wiedza o społeczeństwie','wiedza o kulturze','matematyka',
	'fizyka','chemia','biologia','geografia','podstawy przedsiębiorczości',
	'technologia informacyjna','informatyka','wychowanie fizyczne','przysposobienie obronne','filozofia',
	'przedmiot zawodowy','egzamin zawodowy','inny'
);
$kod='';
foreach ($przedmioty as $v) $kod.='INSERT INTO przedmioty (przid,przedm,uid) VALUES (NULL,\''.$v.'\',1);';
$db->q[]="BEGIN;
	CREATE TABLE przedmioty (
		przid INTEGER PRIMARY KEY NOT NULL,
		przedm CHAR(40),
		uid INTEGER);
		$kod
	COMMIT;";
$db->ile=1;
if ($db->b_exec()) CMsg::kadd('Utworzono tabele: przedmioty.');
//Tabela kategorii użytkownika (nauczyciela)
$db->q[]="BEGIN;
	CREATE TABLE kategorie (
		kid INTEGER PRIMARY KEY NOT NULL,
		kat CHAR(40),
		uid INTEGER,
		przid INTEGER);
		INSERT INTO kategorie VALUES (NULL,'brak kategorii',1,1);	COMMIT;";
$db->ile=1;
if ($db->b_exec()) CMsg::kadd('Utworzono tabele: kategorie.');

//Tabela szkoły
//$db->q[]="BEGIN;
//	CREATE TABLE szkoly (
//		szid INTEGER PRIMARY KEY NOT NULL,
//		szkola CHAR(100),
//		tok CHAR(20) NOT NULL);
//	INSERT INTO szkoly (szid,szkola,tok) VALUES (NULL,'Centrum testów','$tok');
//	COMMIT;";

//Tabela klas
$haslo=sha1('testy');
$db->q[]="BEGIN;
	CREATE TABLE grupy (
		gid INTEGER PRIMARY KEY NOT NULL,
		uid INTEGER NOT NULL,
		grupa CHAR(10),
		opis CHAR(40),
		tok CHAR(10) NOT NULL,
		ilu INTEGER,
		rszk CHAR(4));
	INSERT INTO grupy VALUES (NULL,2,'Testy','Grupa wbudowana','$haslo',0,'$rszk');
	COMMIT;";

//Tabela grupa - użytkownik - nr porządkowy w grupie - podgrupa
$db->q[]="BEGIN;
	CREATE TABLE gunp (
		gid INTEGER,
		uid INTEGER,
		nrp INTEGER,
		pgr INTEGER);
	INSERT INTO gunp VALUES (1,3,0,0);
	COMMIT;";

//Tabela grupa-nauczyciel-przedmiot
//$db->q[]="BEGIN;
//	CREATE TABLE gnp (
//		gid INTEGER DEFAULT 0,
//		uid INTEGER NOT NULL,
//		przid INTEGER DEFAULT 0);
//	INSERT INTO gnp VALUES (1,1,1);	
//	INSERT INTO gnp VALUES (1,2,1);
//	COMMIT;";
$db->ile=4;
if ($db->b_exec()) CMsg::kadd('Utworzono tabele: utok, grupy, gunp.');

$db->ile=10;
//Tabela pytań
//typy pytań 0=>text, 1=>radio, 2=>checkbox
$db->q[]="BEGIN;
	CREATE TABLE pytania (
		pid INTEGER PRIMARY KEY NOT NULL,
		przid INTEGER,
		kid INTEGER,
		uid INTEGER,
		typ INTEGER,
		pyt TEXT,
		odpt TEXT,
		odp TEXT,
		pub BOOLEAN,
		ssid TEXT DEFAULT '');
	COMMIT;";

//Tabela mediów
$db->q[]="BEGIN;
	CREATE TABLE media (
		mid INTEGER PRIMARY KEY NOT NULL,
		uid INTEGER,
		typ INTEGER,
		fname TEXT NOT NULL,
		opis TEXT DEFAULT '',
		pub BOOLEAN);
	COMMIT;";

//Tabela media - pytanie
$db->q[]="BEGIN;
	CREATE TABLE mp (
		mid INTEGER NOT NULL,
		pid INTEGER NOT NULL);
	COMMIT;";

//Tabela skal testów
//typy: 1 - procentowe, 2 - punktowe
$db->q[]="BEGIN;
	CREATE TABLE skale (
		skid INTEGER PRIMARY KEY NOT NULL,
		uid INTEGER,
		typ SMALL INTEGER DEFAULT 1,
		nazwa CHAR(20),
		oceny CHAR(40),
		progi CHAR(40));
		INSERT INTO skale VALUES (NULL,2,1,'Oceny szkolne 0-5','0#1#2#3#4#5','0#0.4#0.5#0.65#0.85#1');
		INSERT INTO skale VALUES (NULL,2,1,'Oceny szkolne 0-6','0#1#2#3#4#5#6','0#0.4#0.5#0.65#0.85#0.95#1');
	COMMIT;";

//Tabela z danymi testów
// open - czy można test pokazać uczniom?
// skid - id skali
// ilep - ile pytań wylosować do testu
$db->q[]="BEGIN;
	CREATE TABLE testy (
		tid INTEGER PRIMARY KEY NOT NULL,
		przid INTEGER,
		kid INTEGER,
		uid INTEGER,
		typ INTEGER,
		wer CHAR(5),
		zakres CHAR(50),
		skid INTEGER NOT NULL DEFAULT 1,
		czas INTEGER,
		ord TEXT,
		losp BOOLEAN,
		loso BOOLEAN,
		open BOOLEAN,
		pub BOOLEAN,
		ilep INTEGER DEFAULT 0);
	COMMIT;";

//Tabela łącząca testy i pytania
$db->q[]="BEGIN;
	CREATE TABLE testp (
		tid INTEGER NOT NULL,
		pid INTEGER NOT NULL);
	COMMIT;";

//Tabela łącząca test - grupa - podgrupa
$db->q[]="BEGIN;
	CREATE TABLE testg (
		tid INTEGER NOT NULL,
		gid INTEGER NOT NULL,
		open BOOLEAN NOT NULL);
	COMMIT;";

//Tabela przechowująca informacje o rozpoczęciu testu
//$db->q[]="BEGIN;
//	CREATE TABLE testu (
//		tid INTEGER NOT NULL,
//		uid INTEGER NOT NULL,
//		czas INTEGER DEFAULT 0);
//	COMMIT;";

//Tabela wyników
$db->q[]="BEGIN;
	CREATE TABLE wyniki (
		wid INTEGER PRIMARY KEY NOT NULL,
		uid INTEGER,
		gid INTEGER NOT NULL DEFAULT 0,
		tid INTEGER,
		host CHAR(50),
		datas INTEGER,
		datak INTEGER,
		ileok INTEGER);
	COMMIT;";

//Tabela odpowiedzi
$db->q[]="BEGIN;
	CREATE TABLE wynpyt (
		uid INTEGER,
		pid INTEGER,
		tid INTEGER DEFAULT 0,
		datas INTEGER,
		odp TEXT,
		wyn INTEGER);//-1|0|1
	COMMIT;";

$db->q[]="BEGIN;
	CREATE TABLE wynank (
		uid INTEGER DEFAULT 0,
		pid INTEGER,
		tid INTEGER DEFAULT 0,
		datas INTEGER,
		odp TEXT);
	COMMIT;";

if ($db->b_exec()) CMsg::kadd('Utworzono bazę i tabele.');
}
//------------------------------------------------------------------------------
if (!file_exists($dbfile)) {
	require_once(CT_CLASSES.'code.php');
	require_once(CT_CLASSES.'baza.php');
	require_once(CT_LIB.'inifun.php');
	$code = new code();
	//$rszk = $code->rszk;
	$db = new baza($dbfile);
	mk_db($db);
	$db->pall(true);
}

/*
$lnma=$vall->getCnt('lnma','p');
$lpsa=$vall->getCnt('lpsa','p');
$lpsa2=$vall->getCnt('nlps2','p');
if (strcmp($lpsa,$lpsa2) != 0) {
	$vall->errmsg('Podane hasła różnią się!');
	exit();
}
if (!is_null($lnma) && !is_null($lpsa)) {
	$lema=$vall->getCnt('nlem','p');
	$imie=$vall->getCnt('imie','p');
	$nazwi=$vall->getCnt('nazwi','p');
	$nazwisko=$nazwi.' '.$imie;
	if (!$vall->vStr($lnma,5,10) || !$vall->vStr($lpsa,5,20) || !$vall->vStr($nazwisko,6,30)) return $vall->errmsg();
	mk_db($lnma,$lpsa,$lema,$nazwisko);
}
*/
?>
