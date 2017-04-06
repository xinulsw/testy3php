	function getMaxPktB($tid=null) {
		$maxpkt=0;
		if (is_null($tid)) $tid=$this->tid;
		if (isset($this->pyt->tb)) {
			$tb=$this->pyt->tb;
		} else {
			$tb=CDBase::dbQuery('SELECT typ,odp,mpkt FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$tid);
		}

		foreach ($tb as $v) {
			switch ($v['typ']) {
				case 0://text
					if (!is_array($v['odp'])) $v['odp']=explode($this->e,$v['odp']);
					$maxpkt+=count($v['odp']);
				break;
				case 1://radio,checkbox
				case 2:
					$maxpkt++;
				break;
				case 3://pytania otwarte;w polu 'odp' zapisana jest ilość pól text
				case 4:
					if (is_array($v['odp'])) $v['odp']=$v['odp'][0];
					$maxpkt+=$v['odp'];
				break;
				case 5:
					if (!is_array($v['odp'])) $v['odp']=explode($this->e,$v['odp']);
					$maxpkt+=count($v['odp']);
				break;
			}
		}
		return $maxpkt;
	}

	function getMaxPkt($tid=null,$tbpids=array()) {
		if (is_null($tid)) $tid=$this->tid;
		if (!empty($tbpids)) $war=' AND (pytania.pid='.implode(' OR pytania.pid=',$tbpids).')'; else $war='';
		$sum=CDBase::dbQuery('SELECT SUM(mpkt) FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$tid.$war,PDO::FETCH_COLUMN,true);
		//if (empty($sum) || $sum==0) return $this->getMaxPktb($tid);
		if (empty($sum)) echo 'Tid: '.$tid.'<hr />';
		return $sum;
	}

	function getOceny($tbwyn) {//tablica $testy $wid = array($tid,$ileok)
		$tbw=array('srednia'=>0);
		if (empty($tbwyn)) return $tbw;
		$tbmpkt=array();//tablica max pkt z testów
		$srednia=array(0);
		foreach ($tbwyn as $wid => $tb) {
			$tid=$tb['tid'];
			$tbw[$wid]['typ']=$this->tb[$tid]['typ'];
			if ($this->tb[$tid]['typ']) continue;//jeżel ankieta
			if (!array_key_exists($tid,$tbmpkt)) $tbmpkt[$tid]=$this->getMaxPkt($tid);
			$tbw[$wid]['max']=$tbmpkt[$tid];
			$pro=($tb['ileok']/$tbmpkt[$tid]);
			$tbw[$wid]['stopien']=fOcena($pro,array('oceny'=>$this->tb[$tid]['oceny'],'progi'=>$this->tb[$tid]['progi']));
			$tbw[$wid]['pro']=round($pro*100,2);
			$srednia[]=$tbw[$wid]['stopien'];
		}
		$tbw['srednia']=round(array_sum($srednia)/count($srednia),2);//średnia
		return $tbw;
	}