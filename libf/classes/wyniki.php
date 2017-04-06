<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
include_cl('tabela');

class wyniki extends tabela {
	var $keys=array('wid','uid','gid','tid','host','datas','datak','ileok','mpkt');
	//ileok => ilość zdobytych punktów, mpkt => maksymalna ilość punktów z uwzględnieniem pytań otwartych
	var $srednia='';

	function __construct($wid=null,$uid=null,$tid=null,$gid=null) {
		$keys=array('tbn','idn','vn','tbid','wid','uid','tid','gid');
		$values=array('wyniki','wid','uid','',$wid,$uid,$tid,$gid);
		parent::__construct($keys,$values);
		$this->shift=true;
	}

	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT * FROM wyniki WHERE ';
		$this->qstr=$qstr;
		$qstr.=$this->setWar(array('wid','uid','tid','gid'));
		parent::getTb2($qstr,$tbp);
	}

//Do usunięcia
//	function savTb($tk=array(),$tadd,$mod=array()) {
//		if (CDBase::dbIsVal('SELECT wid FROM '.$this->tbn.' WHERE uid = ? AND tid = ?',array(array($this->uid,$this->tid))))
//			CMsg::kadd('Ten test już rozwiązywałeś!');
//		parent::savTb($tk,$tadd,$mod);
//	}

	function is_wpis($v) {
		if (is_null($v[0]) || $v[0]=='NULL') return false;
		$war='uid='.$v[1].' AND gid='.$v[2].' AND tid='.$v[3].' AND datas='.$v[5].' LIMIT 1';
		return parent::is_wpis($war);
	}

//--aktualizacja ilości poprawnych odp. w tabeli wyniki
	function updWyn($tbupd) {
		CDBase::dbSetq('UPDATE wyniki SET ileok = ? WHERE wid = ?',$tbupd);
		return CDBase::dbExec();
	}

	function remWyn($tbdel) {
		if (!is_array($tbdel)) $tbdel=array($tbdel);
		foreach ($tbdel as $k => $wid) $tbdel[$k]=array($wid); 
		CDBase::dbSetq('DELETE FROM wyniki WHERE wid=?',$tbdel);
		CDBase::dbSetq('DELETE FROM wynpyt WHERE wid=?',$tbdel);
		CDBase::dbSetq('DELETE FROM wynank WHERE wid=?',$tbdel);
		$ret=CDBase::dbExec();
		$this->tb=array();	
		$this->getTb2($this->qstr);
		return $ret;
	}

	function getOceny($tbtesty) {
		$this->srednia=array();
		$tbmpkt=array();
		foreach ($this->tb as $wid => $v) {
			$tid = $v['tid'];
			$v['typ']=$tbtesty[$tid]['typ'];
			$v['oc']='&#8211;';
			if (!$v['typ']) { //test, potrzeba dodatkowych danych
				if ($tbtesty[$tid]['ilep']>0) {//test z losowaniem pytań
					$tbpids=CDBase::dbQuery('SELECT pid FROM wynpyt WHERE wid='.$wid,PDO::FETCH_COLUMN);
					$v['mpkta']=$this->getMaxPkt($tid,$tbpids);
				} else {
					if (array_key_exists($tid,$tbmpkt)) $v['mpkta']=$tbmpkt[$tid];
					else $v['mpkta']=$tbmpkt[$tid]=$this->getMaxPkt($tid);
				}
				//echo $wid.'=>'.$v['tid'].'=>'.$v['mpkt'].'<hr />';
				$pro=$v['ileok']/$v['mpkta'];
				$v['oc']=fOcena($pro,array('oceny'=>$tbtesty[$tid]['oceny'],'progi'=>$tbtesty[$tid]['progi']));
				$v['pro']=round($pro*100,2);
				$this->srednia[]=$v['oc'];
			}
			$this->tb[$wid]=$v;
		}
		$this->srednia=round(array_sum($this->srednia)/count($this->srednia),2);//średnia
	}

// Zwróć maksymalną ilość punktów możliwą do uzyskania z testu

	function getMaxPkt($tid=null,$tbpids=array()) {
		if (is_null($tid)) return 0;
		if (!empty($tbpids)) {
			$war=' pid='.implode(' OR pid=',$tbpids);
			$qstr='SELECT SUM(mpkt) FROM pytania WHERE '.$war;
		} else {
			$qstr='SELECT SUM(mpkt) FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$tid;
		}
		$mpkt=CDBase::dbQuery($qstr,PDO::FETCH_COLUMN,true);
		return $mpkt;
	}

}
class wynpyt extends tabela {
	var $keys=array('OID','wid','pid','odp','wyn');//test

	function __construct($wid=null,$pid=null,$typ=0) {
		if ($typ) { //ankieta
			$this->keys=array('OID','wid','pid','odp');
			$tbn = 'wynank';
		} else $tbn='wynpyt';
		$keys=array('tbn','idn','vn','wid','pid');
		$values=array($tbn,'rowid','rowid',$wid,$pid);
		parent::__construct($keys,$values);
		$this->explo=array('odp');
	}

	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT OID,* FROM '.$this->tbn.' WHERE ';
		$qstr.=$this->setWar(array('wid','pid'));
		//echo $qstr; //return;
		parent::getTb2($qstr,$tbp);
	}

	function is_wpis($v) {
		if (is_null($v[0])) return false;
		$war='wid='.$v[0].' LIMIT 1';
		return parent::is_wpis($war);
	}
}
class wynpytprv extends tabela {
	var $keys=array('OID','uid','pid','tid','datas','odp','wyn');//test

	function __construct($uid=null,$pid=null,$tid=null,$datas=null,$typ=0) {
		if ($typ) { //ankieta
			$this->keys=array('OID','uid','pid','tid','datas','odp');
			$tbn = 'wynankprv';
		} else $tbn='wynpytprv';
		$keys=array('tbn','idn','vn','uid','pid','tid','datas');
		$values=array($tbn,'rowid','rowid',$uid,$pid,$tid,$datas);
		parent::__construct($keys,$values);
		$this->explo=array('odp');
	}

	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT OID,* FROM '.$this->tbn.' WHERE ';
		$qstr.=$this->setWar(array('uid','pid','tid','datas'));
		echo $qstr; //return;
		parent::getTb2($qstr,$tbp);
	}

	function is_wpis($v) {
		if (is_null($v[0])) return false;
		$war='uid='.$v[0].' AND pid='.$v[1].' AND datas='.$v[3].' LIMIT 1';
		return parent::is_wpis($war);
	}
}
?>