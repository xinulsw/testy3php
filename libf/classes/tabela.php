<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }

class tabela extends CBase {
	var $explo=array();//tablica pól, które należy dodatkowo "explodować"
	var $tbrem=array(); //lista tablic zależnych, z których należy usunąć wpisy
	var $tbupd=array(); //lista tablic zależnych które trzeba zaktualizować, format: 'tbname'=>'wadrtość aktualizacji'
	var $tupd=array();
	var $tdel=array();
	var $pins=array();//tablica parametrów insert
	var $qstr='';//treść zapytania, zazwyczaj SELECT
	var $tbwar=array(); //tablica wartości where
	var $order=NULL; //ciąg order by
	var $ids=array();//tablica id-ów
	var $tk=array(); //tablica id-ów zapisywanych wpisów
	var $ilew=0;//ile wpisów
	var $e='#'; //domyślny separator pól zapisywanych tablic
	var $uni=true;//klucze unikatowe 
	var $rowid=array(); //tablica id nowych wpisów
	var $tbt=array();//typy wartości w tabeli

	function __construct($keys=null,$values=null) {
		$this->dane = array_combine($keys,$values);
		$this->re=true;
		$this->mode=PDO::FETCH_ASSOC;
	}

	function getTb2($qstr,$tbp=null) {
		if (is_null($tbp)) {
			$this->tb=CDBase::dbQuery($qstr,$this->mode);
		} else {
			CDBase::dbSetq($qstr,$tbp,$tbt);
			CDBase::dbExec($this->tb);
		}
		if (empty($this->tb)) { $this->tb=array(); $this->ilew=0; return false; }
		$this->ilew=count($this->tb);
		if ($this->re) $this->reInd2();
	}

	function reInd2() {
		if ($this->ilew==1) {
			$this->sget(current($this->tb));
			foreach ($this->explo as $k) {
				if (isset($this->dane[$k]))
					$this->dane[$k]=explode($this->e,$this->dane[$k]);
			}
		}

		$tb=array();
		foreach ($this->tb as $k => $v) {
			$this->ids[]=$v[$this->idn];
			foreach ($this->explo as $key) {
				if (array_key_exists($key,$v)) $v[$key] = explode($this->e,$v[$key]);
			}
			$tb[$v[$this->idn]]=$v;
		}
		$this->tb=$tb;
	}

	function setWar($tbv) {
		$war=array();
		foreach ($tbv as $v) {
			if (is_null($this->$v)) continue;
			if (is_array($this->$v)) {
				$war[]=' ('.$v.'='.implode(' OR '.$v.'=',$this->db_prep($this->$v)).')';
			} else {
				if (strlen($this->$v)==0) continue;
				$war[]=$this->tbn.'.'.$v.'='.$this->db_prep($this->$v);
			} 
		}
		if (empty($war)) $ret = '';
		else $ret = implode(' AND ',$war);
		if (!is_null($this->order)) $ret .= ' '.$this->order;
		return $ret; 
	}

	function getDane($tbn=null,$k1=null,$k2=null) {//z tablicy tablic zwraca jedną tablicę z wybranymi elementami
		if (is_null($k1)) $k1=$this->idn;
		if (is_null($k2)) $k2=$this->vn;
		$tb=array();
		foreach ($this->tb as $v) {
			if (is_array($k2)) {
				foreach ($k2 as $ind)
					$tb[$v[$k1]][]=$v[$ind];//$tbv=array();$tbv[]=$v[$ind];$tb[$v[$k1]]=$tbv;
			} else {
				$tb[$v[$k1]]=$v[$k2];
			}
		}
		if (is_null($tbn)) return $tb;
		else if ($tbn===1) return current($tb);//zwracamy tylko jedną wartość
		else $this->$tbn=$tb;
		return true;
	}

