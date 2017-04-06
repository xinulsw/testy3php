<?php
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
//ini_set('session.save_path','sesje');
//session_save_path('/home/smastera/public_html/testy3.net/sesje');
ini_set('session.gc_probability', 1);
ini_set('session.cookie_httponly', true);
//ini_set('session.cookie_secure', true);
function get_execution_time() {
	static $microtime_start = null;
	if($microtime_start === null) {
		$microtime_start = microtime(true);
		return 0.0;
	}
	return microtime(true) - $microtime_start;
}
get_execution_time();
date_default_timezone_set('Europe/Warsaw');
setlocale(LC_ALL,"pl_PL.UTF-8"); //ustawienie locale dla Linuksa
//--- konfiguracja
$SITEURL = 'http://localhost/~lo1sand/testy3php/';
$CTLIB = 'libf';
$DBNAME = 'db.sq3';
$CTDEBUG = false;
if ($CTDEBUG) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log','errorlog.txt');
}
define('CT_URL',$SITEURL);
define('CT_LIB', $CTLIB.'/');
define('CT_CTDEBUG',$CTDEBUG);
define('IN_CT', TRUE);
define('CT_ROOT', '');
define('DBNAME', $DBNAME);
define('CT_DANE', CT_ROOT. 'dane/');
define('CT_BASE', CT_DANE. 'baza/');
define('CT_BACKUP', CT_DANE. 'backup/');
define('CT_CACHE', CT_DANE. 'cache/');
define('CT_OTHER', CT_DANE. 'inne/');
define('CT_LOGS', CT_OTHER. 'logs/');
define('CT_IMGS', CT_DANE. 'imgs/');
define('CT_THMB', CT_DANE. 'imgs/thumbs/');
define('CT_THEME', CT_ROOT. 'theme/');
define('CT_CLASSES', CT_LIB. 'classes/');
define('CTDEBUG', $CTDEBUG);
$dbfile = CT_BASE . DBNAME;
if (!file_exists($dbfile)) require_once(CT_LIB.'setup.php');
//--- rozne ---
define('CT_SEP','#');
define('CT_ALT','^+^');
$ppage=10;//ile pytań na stronie
//--- koniec konfiguracji ---
//--- including required classes files
require_once(CT_CLASSES.'code.php');
require_once(CT_CLASSES.'baza.php');
CDBase::getHandler();
include_cl(array('user','menu'));
//--- instatiate objects
$sesja = new CSession();
$user = new user();
// --------------------------
CMenu::getPage($user->kto,CValid::getAcc());
if (CValid::$acc == 24) { include(CT_LIB . 'getres.php'); } //zapytania Ajax
else if (isset($_POST['op'])) { include(CT_LIB . 'testy.php'); } //rozwiązywanie testu
else {
	if (CTDEBUG) {
		CMsg::kadd('Plik: '.CMenu::$plik);
		CMsg::kadd($sesja);
	}
	//$user->dane_p();
	include(CT_THEME . 'template.php');
}
?>
