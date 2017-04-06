<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
include_cl(array('tabela','media'));

class pytania extends tabela {
	var $keys=array('pid','przid','kid','uid','typ','pyt','odpt','odp','pub','mpkt','txt');
	var $types=array(1,1,1,1,1,0,0,0,1,0);
	var $pub=false;
	var $imd=CT_IMGS;
	var $tbimgs=array();//tablica obrazków $pid => array of mids

	function __construct($pid=null,$uid=null,$przid=null,$kid=null) {
		$keys=array('tbn','idn','vn','tbid','pid','uid','przid','kid');
		$values=array('pytania','pid','pyt','',$pid,$uid,$przid,$kid);
		parent::__construct($keys,$values);
		$this->shift=true;
		$this->tbrem=array('mp','testp','wynpyt','wynank');
		$this->explo=array('odpt','odp');
	}

	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT pytania.* FROM pytania WHERE ';
		$qstr.=$this->setWar(array('pid','uid','przid','kid'));
		//echo $qstr;return;
		parent::getTb2($qstr,$tbp);
	}

	function delete($war=null) {
		$tbtids=array();
		foreach ($this->tdel as $tb) {
			$ret=CDBase::dbQuery('SELECT DISTINCT tid,pid FROM testp WHERE pid='.current($tb),PDO::FETCH_KEY_PAIR);
			if ($ret) {
				foreach ($ret as $tid => $pid)
					$tbtids[$tid][]=$pid;
			}
		}
		$this->tbtids=$tbtids;
		parent::delete($war);
	}

	function setOrd($ord) {
		$tb=array();
		foreach ($ord as $pid)
			if (array_key_exists($pid,$this->tb)) $tb[$pid]=$this->tb[$pid];
		$this->tb=$tb;
	}

	function is_wpis($v) {
		if (is_null($v[0]) || $v[0]=='NULL') return false;
		$war='pyt=\''.$v[5].'\' LIMIT 1';
		return parent::is_wpis($war);
	}
}
?>