<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
include_cl('tabela');

class testy extends tabela {
	var $keys=array('tid','przid','kid','uid','typ','wer','zakres','skid','czas','ord','losp','loso','open','pub','ilep');
	var $kshow=array('wer','zakres','czas','typ','ilep','test','pytania');
	var $ktype=array('text','text','text','','','href','href');
	var $ksou=array();
	var $ktbh=array('Wersja','Zakres','Czas (min)','Typ','Pytań','T/A','Pytania');
	var $ksize=array('3','30','6');
	var $pyt=null;//obiekt pytania

	function __construct($tid=null,$typ=null,$uid=null,$kid=null,$przid=null) {
		$keys=array('tbn','idn','vn','tbid','tid','typ','uid','kid','przid');
		$values=array('testy','tid','zakres','tEdit',$tid,$typ,$uid,$kid,$przid);
		parent::__construct($keys,$values);
		$this->shift=true;
		$this->tbrem=array('testp','testg','wyniki');
		$this->explo=array('ord');
	}

	function getTb2($qstr=null,$tbp=null) {
		$this->tb=array();
		if (empty($qstr)) $qstr='SELECT testy.*,kategorie.kat FROM testy,kategorie WHERE testy.kid=kategorie.kid AND ';
		$qstr.=$this->setWar(array('tid','typ','uid','kid','przid','pub'));
		parent::getTb2($qstr,$tbp);
	}

	function savTb($tk=array(),$tadd,$mod=array()) {
		foreach ($tadd as $k => $v) {
			if (count($v)<15) {//gdy zapisujemy dane wersja_zakres_czas
				$this->tb[$v[0]]['przid']=$this->tb[$v[0]]['przid'];//CDBase::dbQuery('SELECT przid FROM kategorie WHERE kid = '.$v[1],PDO::FETCH_COLUMN,true);
				$this->tb[$v[0]]['kid']=$this->tb[$v[0]]['kid'];
				$this->tb[$v[0]]['wer']=$v[1];
				$this->tb[$v[0]]['zakres']=$v[2];
				$this->tb[$v[0]]['czas']=$v[3];
				$this->tb[$v[0]]['skid']=(int)$this->tb[$v[0]]['skid'];
				$this->tb[$v[0]]['ord']=implode($this->e,$this->tb[$v[0]]['ord']);
				$tadd[$k]=array_values($this->tb[$v[0]]);
			}
		}
		//print_r($tk);echo '<hr />';
		//print_r($tadd);echo '<hr />';
		//print_r($mod);return;
		parent::savTb($tk,$tadd,$mod);
	}

	function savPyt($tbpids=array()) {
		if (!$this->tid) return;
		$testp= new testp($this->tid);
		$testp->idn='pid';
		$testp->getTb2('SELECT pid FROM testp WHERE ');
		$testp->getDane('tb','pid','pid');
		$tadd=array();
		foreach ($tbpids as $pid) $tadd[]=array($this->tid,$pid);
		$testp->savTb(array(),$tadd,array());
		if (!empty($tbpids)) $tbpids=implode($this->e,$tbpids);
		else $tbpids='';
		return CDBase::dbExecStr('UPDATE testy SET ord = \''.$tbpids.'\' WHERE tid = '.$this->tid);
	}

	function delete($war=null) {
		foreach ($this->tdel as $v){
			echo '<hr />USUWANIE_TESTÓW (testy.php)<hr />';
			//print_r($v);
		}
		return;	
	}

	function is_wpis($v) {
		if (is_null($v[0])) return false;
		$war=$this->idn.'='.$v[0].' LIMIT 1';
		return parent::is_wpis($war);
	}

	function setNew() {
		$this->tid=0; $this->typ=0;
		$this->wer=''; $this->zakres='';
		$this->skid=1;
		$this->czas=0;
		$this->ord=array();
		$this->losp=0; $this->loso=0; $this->open=0; $this->pub=0;
	}

	function getSkale() {
		$ret=CDBase::dbQuery('SELECT skid,nazwa FROM skale WHERE uid=2 OR uid='.$this->uid);
		$tb=array();
		foreach ($ret as $sk) $tb[$sk['skid']]=$sk['nazwa'];
		return $tb;
	}

function getSkala(){
	$ret=CDBase::dbQuery('SELECT oceny,progi FROM skale WHERE skid='.$this->skid);
	if (!empty($ret)) {
		$ret=current($ret);
		$ret['oceny']=explode($this->e,$ret['oceny']);
		$ret['progi']=explode($this->e,$ret['progi']);
	}
	return $ret;
}

}

class testg extends tabela {
	var $keys=array('tid','gid','open');
	function __construct($tid=null,$gid=null,$open=null) {
		$keys=array('tbn','idn','vn','tbid','tid','gid','open');
		$values=array('testg','OID','OID','',$tid,$gid,$open);
		parent::__construct($keys,$values);
	}

	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT OID,tid,gid,open FROM testg WHERE ';
		$qstr.=$this->setWar(array('tid','gid','open'));
		parent::getTb2($qstr,$tbp);
	}

	function delete($war=null) {
		$war='tid = ? AND gid = ?';
		foreach ($this->tdel as $k=>$v)
			$this->tdel[$k]=array($this->tid,$v[0]);
		parent::delete($war);
	}

	function is_wpis($v) {
		$war='tid='.$v[0].' AND gid='.$v[1].' LIMIT 1';
		return parent::is_wpis($war);
	}
}

class testp extends tabela {
	var $keys=array('tid','pid');
	function __construct($tid=null,$pid=null) {
		$keys=array('tbn','idn','vn','tbid','tid','pid');
		$values=array('testp','OID','OID','',$tid,$pid);
		parent::__construct($keys,$values);
	}

	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT OID,tid,pid FROM testp WHERE ';
		$qstr.=$this->setWar(array('tid','pid'));
		parent::getTb2($qstr,$tbp);
	}

	function is_wpis($v) {
		$war='tid='.$v[0].' AND pid='.$v[1].' LIMIT 1';
		return parent::is_wpis($war);
	}
}

//--- funkcje pomocnicze ---
//--- pokaż tabelę testów użytkownika z danej kategorii ---
function mk_tbtesty($uid,$kid) {
	$testy = new testy(null,null,$uid,$kid);
	$testy->getTb2('SELECT tid,typ,wer,zakres,ord,czas FROM testy WHERE ');
	if (empty($testy->tb)) return false;
	foreach ($testy->tb as $k => $v) {
		$testy->tb[$k]['ilep']=count($v['ord']);
		$testy->tb[$k]['test']='<a class="but" href="?acc=9&amp;acc3=edt&amp;idt='.$k.'">Pokaż</a>';
		$testy->tb[$k]['pytania']='<a class="but" href="?acc=9&amp;acc3=edp&amp;idt='.$k.'">Edytuj</a>';
		$testy->tb[$k]['typ']= ($testy->tb[$k]['typ'] ? 'A' : 'T');
	}
	CValid::$acc3='tsv';
	CValid::$acc='9';
	CHtml::sh_tab($testy,array('kom'=>'','bDel'=>1,'lDel'=>0,'bAdd'=>0,'paramId'=>$kid));
	return true;
}
?>
