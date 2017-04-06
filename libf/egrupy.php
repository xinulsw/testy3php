<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
// --- Grupy ---
function sh_grupy() {//lista grup danego nauczyciela
global $user;
	CHtml::addRes('scripts','tbdelrow.js');
	include_cl(array('grupy'));
	$grupy=new grupy(null,$user->uid);

	if (CValid::$acc3=='gsv') {//zapisywanie/aktualizacja grup
		$grupy->getTb2('SELECT gid,grupa,opis,tok,ilu FROM grupy WHERE ');
		CHtml::sav_tab($grupy,'Zapisywanie listy grup!');
	} else if (CValid::$acc3=='del') {//usuń grupę
		$gid = (int)$_GET['wid'];
		if (!CValid::vInt($wid)) CMsg::eadd('Błędne dane!');
		$grupy->delGrupa($gid);
	}
	$grupy->getTb2('SELECT gid,grupa,opis,tok,ilu FROM grupy WHERE ');
	CValid::$acc3='gsv';
	CHtml::sh_tab($grupy,array('kom'=>'Lista grup','bDel'=>0,'lDel'=>1,'bAdd'=>1));
	CMsg::kadd('<p>Kliknij numer porządkowy grupy, aby wyświetlić listę członków grupy.<br />Nazwy grup muszą być unikalne.</p>');
}

function sh_grls() {//lista uczniów danej grupy
global $user;
	include_cl(array('grupy'));
	if (isset($_SESSION['gid'])) {
		$gid=$_SESSION['gid'];
		unset($_SESSION['gid']);
	} else $gid=CValid::$acc2;
	if (!CValid::vInt($gid,1)) return CMsg::eadd();
	CHtml::addRes('scripts','sortable.js');
	$grupa=CDBase::dbIsVal('SELECT grupa,opis FROM grupy WHERE gid = ?',array(array($gid)));
	if (!empty($grupa)) {
		$info='<p>Grupa: <strong>'.$grupa['grupa'].'</strong> - '.$grupa['opis'].'.</p>';
	} else {
		echo '<h3>Edycja grup</h3><p>Brak informacji o grupie.<br />Po dodanu nowej grupy, należy zdefiniować nauczane przedmioty.</p>';
		return;
	}
	echo '<h3>Edycja grup</h3>'.$info;
	$tbgr=array();
	$tbgr=CDBase::dbQuery('SELECT users.uid,users.login,users.nazwisko,users.email,uinfo.llog,gunp.nrp,gunp.pgr
				FROM users,uinfo,gunp
				WHERE users.uid=uinfo.uid AND users.uid=gunp.uid AND gunp.gid='.$gid);
	if (empty($tbgr)) return CMsg::eadd('Brak uczniów w grupie.');
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="grEdit" id="grEdit">
			<input type="hidden" name="acc3" value="grs" />';
	echo '<table class="tab sortable" id="tbsort">';
	CHtml::tbHead(array('Lp.','Nrp.','Pgr','Login','Nazwisko','Email','Dostęp','Reset','Usuń'),array('tc unsortable','tc','','','','','','unsortable','unsortable'));
	$i=0;
	foreach ($tbgr as $k => $v) {
		CHtml::tbRow(array(
				'<input type="hidden" name="tk[]" value="'.$v['uid'].'" />
				<input class="mod" type="hidden" name="mod[]" id="mod'.$v['uid'].'" />'.++$i,
				'<input class="itext" type="text" name="tadd0[]" value="'.$v['nrp'].'" size="3" />',
				'<input class="itext" type="text" name="tadd1[]" value="'.$v['pgr'].'" size="3" />',
				'<input type="hidden" name="tadd2[]" value="'.$v['login'].'" />'.$v['login'],
				$v['nazwisko'],
				'<input type="hidden" name="tadd3[]" value="'.$v['email'].'" />'.$v['email'],
				fCzdost($v['llog']),
				'<input type="checkbox" name="tres[]" value="'.$v['uid'].'" />',
				'<input type="checkbox" name="tdel[]" value="'.$v['uid'].'" />'
				),array('tc nob','tc','','','','tc','tc','tc')
		);
	}
	echo '</table>';
	echo '<input type="hidden" name="idg" value="'.$gid.'" />';
	echo '<input class="cb but flr" type="submit" value="Zapisz" /></form>';
}

function sav_grls() {
global $user;
echo ($user->uid);
	$tk=CValid::getCnt('tk','p');//tabela idow
	$mod=CValid::getCnt('mod','p');//tabela zmian
	$tadd=CValid::getCnt('tadd','p'); if (!CValid::vIntA($tadd,1)) return CMsg::eadd('Błąd: niewłaściwy numer ucznia.');
	$tdel=CValid::getCnt('tdel','p'); //tabela uid-ów do usunięcia
	//print_r($tdel);
	$_SESSION['gid']=CValid::getCnt('idg','p');
	$tb=array();
	foreach ($tk as $k => $user->uid) {
		if (in_array($user->uid,$mod))
		$tb[]=array($tadd[$k],$user->uid);//numer w dzienniku
	}
	if (!empty($tb)) {
		CDBase::dbSetq('UPDATE gunp SET nrp = ?',$tb);
		CDBase::dbExec();
		//$db->b_update('gunp','nrp','uid',$tb);
	}
	if (!empty($tdel)) {
		foreach ($tdel as $uid) {
			;
			//CDBase::dbSetq('DELETE FROM ');
		}
	}
	$act=CValid::getCnt('act','p');
	$tres=CValid::getCnt('tres','p');//tabele id-ow ktore nalezy zresetować
	$user->actres($act,$tres);
}

//--- sterowanie ---
switch (CValid::$acc3) {
	case 'gsv': //zapisz tabelę grup
	case 'del': //usuwanie grupy
		sh_grupy();
	break;
	case 'grl': //pokaż listę uczniów danej grupy
		sh_grls();
	break;
	case 'grs': //zapisz listę uczniów danej grupy
		sav_grls();
		sh_grls();
	break;
	default:
		sh_grupy();
}
?>