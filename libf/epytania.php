<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
require_once('epyte.php');

function sh_pytkat() {
global $user;
	CHtml::addRes('scripts','tbdelrow.js');
	$kategorie=new kategorie(null,$user->uid);
	if (CValid::$acc3=='del') {//usunięcie kategorii
		$kid=CValid::getCnt('wid');
		$ret=CDBase::dbIsVal('SELECT kat FROM kategorie WHERE kid = ? AND uid = ?',array(array($kid,$user->uid)));
		if (!$ret) return CMsg::eadd('Próbujesz usunąć nie swoją kategorię!');
		CDBase::dbSetq('DELETE FROM kategorie WHERE kid = ?',array(array($kid)));
		CDBase::dbSetq('UPDATE testy SET kid=1 WHERE kid = ?',array(array($kid)));
		CDBase::dbSetq('UPDATE pytania SET kid=1 WHERE kid = ?',array(array($kid)));
		if (CDBase::dbExec()) CMsg::kadd('Usunięto kategorię: '.$ret['kat'].'.');
	} else if (CValid::$acc3=='tsv') {//zapisanie kategorii
		$kategorie->getTb2('SELECT kid,kat,przid FROM kategorie WHERE (uid='.$user->uid.' OR uid=1)');
		CHtml::sav_tab($kategorie,'Zapisywanie listy kategorii.');	
	} else if (CValid::$acc3=='psv') {//zapisanie pytań
	
	}
	$kategorie->getTb2('SELECT kategorie.kid,kat,kategorie.przid,przedm FROM kategorie,przedmioty WHERE kategorie.przid=przedmioty.przid AND (kategorie.uid='.$user->uid.' OR kategorie.uid=1)');
	$where=implode(' OR kid=',$kategorie->ids);
	$res=CDBase::dbQuery('SELECT kid,COUNT(pid) FROM pytania WHERE kid='.$where.' GROUP BY kid');
	foreach ($res as $tb) { $tbilepyt[$tb['kid']]=$tb['COUNT(pid)']; }
	//print_r($tbilepyt);
	$i=0;
	echo '<h3>Edycja pytań</h3>';
	echo '<table class="tab">';
	CHtml::tbHead(array('Nr','Przedmiot','Kategorie'));
	foreach ($kategorie->tb as $k => $v) {
		$clrow='tbrow'.($i%2);
		CHtml::tbRow(array(
			++$i,
			$v['przedm'],
			'<a href="?acc='.CValid::$acc.'&amp;acc2='.$v['kid'].'&amp;acc3=edp">'.$v['kat'].'</a> ('.(isset($tbilepyt[$v['kid']])?$tbilepyt[$v['kid']]:0).')'
			),array('tc'),$clrow
		);
	}
	echo '</table>';
	echo '<div style="margin-top: 3px;"><a href="?acc='.CValid::$acc.'&amp;acc3=shm" class="but">Pokaż media</a></div>';
	echo '<hr />';

	$przedmioty=CDBase::dbQuery('SELECT przid,przedm FROM przedmioty WHERE uid='.$user->uid.' OR uid=1',PDO::FETCH_KEY_PAIR);
	$kategorie->ksou['przid']=array($przedmioty,0,false);
	CValid::$acc3='tsv';
	CHtml::sh_tab($kategorie,array('kom'=>'Lista kategorii','bDel'=>0,'lDel'=>1,'bAdd'=>1));
	CMsg::kadd('
	Kategorie służą grupowaniu i porządkowaniu pytań, testów i ankiet, stanowią uzupełnienie podziału na przedmioty. 
	Można stosować te same kategorie do pytań, testów i ankiet. 
	Testy i ankiety mogą zawierać pytania z różnych kategorii.
	Kategorią wbudowaną jest "brak kategorii". 
	Minimalna dlugość kategorii to 3 znaki. Maksymalna 20.<br /> 
	Można zmieniać nazwy kategorii, nowa nazwa określać będzie dotychczas przypisane do starej nazwy testy, pytania i ankiety.');
}
//--- pokaż listę mediów ---
function sh_media() {
global $user;
include_cl('media');
CHtml::addRes('scripts','tbdelrow.js');	
	$media=new media(null,null,$user->uid);
	//'SELECT media.mid,media.fname,media.opis,media.pub,mp.pid,pytania.pyt FROM media,mp,pytania WHERE media.mid=mp.mid AND mp.pid=pytania.pid AND '
	$media->getTb2('SELECT * FROM media WHERE ');
	foreach ($media->tb as $mid => $v) {
		$pids=CDBase::dbQuery('SELECT pytania.pid,pytania.pyt FROM pytania,mp WHERE pytania.pid=mp.pid AND mp.mid='.$mid);
		if ($pids) $v=array_merge($v,current($pids));
		if ($media->set_img($v['fname'])) {//plik istnieje
			if ($media->mkthumb()) $media->tb[$mid]['fname']='<img src="'.$media->imp.'" alt="'.$v['opis'].'" />';
			if (isset($v['pid'])) $media->tb[$mid]['pids']=$v['pyt'].'&nbsp;<a href="?acc='.CValid::$acc.'&amp;mid='.$v['mid'].'&amp;idp='.$v['pid'].'">&raquo;&raquo;&raquo;</a>';
			else $media->tb[$mid]['pids']='Nieprzypisane';
		} else {
			$media->tb[$mid]['fname']='Brak pliku!';
			$media->tb[$mid]['pids']='';
		}
	}
	$tbtmp=$media->tb;
	CValid::$acc3 = 'svm';
	CHtml::sh_tab($media,array('kom'=>'Przegląd mediów','bDel'=>1,'lDel'=>0,'bAdd'=>0));
}
//--- zapisz listę mediów ---
function sav_media() {
global $user;
include_cl('media');
	$media=new media(null,null,$user->uid);
	$media->getTb2();
	CHtml::sav_tab($media,'Zapisywanie mediów.');
}
//------------------
//print_r($_POST);
//--- sterowanie ---
switch (CValid::$acc3) {
	case 'psv': //zapisz pytania
		sav_Pyt();
		ed_Pyt();
	break;
	case 'edp': //edytuj pytania
		ed_Pyt();
	break;
	case 'shm': // pokaż listę mediów
		sh_media();
	break;
	case 'svm':
		sav_media();
	break;
	case 'tsv': //zapisz kategorie
		sh_pytkat();
	break;
	default:
		sh_pytkat(); //pokaż tabelę pytania - kategorie
		//sh_kat(); //pokaż tabelę przedmiot - kategorie
}
?>