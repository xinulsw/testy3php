<?php if(!defined('IN_CT')) { die('You cannot load this page directly.'); }
include_cl(array('wyniki','testy'));
//--------------------------------------------------------------------------------
if (!$user->uid) {
	echo('<p>Wyniki użytkowników niezalogowanych nie są zapisywane.</p>');
	echo('<p><a href="?acc=12">Załóż konto.</a></p>');
	return;
}
if (CValid::$acc3 == 'wsd') {
	include_cl('pytania');
	CHtml::sh_wdetal($user->uid);	
} else {
	CHtml::sh_wyniki($user->uid); //pokaż listę testów rozwiązanych przez ucznia
}
?>