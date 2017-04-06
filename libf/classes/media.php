<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
include_cl(array('tabela'));

class media extends tabela {
	var $keys=array('mid','uid','typ','fname','opis','pub');
	var $kshow=array('fname','opis','pub','pids');
	var $ktype=array('img','text','checkbox','href');
	var $ksou=array();
	var $ktbh=array('Obrazek','Opis','Publiczny','Pytanie');
	var $ksize=array('','30','','10');
	var $strerr=array('','Za duży plik! ','Za duży plik! ','Niepełny transfer pliku! ','Nie dodano zdjeć. ','Brak katalogu tymczasowego! ','Plik niezapisany! ');
	var $imd=CT_IMGS; //images dir
	var $thd=CT_THMB; //thumbs dir

	function __construct($mid=null,$pid=null,$uid=null,$typ=null) {
		$keys=array('tbn','idn','vn','tbid','mid','pid','uid','typ');
		$values=array('media','mid','fname','medEdit',$mid,$pid,$uid,$typ);
		parent::__construct($keys,$values);
		$this->shift=true;
		$this->tbrem=array('mp');
		$this->q=85;			//setting jpg quality
		$this->mx_s=500000;		//setting maks size of uploded image
		$this->mx_xs=600;		//setting maks width of storage image
		$this->mx_ys=400;		//setting maks hight of storage image
		$this->mx_xd=400;		//setting maks width of displayed image
		$this->mx_yd=300;		//setting maks hight of displayed image
		$this->mx_xt=100;		//thumb width
		$this->mx_yt=100;		//thumb height
		$this->pub=0;
		$this->mp=new mp($mid,$pid);
	}

	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT * FROM media WHERE ';
		$qstr.=$this->setWar(array('mid','pid','uid','typ'));
		//echo $qstr; return;
		parent::getTb2($qstr,$tbp);
	}

	function savTb($tk=array(),$tadd,$mod=array()) {
		//print_r($tadd);echo '<hr />';return;
		foreach ($tadd as $k => $v) {
			$mid=$v[0];
			//mid,uid,typ,fname,opis,pub
			$tadd[$k]=array($mid,$this->uid,$this->tb[$mid]['typ'],$v[1],$v[1],$this->tb[$mid]['pub']);
		}
		//--- zapamiętaj nazwy plików do usunięcia ---
		$tbdel=m_array_diff($this->ids,$tk);
		foreach ($tbdel as $mid) {
			$tdel[]=$this->tb[$mid]['fname'];
			$tdelth[]='th_'.$this->tb[$mid]['fname'];	
		}
		//---
		parent::savTb($tk,$tadd,$mod);
		delplik($tdel,$this->imd);
		delplik($tdelth,$this->thd);
	}

	function is_wpis($v) {
		$war=$this->vn.'=\''.$v[3].'\' LIMIT 1';
		return parent::is_wpis($war);
	}

	function set_img($imn) {//image file name
		$this->imp=$this->imd.$imn;
		if (!file_exists($this->imp)) return false;
		$this->dane=array_merge($this->dane,pathinfo($this->imp));
		list($src_x, $src_y, $type, $sizes) = GetImageSize($this->imp);
		$this->src_x=$src_x;
		$this->im_x=$src_x;
		$this->src_y=$src_y;
		$this->im_y=$src_y;
		$this->type=$type;
		$this->sizes=$sizes;
		return true;
	}

	function resize() {
		if ($this->src_x>$this->mx_x || $this->src_y>$this->mx_y) {
			$f = min( $this->mx_x / $this->src_x, $this->mx_y / $this->src_y, 1 );
			$this->im_x=round($f * $this->src_x);
			$this->im_y=round($f * $this->src_y);
			$this->resample();
			return true;
		}
		return false;
	}

	function resample() {
		switch ($this->type) {
			case 1: $this->src=imagecreatefromgif($this->imp); break;
			case 2: $this->src=imagecreatefromjpeg($this->imp); break;
			case 3: $this->src=imagecreatefrompng($this->imp); break;
			default:
				CMsg::eadd('Nieobsługiwany format obrazka.');
				return false;
		}
		$this->dane['im'] = imagecreatetruecolor($this->im_x,$this->im_y);
		imagecopyresampled($this->dane['im'], $this->src, 0, 0, 0, 0, $this->im_x, $this->im_y, $this->src_x, $this->src_y);
	}

	function mkimg() {
		switch ($this->type) {			case 1:	$ret = imagegif($this->im,$this->toname); break;
			case 2:	$ret = imagejpeg($this->im,$this->toname,$this->q); break;			case 3:	$ret = imagepng($this->im,$this->toname); break;
			default: $ret = false;
		}
		if (!$ret) return CMsg::eadd('Nie można utworzyć/pokazać obrazka!');
		$this->imp=$this->toname;
		return true;
	}

	function mkthumb($op=true) {
		$this->toname=$this->thd.'th_'.$this->basename;
		if (file_exists($this->toname)) { $this->imp=$this->toname; return true; }
		$this->mx_x=$this->mx_xt;
		$this->mx_y=$this->mx_yt;
		if ($this->resize()) return $this->mkimg();
	}
	
	function store($toname=null) {
		if (!is_null($toname)) $this->toname=$toname;
		else $this->toname=$this->imp;
		$this->mx_x=$this->mx_xs;
		$this->mx_y=$this->mx_ys;
		if ($this->resize()) return $this->mkimg();
	}

	function show() {
		switch ($this->type) {
			case 1: header("Content-Type: image/gif"); break;
			case 2: header("Content-Type: image/jpeg"); break;
			case 3: header("Content-Type: image/png"); break;
			default:
				header("Content-Type: image/jpeg"); break;
		}
		return readfile($this->imp);
	}
