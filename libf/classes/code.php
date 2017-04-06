<?php
//-- funkcje --
function include_cl($rclass,$path='classes/') {
	if (!is_array($rclass)) $rclass=array($rclass);
	foreach ($rclass as $v) {
		$klasa=CT_LIB.$path.$v.'.php';
		require_once($klasa);
	}
}
// Strip quotes
function strip_quotes($text)  { 
	$text = strip_tags($text); 
	$code_entities_match = array('"','\'','&quot;'); 
	$text = str_replace($code_entities_match, '', $text); 
	return trim($text); 
}

// Encode Quotes
function encode_quotes($text)  { 
	$text = strip_tags($text); 
	$text = htmlspecialchars($text, ENT_QUOTES); 
	return trim($text); 
}

// Cleaning $data
function cl($data){
	$data = stripslashes(strip_tags(html_entity_decode($data, ENT_QUOTES, 'UTF-8')));
	return $data;
}

//html decoding
function htmldecode($text) {
	return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
}

//Stripping
function strip_decode($text) {
	$text = stripslashes(htmlspecialchars_decode($text, ENT_QUOTES));
	return $text;
}

// Get theme url
function get_theme_path($echo=true) {
global $SITEURL;
	if ($echo) echo $SITEURL . 'theme/';
	else return $SITEURL . 'theme/';
}
// Get libf dir
function get_admin_path() {
	global $CTLIBF;
	return tsl(CTROOTP . $CTLIBF);
}
// Get root of libf dir
function get_root_path() {
  $pos = strrpos(dirname(__FILE__),DIRECTORY_SEPARATOR.'inc');
  $adm = substr(dirname(__FILE__), 0, $pos);
  $pos2 = strrpos($adm,DIRECTORY_SEPARATOR);
  return tsl(substr(__FILE__, 0, $pos2));
}
// Add trailing slash
function tsl($path) {
	if( substr($path, strlen($path) - 1) != '/' ) {
		$path .= '/';
	}
	return $path;
}
//--------------------------------------------------------------
function m_array_diff($tb1,$tb2) {//zwraca tablicę wartości z tb1, których nie ma w tb2.
if (!is_array($tb1) || !is_array($tb2)) return array();
$res=array();
	foreach ($tb1 as $v)
		if (!in_array($v,$tb2)) $res[]=$v;
	return $res;
}
function array_equal($a,$b) {//czy tabele są identyczne?
	sort($a);
	sort($b);
	if ($a == $b) return array(); //zwróć pustą tabelę
	else return array(1); //zwróć 1-elementową tabelę
}
//------------------------- Clean fun --------------------------
function removenl($str) {
	if (!is_array($str)) $str=array($str);
	foreach ($str as $k => $v) {
		$str[$k] = str_replace("\n"," ",$str[$k]);
		$str[$k] = str_replace("\r"," ",$str[$k]);
		$str[$k] = str_replace("  "," ",$str[$k]);
	}
	return $str;
	// preg_replace("/[\n\r]/","",$subject);
}

function rescape($str) {
	if (!($isa=is_array($str))) $str=array($str);
	foreach ($str as $k => $w) $str[$k] = get_magic_quotes_gpc() ? stripslashes($w) : $w;
	if (!$isa) return $str[0]; else return $str;
}

function fhtml($str,$hash=false) {
	if ($hash) $str=hhash($str,false);
	$kody1=array('{br}','{b}','{/b}','{i}','{/i}','{u}','{/u}','{sub}','{/sub}','{sup}','{/sup}','{pre}','{/pre}','{c}','{/c}','^+^');
	$kody2=array('<br />','<strong>','</strong>','<em>','</em>','<u>','</u>','<sub>','</sub>','<sup>','</sup>','<pre>','</pre>','<code>','</code>','#');
	return str_replace($kody1,$kody2,$str);
}

function hhash($str,$hide=true) {
	if (is_array($str)) {
		return array_map('hhash',$str);
	} else {
		$tb1=array('#');
		$tb2=array('^+^');
		if ($hide) return str_replace($tb1,$tb2,$str);
		else return str_replace($tb2,$tb1,$str);
	}
}

function hhtml($str,$hide=true) {
	if (!($isa=is_array($str))) $str=array($str);
	foreach ($str as $k => $w)
		if ($hide) $str[$k]=htmlspecialchars($w,ENT_QUOTES); else $str[$k]=htmlspecialchars_decode($w,ENT_QUOTES);
	if (!$isa) return $str[0]; else return $str;
}

function clrtxt($str,$hide=true) {
	if (!$isa=is_array($str)) $str=array($str);
	foreach ($str as $k => $v) {
		$tb1=array('#');
		$tb2=array('^+^');
		if (get_magic_quotes_gpc()) $v=stripslashes($v);
		$v=htmlspecialchars($v,ENT_QUOTES);
		if ($hide) $v=str_replace($tb1,$tb2,$v);
		$str[$k]=$v;
	}
	if (!$isa) return $str[0]; else return $str;
}
//------------------------OTHER FUNCTIONS-------------------------
function fData($d) {//20091201
	return substr($d,-2).'.'.substr($d,4,2).'.'.substr($d,0,4); 
}
function fGodz($g) {//203854
	return substr($g,0,2).':'.substr($g,2,2).':'.substr($g,-2);
}
function fCzas($cz,$co=1) {
	if ($co) return fData(substr($cz,0,8)).' '.fGodz(substr($cz,-6));
	else if ($co<0) return fData(substr($cz,0,8)); //data
	else return fGodz(substr($cz,-6)); //godz
}
function fCzodp($sec) {
	$m=60;
	$h=60*60;
	if ($sec>($m*60*4)) return 0;//czas rozwiązywania większy niż 4 godz.!
	$ret='';
	if ($sec<$m) { if ($sec<10) $ret='00:00:0'.$sec; else $ret='00:00:'.$sec; return $ret;}
	else if ($sec<$h) {
		$m=floor($sec/$m); $s=$sec%60;
	} else {
		$g=floor($sec/$h); $m=floor(($sec-$h*$g)/$m); $s=($sec-$h*$g)%60;
		$ret='0'.$g.':';
	}
	if ($m<10) $ret='00:0'.$m.':'; else $ret='00:'.$m.':';
	if ($s<10) $ret.='0'.$s; else $ret.=$s;
	return $ret;
}
function fCzdost($sec){
	if ($sec>0) return date('Y.m.d H:i:s',$sec);
	else return '';
}

