<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }

$tabn=array('Wybierz tabelę...','users','uinfo','przedmioty',
'kategorie','utok','grupy','gunp','gnp',
'strony','pytania','media','mp','testy','testp','testg','testu','wyniki',
'wynpyt','varia');

function sh_sqlfrm($tabele) {
	CHtml::addJS('<script type="text/javascript"><!--
		$(document).ready(function(){
			$("#sqlfrm").submit(function(){
				if ($("#tbn").prop("selectedIndex")==0) {
					alert("Wybierz tabelę!");
					return false;
				}
				return true;
			});
		});
	//--></script>');
	echo '<h3>Edycja bazy</h3>';
	echo '<p>Wprowadź polecenie SQL:</p>';
	echo '<form action="?acc='.CValid::$acc.'" method="post" name="sqlfrm" id="sqlfrm">
			<input type="hidden" name="acc3" value="'.CValid::$acc3.'" />';
	CHtml::mk_sel('tbn','tbn',array($tabele,null,1)).'<br />';
	echo '<textarea name="sql" rows="5" cols="50"></textarea><br />';
	echo '<input class="cb but flr" type="submit" value="Zapisz" /></form>';
	echo '<p><a href="?acc='.CValid::$acc.'">Dump all database</a></p>';
}

function mk_sql($tbn,$ret) {
	//global $code;
	$q=array();
	$kod='';
	foreach ($ret as $row) {
		$q[]='INSERT INTO '.$tbn.' VALUES ('.implode(',',$row).')';
	}
	foreach ($q as $v) $kod.=$v.';<br />';
	echo $kod;
}

function dbVPrep(&$tbv) {
	foreach ($tbv as $k => $v) {
		if ($v==='NULL') $tbv[$k]=NULL;
			else if (!is_numeric($v)) $tbv[$k]='\''.$v.'\'';
	}
}

function dump_all($tabele) {
unset($tabele[0]);
$tabele=array_values($tabele);
	$tbq=array();
	foreach ($tabele as $tbn) {
		$q='SELECT * FROM '.$tbn;
	if ($tb=CDBase::dbQuery($q)) {
		foreach ($tb as $v) {
			if ($tbn=='pytania') {
				clrtxt($v['pyt']);
				if ($v['typ']==0) {
					$v['odp']=explode('#',$v['odp']);
					array_walk($v['odp'],'clrtxt');
					$v['odp']=implode('#',$v['odp']);
				}
				$v['odpt']=explode('#',$v['odpt']);
				array_walk($v['odpt'],'clrtxt');
				$v['odpt']=implode('#',$v['odpt']);
			}
			dbVPrep($v);
			
			$tbq[]='INSERT INTO '.$tbn.' VALUES ('.implode(',',$v).')';
		}
	}
	}
	foreach ($tbq as $v) CMsg::kadd($v.';<br />');
	CMsg::kadd('Zrzut wykonano.');
}

function exe_sql() {
//global $code;
	$sql=CValid::getCnt('sql','p');
	$tbn=CValid::getCnt('tbn','p');//nazwa tabeli
	if (!CValid::vStr($sql,1)) CMsg::eadd();
	//$sql=strtolower($sql);
	if (get_magic_quotes_gpc()) $sql=stripslashes($sql);
	echo $sql.'<br />';
	if (stripos($sql,'select')===false) {//upd,del,ins
		CMsg::kadd('Not select!');
		if (CDBase::dbExecStr($sql)) CMsg::kadd('Zapytanie wykonano.');
	} else {
		$ret=CDBase::dbQuery($sql);
		if (!empty($ret)) {
			mk_sql($tbn,$ret);
		}
		CMsg::kadd('Wykonano zapytanie: '.$sql);
	}
}
//echo sha1('zawitajdoulanowa').' '.strlen(sha1('zawitajdoulanowa'));
if (isset($_POST['sql'])) exe_sql($tabn);
sh_sqlfrm($tabn);

?>