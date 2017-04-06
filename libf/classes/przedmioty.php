<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
include_cl(array('tabela'));

class przedmioty extends tabela {
	var $keys=array('przid','przedm','uid');
	var $kshow=array('przedm');
	var $ktype=array('text');
	var $ksou=array();
	var $ktbh=array('Przedmiot');
	var $ksize=array('30');

	function __construct($przid=null,$uid=null) {
		$keys=array('tbn','idn','vn','tbid','przid','uid');
		$values=array('przedmioty','przid','przedm','tbEdit',$przid,$uid);
		parent::__construct($keys,$values);
		$this->shift=true;
		$this->tbupd=array('kategorie');
		$this->dv=1;//domyślna wartość
	}

	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT przid,przedm FROM przedmioty WHERE ';
		$qstr.=$this->setWar(array('przid','uid'));
		parent::getTb2($qstr,$tbp);
	}

	function is_wpis($v) {
		if (is_null($v[0]) || $v[0]=='NULL') return false;
		$war=$this->vn.'=\''.$v.'\' LIMIT 1';
		return parent::is_wpis($war);
	}
}

class kategorie extends tabela {
	var $keys=array('kid','kat','uid','przid');
	var $kshow=array('przid','kat');
	var $ktype=array('select','text');
	var $ksou=array('przid'=>array());
	var $ktbh=array('Przedmiot','Kategoria');
	var $ksize=array('sel260','50');
	
	function __construct($kid=null,$uid=null,$przid=null) {
		$keys=array('tbn','idn','vn','tbid','kid','uid','przid');
		$values=array('kategorie','kid','kat','katEdit',$kid,$uid,$przid);
		parent::__construct($keys,$values);
		$this->one=true;//zabezpieczenie pierwszego wpisu
		$this->shift=true;
		$this->tbupd=array('pytania','testy');
		$this->dv=1;
		//print_r($this->dane);
	}

	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT * FROM kategorie WHERE ';
		$qstr.=$this->setWar(array('kid','przid'));
		//echo $qstr; return;
		parent::getTb2($qstr,$tbp);
	}

	function savTb($tk=array(),$tadd,$mod=array()) {
		foreach ($tadd as $k => $v) {
			if (count($v)==4) break;
			$tadd[$k]=array($v[0],$v[2],$this->uid,$v[1]);
		}
		parent::savTb($tk,$tadd,$mod);
	}

	function is_wpis($v) {
		if (is_null($v[0]) || $v[0]=='NULL') return false;
		$war='kat=\''.$v[1].'\' AND uid='.$v[2].' AND przid='.$v[3].' LIMIT 1';
		return parent::is_wpis($war);
	}	
}