/*
$proc - procentowa wartość poprawnych odpowiedzi
$tbsk - Tabela zawierająca progi i oceny
return ocena odpowiadająca progom
*/
function fOcena($proc,$tbsk) {//zwraca ocenę
	foreach ($tbsk['progi'] as $k => $prog) {
		if ($proc <= $prog) return $tbsk['oceny'][$k];
	}
	return 0;
}
/*
$tbsk - Tabela zawierająca progi i oceny
zwracamy ciąg zawierający oceny i progi
*/
function fSkala($tbsk) {
	$ret='';
	foreach ($tbsk['progi'] as $k => $prog) {
			$ret.=$tbsk['oceny'][$k].' <= '.($prog*100).'% ';
	}
	return substr($ret,0,-1);
}
//------------------------------------------------------------------------------
function GetDzis() { //--- zwraca bieżącą datę
	$d=date('w');
	switch ($d) {
		case 0: $d='Niedziela'; break;
		case 1: $d='Poniedziałek'; break;
		case 2: $d='Wtorek'; break;
		case 3: $d='Środa'; break;
		case 4: $d='Czwartek'; break;
		case 5: $d='Piątek'; break;
		case 6: $d='Sobota'; break;
		default: $d='Dziwny dzień';
	}
	$d2=$d.' '.date("d.m.Y");
	echo $d2;
}
//------------------------------------------------------------------------------
function delplik($tbdel,$pre='') {
global $code;
	$ile=0;
	if (!is_array($tbdel)) $tbdel=array($tbdel);
	foreach ($tbdel as $plik)
		if (file_exists($pre.$plik)) { if (@unlink($pre.$plik)) $ile++; else CMsg::eadd('Usuwanie: błąd usnięcia pliku '.$pre.$plik.'.');}
		else { CMsg::eadd('Usuwanie: plik '.$pre.$plik.' nie istnieje.');}
	return $ile;
}
//-----------------------------------------------------------
function redirect($url) {
global $code;
	if (!headers_sent($filename, $linenum)) {
		$host=$_SERVER['HTTP_HOST'];
		$uri=rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		//$code->addCode('<p>'."Location: http://$host$uri/$url".'</p>'); return;
		header("Location: http://$host$uri/$url");
	} else {
		echo "<html><head><title>Przekierowanie</title></head><body>";
		if ( !defined('CT_CTDEBUG') || (CT_CTDEBUG != TRUE) ) {
			echo '<script type="text/javascript">';
			echo 'window.location.href="'.$url.'";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
			echo '</noscript>';
		}
		echo 'Błąd: Nagłówki zostały już wysłane w '.$filename.' nr linii: '.$linenum."\n";
		printf("If your browser does not redirect you, click <a href=\"%s\">here</a>", $url);
		echo "</body></html>";
	}
	exit;
}
//-----------------------------------------------------------
function sendmail($to,$subject,$message) {

	if (defined('GSFROMEMAIL')){
		$fromemail = GSFROMEMAIL; 
	} else {
		$fromemail = 'noreply@centrum.vot.pl';
	}
	
	global $EMAIL;
	$headers  = "From: ".$fromemail."\r\n";
	$headers .= "Reply-To: ".$fromemail."\r\n";
	$headers .= "Return-Path: ".$fromemail."\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=UTF-8\r\n";
	
	if( mail($to,'=?UTF-8?B?'.base64_encode($subject).'?=',"$message",$headers) ) {
		return 'success';
	} else {
		return 'error';
	}
}
//-- klasy
class CBase {
	var $dane=array();//tablica danych
	var $tb=array();

	function __set($k,$v) {
		$this->dane[$k]=$v;
	}
	function __get($k) {
		if (array_key_exists($k, $this->dane))
			return $this->dane[$k];
		return null;
	}
	function sget($co,$v=null) {//ustawia, zwraca wartości z tablicy danych
		if (!is_null($v)) $this->dane[$co]=$v;
		else if (is_array($co)) {
			foreach ($co as $k => $v) $this->dane[$k]=$v;
		} else if (array_key_exists($co, $this->dane)) return $this->dane[$co];
		return null;
	}

	function vdmp($var,$e=false) {
		$str='';
		if (is_array($var)) {
			foreach ($var as $k  => $v)
				$str.='['.$k.'] => '.$v.'<br />';
		} else {
			ob_start();
			var_dump($var);
			$str=ob_get_clean();
		}
		if ($e) CMsg::kadd($str);
		else return $str;
	}
	function dane_p() {
		if (CT_CTDEBUG)
		foreach ($this->dane as $k => $v) {
			if (is_array($v)) {
				ob_start();
				print_r($v);
				$v=ob_get_contents();
				ob_clean();	
			}
			CMsg::kadd($k.' => '.$v);
		}
	}
}