//--- processing and adding images to database
	function addImgs($tbimgs) {
		if (empty($tbimgs)) return;
		$tbpids=$tadd=array();
		foreach ($tbimgs as $pid => $v) {
			foreach ($v as $k => $imn) {
				$this->set_img($imn);
				$this->store();
				$tadd[]=array(NULL , $this->uid , $this->type , $this->basename , $this->basename , $this->pub );
				$tbpids[]=$pid;
			}
		}
		parent::savTb(array(),$tadd,array());
		$tb=@array_combine($this->rowid,$tbpids);
//print_r($tb); echo '<br /><br />';
		if (!$tb) return CMsg::eadd('Błąd przypisania mid-pid.');
		foreach ($tb as $mid => $pid) {
			$tbpins[]=array($mid,$pid);
		}
		CDBase::dbSetq('INSERT INTO mp (mid,pid) VALUES (?,?)',$tbpins);
		return CDBase::dbExec();
	}
//--- zapisz obrazki do pytań - generowanie nazw plików obrazków
	function get_name($imn,$ext) {
		$k=-1;
		do {
			$tmp=$imn.'_'.++$k;
		} while (file_exists($this->imd.$tmp.$ext));
		return $tmp.$ext;
	}
//--- zapisz obrazki do pytań
	function savImgs($tbpids) {//zapisz obrazki w bazie
		//print_r($_FILES); return;
		$tbimgs=array();//tabela przechowa nazwy uploadowanych obrazków
		foreach ($_FILES as $k => $v) {
			foreach ($v['name'] as $k2 => $name) {
				if (empty($name)) continue;
				if ($v['error'][$k2]===UPLOAD_ERR_OK && $v['size'][$k2]<=$this->mx_s) {
					$typ=exif_imagetype($v['tmp_name'][$k2]);
					if ($typ && $typ<4) {
						//generowanie nazwy
						if (strpos($k,'file')===false) {//obrazki do podpunktów
							$nrpyt=substr($k,1);
							$pid=$tbpids[$nrpyt-1];
							$imn=$pid.'_t';
						} else {//obrazki do całego pytania
							$nrpyt=substr($k,4); //nrpyt
							$pid=$tbpids[$nrpyt-1]; //pid
							$imn=$pid.'_'.substr($name, 0, strrpos($name,'.'));
						}
						$imn=$this->get_name($imn,image_type_to_extension($typ));
						//____ koniec generowania nazwy						
						if (@move_uploaded_file($v['tmp_name'][$k2],$this->imd.$imn)) {
							@chmod($this->imd.$imn,0664);
							$tbimgs[$pid][]=$imn;
						} else CMsg::eadd('Błąd przenoszenia pliku.');
					} else CMsg::eadd('Nieobsługiwany typ obrazka.');
				} else CMsg::eadd('Błąd transferu lub zbyt duży rozmiar obrazka.');
			}//end second foreach
		}//end first foreach
		if (empty($tbimgs)) return CMsg::eadd('Błąd dodawania obrazków!');
		$this->addImgs($tbimgs);
	}
