<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
include_cl(array('tabela'));

class grupy extends tabela{
	var $keys=array('gid','uid','grupa','opis','tok','ilu');
	var $kshow=array('grupa','opis','tok','ilu');
	var $ktype=array('text','text','text','text');
	var $ktbh=array('Grupa','Opis','Token','Ilu');
	var $ksize=array('10','40','10','3');
	var $href=true;

	function __construct($gid=null,$uid=null) {
		$keys=array('tbn','idn','vn','tbid','gid','uid');
		$values=array('grupy','gid','grupa','grEd',$gid,$uid);
		parent::__construct($keys,$values);
		$this->shift=true;
		$this->tbrem=array('gunp','testg');
		$this->tbupd=array('wyniki');
		$this->dv=0;
	}
	
	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT * FROM grupy WHERE ';
		$qstr.=$this->setWar(array('gid','uid'));
		parent::getTb2($qstr,$tbp);
	}

	function savTb($tk=array(),$tadd,$mod=array()) {
		$tb=$tbgr=$tbtok=array();
		//print_r($tadd); echo '<hr />';
		$tbgr=$tbtok=array();
		foreach ($tadd as $k => $v) {
			$v[1]=trim($v[1]);
			$v[3]=trim($v[3]);
			if ($v['0'] != 'NULL') {
				$tbgr[]=$v[1];//tabela nazw grup
				$tbtok[]=$v[3];//tabela tokenów
			}
			$tadd[$k]=$v;
		}
		foreach ($tadd as $k => $v) {
			$error = false;
			if ($v['0']=='NULL') {
				if (in_array($v[1],$tbgr) || strpos($v[1]," ",1) || strlen($v[3])<5 || in_array($v[3],$tbtok)) {
					CMsg::eadd('Nazwa grupy '.$v[1].' i token '.$v[3].' muszą być unikalne i nie mogą zawierać spacji.');
					$error = true;			
				}
			} else if (!empty($mod[$k])) {
				$ret1=CDBase::dbIsVal('SELECT count(uid) FROM grupy WHERE uid = ? AND grupa = ? AND gid != ? ',array(array($this->uid,$v[1],$v[0])));
				$ret2=CDBase::dbIsVal('SELECT count(uid) FROM grupy WHERE uid = ? AND tok = ? AND gid != ?',array(array($this->uid,$v[3],$v[0])));
				if (current($ret1) > 0 || current($ret2) > 0) {
					CMsg::eadd('Nazwa grupy '.$v[1].' i token '.$v[3].' muszą być unikalne i nie mogą zawierać spacji.');
					$error = true;
				}
			}
			if ($error) {
				if ($v['0']=='NULL') continue;
				else $mod[$k]='';
			}
			$tb[]=array($v[0],$this->uid,$v[1],$v[2],$v[3],$v[4]);
		}
		//print_r($tb);return;
		parent::savTb($tk,$tb,$mod);
	}

	function delGrupa($wid){
		$war='gid='.$wid;
		if (parent::is_wpis($war)) {
			$this->tdel[]=array($wid);
			$this->savDb();
		}
	}

	function is_wpis($v) {
		$war='uid='.$v[1].' AND grupa=\''.$v[2].'\' AND tok=\''.$v[4].'\' LIMIT 1';
		return parent::is_wpis($war);
	}
}

?>