class CMsg {
	private static $err=array();//błedy do wyświetlenia w stopce
	private static $kom=array();//komunikaty do wyświetlenia w stopce
	private static $ontop=array();//komunikaty do wyświetlenia na początku strony
	private static $onend=array();//komunikaty do wyświetlenia na końcu strony
	private static $dane=array();
	
	public static function eadd($msg) {
		if (strlen($msg)) {
			self::$err[]=trim($msg);
		}
		return true;
	}
	public static function kadd($msg) {
		if (strlen($msg)) {
			self::$kom[]=trim($msg);
		}
		return true;
	}
	public static function perr($e=false) {
		if (count(self::$err)<1) return;
		$tmp='<span class="error">'.implode('<br />',self::$err).'</span>';
		if ($e) { echo $tmp; echo '<br />'; return true; } else return $tmp;
	}
	public static function pkom($e=false) {
		if (count(self::$kom)<1) return;
		$tmp='<span class="kom">'.implode('<br />',self::$kom).'</span>';
		if ($e) { echo $tmp; return true; } else return $tmp;
	}
	public static function pall($e=false) {
		self::perr($e);
		self::pkom($e);
	}
	public static function addk($str,$ontop=1) {
		if ($ontop) self::$ontop[]=trim($str);
		else self::$onend[]=trim($str);
	}
	public static function getKom($ontop=1) {
		if ($ontop) {
			$ik=CValid::getCnt('ik'); //indeks komunikatu do wyświetlenia
			CValid::vInt($ik,0,10);
			$tbkom=array(
				'Zaktualizowano Twoje konto. Możesz dodać 5 testów i 5 grup.',
				'Założono konto! Możesz się zalogować.',
				'Zapisano pytania.'
			);
			if (array_key_exists($ik,$tbkom)) echo '<p class="info">'.$tbkom[$ik].'</p>';	
			foreach (self::$ontop as $str)
				echo '<p class="info">'.$str.'</p>';
		} else
			foreach (self::$onend as $str)
				echo '<p class="info">'.$str.'</p>';
	}
}

class CValid {
	private static $e='#';
	public static $acc=1;
	public static $acc2='';
	public static $acc3='';
	
	private function __construct() {
		//$this->err=new err();
	}
	
	public static function reDef(&$v,$estr,$def=null)	{//zwraca domyślną wartość w przypadku błędu walidacji
		if (!is_null($def)) {
			$v=$def;
			if (CT_CTDEBUG) CMsg::eadd('Błędna wartość: '.$estr);
		}
		return false;
	}
	
	public static function vStr(&$str,$min=1,$max=null,$def=null,$preg=null) {
		if (is_null($str)) return self::reDef($str,'null',$def);
		if (!is_string($str)) return self::reDef($str,'not string',$def);
		if (strlen($str)<$min) return self::reDef($str,'err min',$def);
		if ($max && strlen($str)>$max) return self::reDef($str,'err max',$def);
		if ($preg && !preg_match($preg,$str)) return self::reDef($str,'dont match',$def);
		return true;
	}

	public static function vStrA($tb,$min=1,$max=null) {
		if (empty($tb)) return true;
		foreach ($tb as $v)
			if (!self::vStr($v,$min,$max)) return false;
		return true;
	}

	public static function vInt(&$i,$min=null,$max=null,$def=null) {
		if (is_null($i)) return self::reDef($i,'null',$def);
		if (is_string($i) && !ctype_digit($i)) return self::reDef($i,'not digit',$def);
		$int=(int)$i;
		if (!is_int($int)) return self::reDef($i,'not int',$def);
		if(!empty($min) && ($int < $min)) return self::reDef($i,'err min',$def);
		if(!empty($max) && ($int > $max)) return self::reDef($i,'err max',$def);
		$i=$int;
		return true;
	}

	public static function vIntA(&$tb,$min=1,$max=null) {
		if (empty($tb)) return true;
		foreach ($tb as $k => $v)
			if (!self::vInt($v,$min,$max)) return false;
			else $tb[$k]=$v;
		return true;
	}