	function savTb($tk=array(),$tadd,$mod=array()) {//zapisz,usuń,zaktualizuj wpisy
		//if (empty($tadd)) return;
		if (!is_array($tk)) $tk=array();
		if (!is_array($mod)) $mod=array();

		$this->rowid=array();

		if ($this->one) {//zabezpieczenie pierwszego wpisu
			unset($this->ids[array_search(1,$this->ids)]);
			unset($tk[$k=array_search(1,$tk)]);
			unset($tadd[$k]);
		}
		foreach ($tadd as $k => $v) {
			//$v=$this->db->escape($v);
			if (array_key_exists($k,$tk)) {
				$wid=$tk[$k];
				$this->tk[$k]=$wid;
				if (in_array($wid,$mod)) {
					if ($this->shift) array_shift($v);
					$v[]=$wid;
					$this->tupd[]=$v;//zapisz wartości do zaktualizowania w tablicy
					//print_r($this->tupd);echo '<hr />';
				}
			} else if (!$rid=$this->is_wpis($v)) {//jeżeli wpisu nie ma w bazie
				$this->tk[$k]=0;
				$this->rowid[]=$k;
				$this->pins[]=$this->db_prep($v);
			} else { $this->tk[]=$rid; $tk[]=$rid; }
		}
		if (is_null($this->norem)) {
			$this->tdel=m_array_diff($this->ids,$tk);
			foreach ($this->tdel as $k => $v) $this->tdel[$k]=array($v);
		}
		$this->tb=$tadd;
		$this->savDb();
	}

	function savDb() {
		//print_r($this->pins);return;
		if (count($this->pins)>0) {
			$this->insert();
			$this->success=CDBase::dbExec();
			if (count($this->rowid)) {
				$this->rowid=array_combine($this->rowid,CDBase::dbGet('tbr'));
				foreach ($this->rowid as $k => $v) {
					$this->tb[$k][0]=$v;
					$this->tk[$k]=$v;
				}
			}
		}

		if (count($this->tupd)>0) $this->update();
		if (count($this->tdel)>0) $this->delete();
		if (count($this->tupd)>0 || count($this->tdel)>0) {
			$this->success=CDBase::dbExec();
		}
	}

	function insert() {
		$qmark=array_fill(0,count($this->keys),'?');
		CDBase::dbSetq('INSERT INTO '.$this->tbn.'('.implode(',',$this->keys).') VALUES ('.implode(',',$qmark).')',$this->pins);
	}

	function update() {
		$keys=$this->keys;
		if ($this->shift) {//jeżeli pierwszym el. tablic z $tadd jest idn, trzeba go usunąć
			 array_shift($keys);
		}
		CDBase::dbSetq('UPDATE '.$this->tbn.' SET '.implode(' = ?, ',$keys).' = ? WHERE '.$this->idn.' = ?',$this->tupd);		
	}

	function delete($war=null) {
		if (empty($war)) $war=$this->idn.' = ?';
		//echo 'DELETE FROM '.$this->tbn.' WHERE '.$war; return;
		CDBase::dbSetq('DELETE FROM '.$this->tbn.' WHERE '.$war,$this->tdel);
		foreach ($this->tbrem as $tbn) {
			CDBase::dbSetq('DELETE FROM '.$tbn.' WHERE '.$war,$this->tdel);
		}
		foreach ($this->tbupd as $tbn) {
			foreach ($this->tdel as $v) $tb[]=array($this->dv,$v[0]);
			CDBase::dbSetq('UPDATE '.$tbn.' SET '.$war.' WHERE '.$war,$tb);
		}
	}

	function db_prep($v) {
		$isa=is_array($v);
		if (!$isa) $v=array($v);
		foreach ($v as $k => $v2) {
				if ($v2==='NULL') $v[$k]=NULL;
				//else if (!is_numeric($v2)) $v[$k]='\''.$v2.'\'';
		}
		if (!$isa) return $v[0];
		else return $v;
	}

	function is_wpis($war) {
		$ret=CDBase::dbQuery('SELECT '.$this->idn.' FROM '.$this->tbn.' WHERE '.$war);
		if (!empty($ret)) {
			return $ret[0][$this->idn];
		}
		return false;
	}

	function explo(&$tab,$key) {
		if (array_key_exists($key, $tab)) $tab[$key] = explode($this->e,$tab[$key]);
	}

	function mktb($v) {
		if (is_null($v) || is_array($v)) return $v;
		return array($v);
	}
}
?>
