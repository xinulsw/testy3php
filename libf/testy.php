<?php if(!defined('IN_CT')) { die('You cannot load this page directly.'); }
include_once('tean.php');

if (CValid::$acc2 == 'del') {
	ftclean(); //wyczyść dane z sesji dotyczące rozwiązanego testu
}

if (CValid::$acc2 == 'oct') { //ocena testu
	ocen($user);
} else if (isset($_POST['tk'])) { //zapisanie odpowiedzi na pytanie
	fodp($user);
} else if (isset($_GET['idt'])) { //inicjalizacja testu
	mk_test($user);
} else {
	sh_testy($user);
}
?>