	public static function vEmail($em) {
		if (preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/i', $em)) return true;
		return false;
	}

	public static function vEmailA($tb) {
		if (empty($tb)) return false;
		foreach ($tb as $v)
			if (!$this->vEmail($v)) return false;
		return true;
	}

	public static function vOn(&$v,$def=0) {//checkboxy,radio itp.
		if (self::vStr($v,2,2)) { $v=1; return true; }
		$v=$def;
		return false;
	}

	private static function clean($s,$tol=false) {//czyszczenie zmiennych z formularzy
		if (empty($s)) return $s;
		if (!($isa=is_array($s))) $s=array($s);
		foreach ($s as $k => $v) {
			$s[$k] = trim($v);
			if ($tol) $s[$k]=strtolower($s[$k]);
		}
		return ($isa) ? $s : $s[0];
	}
//$z nazwa zmiennej w tabelach _POST _GET, $m typ POST lub GET
//$tbp = array() //tablica asocjacyjna parametrów:
//typ => i (integer),s(string),b(bool,on),a(array)
//min => wartość min, max => wartość max, def => wartość domyślna
//tol => czy zmienić na małe
	public static function getCnt($z,$m=null,$tab=false,$tol=false) {
		$n=null;
		if (is_null($m)) isset($_POST[$z]) ? $n = $_POST[$z] : (isset($_GET[$z]) ? $n = $_GET[$z] : $n);
		else if ($m=='g' && isset($_GET[$z])) $n = $_GET[$z];
		else if ($m=='p' && isset($_POST[$z])) $n = $_POST[$z];
		$n=self::clean($n,$tol);
		if (empty($n) && $tab) $n=array();
		return $n;
	}

	public static function getDane(&$co) {
		foreach ($co as $k => $v) {//nazwa zmiennej, kryteria poprawności, komunikat błędu
			$d=self::getCnt($v[0]);
			list($typ,$min,$max,$def)=explode(self::$e,$v[1]);
			if (empty($d)) {
				if ($typ=='a') $co[$k]=array();
				else $co[$k]=$def;
				continue;
			}
			$ret='';
			if ($typ=='i') {
				if (!self::vInt($d,$min,$max)) $ret=$v[2];
			} else if ($typ=='s') {
				if (!self::vStr($d,$min,$max)) $ret=$v[2];
			} else if ($typ=='b') {
				if (isset($d)) $d=1; else $d=$def;
			}
			if ($v[0]=='acc2') echo $ret;
			if (strpos($ret,'Błąd')===false) $co[$k]=$d; else return CMsg::eadd($ret);
		}
	}

	public static function getAcc() {
		$acc=self::getCnt('acc');
		$acc2=self::getCnt('acc2');
		$acc3=self::getCnt('acc3');
		//-- acc id strony --//
		if (empty($acc)) $acc=1;
		self::vInt($acc,0,24,1);
		self::$acc=$acc;
		//-- acc2 acc3 operacje dodatkowe --//
		self::vStr($acc2,1,4,'');
		self::vStr($acc3,1,4,'');
		self::$acc2=$acc2;
		self::$acc3=$acc3;
		return $acc;
	}
}
//--------------------------------------------------------------
class CHtml {
	//var $typyp=array(0,1,2,3,4,5); //typy pytań: 0=>text, 1=>radio, 2=>checkbox, 3=>user short text, 4=>textarea, 5=>dyktando
	private static $res=array('styles'=>array(),'scripts'=>array()); //adding styles from css theme's dir and/or scripts from js theme's dir
	private static $js='';
	private function __construct() {
	}

	public static function addRes($key,$v) {
		if (!in_array($v,self::$res[$key])) self::$res[$key][]=$v;
	}
	public static function addJS($str) {
		self::$js.=$str;
	}
	public static function getHeader($co='scripts',$echo=true) {
		$tab = array();
		if (array_key_exists($co, self::$res)) $tab = self::$res[$co];
		$inc='';
		foreach ($tab as $k => $v) {
			if ($co == 'scripts') $inc.='<script type="text/javascript" src="'.get_theme_path(false).'js/'.$v.'"><!-- //--></script>
';
			else $inc.='<link rel="stylesheet" href="'.get_theme_path(false).'css/'.$v.'" type="text/css" />
';
		}
		if ($echo) echo $inc; else return $inc;
	}
	public static function getJS($echo=true){
		if ($echo) echo self::$js."\r\n"; else return self::$js; 
	}

	public static function tbHead($tb,$tbcl=array()) {
		echo '<thead><tr>';
		foreach ($tb as $k=>$v) {
			if (isset($tbcl[$k])) echo '<th '.$tbcl[$k].'>'.$v.'</th>';
			else echo '<th>'.$v.'</th>';
		}
		echo '</tr></thead>';
		//return $kod;
	}

	public static function tbRow($td,$cltd=array(),$clrow='') {
		echo '<tr'.(empty($clrow) ? '' : ' class="'.$clrow.'"').'>';
		foreach ($td as $k => $v) {
			echo '<td'.( empty($cltd[$k]) ? '>' : ' class="'.$cltd[$k].'">').$v.'</td>';
		}
		echo '</tr>';
		//return $kod;
	}	

	//-zwraca tag select, korzysta z mk_opts
	public static function mk_sel($name,$id=null,$tab,$cl='selmod',$dis=false) {
		//print_r($tab);
		if (!is_null($id)) $id=' id="'.$id.'"'; else $id='';
		$kod='<select name="'.$name.'"'.$id.' class="'.$cl.'"';
		//if (is_numeric($onch)) $onch='setmod(\'mod'.$onch.'\');';
		//$kod.=' onchange="'.$onch.'"';
		if ($dis) $kod.=' disabled="disabled"';
		$kod.='>';
		$kod.=self::mk_opts($tab);
		$kod.='</select>';
		return $kod;
	}
	//zwraca listę options
	public static function mk_opts($tab) {//[0] -> tablica opcji [1] -> selectedIndex [2] -> value or index
		if (empty($tab)) return;
		if (isset($tab[1])) $idw=$tab[1]; else $idw=null;
		if (isset($tab[2])) $val=$tab[2]; else $val=false;
		//echo 'IDW'.$idw;
		$kod='';
		foreach ($tab[0] as $k => $v) {
			$kod.=($val ? '<option value="'.$v.'"' : '<option value="'.$k.'"');
			//Uwaga: było $k==$idw;
			if (isset($idw) && ($k===$idw || $v===$idw)) {
				$kod.=' selected="selected"';
			}
			$kod.='>'.$v.'</option>';
		}
		return $kod;
	}

