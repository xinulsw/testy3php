<?php
class CDBase {
	static private $dbH = null;
	static private $szDBName = 'dane/baza/db.sq3';
	static public $tbq = array();//tablica zapytań
	static private $tbp = array();//tablica parametrów
	static private $tbt = array();//tablica typów
	static private $k = 0;//indeks dla powyższych tablic
	static private $tbr = array();//tablica rowids po INSERT
	static public $mode=PDO::FETCH_ASSOC;

	static public function init($szDBName = 'dane/baza/db.sq3') {
		self::$szDBName = $szDBName;
	}
	static public function getHandler() {
		#if ((float)phpversion() > 5.4) self::$szDBName=$_SERVER['DOCUMENT_ROOT'].'/'.CT_BASE.'db.sq3';
		if(self::$dbH === null) {
			try {
				self::$dbH = new PDO('sqlite:'.self::$szDBName);
				self::$dbH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch(PDOException $e) {
				CMsg::eadd($e->getMessage());
			}
		}
		return self::$dbH;
	}

	static public function dbSetq($qstr,$tbp=null,$tbt=array()) {
		if (empty($qstr)) return CMsg::eadd('Puste zapytanie!');
		if(CT_CTDEBUG) CMsg::kadd($qstr.' >CDBase<');
		if (is_null($tbp)) {
			self::$tbq[self::$k]=$qstr;
		} else {
			self::$tbq[self::$k]=self::$dbH->prepare($qstr);
			self::$tbp[self::$k]=$tbp;
			self::$tbt[self::$k]=$tbt;
		}
		self::$k++;
		return (self::$k-1);
	}

	static public function dbExec(&$ret=null,$mode=PDO::FETCH_ASSOC) {
		self::$mode=$mode;
		if (self::$k<1 && CT_CTDEBUG) { CMsg::eadd('Brak danych do usunięcia/zaktualizowania!(dbExec)'); return true; }
		if (self::$k>1) self::$dbH->beginTransaction();
		$ile=0;
		foreach (self::$tbq as $i => $sth) {
			foreach (self::$tbp[$i] as $j => $tbps) {
				if (isset(self::$tbt[$i][$j])) $tbts=self::$tbt[$i][$j];
				try {
					if (isset($tbts)) {
						foreach ($tbps as $k => $p) {
//echo ($k+1).' => '.$p.' => '.$tbps[$k].'<br />';
							$sth->bindParam(($k+1),$tbps[$k]);
						}
						$sth->execute();
					} else {
						//echo 'TBPS: ';print_r($tbps);echo '<br />';
						$sth->execute($tbps);
					}
				} catch(PDOException $e) {
					self::dbErr($e->getMessage());
				}
				$lrowid=self::$dbH->lastInsertId();
				if (!empty($lrowid)) {
					//echo('LROWID: '.$lrowid.'<br />');
					self::$tbr[$j]=$lrowid;
				}
				$ile+=$sth->rowCount();
			}
		}
		if (self::$k>1) self::$dbH->commit();
//print_r($sth->fetchAll(self::$mode));
		if (is_array($ret)) $ret=$sth->fetchAll(self::$mode);
		if (CT_CTDEBUG) CMsg::kadd('Zaktualizowano/usunięto ['.$ile.']');
		self::dbReset();
		return $ile;
	}

	static public function dbGetLastId($i=null) {
		$ret=null;
		if (!is_null($i) && array_key_exists($i,self::$tbr)) $ret=self::$tbr[$i];
		else if (count(self::$tbr)) $ret=current(self::$tbr);
		if (is_null($i)) self::$tbr=array();
		return $ret;
	}

	static public function dbExecStr($qstr=null) {
		if (empty($qstr)) { CMsg::eadd('Brak danych do usunięcia/zaktualizowania!(dbExec)'); return true; }
		//if (!empty($qstr)) self::dbSetq($qstr);
		//if (self::$k<1) { CMsg::eadd('Brak danych do usunięcia/zaktualizowania!(dbExec)'); return true; }
		//foreach (self::$tbq as $i => $str) {
			try {
				$ret=self::$dbH->exec($qstr);
			} catch(PDOException $e) {
				self::dbErr($e->getMessage());
			}
		//}
		//self::dbReset();
		return $ret;
	}

	static public function dbQuery($qstr,$mode=PDO::FETCH_ASSOC,$one=false) {
		if (CT_CTDEBUG) CMsg::kadd($qstr.' >CDBase<');
		self::$mode=$mode;
		$ret=self::$dbH->query($qstr)->fetchAll(self::$mode);
		self::$mode=PDO::FETCH_ASSOC;
		if (empty($ret)) return false;
		if ($one) return current($ret);
		return $ret;
	}

	static public function dbIsVal($qstr,$tbp=null,$tbt=array(),$mode=PDO::FETCH_ASSOC) {
		self::dbSetq($qstr,$tbp,$tbt);
		self::$mode=$mode;
		$ret=array();
		self::dbExec($ret);
		if (empty($ret[0])) return false;
		return $ret[0];
	}

	static public function dbGet($co) {
		$ret=false;
		if (!empty(self::${$co})) $ret=self::${$co};
		if ($co=='tbr') self::$tbr=array();
		return $ret;
	}

	static private function dbReset() {
		self::$tbq=array();
		self::$tbp=array();
		self::$tbt=array();
		self::$k=0;
		self::$mode=PDO::FETCH_ASSOC;
	}

	static private function dbErr($errstr=null) {
		if (is_null($errstr)) {
			foreach (self::$dbH->errorInfo() as $e)
				if ($e!='00000') CMsg::eadd($e);
		} else CMsg::eadd($errstr);
	}

	private function __construct() {}
	private function __destruct() {}
}
?>