//--- zapisz obrazki do podpunktów a,b,c...
	function savImgsP($tbpids) {
		//print_r($_FILES); return;
		$tbimgs=array();
		foreach ($_FILES as $k => $v) {
			if (strpos($k,'file')===false) {
				$nrpyt=substr($k,1,strpos($k,'_')-1);
				$pid=$tbpids[$nrpyt-1]; //pid
				if (empty($v['name'])) continue;
				if ($v['error']==UPLOAD_ERR_OK && $v['size']<=$this->mx_s) {
					$typ=exif_imagetype($v['tmp_name']);
					if ($typ && $typ<4) {
						$imn=$pid.'_t';
						$imn=$this->get_name($imn,image_type_to_extension($typ));
						if (@move_uploaded_file($v['tmp_name'],$this->imd.$imn)) {
							@chmod($this->imd.$imn,0664);
							$tbimgs[$pid][]=$imn;
						} else CMsg::eadd('Błąd przenoszenia pliku.');
					} else CMsg::eadd('Nieobsługiwany typ obrazka.');
				} else CMsg::eadd('Błąd transferu lub zbyt duży rozmiar obrazka.');
			}
		}
		if (empty($tbimgs)) return CMsg::eadd('Błąd dodawania obrazków!');
		$this->addImgs($tbimgs);
		//$tbupd=array();
		//foreach ($tbimgs as $pid => $v) $tbupd[]=array($pid,implode('#',$v));
		//CDBase::dbSetq('UPDATE pytania SET odpt=? WHERE pid=?',$tbupd);
		//return CDBase::dbExec();
	}
//--- usuń obrazki z pytań
	function remImgs($tbdelim) {
		$this->mp->remImgs($tbdelim);
	}
}

class mp extends tabela {
	var $keys=array('mid','pid');
	var $kshow=array('mid','pid');
	var $ktbh=array('Obrazek','Pytania');
	var $ksize=array('100','50');
	
	function __construct($mid=null,$pid=null) {
		$keys=array('tbn','idn','vn','tbid','mid','pid');
		$values=array('mp','rowid','OID','pmEdit',$mid,$pid);
		parent::__construct($keys,$values);
	}
	
	function getTb2($qstr=null,$tbp=null) {
		if (empty($qstr)) $qstr='SELECT rowid,* FROM mp WHERE';
		$qstr.=$this->setWar(array('mid','pid'));
		parent::getTb2($qstr,$tbp);
	}

	function is_wpis($v) {
		$war='mid='.$v[1].' AND pid='.$v[2].' LIMIT 1';
		return parent::is_wpis($war);
	}
	
	function remImgs($tbdelim) {
		if (empty($tbdelim)) return;
		foreach ($tbdelim as $pid => $tbmid) {
			foreach ($tbmid as $mid)
				$tbdel[]=array($mid,$pid);
		}
		CDBase::dbSetq('DELETE FROM '.$this->tbn.' WHERE mid = ? AND pid = ?',$tbdel);
		return CDBase::dbExec();
	}
}
?>