	public static function sh_tab($tab,$tbop=array('kom'=>'Lista danych','bDel'=>0,'lDel'=>0,'bAdd'=>0,'paramId'=>0)) {
		echo '<h3>'.$tbop['kom'].'</h3>';
		array_unshift($tab->ktbh,'Lp.');
		echo '<form enctype="multipart/form-data" action="?acc='.CValid::$acc.'" method="post" name="'.$tab->tbid.'" id="'.$tab->tbid.'">
			<input type="hidden" name="acc3" value="'.CValid::$acc3.'" />';
		if (array_key_exists('paramId',$tbop) && $tbop['paramId']>0) echo '<input type="hidden" name="paramId" value="'.$tbop['paramId'].'" />';
		echo '<table id="'.$tab->tbid.'" class="tab tbaddrow">';
		if ($tbop['bDel'] || $tbop['lDel']) $tab->ktbh[]='U';
		self::tbHead($tab->ktbh);
		echo '<tbody>';
		//print_r($tab->tb); echo '<br />'; //print_r($tab->kshow); echo '<br />'; //print_r($tab->ksou);
		$i=0;
		$tbsel=array();
		foreach ($tab->kshow as $k2 => $v2) {
			if ($tab->ktype[$k2]=='select')
				$tbsel[$v2]=CHtml::mk_sel('tadd'.$k2.'[]',null,$tab->ksou[$v2],$tab->ksize[$k2]);
				//if (isset($tbsel[$v2])) echo $tbsel[$v2].'<br />';
		}

		foreach ($tab->tb as $k => $v) {
			$row=$cl=array();
			$clrow='tbrow'.(++$i%2);
			$wid=$v[$tab->idn];
			if (isset($tab->href)) $row[0]='<a href="?acc='.CValid::$acc.'&amp;acc2='.$wid.'&amp;acc3=grl">'.$i.'</a>'; //show id grup
			else $row[0]=$i;
			$row[0].='<input type="hidden" name="tk[]" value="'.$wid.'" /><input class="mod" type="hidden" name="mod[]" id="mod'.$wid.'" />';
			$cl[0]='tc nob';
			foreach ($tab->kshow as $k2 => $v2) {
				switch ($tab->ktype[$k2]) {
					case 'select':
						$tab->ksou[$v2][1]=(int)$v[$v2];
						$row[]=CHtml::mk_sel('tadd'.$k2.'[]',null,$tab->ksou[$v2],$tab->ksize[$k2]);
						$cl[]='';
					break;
					case 'text':
						$row[]='<input class="itext" type="text" name="tadd'.$k2.'[]" value="'.$v[$v2].'" size="'.$tab->ksize[$k2].'" />';
						$cl[]='';
					break;
					case 'checkbox':
						$row[]='<input class="icheck" type="checkbox" name="tadd'.$k2.'[]" value="'.$wid.'" '.($v[$v2] ? 'checked="checked"' : '').' />';
						$cl[]='tc';
					break;
					case 'img':
						$row[]=$v[$v2];
						$cl[]='tc';
					break;
					case 'href':
						$row[]=$v[$v2];
						$cl[]='';
					break;
					default:
						$row[]=$v[$v2];
						$cl[]='tc';
				}
			}
			if ($tbop['lDel']) $row[]='<a class="but" href="?acc='.CValid::$acc.'&amp;acc3=del&amp;wid='.$wid.'">Usuń</a>';
			if ($tbop['bDel']) $row[]='<input class="btnDel but" type="button" value="Usuń" />';
			self::tbRow($row,$cl,$clrow);
		}
		echo '</tbody>';
		echo '</table>';
		if ($tbop['bAdd']) echo '<input class="but btntab" id="btnAddRow" type="button" value="Dodaj" />';
		echo '<input class="cb but flr" type="submit" value="Zapisz" />';
		echo '</form>';
		foreach ($tbsel as $k => $v) {
			echo '<div class="tbsel" id="'.$k.'">'.$v.'</div>';
		}
	}
	
	public static function sav_tab($tab,$kom='Zapisywanie danych...') {
		if (CT_CTDEBUG) echo __FUNCTION__.', '.__FILE__.'<br />';
		//print_r($_POST); echo '<br />';
		$tadd=$tb=array();
		$tk=CValid::getCnt('tk');
		if (!CValid::vIntA($tk,0)) return CMsg::eadd('Błąd danych! (sav_tab)');
		$mod=CValid::getCnt('mod');
		$ile=count($tab->kshow);
		for ($i=0; $i<$ile; $i++) {
			if (isset($_POST['tadd'.$i])) $tb[]=clrtxt(CValid::getCnt('tadd'.$i,'p'));
		}
		//print_r($tb);return;
		$ile=count($tb);
		$ile2=@count($tb[0]);
		//echo $ile.'<br />';
		for ($i=0; $i<$ile2; $i++) {
			if (isset($tk[$i])) $id=$tk[$i]; else $id='NULL';
			$tadd[$i]=array($id);
			for ($j=0; $j<$ile; $j++) isset($tb[$j][$i]) ? $tadd[$i][]=$tb[$j][$i] : $tadd[$i][]='';//${"tadd$j"}[$i];
		}
		//print_r($tk);echo '<hr />';
		//print_r($tadd);echo '<hr />';
		//print_r($mod);echo '<hr />'; return;
		$tab->savTb($tk,$tadd,$mod);
		return true;
	}
	
	public static function sh_wyniki($uid) {//pokaż wyniki ucznia
		global $user;
		if (CT_CTDEBUG) echo __FUNCTION__.', '.__FILE__.'<br />';
		if (isset($_SESSION['sid'])) $acc=$_SESSION['sid']; else $acc=CValid::$acc;

		$wyniki = new wyniki(null,$uid);
		$wyniki->getTb2();
		if (CValid::$acc3 == 'wde') {
			$wyniki->remWyn(CValid::$acc2);
		}

		if ($wyniki->ilew < 1) {
			if (CValid::$acc == 24) echo 'Brak wyników.'; //wywołanie z getres.php
			else CMsg::kadd('Brak wyników.');
			return false;
		};
		$wyniki->getDane('tids','wid','tid');
		$testy=new testy(array_unique($wyniki->tids));
		$testy->explo[]='oceny';
		$testy->explo[]='progi';
		$testy->getTb2('SELECT testy.tid,testy.typ,testy.wer,testy.zakres,ord,ilep,skale.oceny,skale.progi,przedmioty.przedm FROM testy,skale,przedmioty WHERE testy.skid=skale.skid AND testy.przid=przedmioty.przid AND ');
		$wyniki->getOceny($testy->tb);
		$tbgr=CDBase::dbQuery('SELECT grupy.gid,grupa FROM grupy,gunp WHERE grupy.gid=gunp.gid AND gunp.uid='.$uid,PDO::FETCH_KEY_PAIR);
		if (is_array($tbgr)) $tblst=implode(', ',$tbgr); else $tblst='';
		$nazwisko=CDBase::dbQuery('SELECT nazwisko FROM users WHERE users.uid='.$uid,PDO::FETCH_COLUMN,true);//pobierz nazwisko ucznia
//print_r($testy->tb);echo '<hr />';
//print_r($wyniki->tb);return;
		echo '<div id="content">';
		echo '<h3>'.$nazwisko.'  <span class="tah8">['.$tblst.']</span></h3>';
		echo '<table class="tab sortable" id="tbsort">';
		CHtml::tbHead(array('Lp.','Przedmiot','Zakres','Data','[%][pkt/max]','Ocena','Grupa','Usuń','Szczegóły'),array('','','','','','','unsortable tc','unsortable tc'));
		echo('<tfoot><tr><td colspan="4">&nbsp;</td><td colspan="2" class="tc pb">Średnia: '.$wyniki->srednia.'</td><td colspan="2">&nbsp;</td></tr></tfoot>');
		$i=0;
		echo('<tbody>');
		
		foreach ($wyniki->tb as $wid => $v) {
			//var_dump($v['typ']);continue;
			$usun='';
			$grupa='Brak grupy';
			if ($user->kto == 'n' || $v['gid']<2) {//nauczyciel
				$usun='<a class="but" href="?acc='.$acc.'&amp;acc2='.$v['wid'].'&amp;acc3=wde&amp;idu='.$uid.'">Usuń</a>';
				if ($v['gid']) $grupa='<a href="?acc=11&amp;acc3=lwt&amp;idt='.$v['tid'].'&amp;idg='.$v['gid'].'">'.$tbgr[$v['gid']].'</a>'; 
			} else if ($v['gid']) $grupa=$tbgr[$v['gid']];//uczen
			if (!$v['typ'] && $v['ileok']>$v['mpkta']) {//zmieniono test, np. usunięto pytania
				if (!empty($v['mpkt'])) {
					$pro=$v['ileok']/$v['mpkt'];
					$v['pro']=round($pro*100,2);
					$v['oc']=fOcena($pro,array('oceny'=>$tbtesty[$v['tid']]['oceny'],'progi'=>$tbtesty[$v['tid']]['progi']));
					$v['mpkta']=$v['mpkt'];
				} else {
					$v['pro']=$v['mpkta']=0;
					$v['oc']='brak danych';
				}
			}
			CHtml::tbRow(array(
				++$i,
				$testy->tb[$v['tid']]['przedm'],
				$testy->tb[$v['tid']]['zakres'].($v['typ'] ? ' [A]' : ' [T]'),
				date("d.m.Y H:i:s",$v['datak']),
				$v['typ'] ? '&#8211;' : $v['pro'].' ['.$v['ileok'].'/'.$v['mpkta'].']',
				$v['oc'],
				$grupa,
				$usun,
				'<a class="but" href="?acc='.$acc.'&amp;acc2='.$v['wid'].'&amp;acc3=wsd&amp;idu='.$uid.'">Pokaż</a>'
			),array('tc','','','','tc','tc','tc','tc','tc'));
		}
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
	}

//pokaż szczegóły wyniku z testu danego użytkownika
//umożliwia też ocenę pytań otwartych
	public static function sh_wdetal($uid) {
	global $user;
	include_cl(array('testy','pytania'));
	if (CT_CTDEBUG) echo __FUNCTION__.', '.__FILE__.'<br />';
		$ido=CValid::getCnt('ido','g');
		if ($ido) {//usunięcie odwpowiedzi z tabeli wynpyt
			$ido=explode('*',$ido);
			foreach ($ido as $k => $v) $ido[$k]=array($v);
			CDBase::dbSetq('DELETE FROM wynpyt WHERE oid = ?',$ido);
			if (CDBase::dbExec()) echo '<p>Usunięto odpowiedź(i)!</p>';
		}
		$wid=CValid::$acc2;
		$wynik= new wyniki($wid);
		$wynik->getTb2();
		if (is_null($wynik->tid)) return CMsg::eadd('Brak wyniku!');
		$test= new testy($wynik->tid);
		$test->getTb2('SELECT testy.tid,testy.typ,testy.zakres,testy.open,przedmioty.przedm FROM testy,przedmioty WHERE testy.przid=przedmioty.przid AND ');
		if ($user->kto == 'u' && $wynik->gid > 1) {//test grupowy
			$isOpen=CDBase::dbQuery('SELECT open FROM testg WHERE tid='.$wynik->tid.' AND gid='.$wynik->gid.' LIMIT 1',PDO::FETCH_COLUMN,true);
			if (!$isOpen) return CMsg::kadd('Aby przeglądać wyniki szczegółowe, poproś autora o odblokowanie testu.');
		} else if (!$test->open && $user->kto == 'u') return CMsg::kadd('Aby przeglądać wyniki szczegółowe, poproś autora o odblokowanie testu.');
		$wynpyt= new wynpyt($wid,null,$test->typ);
		$wynpyt->getTb2();
//		var_dump($wynpyt->tb); return;
		$nazwisko=CDBase::dbQuery('SELECT nazwisko FROM users WHERE users.uid='.$uid,PDO::FETCH_COLUMN,true);
		$pytania=new pytania();
		$pytania->getTb2('SELECT pytania.pid,pyt,odpt,odp,typ,mpkt,txt FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$wynik->tid);
		$pytania->getDane('tbpids','pid','pid');
		$media = new mp(null,$pytania->tbpids);
		$media->idn='mid';
		$media->getTb2('SELECT mp.mid,pid,fname,opis FROM mp,media WHERE mp.mid=media.mid AND ');
		$media->getDane('pids','pid','pid');
		//CHtml::addRes('scripts','stupidtable.min.js');
		echo '<div id="content">';
		echo '<h3><a href="?acc='.CValid::$acc.'&amp;idu='.$uid.'&amp;acc3=wsh">'.$nazwisko.'</a>';
		if ($wynik->gid) echo ' (Grupa: <a href="?acc='.CValid::$acc.'&amp;acc3=lwt&amp;idt='.$wynik->tid.'&amp;idg='.$wynik->gid.'">'.$wynik->gid.'</a>)';
		echo '</h3>
				<table class="tbinfo"><tr><td><strong>Przedmiot:</strong></td><td>'.$test->przedm.'.</td></tr><tr><td><strong>Zakres:</strong></td><td>'.$test->zakres.' ['.($test->typ ? 'A' : 'T').'].</td></tr><tr><td><strong>Data:</strong></td><td>'.date("d.m.Y H:i:s",$wynik->datas).'.</td></tr></table><br />';

		if ($wynpyt->ilew == 0) {//brak odpowiedzi do testu
			$maxpkt=CDBase::dbQuery('SELECT sum (mpkt) FROM pytania,testp WHERE pytania.pid=testp.pid AND testp.tid='.$wynik->tid,PDO::FETCH_COLUMN);
			echo '<p>Brak odpowiedzi do tego wyniku! Zapisana ilość punktów: '.$wynik->ileok.' / '.current($maxpkt).'.</p>';
			echo '<p><a href="?acc='.CValid::$acc.'&amp;acc2='.$wid.'&amp;acc3=wde&amp;idu='.$uid.'" class="but">Usuń</a></p>';
			return;
		}

		echo '<table id="tbsort" class="tbsort" cellpadding="2" cellspacing="0">';
		if ($test->typ)
			echo '<tr><th>Lp.</th><th>Pytanie</th><th>Odpowiedź</th><th>U</th></tr>'; //ankieta
		else
			echo '<tr><th>Lp.</th><th>Pytanie</th><th width="25%">Odpowiedź</th><th width="25%">Poprawna odpowiedź</th><th>Pkt</th><th>U</th></tr>'; //test
		echo '<tbody>';

		$tbupd=null;//tablica wyników oid -> iledb do zaktualizowania ilości dobrych punktów
		$i=0;
		$ileok=0;//ilość poprawnych odpowiedzi
		$maxall=0;//maks. pkt za test
//		print_r($wynpyt->tb); return;
		foreach ($wynpyt->tb as $oid => $wyn) {
			if (empty($wyn['pid'])) { Cmsg::eadd('Brak pytania!'); continue; } //jesli pytanie zostało usunięte
			if (array_key_exists($wyn['pid'],$pytania->tb)) $p=$pytania->tb[$wyn['pid']];
			else {//z testu usunięto pytanie?
				$tbnopyt[]=$oid;
				$p=array('pid'=>$wyn['pid'],'pyt'=>'<span class="error" style="background:#ccc; font-weight:bold;">Brak pytania w teście</span>','odpt'=>array(),'odp'=>array(),'typ'=>-1,'mpkt'=>0);
			}
			$delwid='<a href="?acc='.CValid::$acc.'&amp;acc2='.$wid.'&amp;acc3=wsd&amp;idu='.$uid.'&amp;ido='.$oid.'" class="but">U</a>';
		if ($test->typ) { //ankieta
			$odpu='';
			switch($p['typ']){
				case 0:
				case 6:
					$odpu=implode(', ',$wyn['odp']);
				break;
				case 1:
				case 2:
					foreach ($wyn['odp'] as $o) if (isset($p['odpt'][$o])) $odpu.=fhtml($p['odpt'][$o],true).', '; else $odpu.='brak odpowiedzi, ';
					$odpu=substr($odpu,0,-2);
				break;
				case 3:
				case 4:
					$odpu=implode('<hr />',$wyn['odp']);
				break;
				case 5:
					$odpu=implode(', ',$wyn['odp']);
				break;
				default:
					;
			}
		} else { //test
			$tbwyn=array();
			$iledb=$ilezle=0;
			$styl='';
			$odpok='';//poprawne odpowiedzi (tekstowo)
			$odpu='';//odpowiedzi użytkownika (tekstowo)
			$maxall+=$p['mpkt'];
			$maxpkt=$p['mpkt'];
			switch($p['typ']) {
				case 0://text
				case 6://obrazek - odp
					$tbwyn=array_diff_assoc($p['odp'],$wyn['odp']);
					$ilezle=count($tbwyn);
					$iledb=$maxpkt-$ilezle;
					if ($iledb != $wyn['wyn']) $tbupd[]=array($iledb,$oid);
					$odpok=implode(', ',$p['odp']);
					$odpu=implode(', ',$wyn['odp']);
				break;
				case 1://radio
				case 2://checkbox
					if ($p['typ'] == 1) $tbwyn=array_diff($p['odp'],$wyn['odp']);
					else $tbwyn=array_equal($p['odp'],$wyn['odp']);
					$ilezle=count($tbwyn);
					if ($ilezle==0) $iledb=$maxpkt;
					if ($iledb != $wyn['wyn']) $tbupd[]=array($iledb,$oid);
					foreach ($p['odp'] as $o) $odpok.='<li>'.fhtml($p['odpt'][$o],true).'</li>';
					foreach ($wyn['odp'] as $o) if (isset($p['odpt'][$o])) $odpu.='<li>'.fhtml($p['odpt'][$o],true).'</li>'; else $odpu.='brak odpowiedzi, ';
					$odpok='<ul class="shwdetal">'.$odpok.'</ul>';//substr($odpok,0,-2);
					$odpu='<ul class="shwdetal">'.$odpu.'</ul>';//substr($odpu,0,-2);
				break;
				case 3:
				case 4:
					$ilezle=0;
					if ($wyn['wyn']>-1) {//czyli oceniono pytanie otwarte
						$ilezle=$maxpkt-$wyn['wyn'];
						$iledb=$maxpkt-$ilezle;
					}
					if ($user->kto == 'n') {//pytania typu otwartego, muszą zostać ocenione przez autora testu
						$odpok='
							<input type="text" value="'.(($wyn['wyn'] == '') ? ('0-'.$maxpkt.' pkt.') : $wyn['wyn']).'" style="width:60%" />&nbsp;
							<input class="ocen but" type="button" value="Oceń" id="mpkt'.$maxpkt.'" />
							<input type="hidden" value="'.implode('#',array($wyn['rowid'],$wid,$uid,$wynik->tid,$wynik->datas)).'" />';
					} else {//uczeń
						$odpok=($wyn['wyn'] == '') ? '-- nieoceniono --' : '-- pytanie otwarte --';
					}
					$odpu=implode('<hr />',$wyn['odp']);				
				break;
				case 5://dyktanda
					//$maxpkt=count($p['odp']);
					$tbwyn=array_diff($p['odp'],$wyn['odp']);
					$ilezle=count($tbwyn);
					$iledb=$maxpkt-$ilezle;
					if ($iledb != $wyn['wyn']) $tbupd[]=array($iledb,$oid);
					$odpok=implode(', ',$p['odp']);
					$odpu=implode(', ',$wyn['odp']);
				break;
				default:
					;
					
			}
			$ileok+=$iledb;
		}
		
			$padd='';
			if (!empty($p['txt'])) $padd='<br /><input type="button" class="but showdlg" value="txt" title="'.$p['txt'].'" /><div class="hideme">'.$p['txt'].'</div>';
			if (array_key_exists($wyn['pid'],$media->pids)) {//obrazki
				$padd.='<br />
						<input type="button" class="but showdlg" value="img" />
						<div class="hideme">';
				foreach ($media->tb as $v)
					if ($v['pid']==$wyn['pid'])
						$padd.='<img src="'.CT_IMGS.$v['fname'].'" width="100" style="margin-left:1em;" />';
				$padd.='</div>';
			}
			if ($test->typ) { //ankieta
				CHtml::tbRow(array(++$i, nl2br(fhtml($p['pyt'])), hhash($odpu,false),$delwid));
			} else { //test
				
				if ($ilezle>0) { if ($ilezle<$maxpkt) $styl='halfbad'; else $styl='bad'; }
				CHtml::tbRow(array(++$i, ' ['.$maxpkt.'] '.nl2br(fhtml($p['pyt'])).$padd, hhash($odpu,false), hhash($odpok,false), $iledb, $delwid),array($styl));
			}
		}
		echo '</tbody></table>';

		if (!$test->typ) {//test
			echo '<p>Ilość zapisanych/wyliczonych/maksimum punktów: '.$wynik->ileok.' / '.$ileok.' / '.$maxall.'.</p>';
			if (!empty($tbnopyt)) {
				echo '<p>Wyniki zawierają odpowiedzi na pytania, których w teście już nie ma. Zalecea się usunięcie tych odpowiedzi: <a href="?acc='.CValid::$acc.'&acc2='.CValid::$acc2.'&acc3=wsd&idu='.$uid.'&amp;ido='.implode('*',$tbnopyt).'" class="but">Usuń odpowiedzi</a></p>';
			} else if (!is_null($tbupd)) {//zaktualizowanie ilości dobrych odpowiedzi w pytaniach typu 0,1,2,5
				CDBase::dbSetq('UPDATE wynpyt SET wyn = ? WHERE oid = ?',$tbupd);
				CDBase::dbSetq('UPDATE wyniki SET ileok = ? WHERE wid = ?',array(array($ileok,$wid)));
				if (CDBase::dbExec()) echo('<p>Zaktualizowano wyniki. Przeładuj stronę [F5]!</p>');
				else echo('<p>Błąd aktualizacji wyników.</p>');
			} else if ($user->kto=='n') {
				if ($ileok != $wynik->ileok) {
					echo ('
					<input class="ocen but" type="button" value="Aktualizuj wynik" />
					<input type="hidden" value="'.implode('#',array($ileok,$wid)).'" />
					');
				}
			}
		}
		echo '</div>';//koniec #content
	}

//generowanie linków do widoków
	public static function mk_link($link,$params) {
		switch($link) {
			case 'grupa': //$params musi zawierać gid
				return '?acc=10&acc2='.$params.'&acc3=grl';
			break;
		}	
	}
}
//--------------------------------------------------------------
?>
